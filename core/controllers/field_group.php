<?php 

/*
*  Field Group
*
*  @description: All the functionality for creating / editing a field group
*  @since 3.2.6
*  @created: 23/06/12
*/

 
class acf_field_group
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
		add_action('save_post', array($this, 'save_post'));
		
		
		// filters
		add_filter('name_save_pre', array($this, 'save_name'));
		
		
		// ajax
		add_action('wp_ajax_acf_field_options', array($this, 'ajax_acf_field_options'));
		add_action('wp_ajax_acf_location', array($this, 'ajax_acf_location'));
		add_action('wp_ajax_acf_next_field_id', array($this, 'ajax_acf_next_field_id'));
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
			if( $typenow == "acf" )
			{
				$return = true;
			}
			
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
		
		
		// no autosave
		wp_dequeue_script( 'autosave' );
		
		
		// custom scripts
		wp_enqueue_script(array(
			'acf-fields',
		));
		
		
		do_action('acf_print_scripts-fields');
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
		
		
		// custom styles
		wp_enqueue_style(array(
			'acf-global',
			'acf-fields',
		));
		
		
		do_action('acf_print_styles-fields');
		
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
		
		
		global $post;
		
		
		// add js vars
		echo '<script type="text/javascript">
			acf.nonce = "' . wp_create_nonce( 'acf_nonce' ) . '";
			acf.post_id = ' . $post->ID . ';
		</script>';
		
		
		do_action('acf_head-fields');
		
		
		// add metaboxes
		add_meta_box('acf_fields', __("Fields",'acf'), array($this, 'meta_box_fields'), 'acf', 'normal', 'high');
		add_meta_box('acf_location', __("Location",'acf'), array($this, 'meta_box_location'), 'acf', 'normal', 'high');
		add_meta_box('acf_options', __("Options",'acf'), array($this, 'meta_box_options'), 'acf', 'normal', 'high');
		
		
		// add screen settings
		add_filter('screen_settings', array($this, 'screen_settings'), 10, 1);
	}
	
	
	/*
	*  screen_settings
	*
	*  @description: 
	*  @created: 4/09/12
	*/
	
	function screen_settings( $current )
	{
	    $current .= '<h5>' . __("Fields",'acf') . '</h5>';
	    
	    $current .= '<div class="show-field_key">Show Field Key:';
	    	 $current .= '<label class="show-field_key-no"><input checked="checked" type="radio" value="0" name="show-field_key" /> No</label>';
	    	 $current .= '<label class="show-field_key-yes"><input type="radio" value="1" name="show-field_key" /> Yes</label>';
		$current .= '</div>';
	    
	    return $current;
	}
	
	
	/*
	*  meta_box_fields
	*
	*  @description: 
	*  @since 1.0.0
	*  @created: 23/06/12
	*/
	
	function meta_box_fields()
	{
		include( $this->parent->path . 'core/views/meta_box_fields.php' );
	}
	
	
	/*
	*  meta_box_location
	*
	*  @description: 
	*  @since 1.0.0
	*  @created: 23/06/12
	*/

	function meta_box_location()
	{
		include( $this->parent->path . 'core/views/meta_box_location.php' );
	}
	
	
	/*
	*  meta_box_options
	*
	*  @description: 
	*  @since 1.0.0
	*  @created: 23/06/12
	*/
	
	function meta_box_options()
	{
		include( $this->parent->path . 'core/views/meta_box_options.php' );
	}
	
	
	/*
	*  ajax_acf_field_options
	*
	*  @description: creates the HTML for a field's options (field group edit page)
	*  @since 3.1.6
	*  @created: 23/06/12
	*/
	
	function ajax_acf_field_options()
	{
		// vars
		$options = array(
			'field_key' => '',
			'field_type' => '',
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
		
		
		
		// required
		if( ! $options['field_type'] )
		{
			die(0);
		}
		
		
		// find key (not actual field key, more the html attr name)
		$options['field_key'] = str_replace("fields[", "", $options['field_key']);
		$options['field_key'] = str_replace("][type]", "", $options['field_key']) ;
		
		

		$field = array();
		
		// render options
		$this->parent->fields[ $options['field_type'] ]->create_options($options['field_key'], $field);
		
		die();
		
	}
	
	
	/*
	*  ajax_acf_location
	*
	*  @description: creates the HTML for the field group location metabox. Called from both Ajax and PHP
	*  @since 3.1.6
	*  @created: 23/06/12
	*/
	
	function ajax_acf_location($options = array())
	{
		// defaults
		$defaults = array(
			'key' => null,
			'value' => null,
			'param' => null,
		);
		
		// Is AJAX call?
		if(isset($_POST['action']) && $_POST['action'] == "acf_location")
		{
			$options = array_merge($defaults, $_POST);
		}
		else
		{
			$options = array_merge($defaults, $options);
		}
		
		
		// some case's have the same outcome
		if($options['param'] == "page_parent")
		{
			$options['param'] = "page";
		}

		
		$choices = array();
		$optgroup = false;
		
		switch($options['param'])
		{
			case "post_type":
				
				// all post types except attachment
				$choices = $this->parent->get_post_types( array('attachment') );

				break;
			
			
			case "page":
				
				$optgroup = true;
				$post_types = get_post_types( array('capability_type'  => 'page') );
				unset( $post_types['attachment'], $post_types['revision'] , $post_types['nav_menu_item'], $post_types['acf']  );
				
				if( $post_types )
				{
					foreach( $post_types as $post_type )
					{
						$pages = get_pages(array(
							'numberposts' => -1,
							'post_type' => $post_type,
							'sort_column' => 'menu_order',
							'order' => 'ASC',
							'post_status' => array('publish', 'private', 'draft', 'inherit', 'future'),
							'suppress_filters' => false,
						));
						
						if( $pages )
						{
							$choices[$post_type] = array();
							
							foreach($pages as $page)
							{
								$title = '';
								$ancestors = get_ancestors($page->ID, 'page');
								if($ancestors)
								{
									foreach($ancestors as $a)
									{
										$title .= '- ';
									}
								}
								
								$title .= apply_filters( 'the_title', $page->post_title, $page->ID );
								
								
								// status
								if($page->post_status != "publish")
								{
									$title .= " ($page->post_status)";
								}
								
								$choices[$post_type][$page->ID] = $title;
								
							}
							// foreach($pages as $page)
						}
						// if( $pages )
					}
					// foreach( $post_types as $post_type )
				}
				// if( $post_types )
				
				break;
			
			
			case "page_type" :
				
				$choices = array(
					'front_page'	=>	__("Front Page",'acf'),
					'posts_page'	=>	__("Posts Page",'acf'),
					'parent'		=>	__("Parent Page",'acf'),
					'child'			=>	__("Child Page",'acf'),
				);
								
				break;
				
			case "page_template" :
				
				$choices = array(
					'default'	=>	__("Default Template",'acf'),
				);
				
				$templates = get_page_templates();
				foreach($templates as $k => $v)
				{
					$choices[$v] = $k;
				}
				
				break;
				
			case "post_template" :
				
				$choices = array(
					'default'	=>	__("Default Template",'acf'),
				);
				
				$templates = $this->parent->get_post_templates();
				foreach($templates as $k => $v)
				{
					$choices[$k] = $v;
				}
				
				break;
			
			
			case "post" :
				
				$optgroup = true;
				$post_types = get_post_types( array('capability_type'  => 'post') );
				unset( $post_types['attachment'], $post_types['revision'] , $post_types['nav_menu_item'], $post_types['acf']  );
				
				if( $post_types )
				{
					foreach( $post_types as $post_type )
					{
						
						$posts = get_posts(array(
							'numberposts' => '-1',
							'post_type' => $post_type,
							'post_status' => array('publish', 'private', 'draft', 'inherit', 'future'),
							'suppress_filters' => false,
						));
						
						if( $posts)
						{
							$choices[$post_type] = array();
							
							foreach($posts as $post)
							{
								$title = apply_filters( 'the_title', $post->post_title, $post->ID );
								
								// status
								if($post->post_status != "publish")
								{
									$title .= " ($post->post_status)";
								}
								
								$choices[$post_type][$post->ID] = $title;

							}
							// foreach($posts as $post)
						}
						// if( $posts )
					}
					// foreach( $post_types as $post_type )
				}
				// if( $post_types )
				
				
				break;
			
			case "post_category" :
				
				$category_ids = get_all_category_ids();
		
				foreach($category_ids as $cat_id) 
				{
				  $cat_name = get_cat_name($cat_id);
				  $choices[$cat_id] = $cat_name;
				}
				
				break;
			
			case "post_format" :
				
				$choices = get_post_format_strings();
								
				break;
			
			case "user_type" :
				
				global $wp_roles;
				
				$choices = $wp_roles->get_names();
								
				break;
			
			case "options_page" :
				
				$defaults = $this->parent->defaults['options_page'];
				
				$choices = array(
					'acf-options' => $defaults['title']
				);
				
				$titles = $defaults['pages'];
				if( !empty($titles) )
				{
					$choices = array();
					foreach( $titles as $title )
					{
						$slug = 'acf-options-' . sanitize_title( $title );
						$choices[ $slug ] = $title;
					}
				}
	
				break;
			
			case "taxonomy" :
				
				$choices = $this->parent->get_taxonomies_for_select( array('simple_value' => true) );
				$optgroup = true;
								
				break;
			
			case "ef_taxonomy" :
				
				$choices = array('all' => __('All', 'acf'));
				$taxonomies = get_taxonomies( array('public' => true), 'objects' );
				
				foreach($taxonomies as $taxonomy)
				{
					$choices[ $taxonomy->name ] = $taxonomy->labels->name;
				}
				
				// unset post_format (why is this a public taxonomy?)
				if( isset($choices['post_format']) )
				{
					unset( $choices['post_format']) ;
				}
			
								
				break;
			
			case "ef_user" :
				
				global $wp_roles;
				
				$choices = array_merge( array('all' => __('All', 'acf')), $wp_roles->get_names() );
			
				break;
				
				
			case "ef_media" :
				
				$choices = array('all' => __('All', 'acf'));
			
				break;
				
		}
		
		$this->parent->create_field(array(
			'type'	=>	'select',
			'name'	=>	'location[rules][' . $options['key'] . '][value]',
			'value'	=>	$options['value'],
			'choices' => $choices,
			'optgroup' => $optgroup,
		));
		
		// ajax?
		if(isset($_POST['action']) && $_POST['action'] == "acf_location")
		{
			die();
		}
								
	}	
	
	
	/*
	*  save_name
	*
	*  @description: intercepts the acf post obejct and adds an "acf_" to the start of 
	*				 it's name to stop conflicts between acf's and page's urls
	*  @since 1.0.0
	*  @created: 23/06/12
	*/
		
	function save_name($name)
	{
        if (isset($_POST['post_type']) && $_POST['post_type'] == 'acf') 
        {
			$name = 'acf_' . sanitize_title_with_dashes($_POST['post_title']);
        }
        
        return $name;
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
		
		// only for save acf
		if( ! isset($_POST['acf_field_group']) || ! wp_verify_nonce($_POST['acf_field_group'], 'acf_field_group') )
		{
			return $post_id;
		}
		
		
		// do not save if this is an auto save routine
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
		
		
		// only save once! WordPress save's a revision as well.
		if( wp_is_post_revision($post_id) )
		{
	    	return $post_id;
        }
		
		
		/*
		*  save fields
		*/
		

		// vars
		$dont_delete = array();
		
		
		
		if( $_POST['fields'] )
		{
			$i = -1;
			

			// remove clone field
			unset( $_POST['fields']['field_clone'] );
			

			// loop through and save fields
			foreach( $_POST['fields'] as $key => $field )
			{
				$i++;
				
				
				// order + key
				$field['order_no'] = $i;
				$field['key'] = $key;
				
				
				// save
				$this->parent->update_field( $post_id, $field);
				
				
				// add to dont delete array
				$dont_delete[] = $field['key'];
			}
		}
		
		
		// delete all other field
		$keys = get_post_custom_keys($post_id);
		foreach( $keys as $key )
		{
			if( strpos($key, 'field_') !== false && !in_array($key, $dont_delete) )
			{
				// this is a field, and it wasn't found in the dont_delete array
				delete_post_meta($post_id, $key);
			}
		}
		
		
		/*
		*  save location rules
		*/
		
		$location = $_POST['location'];
		update_post_meta($post_id, 'allorany', $location['allorany']);
		
		delete_post_meta($post_id, 'rule');
		if($location['rules'])
		{
			foreach($location['rules'] as $k => $rule)
			{
				$rule['order_no'] = $k;
				add_post_meta($post_id, 'rule', $rule);
			}
		}
		
		
		/*
		*  save options
		*/
		
		$options = $_POST['options'];
		
		if(!isset($options['position'])) { $options['position'] = 'normal'; }
		if(!isset($options['layout'])) { $options['layout'] = 'default'; }
		if(!isset($options['hide_on_screen'])) { $options['hide_on_screen'] = array(); }
		
		update_post_meta($post_id, 'position', $options['position']);
		update_post_meta($post_id, 'layout', $options['layout']);
		update_post_meta($post_id, 'hide_on_screen', $options['hide_on_screen']);
		
	
		
	}
		
	
	/*
	*  ajax_next_field_id
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function ajax_acf_next_field_id()
	{
		// vars
		$options = array(
			'nonce' => '',
		);
		$options = array_merge($options, $_POST);
		
		
		// verify nonce
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			die(0);
		}
		
		
		// get next id
		$next_id = intval( get_option('acf_next_field_id', 1) );
		
		
		// update the acf_next_field_id
		update_option('acf_next_field_id', ($next_id + 1) );
		
		
		// return id
		die('field_' . $next_id);
	}

}

?>