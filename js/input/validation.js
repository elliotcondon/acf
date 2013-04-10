/*
*  Validation
*
*  @description: 
*  @since: 3.5.8
*  @created: 17/01/13
*/

(function($){
	
	
	var _validation = acf.validation;
	
	
	/*
	*  do_validation
	*
	*  @description: checks fields for required input
	*  @created: 1/03/2011
	*/
	
	_validation.run = function(){
		
		_validation.status = true;
		
		$('.postbox:not(.acf-hidden) .field.required, .form-field.required').each(function(){
			
			// vars
			var div = $(this);
			
			
			// set validation data
			div.data('validation', true);
			

			// if is hidden by conditional logic, ignore
			if( div.hasClass('acf-conditional_logic-hide') )
			{
				return;
			}
			
			
			// text / textarea
			if( div.find('input[type="text"], input[type="number"], input[type="hidden"], textarea').val() == "" )
			{
				div.data('validation', false);
			}
			
			
			// wysiwyg
			if( div.find('.acf_wysiwyg').exists() && typeof(tinyMCE) == "object")
			{
				div.data('validation', true);
				
				var id = div.find('.wp-editor-area').attr('id'),
					editor = tinyMCE.get( id );


				if( editor && !editor.getContent() )
				{
					div.data('validation', false);
				}
			}
			
			
			// select
			if( div.find('select').exists() )
			{
				div.data('validation', true);

				if( div.find('select').val() == "null" || ! div.find('select').val() )
				{
					div.data('validation', false);
				}
			}

			
			// checkbox
			if( div.find('input[type="checkbox"]:checked').exists() )
			{
				div.data('validation', true);
			}
			
			
			// relationship
			if( div.find('.acf_relationship').exists() )
			{
				div.data('validation', false);
				
				if( div.find('.acf_relationship .relationship_right input').exists() )
				{
					div.data('validation', true);
				}
			}
			
			
			// repeater
			if( div.find('.repeater').exists() )
			{
				div.data('validation', false);
				
				if( div.find('.repeater tr.row').exists() )
				{
					div.data('validation', true);
				}			
			}
			
			
			// flexible content
			if( div.find('.acf_flexible_content').exists() )
			{
				div.data('validation', false);
				if( div.find('.acf_flexible_content .values table').exists() )
				{
					div.data('validation', true);
				}	
			}
			
			
			// gallery
			if( div.find('.acf-gallery').exists() )
			{
				div.data('validation', false);
				
				if( div.find('.acf-gallery .thumbnail').exists())
				{
					div.data('validation', true);
				}
			}
			
			
			// hook for custom validation
			$(document).trigger('acf/validate_field', div );
			
			
			// set validation
			if( ! div.data('validation') )
			{
				_validation.status = false;
				div.closest('.field').addClass('error');
			}
			

		});
		
		
	}
	
	
	/*
	*  Remove error class on focus
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/

	// inputs / textareas
	$('.field.required input, .field.required textarea, .field.required select').live('focus', function(){
		$(this).closest('.field').removeClass('error');
	});
	
	// checkbox
	$('.field.required input:checkbox').live('click', function(){
		$(this).closest('.field').removeClass('error');
	});

})(jQuery);