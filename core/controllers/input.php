<?php

/*
*  Input
*
*  @description: All the functionality for adding fields to a page / post
*  @since 3.2.6
*  @created: 23/06/12
*/

class acf_input
{

	var $parent,
		$data;


	/*
	*  __construct
	*
	*  @description:
	*  @since 3.1.8
	*  @created: 23/06/12
	*/

	function __construct($parent)
	{

		// vars
		$this->parent = $parent;


		// actions
		add_action('admin_print_scripts', array($this,'admin_print_scripts'));
		add_action('admin_print_styles', array($this,'admin_print_styles'));
		add_action('admin_head', array($this,'admin_head'));


		// save
		add_action('save_post', array($this, 'save_post'), 10, 1);


		// custom actions (added in 3.1.8)
		add_action('acf_head-input', array($this, 'acf_head_input'));
		add_action('acf_print_scripts-input', array($this, 'acf_print_scripts_input'));
		add_action('acf_print_styles-input', array($this, 'acf_print_styles_input'));
		add_action('wp_restore_post_revision', array($this, 'wp_restore_post_revision'), 10, 2 );
		add_filter('_wp_post_revision_fields', array($this, 'wp_post_revision_fields') );

		// ajax
		add_action('wp_ajax_acf_input', array($this, 'ajax_acf_input'));
		add_action('wp_ajax_get_input_style', array($this, 'ajax_get_input_style'));


		// edit attachment hooks (used by image / file / gallery)
		add_action('admin_head-media.php', array($this, 'admin_head_media'));
		add_action('admin_head-upload.php', array($this, 'admin_head_upload'));

	}


	/*
	*  validate_page
	*
	*  @description: returns true | false. Used to stop a function from continuing
	*  @since 3.2.6
	*  @created: 23/06/12
	*/

	function validate_page()
	{
		// global
		global $pagenow, $typenow;


		// vars
		$return = false;


		// validate page
		if( in_array( $pagenow, array('post.php', 'post-new.php') ) )
		{

			// validate post type
			global $typenow;

			if( $typenow != "acf" )
			{
				$return = true;
			}

		}


		// validate page (Shopp)
		if( $pagenow == "admin.php" && isset( $_GET['page'] ) && $_GET['page'] == "shopp-products" && isset( $_GET['id'] ) )
		{
			$return = true;
		}


		// return
		return $return;
	}


	/*
	*  admin_print_scripts
	*
	*  @description:
	*  @since 3.1.8
	*  @created: 23/06/12
	*/

	function admin_print_scripts()
	{
		// validate page
		if( ! $this->validate_page() ) return;


		do_action('acf_print_scripts-input');


		// only "edit post" input pages need the ajax
		wp_enqueue_script(array(
			'acf-input-ajax',
		));
	}


	/*
	*  admin_print_styles
	*
	*  @description:
	*  @since 3.1.8
	*  @created: 23/06/12
	*/

	function admin_print_styles()
	{
		// validate page
		if( ! $this->validate_page() ) return;

		do_action('acf_print_styles-input');
	}


	/*
	*  admin_head
	*
	*  @description:
	*  @since 3.1.8
	*  @created: 23/06/12
	*/

	function admin_head()
	{
		// validate page
		if( ! $this->validate_page() ) return;


		// globals
		global $post, $pagenow, $typenow;


		// shopp
		if( $pagenow == "admin.php" && isset( $_GET['page'] ) && $_GET['page'] == "shopp-products" && isset( $_GET['id'] ) )
		{
			$typenow = "shopp_product";
		}


		// vars
		$post_id = $post ? $post->ID : 0;


		// get field groups
		$filter = array(
			'post_id' => $post_id,
			'post_type' => $typenow
		);
		$metabox_ids = array();
		$metabox_ids = apply_filters( 'acf/location/match_field_groups', $metabox_ids, $filter );


		// get style of first field group
		$style = '';
		if( isset($metabox_ids[0]) )
		{
			$style = $this->get_input_style( $metabox_ids[0] );
		}


		// Style
		echo '<style type="text/css" id="acf_style" >' . $style . '</style>';


		// add user js + css
		do_action('acf_head-input');


		// get acf's
		$acfs = apply_filters('acf/get_field_groups', false);


		if($acfs)
		{
			foreach($acfs as $acf)
			{
				// hide / show
				$show = in_array($acf['id'], $metabox_ids) ? 1 : 0;
				$priority = 'high';
				if( $acf['options']['position'] == 'side' )
				{
					$priority = 'core';
				}


				// add meta box
				add_meta_box(
					'acf_' . $acf['id'],
					$acf['title'],
					array($this, 'meta_box_input'),
					$typenow,
					$acf['options']['position'],
					$priority,
					array( 'fields' => $acf['fields'], 'options' => $acf['options'], 'show' => $show, 'post_id' => $post->ID )
				);

			}
			// foreach($acfs as $acf)
		}
		// if($acfs)
	}


