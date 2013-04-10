<?php 

/*
*  Input
*
*  @description: controller for adding field HTML to edit screens
*  @since: 3.6
*  @created: 25/01/13
*/

class acf_input
{
	
	var $action;
	
	
	/*
	*  __construct
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function __construct()
	{
		// actions
		add_action('admin_enqueue_scripts', array($this,'admin_enqueue_scripts'));
		
		
		// save
		add_action('save_post', array($this, 'save_post'), 10, 1);
		
		
		// actions
		add_action('acf/input/admin_head', array($this, 'input_admin_head'));
		add_action('acf/input/admin_enqueue_scripts', array($this, 'input_admin_enqueue_scripts'));
		
		
		add_action('wp_restore_post_revision', array($this, 'wp_restore_post_revision'), 10, 2 );
		
		
		// filters
		add_filter('_wp_post_revision_fields', array($this, 'wp_post_revision_fields') );
		
		
		// ajax acf/update_field_groups
		add_action('wp_ajax_acf/input/render_fields', array($this, 'ajax_render_fields'));
		add_action('wp_ajax_acf/input/get_style', array($this, 'ajax_get_style'));
		
		
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
	*  admin_enqueue_scripts
	*
	*  @description: run after post query but before any admin script / head actions. A good place to register all actions.
	*  @since: 3.6
	*  @created: 26/01/13
	*/
	
