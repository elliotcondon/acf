<?php

class acf_field_file extends acf_field
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
		$this->name = 'file';
		$this->label = __("File",'acf');
		$this->category = __("Content",'acf');
		
		
		// do not delete!
    	parent::__construct();
    	
    	
    	// extra
		add_filter('get_media_item_args', array($this, 'get_media_item_args'));
		add_action('acf_head-update_attachment-' . $this->name, array($this, 'acf_head_update_attachment'));
		add_action('wp_ajax_acf/fields/file/get_files', array($this, 'ajax_get_files'));
		add_action('admin_head-media-upload-popup', array($this, 'popup_head'));
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
		$options = array(
			'class' => '',
			'icon' => '',
			'file_name' => ''
		);
		
		if( $field['value'] )
		{
			$file_src = wp_get_attachment_url( $field['value'] );
			preg_match("~[^/]*$~", $file_src, $file_name);
		
			$options['class'] = 'active';
			$options['icon'] = wp_mime_type_icon( $field['value'] );
			$options['file_name'] = $file_name[0];
		}
		
		?>
<div class="acf-file-uploader <?php echo $options['class']; ?>">
	<input class="acf-file-value" type="hidden" name="<?php echo $field['name']; ?>" value="<?php echo $field['value']; ?>" />
	<div class="has-file">
		<ul class="hl clearfix">
			<li>
				<img class="acf-file-icon" src="<?php echo $options['icon']; ?>" alt=""/>
			</li>
			<li>
				<span class="acf-file-name"><?php echo $options['file_name']; ?></span><br />
				<a href="#" class="edit-file"><?php _e('Edit','acf'); ?></a> 
				<a href="#" class="remove-file"><?php _e('Remove','acf'); ?></a>
			</li>
		</ul>
	</div>
	<div class="no-file">
		<ul class="hl clearfix">
			<li>
				<span><?php _e('No File Selected','acf'); ?></span>. <a href="#" class="button add-file"><?php _e('Add File','acf'); ?></a>
			</li>
		</ul>
	</div>
</div>
		<?php
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
			'save_format'	=>	'id',
		);
		
		$field = array_merge($defaults, $field);
		$key = $field['name'];
		
		?>
