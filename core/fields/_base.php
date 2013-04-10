<?php

/*
*  acf_field
*
*  @description: This is the base class for all fields.
*  @since: 3.6
*  @created: 30/01/13
*/
 
class acf_field
{
	/*
	*  Vars
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 30/01/13
	*/
	
	var $name,
		$title,
		$category;
	
	
	/*
	*  __construct()
	*
	*  Adds neccessary Actions / Filters
	*
	*  @since	3.6
	*  @date	30/01/13
	*/
	
	function __construct()
	{
		// register field
		add_filter('acf/registered_fields', array($this, 'registered_fields'), 10, 1);
		
		
		// value
		$this->add_filter('acf/load_value/type=' . $this->name, array($this, 'load_value'), 10, 3);
		$this->add_filter('acf/update_value/type=' . $this->name, array($this, 'update_value'), 10, 3);
		$this->add_filter('acf/format_value/type=' . $this->name, array($this, 'format_value'), 10, 3);
		$this->add_filter('acf/format_value_for_api/type=' . $this->name, array($this, 'format_value_for_api'), 10, 3);
		
		
		// field
		$this->add_filter('acf/load_field/type=' . $this->name, array($this, 'load_field'), 10, 3);
		$this->add_filter('acf/update_field/type=' . $this->name, array($this, 'update_field'), 10, 2);
		$this->add_action('acf/create_field/type=' . $this->name, array($this, 'create_field'), 10, 1);
		$this->add_action('acf/create_field_options/type=' . $this->name, array($this, 'create_options'), 10, 1);
		
		
		// input actions
		$this->add_action('acf/input/admin_enqueue_scripts', array($this, 'input_admin_enqueue_scripts'), 10, 0);
		$this->add_action('acf/input/admin_head', array($this, 'input_admin_head'), 10, 0);
		
		
		// field group actions
		$this->add_action('acf/field_group/admin_enqueue_scripts', array($this, 'field_group_admin_enqueue_scripts'), 10, 0);
		$this->add_action('acf/field_group/admin_head', array($this, 'field_group_admin_head'), 10, 0);
		
	}
	
	
	/*
	*  add_filter
	*
	*  @description: checks if the function is_callable before adding the filter
	*  @since: 3.6
	*  @created: 30/01/13
	*/
	
	function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
	{
		if( is_callable($function_to_add) )
		{
			add_filter($tag, $function_to_add, $priority, $accepted_args);
		}
	}
	
	
	/*
	*  add_action
	*
	*  @description: checks if the function is_callable before adding the action
	*  @since: 3.6
	*  @created: 30/01/13
	*/
	
	function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1)
	{
		if( is_callable($function_to_add) )
		{
			add_action($tag, $function_to_add, $priority, $accepted_args);
		}
	}
	
	
	/*
	*  registered_fields()
	*
	*  Adds this field to the select list when creating a new field
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$fields	- the array of all registered fields
	*
	*  @return	$fields - the array of all registered fields
	*/
	
	function registered_fields( $fields )
	{
		// defaults
		if( !$this->category )
		{
			$this->category = __('Basic', 'acf');
		}
		
		
		// add to array
		$fields[ $this->category ][ $this->name ] = $this->label;
		
		
		// return array
		return $fields;
	}
	
	
}

?>