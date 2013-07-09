<?php

class acf_field_page_link extends acf_field
{
	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function __construct()
	{
		// vars
		$this->name = 'page_link';
		$this->label = __("Page Link",'acf');
		$this->category = __("Relational",'acf');
		$this->defaults = array(
			'post_type' => array('all'),
			'multiple' => 0,
			'allow_null' => 0,
		);
		
		
		// do not delete!
    	parent::__construct();
  
	}
	
	
	/*
	*  load_field()
	*  
	*  This filter is appied to the $field after it is loaded from the database
	*  
	*  @type filter
	*  @since 3.6
	*  @date 23/01/13
	*  
	*  @param $field - the field array holding all the field options
	*  
	*  @return $field - the field array holding all the field options
	*/
	
	function load_field( $field )
	{

		// validate post_type
		if( !$field['post_type'] || !is_array($field['post_type']) || in_array('', $field['post_type']) )
		{
			$field['post_type'] = array( 'all' );
		}

		
		// return
		return $field;
	}
	
	
	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function create_field( $field )
	{
		// let post_object create the field
		$field['type'] = 'post_object';
		$field['is_page_link'] = true;
		
		do_action('acf/create_field', $field );
	}
	
	
	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function create_options( $field )
	{
		$key = $field['name'];
		
		?>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label for=""><?php _e("Post Type",'acf'); ?></label>
	</td>
	<td>
		<?php 
		
		$choices = array(
			'all'	=>	__("All",'acf')
		);
		$choices = apply_filters('acf/get_post_types', $choices);
		$choices[ACF_NONE_VALUE] = __("None",'acf');
		
		do_action('acf/create_field', array(
			'type'	=>	'select',
			'name'	=>	'fields['.$key.'][post_type]',
			'value'	=>	$field['post_type'],
			'choices'	=>	$choices,
			'multiple'	=>	1,
		));
		
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label for=""><?php _e("Taxonomy Listings",'acf'); ?></label>
	</td>
	<td>
		<?php 
		
		$choices = array(
			'all'	=>	__("All",'acf')
		);
		
		$choices = apply_filters('acf/get_taxonomies_with_post_type_names', $choices);
		$choices[ACF_NONE_VALUE] = __("None",'acf');
		
		do_action('acf/create_field', array(
			'type'	=>	'select',
			'name'	=>	'fields['.$key.'][taxonomies]',
			'value'	=>	$field['taxonomies'],
			'choices'	=>	$choices,
			'multiple'	=>	1,
		));
		
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Allow Null?",'acf'); ?></label>
	</td>
	<td>
		<?php
		
		do_action('acf/create_field', array(
			'type'	=>	'radio',
			'name'	=>	'fields['.$key.'][allow_null]',
			'value'	=>	$field['allow_null'],
			'choices'	=>	array(
				1	=>	__("Yes",'acf'),
				0	=>	__("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Select multiple values?",'acf'); ?></label>
	</td>
	<td>
		<?php
		
		do_action('acf/create_field', array(
			'type'	=>	'radio',
			'name'	=>	'fields['.$key.'][multiple]',
			'value'	=>	$field['multiple'],
			'choices'	=>	array(
				1	=>	__("Yes",'acf'),
				0	=>	__("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		?>
	</td>
</tr>
		<?php
		
	}
	
	
	/*
	*  format_value_for_api()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is passed back to the api functions such as the_field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value	- the value which was loaded from the database
	*  @param	$post_id - the $post_id from which the value was loaded
	*  @param	$field	- the field array holding all the field options
	*
	*  @return	$value	- the modified value
	*/
	
	function format_value_for_api( $value, $post_id, $field )
	{
		if( !$value )
		{
			return false;
		}
		
		if( $value == 'null' )
		{
			return false;
		}
		
		if( is_array($value) )
		{
			foreach( $value as $k => $v )
			{
				$value[ $k ] = $this->get_page_link_value($v);
			}
		}
		else
		{
			$value = $this->get_page_link_value($value);
		}
		
		return $value;
	}
	
	function get_page_link_value($v) {
		if (strpos($v,'tax:') === 0) {
			$tax_arr = explode(':',$v);
			if ($tax_arr[1] == 'category') return get_category_link($tax_arr[2]);
			else return get_term_link((int)$tax_arr[2],$tax_arr[1]);
		}
		else if (strpos($v,'ptarchive:') === 0) {
			$ptype_arr = explode(':',$v);
			return get_post_type_archive_link($ptype_arr[1]);
		}
		else return get_permalink($v);
	}
	
}

new acf_field_page_link();

?>