	function admin_enqueue_scripts()
	{
		// validate page
		if( ! $this->validate_page() ){ return; }

		
		// only "edit post" input pages need the ajax
		wp_enqueue_script(array(
			'acf-input-ajax',	
		));
		
		
		// actions
		do_action('acf/input/admin_enqueue_scripts');
		add_action('admin_head', array($this,'admin_head'));
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
			$style = $this->get_style( $metabox_ids[0] );
		}
		
		
		// Style
		echo '<style type="text/css" id="acf_style" >' . $style . '</style>';
		echo '<style type="text/css">.acf_postbox, .postbox[id*="acf_"] { display: none; }</style>';
		
		
		// add user js + css
		do_action('acf/input/admin_head');
		
		
		// get field groups
		$acfs = apply_filters('acf/get_field_groups', array());
		
		
		if( $acfs )
		{
			foreach( $acfs as $acf )
			{
				// load options
				$acf['options'] = apply_filters('acf/field_group/get_options', array(), $acf['id']);
				
				
				// vars
				$show = in_array( $acf['id'], $metabox_ids ) ? 1 : 0;
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
					array( 'field_group' => $acf, 'show' => $show, 'post_id' => $post_id )
				);
				
			}
			// foreach($acfs as $acf)
		}
		// if($acfs)
	}
	
	
	/*
	*  get_style
	*
	*  @description: called by admin_head to generate acf css style (hide other metaboxes)
	*  @since 2.0.5
	*  @created: 23/06/12
	*/

	function get_style( $acf_id )
	{
		// vars
		$options = apply_filters('acf/field_group/get_options', array(), $acf_id);
		$html = '';
		
		
		// add style to html 
		if( in_array('the_content',$options['hide_on_screen']) )
		{
			$html .= '#postdivrich {display: none;} ';
		}
		if( in_array('excerpt',$options['hide_on_screen']) )
		{
			$html .= '#postexcerpt, #screen-meta label[for=postexcerpt-hide] {display: none;} ';
		}
		if( in_array('custom_fields',$options['hide_on_screen']) )
		{
			$html .= '#postcustom, #screen-meta label[for=postcustom-hide] { display: none; } ';
		}
		if( in_array('discussion',$options['hide_on_screen']) )
		{
			$html .= '#commentstatusdiv, #screen-meta label[for=commentstatusdiv-hide] {display: none;} ';
		}
		if( in_array('comments',$options['hide_on_screen']) )
		{
			$html .= '#commentsdiv, #screen-meta label[for=commentsdiv-hide] {display: none;} ';
		}
		if( in_array('slug',$options['hide_on_screen']) )
		{
			$html .= '#slugdiv, #screen-meta label[for=slugdiv-hide] {display: none;} ';
		}
		if( in_array('author',$options['hide_on_screen']) )
		{
			$html .= '#authordiv, #screen-meta label[for=authordiv-hide] {display: none;} ';
		}
		if( in_array('format',$options['hide_on_screen']) )
		{
			$html .= '#formatdiv, #screen-meta label[for=formatdiv-hide] {display: none;} ';
		}
		if( in_array('featured_image',$options['hide_on_screen']) )
		{
			$html .= '#postimagediv, #screen-meta label[for=postimagediv-hide] {display: none;} ';
		}
		if( in_array('revisions',$options['hide_on_screen']) )
		{
			$html .= '#revisionsdiv, #screen-meta label[for=revisionsdiv-hide] {display: none;} ';
		}
		if( in_array('categories',$options['hide_on_screen']) )
		{
			$html .= '#categorydiv, #screen-meta label[for=categorydiv-hide] {display: none;} ';
		}
		if( in_array('tags',$options['hide_on_screen']) )
		{
			$html .= '#tagsdiv-post_tag, #screen-meta label[for=tagsdiv-post_tag-hide] {display: none;} ';
		}
		if( in_array('send-trackbacks',$options['hide_on_screen']) )
		{
			$html .= '#trackbacksdiv, #screen-meta label[for=trackbacksdiv-hide] {display: none;} ';
		}
		
				
		return $html;
	}
	
	
	/*
	*  ajax_get_input_style
	*
	*  @description: called by input-actions.js to hide / show other metaboxes
	*  @since 2.0.5
	*  @created: 23/06/12
	*/
	
	function ajax_get_style()
	{
		// vars
		$options = array(
			'acf_id' => 0,
			'nonce' => ''
		);
		
		// load post options
		$options = array_merge($options, $_POST);
		
		
		// verify nonce
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			die(0);
		}
		
		
		// return style
		echo $this->get_style( $options['acf_id'] );
		
		
		// die
		die;
	}
	
	
	/*
	*  meta_box_input
	*
	*  @description: 
	*  @since 1.0.0
	*  @created: 23/06/12
	*/
	
	function meta_box_input( $post, $args )
	{
		// vars
		$options = $args['args'];
		
		echo '<input type="hidden" name="acf_nonce" value="' . wp_create_nonce( 'input' ) . '" />';
		echo '<div class="options" data-layout="' . $options['field_group']['options']['layout'] . '" data-show="' . $options['show'] . '" style="display:none"></div>';
		
		if( $options['show'] )
		{
			$fields = apply_filters('acf/field_group/get_fields', array(), $options['field_group']['id']);
	
			do_action('acf/create_fields', $fields, $options['post_id']);
		}
		else
		{
			echo '<div class="acf-replace-with-fields"><div class="acf-loading"></div></div>';
		}
	}
	
	
	/*
	*  ajax_render_fields
	*
	*  @description: 
	*  @since 3.1.6
	*  @created: 23/06/12
	*/

	function ajax_render_fields()
	{
		
		// defaults
		$options = array(
			'acf_id' => 0,
			'post_id' => 0,
			'nonce' => ''
		);
		
		
		// load post options
		$options = array_merge($options, $_POST);
		
		
		// verify nonce
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			die(0);
		}
		
		
		// get acfs
		$acfs = apply_filters('acf/get_field_groups', array());
		if( $acfs )
		{
			foreach( $acfs as $acf )
			{
				if( $acf['id'] == $options['acf_id'] )
				{
					$fields = apply_filters('acf/field_group/get_fields', array(), $acf['id']);
					
					do_action('acf/create_fields', $fields, $options['post_id']);
					
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
	
	function save_post( $post_id )
	{	
		
		// do not save if this is an auto save routine
		if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		{
			return $post_id;
		}
		
		
		// verify nonce
		if( !isset($_POST['acf_nonce']) || !wp_verify_nonce($_POST['acf_nonce'], 'input') )
		{
			return $post_id;
		}

		
		// update the post (may even be a revision / autosave preview)
		do_action('acf/save_post', $post_id);
        
        
	}
	
		
	
	/*--------------------------------------------------------------------------------------
	*
	*	input_admin_head
	*
	*	This is fired from an action: acf/input/admin_head
	*
	*	@author Elliot Condon
	*	@since 3.0.6
	* 
	*-------------------------------------------------------------------------------------*/
	
	function input_admin_head()
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
acf.ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
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
	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 30/01/13
	*/
	
	function input_admin_enqueue_scripts()
	{

		// scripts
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

		
		// 3.5 media gallery
		if( function_exists('wp_enqueue_media') && !did_action( 'wp_enqueue_media' ))
		{
			wp_enqueue_media();
		}
		
		
		// styles
		wp_enqueue_style(array(
			'thickbox',
			'farbtastic',
			'acf-global',
			'acf-input',
			'acf-datepicker',	
		));
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

new acf_input();

?>