	/*
	*  get_input_style
	*
	*  @description: called by admin_head to generate acf css style (hide other metaboxes)
	*  @since 2.0.5
	*  @created: 23/06/12
	*/

	function get_input_style($acf_id = false)
	{
		// vars
		$acfs = apply_filters('acf/get_field_groups', false);
		$html = "";

		// find acf
		if($acfs)
		{
			foreach($acfs as $acf)
			{
				if($acf['id'] != $acf_id) continue;


				// add style to html
				if( in_array('the_content',$acf['options']['hide_on_screen']) )
				{
					$html .= '#postdivrich {display: none;} ';
				}
				if( in_array('excerpt',$acf['options']['hide_on_screen']) )
				{
					$html .= '#postexcerpt, #screen-meta label[for=postexcerpt-hide] {display: none;} ';
				}
				if( in_array('custom_fields',$acf['options']['hide_on_screen']) )
				{
					$html .= '#postcustom, #screen-meta label[for=postcustom-hide] { display: none; } ';
				}
				if( in_array('discussion',$acf['options']['hide_on_screen']) )
				{
					$html .= '#commentstatusdiv, #screen-meta label[for=commentstatusdiv-hide] {display: none;} ';
				}
				if( in_array('comments',$acf['options']['hide_on_screen']) )
				{
					$html .= '#commentsdiv, #screen-meta label[for=commentsdiv-hide] {display: none;} ';
				}
				if( in_array('slug',$acf['options']['hide_on_screen']) )
				{
					$html .= '#slugdiv, #screen-meta label[for=slugdiv-hide] {display: none;} ';
				}
				if( in_array('author',$acf['options']['hide_on_screen']) )
				{
					$html .= '#authordiv, #screen-meta label[for=authordiv-hide] {display: none;} ';
				}
				if( in_array('format',$acf['options']['hide_on_screen']) )
				{
					$html .= '#formatdiv, #screen-meta label[for=formatdiv-hide] {display: none;} ';
				}
				if( in_array('featured_image',$acf['options']['hide_on_screen']) )
				{
					$html .= '#postimagediv, #screen-meta label[for=postimagediv-hide] {display: none;} ';
				}
				if( in_array('revisions',$acf['options']['hide_on_screen']) )
				{
					$html .= '#revisionsdiv, #screen-meta label[for=revisionsdiv-hide] {display: none;} ';
				}
				if( in_array('categories',$acf['options']['hide_on_screen']) )
				{
					$html .= '#categorydiv, #screen-meta label[for=categorydiv-hide] {display: none;} ';
				}
				if( in_array('tags',$acf['options']['hide_on_screen']) )
				{
					$html .= '#tagsdiv-post_tag, #screen-meta label[for=tagsdiv-post_tag-hide] {display: none;} ';
				}
				if( in_array('send-trackbacks',$acf['options']['hide_on_screen']) )
				{
					$html .= '#trackbacksdiv, #screen-meta label[for=trackbacksdiv-hide] {display: none;} ';
				}


				break;

			}
			// foreach($acfs as $acf)
		}
		//if($acfs)

		return $html;
	}


	/*
	*  the_input_style
	*
	*  @description: called by input-actions.js to hide / show other metaboxes
	*  @since 2.0.5
	*  @created: 23/06/12
	*/

	function ajax_get_input_style()
	{
		// overrides
		if(isset($_POST['acf_id']))
		{
			echo $this->get_input_style($_POST['acf_id']);
		}

		die;
	}


	/*
	*  meta_box_input
	*
	*  @description:
	*  @since 1.0.0
	*  @created: 23/06/12
	*/

	function meta_box_input($post, $args)
	{
		// vars
		$options = array(
			'fields' => array(),
			'options' => array(
				'layout'	=>	'default'
			),
			'show' => 0,
			'post_id' => 0,
		);
		$options = array_merge( $options, $args['args'] );


		// needs fields
		if( $options['fields'] )
		{
			echo '<input type="hidden" name="save_input" value="true" />';
			echo '<div class="options" data-layout="' . $options['options']['layout'] . '" data-show="' . $options['show'] . '" style="display:none"></div>';

			if( $options['show'] )
			{
				$this->parent->render_fields_for_input( $options['fields'], $options['post_id'] );
			}
			else
			{
				echo '<div class="acf-replace-with-fields"><div class="acf-loading"></div></div>';
			}

		}
	}


	/*
	*  ajax_acf_input
	*
	*  @description:
	*  @since 3.1.6
	*  @created: 23/06/12
	*/

