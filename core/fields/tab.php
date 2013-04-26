<?php

class acf_field_tab extends acf_field
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
		$this->name = 'tab';
		$this->label = __("Tab",'acf');
		$this->category = __("Layout",'acf');
		
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
		echo '<div class="acf-tab" data-id="' . $field['key'] . '">' . $field['label'] . '</div>';
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
		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Instructions",'acf'); ?></label>
			</td>
			<td>
				<p><?php _e("All fields proceeding this \"tab field\" (or until another \"tab field\"  is defined) will appear grouped on the edit screen.",'acf'); ?></p>
				<p><?php _e("You can use multiple tabs to break up your fields into sections.",'acf'); ?></p>
			</td>
		</tr>
		<?php
		
	}
	
}

new acf_field_tab();