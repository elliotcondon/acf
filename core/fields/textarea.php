<?php

class acf_field_textarea extends acf_field
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
		$this->name = 'textarea';
		$this->label = __("Text Area",'acf');
		$this->defaults = array(
			'default_value'	=>	'',
			'formatting' 	=>	'br',
			'max_length' => 0
		);
		
		
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
		$field['value'] = esc_textarea($field['value']);
		$maxlength = (!empty($field['max_length'])) ? 'maxlength="'.esc_attr($field['max_length']).'"' : '';
		
		echo '<textarea id="' . $field['id'] . '" rows="4" class="' . $field['class'] . '" name="' . $field['name'] . '" '.$maxlength.'>' . $field['value'] . '</textarea>';
	}
	
	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @param	$field	- an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function create_options( $field )
	{
		// vars
		$key = $field['name'];

?>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Default Value",'acf'); ?></label>
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
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Formatting",'acf'); ?></label>
		<p class="description"><?php _e("Define how to render html tags / new lines",'acf'); ?></p>
	</td>
	<td>
		<?php 
		do_action('acf/create_field', array(
			'type'	=>	'select',
			'name'	=>	'fields['.$key.'][formatting]',
			'value'	=>	$field['formatting'],
			'choices' => array(
				'none'	=>	__("None",'acf'),
				'br'	=>	__("auto &lt;br /&gt;",'acf'),
				'html'	=>	__("HTML",'acf'),
			)
		));
		?>
	</td>
</tr>
<tr class="field_option field_option_<?php echo $this->name; ?>">
    <td class="label">
        <label><?php _e("Max Length",'acf'); ?></label>
        <p class="description"><?php _e("Set a character limit for this field.<br />(0 = unlimited)",'acf'); ?></p>
    </td>
    <td>
        <?php 
        do_action('acf/create_field', array(
            'type'  =>  'number',
            'name'  =>  'fields[' .$key.'][max_length]',
            'value' =>  $field['max_length'],
            'maxlength' => 100
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
		// validate type
		if( !is_string($value) )
		{
			return $value;
		}
		
		
		if( $field['formatting'] == 'none' )
		{
			$value = htmlspecialchars($value, ENT_QUOTES);
		}
		elseif( $field['formatting'] == 'html' )
		{
			//$value = html_entity_decode($value);
			//$value = nl2br($value);
		}
		elseif( $field['formatting'] == 'br' )
		{
			$value = htmlspecialchars($value, ENT_QUOTES);
			$value = nl2br($value);
		}
		
		
		return $value;
	}
	
}

new acf_field_textarea();

?>