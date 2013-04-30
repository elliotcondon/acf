/*
*  Date Picker
*
*  @description: 
*  @since: 3.5.8
*  @created: 17/01/13
*/

(function($){

	var _date_picker = acf.fields.date_picker;
	
	
	/*
	*  acf/setup_fields
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	$(document).live('acf/setup_fields', function(e, postbox){
		
		$(postbox).find('input.acf_datepicker').each(function(){
			
			// vars
			var input = $(this),
				alt_field = input.siblings('.acf-hidden-datepicker'),
				save_format = input.attr('data-save_format'),
				display_format = input.attr('data-display_format'),
				first_day = input.attr('data-first_day');
			
			
			// is clone field?
			if( acf.helpers.is_clone_field(alt_field) )
			{
				return;
			}
			
			
			// get and set value from alt field
			input.val( alt_field.val() );
			
			
			// create options
			var options = $.extend( {}, _date_picker.text, { 
				dateFormat : save_format,
				altField : alt_field,
				altFormat :  save_format,
				changeYear: true,
				yearRange: "-100:+100",
				changeMonth: true,
				showButtonPanel : true,
				firstDay: first_day
			});
			
			
			// add date picker and refocus
			input.addClass('active').datepicker(options);
			
			
			// now change the format back to how it should be.
			input.datepicker( "option", "dateFormat", display_format );
			
			
			// wrap the datepicker (only if it hasn't already been wrapped)
			if($('body > #ui-datepicker-div').length > 0)
			{
				$('#ui-datepicker-div').wrap('<div class="ui-acf" />');
			}
			
			
			// allow null
			input.blur(function(){
				
				if( !input.val() )
				{
					alt_field.val('');
				}
				
			});
			
		});
		
	});
	

})(jQuery);