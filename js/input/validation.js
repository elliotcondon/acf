(function($){
	
	
	/*
	*  Validation
	*
	*  JS model
	*
	*  @type	object 
	*  @date	1/06/13
	*
	*/
	
	acf.validation = {
	
		status		: true,
		disabled	: false,
		
		run : function(){
			
			// reference
			var _this = this;
			
			
			// reset
			_this.status = true;
			
			
			// loop through all fields
			$('.field.required, .form-field.required').each(function(){
				
				// run validation
				_this.validate( $(this) );
				
	
			});
			// end loop through all fields
		},
		
		/*
		*  show_spinner
		*
		*  This function will show a spinner element. Logic changed in WP 4.2
		*
		*  @type	function
		*  @date	3/05/2015
		*  @since	5.2.3
		*
		*  @param	$spinner (jQuery)
		*  @return	n/a
		*/
		
		show_spinner: function( $spinner ){
			
			// bail early if no spinner
			if( !$spinner.exists() ) {
				
				return;
				
			}
			
			
			// vars
			var wp_version = acf.o.wp_version;
			
			
			// show
			if( parseFloat(wp_version) >= 4.2 ) {
				
				$spinner.addClass('is-active');
			
			} else {
				
				$spinner.css('display', 'inline-block');
			
			}
			
		},
		
		
		/*
		*  hide_spinner
		*
		*  This function will hide a spinner element. Logic changed in WP 4.2
		*
		*  @type	function
		*  @date	3/05/2015
		*  @since	5.2.3
		*
		*  @param	$spinner (jQuery)
		*  @return	n/a
		*/
		
		hide_spinner: function( $spinner ){
			
			// bail early if no spinner
			if( !$spinner.exists() ) {
				
				return;
				
			}
			
			
			// vars
			var wp_version = acf.o.wp_version;
			
			
			// hide
			if( parseFloat(wp_version) >= 4.2 ) {
				
				$spinner.removeClass('is-active');
			
			} else {
				
				$spinner.css('display', 'none');
			
			}
			
		},
		
		validate : function( div ){
			
			// var
			var ignore = false,
				$tab = null;
			
			
			// set validation data
			div.data('validation', true);
			
			
			// not visible
			if( div.is(':hidden') )
			{
				// ignore validation
				ignore = true;
				
				
				// if this field is hidden by a tab group, allow validation
				if( div.hasClass('acf-tab_group-hide') )
				{
					ignore = false;
					
					
					// vars
					var $tab_field = div.prevAll('.field_type-tab:first'),
						$tab_group = div.prevAll('.acf-tab-wrap:first');
					
					
					// if the tab itself is hidden, bypass validation
					if( $tab_field.hasClass('acf-conditional_logic-hide') )
					{
						ignore = true;
					}
					else
					{
						// activate this tab as it holds hidden required field!
						$tab = $tab_group.find('.acf-tab-button[data-key="' + $tab_field.attr('data-field_key') + '"]');
					}
				}
			}
			
			
			// if is hidden by conditional logic, ignore
			if( div.hasClass('acf-conditional_logic-hide') )
			{
				ignore = true;
			}
			
			
			// if field group is hidden, igrnoe
			if( div.closest('.postbox.acf-hidden').exists() ) {
				
				ignore = true;
				
			}
			
			
			if( ignore )
			{
				return;
			}
			
			
			
			// text / textarea
			if( div.find('input[type="text"], input[type="email"], input[type="number"], input[type="hidden"], textarea').val() == "" )
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

			
			// radio
			if( div.find('input[type="radio"]').exists() )
			{
				div.data('validation', false);

				if( div.find('input[type="radio"]:checked').exists() )
				{
					div.data('validation', true);
				}
			}
			
			
			// checkbox
			if( div.find('input[type="checkbox"]').exists() )
			{
				div.data('validation', false);

				if( div.find('input[type="checkbox"]:checked').exists() )
				{
					div.data('validation', true);
				}
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
			$(document).trigger('acf/validate_field', [ div ] );
			
			
			// set validation
			if( ! div.data('validation') )
			{
				// show error
				this.status = false;
				div.closest('.field').addClass('error');
				
				
				// custom validation message
				if( div.data('validation_message') )
				{
					var $label = div.find('p.label:first'),
						$message = null;
						
					
					// remove old message
					$label.children('.acf-error-message').remove();
					
					
					$label.append( '<span class="acf-error-message"><i class="bit"></i>' + div.data('validation_message') + '</span>' );
				}
				
				
				// display field (curently hidden due to another tab being active)
				if( $tab )
				{
					$tab.trigger('click');
				}
				
			}
		}
		
	};
	
	
	/*
	*  Events
	*
	*  Remove error class on focus
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('focus click', '.field.required input, .field.required textarea, .field.required select', function( e ){
	
		$(this).closest('.field').removeClass('error');
		
	});
	
	
	/*
	$(document).on('blur change', '.field.required input, .field.required textarea, .field.required select', function( e ){
		
			acf.validation.validate( $(this).closest('.field') );
			
		});
	*/
	
	
	/*
	*  Save Post
	*
	*  If user is saving a draft, allow them to bypass the validation
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('click', '#save-post', function(){
		
		acf.validation.disabled = true;
		
	});
	
	
	/*
	*  Submit Post
	*
	*  Run validation and return true|false accordingly
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('submit', '#post', function(){
		
		// If disabled, bail early on the validation check
		if( acf.validation.disabled )
		{
			return true;
		}
		
		
		// do validation
		acf.validation.run();
			
			
		if( ! acf.validation.status ) {
			
			// vars
			var $form = $(this);
			
			
			// show message
			$form.siblings('#message').remove();
			$form.before('<div id="message" class="error"><p>' + acf.l10n.validation.error + '</p></div>');
			
			
			// hide ajax stuff on submit button
			if( $('#submitdiv').exists() ) {
				
				// remove disabled classes
				$('#submitdiv').find('.disabled').removeClass('disabled');
				$('#submitdiv').find('.button-disabled').removeClass('button-disabled');
				$('#submitdiv').find('.button-primary-disabled').removeClass('button-primary-disabled');
				
				
				// remove spinner
				acf.validation.hide_spinner( $('#submitdiv .spinner') );
				
			}
			
			return false;
		}

		
		// remove hidden postboxes
		// + this will stop them from being posted to save
		$('.acf_postbox.acf-hidden').remove();
		

		// submit the form
		return true;
		
	});
	

})(jQuery);