	function ajax_acf_input()
	{

		// defaults
		$defaults = array(
			'acf_id' => null,
			'post_id' => null,
		);

		// load post options
		$options = array_merge($defaults, $_POST);

		// required
		if(!$options['acf_id'] || !$options['post_id'])
		{
			echo "";
			die();
		}

		// get acfs
		$acfs = apply_filters('acf/get_field_groups', false);
		if( $acfs )
		{
			foreach( $acfs as $acf )
			{
				if( $acf['id'] == $options['acf_id'] )
				{
					$this->parent->render_fields_for_input( $acf['fields'], $options['post_id']);

					break;
				}
			}
		}

		die();

	}


	/*
	*  save_post
	*
	*  @description: Saves the field / location / option data for a field group
	*  @since 1.0.0
	*  @created: 23/06/12
	*/

	function save_post($post_id)
	{

		// do not save if this is an auto save routine
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;


		// only for save acf
		if( ! isset($_POST['save_input']) || $_POST['save_input'] != 'true')
		{
			return $post_id;
		}


		// Save revision (copy and paste of current metadata. ie: what it was)
		$parent_id = wp_is_post_revision( $post_id );
		if( $parent_id )
		{
			$this->save_post_revision( $parent_id, $post_id );
        }
        else
        {
	        do_action('acf_save_post', $post_id);
        }

	}


	/*
	*  save_post_revision
	*
	*  @description: simple copy and paste of fields
	*  @since 3.4.4
	*  @created: 4/09/12
	*/

	function save_post_revision( $parent_id, $revision_id )
	{

		// load from post
		if( !isset($_POST['fields']) )
		{
			return false;
		}


		// field data was posted. Find all values (not references) and copy / paste them over.

		global $wpdb;


		// get field from postmeta
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key NOT LIKE %s",
			$parent_id,
			'\_%'
		), ARRAY_A);


		if( $rows )
		{
			foreach( $rows as $row )
			{
				$wpdb->insert(
					$wpdb->postmeta,
					array(
						'post_id' => $revision_id,
						'meta_key' => $row['meta_key'],
						'meta_value' => $row['meta_value']
					)
				);
			}
		}

		return true;
	}



	/*--------------------------------------------------------------------------------------
	*
	*	acf_head_input
	*
	*	This is fired from an action: acf_head-input
	*
	*	@author Elliot Condon
	*	@since 3.0.6
	*
	*-------------------------------------------------------------------------------------*/

	function acf_head_input()
	{
		// global
		global $wp_version, $post;


		// vars
		$toolbars = apply_filters( 'acf/fields/wysiwyg/toolbars', array() );
		$post_id = 0;
		if( $post )
		{
			$post_id = $post->ID;
		}

		?>
<script type="text/javascript">

// vars
acf.post_id = <?php echo $post_id; ?>;
acf.nonce = "<?php echo wp_create_nonce( 'acf_nonce' ); ?>";
acf.admin_url = "<?php echo admin_url(); ?>";
acf.wp_version = "<?php echo $wp_version; ?>";


// text
acf.validation.text.error = "<?php _e("Validation Failed. One or more fields below are required.",'acf'); ?>";

acf.fields.relationship.max = "<?php _e("Maximum values reached ( {max} values )",'acf'); ?>";

acf.fields.image.text.title_add = "Select Image";
acf.fields.image.text.title_edit = "Edit Image";
acf.fields.image.text.button_add = "Select Image";

acf.fields.file.text.title_add = "Select File";
acf.fields.file.text.title_edit = "Edit File";
acf.fields.file.text.button_add = "Select File";

acf.fields.gallery.title_add = "<?php _e("Add Image to Gallery",'acf'); ?>";
acf.fields.gallery.title_edit = "<?php _e("Edit Image",'acf'); ?>";


// WYSIWYG
<?php

if( is_array($toolbars) ):
	foreach( $toolbars as $label => $rows ):
		$name = sanitize_title( $label );
		$name = str_replace('-', '_', $name);
	?>
acf.fields.wysiwyg.toolbars.<?php echo $name; ?> = {};
		<?php if( is_array($rows) ):
			foreach( $rows as $k => $v ): ?>
acf.fields.wysiwyg.toolbars.<?php echo $name; ?>.theme_advanced_buttons<?php echo $k; ?> = '<?php echo implode(',', $v); ?>';
			<?php endforeach;
		endif;
	endforeach;
endif;

?>
</script>
		<?php


		foreach($this->parent->fields as $field)
		{
			$field->admin_head();
		}
	}


	/*--------------------------------------------------------------------------------------
	*
	*	acf_print_scripts
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	*
	*-------------------------------------------------------------------------------------*/

	function acf_print_scripts_input()
	{
		wp_enqueue_script(array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-tabs',
			'jquery-ui-sortable',
			'farbtastic',
			'thickbox',
			'media-upload',
			'acf-input',
			'acf-datepicker',
		));


		foreach($this->parent->fields as $field)
		{
			$field->admin_print_scripts();
		}


		// 3.5 media gallery
		if( function_exists('wp_enqueue_media') && !did_action( 'wp_enqueue_media' ))
		{
			wp_enqueue_media();
		}
	}


	/*--------------------------------------------------------------------------------------
	*
	*	acf_print_styles
	*
	*	@author Elliot Condon
	*	@since 3.1.8
	*
	*-------------------------------------------------------------------------------------*/

	function acf_print_styles_input()
	{
		wp_enqueue_style(array(
			'thickbox',
			'farbtastic',
			'acf-global',
			'acf-input',
			'acf-datepicker',
		));

		foreach($this->parent->fields as $field)
		{
			$field->admin_print_styles();
		}
	}


	/*
	*  admin_head_upload
	*
	*  @description:
	*  @since 3.2.6
	*  @created: 3/07/12
	*/

	function admin_head_upload()
	{
		// vars
		$defaults = array(
			'acf_action'	=>	null,
			'acf_field'		=>	'',
		);

		$options = array_merge($defaults, wp_parse_args( wp_get_referer() ));


		// validate
		if( $options['acf_action'] != 'edit_attachment')
		{
			return false;
		}


		// call the apropriate field action
		do_action('acf_head-update_attachment-' . $options['acf_field']);

		?>
<script type="text/javascript">

	// remove tb
	self.parent.tb_remove();

</script>
</head>
<body>

</body>
</html
		<?php

		die;
	}


	/*
	*  admin_head_media
	*
	*  @description:
	*  @since 3.2.6
	*  @created: 3/07/12
	*/

	function admin_head_media()
	{

		// vars
		$defaults = array(
			'acf_action'	=>	null,
			'acf_field'		=>	'',
		);

		$options = array_merge($defaults, $_GET);


		// validate
		if( $options['acf_action'] != 'edit_attachment')
		{
			return false;
		}

		?>
<style type="text/css">
#wpadminbar,
#adminmenuback,
#adminmenuwrap,
#footer,
#wpfooter,
#media-single-form > .submit:first-child,
#media-single-form td.savesend,
.add-new-h2 {
	display: none;
}

#wpcontent {
	margin-left: 0px !important;
}

