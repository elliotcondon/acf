<?php

/*
*  Meta Box: Location
*
*  @description: 
*  @created: 23/06/12
*/

// global
global $post;
		
		
// vars
$location = apply_filters('acf/field_group/get_location', array(), $post->ID);


// at lease 1 location rule
if( empty($location['rules']) )
{
	$location['rules'] = array(
		array(
			'param'		=>	'post_type',
			'operator'	=>	'',
			'value'		=>	'',
		)
	);
}

?>
<table class="acf_input widefat" id="acf_location">
	<tbody>
	<tr>
		<td class="label">
			<label for="post_type"><?php _e("Rules",'acf'); ?></label>
			<p class="description"><?php _e("Create a set of rules to determine which edit screens will use these advanced custom fields",'acf'); ?></p>
		</td>
		<td>
			<div class="location_rules">
				<table class="acf_input widefat acf-rules <?php if( count($location['rules']) == 1) echo 'remove-disabled'; ?>" id="location_rules">
					<tbody>
						<?php foreach($location['rules'] as $k => $rule): ?>
						<tr data-i="<?php echo $k; ?>">
						<td class="param"><?php 
							
							$choices = array(
								__("Basic",'acf') => array(
									'post_type'		=>	__("Post Type",'acf'),
									'user_type'		=>	__("Logged in User Type",'acf'),
								),
								__("Page",'acf') => array(
									'page'			=>	__("Page",'acf'),
									'page_type'		=>	__("Type",'acf'),
									'page_parent'	=>	__("Parent",'acf'),
									'page_template'	=>	__("Template",'acf'),
								),
								__("Post",'acf') => array(
									'post'			=>	__("Post",'acf'),
									'post_category'	=>	__("Category",'acf'),
									'post_format'	=>	__("Format",'acf'),
									'taxonomy'		=>	__("Taxonomy",'acf'),
								),
								__("Other",'acf') => array(
									'ef_taxonomy'	=>	__("Taxonomy Term (Add / Edit)",'acf'),
									'ef_user'		=>	__("User (Add / Edit)",'acf'),
									'ef_media'		=>	__("Media Attachment (Edit)",'acf')
								)
							);
							

							// validate
							/*
if($this->parent->is_field_unlocked('options_page'))
							{
								$choices[__("Options Page",'acf')]['options_page'] = __("Options Page",'acf');
							}
*/
							
							
							// allow custom location rules
							$choices = apply_filters( 'acf/location/rule_types', $choices );
							
							
							// create field
							$args = array(
								'type'	=>	'select',
								'name'	=>	'location[rules]['.$k.'][param]',
								'value'	=>	$rule['param'],
								'choices' => $choices,
								'optgroup' => true,
							);
							
							do_action('acf/create_field', $args);							
							
						?></td>
						<td class="operator"><?php 	
							
							$choices = array(
								'=='	=>	__("is equal to",'acf'),
								'!='	=>	__("is not equal to",'acf'),
							);
							
							
							// allow custom location rules
							$choices = apply_filters( 'acf/location/rule_operators', $choices );
							
							
							// create field
							do_action('acf/create_field', array(
								'type'	=>	'select',
								'name'	=>	'location[rules]['.$k.'][operator]',
								'value'	=>	$rule['operator'],
								'choices' => $choices
							)); 	
							
						?></td>
						<td class="value"><?php 
							
							$this->ajax_render_location(array(
								'key' => $k,
								'value' => $rule['value'],
								'param' => $rule['param'],
							)); 
							
						?></td>
						<td class="buttons">
							<ul class="hl clearfix">
								<li><a href="javascript:;" class="acf-button-remove"></a></li>
								<li><a href="javascript:;" class="acf-button-add"></a></li>
							</ul>
						</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
					
				</table>
				<ul class="hl clearfix">
					<li style="padding:4px 4px 0 0;"><?php _e("match",'acf'); ?></li>
					<li><?php do_action('acf/create_field', array(
									'type'	=>	'select',
									'name'	=>	'location[allorany]',
									'value'	=>	$location['allorany'],
									'choices' => array(
										'all'	=>	__("all",'acf'),
										'any'	=>	__("any",'acf'),							
									),
					)); ?></li>
					<li style="padding:4px 0 0 4px;"><?php _e("of the above",'acf'); ?></li>
				</ul>
			</div>
			
			
		</td>
		
	</tr>

	</tbody>
</table>
<script type="text/html" id="acf_location_options_deactivated">
	<optgroup label="<?php _e("Options",'acf'); ?>" disabled="true">
		<option value="" disabled="true"><?php _e("Unlock options add-on with an activation code",'acf'); ?></option>
	</optgroup>
</script>