<tr class="field_option field_option_<?php echo $this->name; ?>">
	<td class="label">
		<label><?php _e("Return Value",'acf'); ?></label>
	</td>
	<td>
		<?php
		
		do_action('acf/create_field', array(
			'type'		=>	'radio',
			'name'		=>	'fields['.$key.'][save_format]',
			'value'		=>	$field['save_format'],
			'layout'	=>	'horizontal',
			'choices' 	=>	array(
				'object'	=>	__("File Object",'acf'),
				'url'		=>	__("File URL",'acf'),
				'id'		=>	__("File ID",'acf')
			)
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
		// vars
		$defaults = array(
			'save_format'	=>	'url',
		);
		
		$field = array_merge($defaults, $field);
		
		
		// validate
		if( !$value )
		{
			return false;
		}
		
		
		// format
		if( $field['save_format'] == 'url' )
		{
			$value = wp_get_attachment_url($value);
		}
		elseif( $field['save_format'] == 'object' )
		{
			$attachment = get_post( $value );
			
			
			// validate
			if( !$attachment )
			{
				return false;	
			}
			
			
			// create array to hold value data
			$value = array(
				'id' => $attachment->ID,
				'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
				'title' => $attachment->post_title,
				'caption' => $attachment->post_excerpt,
				'description' => $attachment->post_content,
				'url' => wp_get_attachment_url( $attachment->ID ),
			);
		}
		
		return $value;
	}
	
	
	/*
	*  get_media_item_args
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 27/01/13
	*/
	
	function get_media_item_args( $vars )
	{
	    $vars['send'] = true;
	    return($vars);
	}
	
	
	/*
   	*  acf_head_update_attachment
   	*
   	*  @description: 
   	*  @since: 3.2.7
   	*  @created: 4/07/12
   	*/
   	
   	function acf_head_update_attachment()
	{
		?>
<script type="text/javascript">
(function($){
	
	// vars
	var div = self.parent.acf.media.div;
	
	
	// add message
	self.parent.acf.helpers.add_message("<?php _e("File Updated.",'acf'); ?>", div);
	

})(jQuery);
</script>
		<?php
	}
	
	
	/*
   	*  ajax_get_files
   	*
   	*  @description: 
   	*  @since: 3.5.7
   	*  @created: 13/01/13
   	*/
	
   	function ajax_get_files()
   	{
   		// vars
		$options = array(
			'nonce' => '',
			'files' => array()
		);
		$return = array();
		
		
		// load post options
		$options = array_merge($options, $_POST);
		
		
		// verify nonce
		if( ! wp_verify_nonce($options['nonce'], 'acf_nonce') )
		{
			die(0);
		}
		
		
		if( $options['files'] )
		{
			foreach( $options['files'] as $id )
			{
				$file_src = wp_get_attachment_url( $id );
				preg_match("~[^/]*$~", $file_src, $file_name);
					
					
				// vars
				$return[] = array(
					'id' => $id,
					'icon' => wp_mime_type_icon( $id ),
					'name' => $file_name[0]
				);
			}
		}
		
		
		// return json
		echo json_encode( $return );
		die;
		
   	}
   	
   	
   	/*
	*  popup_head
	*
	*  @description: css + js for thickbox
	*  @since: 1.1.4
	*  @created: 7/12/12
	*/
	
	function popup_head()
	{
		// options
		$defaults = array(
			'acf_type' => '',
			'tab'	=>	'type',	
		);
		
		$options = array_merge($defaults, $_GET);
		
		
		// validate
		if( $options['acf_type'] != 'file' )
		{
			return;
		}
		
		
		// update attachment
		if( isset($_POST["attachments"]) )
		{
			echo '<div class="updated"><p>' . __("Media attachment updated.") . '</p></div>';
		}
		
					
?><style type="text/css">
	#media-upload-header #sidemenu li#tab-type_url,
	#media-items .media-item a.toggle,
	#media-items .media-item tr.image-size,
	#media-items .media-item tr.align,
	#media-items .media-item tr.url,
	#media-items .media-item .slidetoggle {
		display: none !important;
	}
	
	#media-items .media-item {
		position: relative;
		overflow: hidden;
	}
	
	#media-items .media-item .acf-checkbox {
		float: left;
		margin: 28px 10px 0;
	}
	
	#media-items .media-item .pinkynail {
		max-width: 64px;
		max-height: 64px;
		display: block !important;
		margin: 2px;
	}
	
	#media-items .media-item .filename.new {
		min-height: 0;
		padding: 10px;
		line-height: 15px;
	}
	
	#media-items .media-item .title {
		line-height: 14px;
	}
	
	#media-items .media-item .acf-select {
		float: right;
		margin: 22px 12px 0 10px;
	}
	
	#media-upload .ml-submit {
		display: none !important;
	}

	#media-upload .acf-submit {
		margin: 1em 0;
		padding: 1em 0;
		position: relative;
		overflow: hidden;
		display: none; /* default is hidden */
		clear: both;
	}
	
	#media-upload .acf-submit a {
		float: left;
		margin: 0 10px 0 0;
	}
	
	#media-items .media-item .acf-filename {
		color: #999;
		font-size: 11px;
		margin: 0 0 3px;
		display: block;
	}
	
	
<?php if( $options['tab'] == 'gallery' ): ?>
	#sort-buttons,
	#gallery-form > .widefat,
	#media-items .menu_order,
	#gallery-settings {
		display: none !important;
	}
<?php endif; ?>

