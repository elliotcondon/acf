<?php 

/*
*  acf_addons
*
*  @description: controller for add-ons sub menu page
*  @since: 3.6
*  @created: 25/01/13
*/

class acf_addons
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
		add_action('admin_menu', array($this,'admin_menu'), 11, 0);
	}
	
	
	/*
	*  admin_menu
	*
	*  @description: 
	*  @created: 2/08/12
	*/
	
	function admin_menu()
	{
		// add page
		$page = add_submenu_page('edit.php?post_type=acf', __('Add-Ons','acf'), __('Add-Ons','acf'), 'manage_options', 'acf-addons', array($this,'html'));
		
		
		// actions
		add_action('load-' . $page, array($this,'load'));
		add_action('admin_print_scripts-' . $page, array($this, 'admin_print_scripts'));
		add_action('admin_print_styles-' . $page, array($this, 'admin_print_styles'));
		add_action('admin_head-' . $page, array($this,'admin_head'));
	}
	
	
	/*
	*  load
	*
	*  @description: 
	*  @since 3.5.2
	*  @created: 16/11/12
	*  @thanks: Kevin Biloski and Charlie Eriksen via Secunia SVCRP
	*/
	
	function load()
	{
		
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
		wp_enqueue_style(array(
			'wp-pointer',
			'acf-global',
			'acf',
		));
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
				
	}
	
	
	/*
	*  html
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function html()
	{
		// vars
		$dir = apply_filters('acf/get_info', 'dir');
		
		
		$active = array(
			'repeater' => class_exists('acf_field_repeater'),
			'gallery' => class_exists('acf_field_gallery'),
			'options_page' => class_exists('acf_options_page_plugin'),
			'flexible_content' => class_exists('acf_field_flexible_content')
		);
		
		?>
<div class="wrap">

	<div class="icon32" id="icon-acf"><br></div>
	<h2 style="margin: 4px 0 15px;"><?php _e("Advanced Custom Fields Add-Ons",'acf'); ?></h2>
	
	<p style="margin: 0 0 20px;"><?php _e("The following Add-ons are available to increase the functionality of the Advanced Custom Fields plugin.",'acf'); ?><br />
	<?php _e("Each Add-on can be installed as a separate plugin (receives updates) or included in your theme (does not receive updates).",'acf'); ?></p>
	
	<div class="acf-alert">
			<p><strong><?php _e("Just updated to version 4?",'acf'); ?></strong> <?php _e("Activation codes have changed to plugins! Download your purchased add-ons",'acf'); ?> <a href="http://www.advancedcustomfields.com/add-ons-download/" target="_blank"><?php _e("here",'acf'); ?></a></p>
		</div>
	
	<div id="add-ons" class="clearfix">
		
		<div class="add-on wp-box <?php if( $active['repeater'] ): ?>add-on-active<?php endif; ?>">
			<img src="<?php echo $dir; ?>images/add-ons/repeater-field-thumb.jpg" />
			<div class="inner">
				<h3><?php _e("Repeater Field",'acf'); ?></h3>
				<p><?php _e("Create infinite rows of repeatable data with this versatile interface!",'acf'); ?></p>
			</div>
			<div class="footer">
				<?php if( $active['repeater'] ): ?>
					<a class="button button-disabled"><span class="tick"></span><?php _e("Installed",'acf'); ?></a>
				<?php else: ?>
					<a target="_blank" href="http://www.advancedcustomfields.com/add-ons/repeater-field/" class="button"><?php _e("Purchase & Install",'acf'); ?></a>
				<?php endif; ?>
			</div>
		</div>
		
		
		<div class="add-on wp-box <?php if( $active['gallery'] ): ?>add-on-active<?php endif; ?>">
			<img src="<?php echo $dir; ?>images/add-ons/gallery-field-thumb.jpg" />
			<div class="inner">
				<h3><?php _e("Gallery Field",'acf'); ?></h3>
				<p><?php _e("Create image galleries in a simple and intuitive interface!",'acf'); ?></p>
			</div>
			<div class="footer">
				<?php if( $active['gallery'] ): ?>
					<a class="button button-disabled"><span class="tick"></span><?php _e("Installed",'acf'); ?></a>
				<?php else: ?>
					<a target="_blank" href="http://www.advancedcustomfields.com/add-ons/gallery-field/" class="button"><?php _e("Purchase & Install",'acf'); ?></a>
				<?php endif; ?>
			</div>
		</div>
		
		
		<div class="add-on wp-box <?php if( $active['options_page'] ): ?>add-on-active<?php endif; ?>">
			<img src="<?php echo $dir; ?>images/add-ons/options-page-thumb.jpg" />
			<div class="inner">
				<h3><?php _e("Options Page",'acf'); ?></h3>
				<p><?php _e("Create global data to use throughout your website!",'acf'); ?></p>
			</div>
			<div class="footer">
				<?php if( $active['options_page'] ): ?>
					<a class="button button-disabled"><span class="tick"></span><?php _e("Installed",'acf'); ?></a>
				<?php else: ?>
					<a target="_blank" href="http://www.advancedcustomfields.com/add-ons/options-page/" class="button"><?php _e("Purchase & Install",'acf'); ?></a>
				<?php endif; ?>
			</div>
		</div>

		
		<div class="add-on wp-box <?php if( $active['flexible_content'] ): ?>add-on-active<?php endif; ?>">
			<img src="<?php echo $dir; ?>images/add-ons/flexible-content-field-thumb.jpg" />
			<div class="inner">
				<h3><?php _e("Flexible Content Field",'acf'); ?></h3>
				<p><?php _e("Create unique designs with a flexible content layout manager!",'acf'); ?></p>
			</div>
			<div class="footer">
				<?php if( $active['flexible_content'] ): ?>
					<a class="button button-disabled"><span class="tick"></span><?php _e("Installed",'acf'); ?></a>
				<?php else: ?>
					<a target="_blank" href="http://www.advancedcustomfields.com/add-ons/flexible-content-field/" class="button"><?php _e("Purchase & Install",'acf'); ?></a>
				<?php endif; ?>
			</div>
		</div>

		
	</div>
	
	
</div>
		<?php
		
		return;
		
	}		
}

new acf_addons();

?>