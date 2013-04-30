<?php

class acf_field_checkbox extends acf_field
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
		$this->name = 'checkbox';
		$this->label = __("Checkbox",'acf');
		$this->category = __("Choice",'acf');
		
		
		// do not delete!
    	parent::__construct();
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
		// vars
		$defaults = array(
			'layout'		=>	'vertical',
			'choices'		=>	array(),
		);
		
		$field = array_merge($defaults, $field);
		
		
		// value must be array
		if( !is_array($field['value']) )
		{
			// perhaps this is a default value with new lines in it?
			if( strpos($field['value'], "\n") !== false )
			{
				// found multiple lines, explode it
				$field['value'] = explode("\n", $field['value']);
			}
			else
			{
				$field['value'] = array( $field['value'] );
			}
		}
		
		
		// trim value
		$field['value'] = array_map('trim', $field['value']);
		
		
		echo '<input type="hidden" name="' . $field['name'] . '" value="" />';
		echo '<ul class="checkbox_list ' . $field['class'] . '">';
		
		
		// checkbox saves an array
		$field['name'] .= '[]';
		
		
		// foreach choices
		foreach($field['choices'] as $key => $value)
		{
			$selected = '';
			if( in_array($key, $field['value']) )
			{
				$selected = 'checked="yes"';
			}
			if( isset($field['disabled']) && in_array($key, $field['disabled']) )
			{
				$selected .= ' disabled="true"';
			}
			
			
			echo '<li><label><input id="' . $field['id'] . '-' . $key . '" type="checkbox" class="' . $field['class'] . '" name="' . $field['name'] . '" value="' . $key . '" ' . $selected . ' />' . $value . '</label></li>';
		}
		
		echo '</ul>';
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
		// vars
		$defaults = array(
			'default_value'	=>	'',
			'choices'		=>	'',
		);
		
		$field = array_merge($defaults, $field);
		$key = $field['name'];
		
		
		// implode checkboxes so they work in a textarea
		if( is_array($field['choices']) )
		{		
			foreach( $field['choices'] as $k => $v )
			{
				$field['choices'][ $k ] = $k . ' : ' . $v;
			}
			$field['choices'] = implode("\n", $field['choices']);
		}
		
		?>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label for=""><?php _e("Choices",'acf'); ?></label>
		<p><?php _e("Enter each choice on a new line.",'acf'); ?></p>
		<p><?php _e("For more control, you may specify both a value and label like this:",'acf'); ?></p>
		<p><?php _e("red : Red",'acf'); ?><br /><?php _e("blue : Blue",'acf'); ?></p>
	</td>
	<td>
		<?php
		
		do_action('acf/create_field', array(
			'type'	=>	'textarea',
			'class' => 	'textarea field_option-choices',
			'name'	=>	'fields['.$key.'][choices]',
			'value'	=>	$field['choices'],
		));
		
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Default Value",'acf'); ?></label>
		<p class="description"><?php _e("Enter each default value on a new line",'acf'); ?></p>
	</td>
	<td>
		<?php
		
		do_action('acf/create_field', array(
			'type'	=>	'textarea',
			'name'	=>	'fields['.$key.'][default_value]',
			'value'	=>	$field['default_value'],
		));
		
		?>
	</td>
</tr>
		<?php
		
	}
	
}

new acf_field_checkbox();

?>