</style>
<script type="text/javascript">
(function($){
	
	
	/*
	*  Select File
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/
	
	$('#media-items .media-item a.acf-select').live('click', function(){
		
		var id = $(this).attr('href');
		
		
		// IE7 Fix
		if( id.indexOf("/") != -1 )
		{
			var split = id.split("/");
			id = split[split.length-1];
		}
		
		
		var ajax_data = {
			action : 'acf/fields/file/get_files',
			nonce : self.parent.acf.nonce,
			files : [ id ]
		};
	
		
		// ajax
		$.ajax({
			url: ajaxurl,
			type: 'post',
			data : ajax_data,
			cache: false,
			dataType: "json",
			success: function( json ) {	    	

				// validate
				if( !json )
				{
					return false;
				}
				
				
				// add file
				self.parent.acf.fields.file.add( json[0] );
				
	 			self.parent.tb_remove();
	 	
	 	
			}
		});
		
 						
		return false;
	});
	
	
	/*
	*  Select Files
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/
	
	$('#acf-add-selected').live('click', function(){ 
		 
		// check total 
		var total = $('#media-items .media-item .acf-checkbox:checked').length;
		if( total == 0 ) 
		{ 
			alert("<?php _e("No files selected",'acf'); ?>"); 
			return false; 
		} 
		
		 
		var ajax_data = {
			action : 'acf/fields/file/get_files',
			nonce : self.parent.acf.nonce,
			files : []
		};
		
		
		// add to id array
		$('#media-items .media-item .acf-checkbox:checked').each(function(){
			
			ajax_data.files.push( $(this).val() );
			
		});
		
		
		// ajax
		$.ajax({
			url: ajaxurl,
			type: 'post',
			data : ajax_data,
			cache: false,
			dataType: "json",
			success: function( json ) {
			
				// validate
				if( !json )
				{
					return false;
				}
				
				
				
				var selection = json,
		    		i = 0;
		    		
		    	
		    	$.each( json, function( k, file ){

			    	// counter
			    	i++;
			    	
			    	
			    	// vars
			    	var div = self.parent.acf.media.div;
			    	
			    	
			    	// add image to field
			        self.parent.acf.fields.file.add( file );
			        
			        
			        // select / add another file field?
			        if( i < selection.length )
					{
						var tr = div.closest('tr'),
							repeater = tr.closest('.repeater');
						
						
						if( tr.next('.row').exists() )
						{
							self.parent.acf.media.div = tr.next('.row').find('.acf-file-uploader');
						}
						else
						{
							// add row 
			 				repeater.find('.add-row-end').trigger('click'); 
			 			 
			 				// set div to new row file 
			 				self.parent.acf.media.div = repeater.find('> table > tbody > tr.row:last .acf-file-uploader');
						}
					}
					
			    });

				
	 			self.parent.tb_remove();
	 	
			}
		});
 		
 		
		return false; 
		 
	}); 
	
	
	/*
	*  Edit Attachment Toggle
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/
	
	$('#media-items .media-item a.acf-toggle-edit').live('click', function(){
		
		// vars
		var a = $(this),
			item = a.closest('.media-item');
		
		
		// toggle
		if( a.hasClass('active') )
		{
			a.removeClass('active');
			item.find('.slidetoggle').attr('style', 'display: none !important');
		}
		else
		{
			a.addClass('active');
			item.find('.slidetoggle').attr('style', 'display: table !important');
		}
		
		
		// return
		return false;
		
	});
	
	
	/*
	*  add_buttons
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 11/12/12
	*/

	function add_buttons()
	{
		// vars
		var is_sub_field = (self.parent.acf.media.div.closest('.repeater').length > 0) ? true : false;
		
		
		// add submit after media items (on for sub fields)
		if($('.acf-submit').length == 0 && is_sub_field)
		{
			$('#media-items').after('<div class="acf-submit"><a id="acf-add-selected" class="button"><?php _e("Add Selected Files",'acf'); ?></a></div>');
		}
		
		
		// add buttons to media items
		$('#media-items .media-item:not(.acf-active)').each(function(){
			
			// show the add all button
			$('.acf-submit').show();
			
			// needs attachment ID
			if($(this).children('input[id*="type-of-"]').length == 0){ return false; }
			
			// only once!
			$(this).addClass('acf-active');
			
			// find id
			var id = $(this).children('input[id*="type-of-"]').attr('id').replace('type-of-', '');
			
			// if inside repeater, add checkbox
			if(is_sub_field)
			{
				$(this).prepend('<input type="checkbox" class="acf-checkbox" value="' + id + '" <?php if( $options['tab'] == 'type' ){echo 'checked="checked"';} ?> />');
			}
			
			// find file url
			var file_url = $(this).find('.slidetoggle tr.url .urlfile').attr('data-link-url');
			$(this).find('.filename.new').append('<span class="acf-filename">' + file_url + '</span>');
			
			// Add edit button
			$(this).find('.filename.new').append('<a href="#" class="acf-toggle-edit">Edit</a>');
			
			// Add select button
			$(this).find('.filename.new').before('<a href="' + id + '" class="button acf-select"><?php _e("Select File",'acf'); ?></a>');
			
			// add save changes button
			$(this).find('tr.submit input.button').hide().before('<input type="submit" value="<?php _e("Update File",'acf'); ?>" class="button savebutton" />');
			
		});
	}
	<?php
	
	// run the acf_add_buttons ever 500ms when on the file upload tab
	if( $options['tab'] == 'type' ): ?>
	var acf_t = setInterval(function(){
		add_buttons();
	}, 500);
	<?php endif; ?>
	
	
	// add acf input filters to allow for tab navigation
	$(document).ready(function(){
		
		setTimeout(function(){
			add_buttons();
		}, 1);
		
		
		$('form#filter').each(function(){
			
			$(this).append('<input type="hidden" name="acf_type" value="file" />');
						
		});
		
		$('form#image-form, form#library-form').each(function(){
			
			var action = $(this).attr('action');
			action += "&acf_type=file";
			$(this).attr('action', action);
			
		});
	});
				
})(jQuery);
</script><?php

	}
	
}

new acf_field_file();

?>