.wrap {
	margin: 20px 15px;
}

html.wp-toolbar {
    padding-top: 0px;
}
</style>
<script type="text/javascript">
(function($){

	$(document).ready( function(){

		$('#media-single-form').append('<input type="hidden" name="acf_action" value="<?php echo $options['acf_action']; ?>" />');
		$('#media-single-form').append('<input type="hidden" name="acf_field" value="<?php echo $options['acf_field']; ?>" />');

	});

})(jQuery);
</script>
		<?php

		do_action('acf_head-edit_attachment');
	}


	/*
	*  wp_restore_post_revision
	*
	*  @description:
	*  @since 3.4.4
	*  @created: 4/09/12
	*/

	function wp_restore_post_revision( $parent_id, $revision_id )
	{
		global $wpdb;


		// get field from postmeta
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key NOT LIKE %s",
			$revision_id,
			'\_%'
		), ARRAY_A);


		if( $rows )
		{
			foreach( $rows as $row )
			{
				update_post_meta( $parent_id, $row['meta_key'], $row['meta_value'] );
			}
		}

	}


	/*
	*  wp_post_revision_fields
	*
	*  @description:
	*  @since 3.4.4
	*  @created: 4/09/12
	*/

	function wp_post_revision_fields( $fields ) {

		global $post, $wpdb, $revision, $left_revision, $right_revision, $pagenow;


		if( $pagenow != "revision.php" )
		{
			return $fields;
		}


		// get field from postmeta
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key NOT LIKE %s",
			$post->ID,
			'\_%'
		), ARRAY_A);


		if( $rows )
		{
			foreach( $rows as $row )
			{
				$fields[ $row['meta_key'] ] =  ucwords( str_replace('_', ' ', $row['meta_key']) );


				// left vs right
				if( isset($_GET['left']) && isset($_GET['right']) )
				{
					$left_revision->$row['meta_key'] = get_metadata( 'post', $_GET['left'], $row['meta_key'], true );
					$right_revision->$row['meta_key'] = get_metadata( 'post', $_GET['right'], $row['meta_key'], true );
				}
				else
				{
					$revision->$row['meta_key'] = get_metadata( 'post', $revision->ID, $row['meta_key'], true );
				}

			}
		}


		return $fields;

	}




}

?>