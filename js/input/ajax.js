(function($){
	
	
	/*
	*  acf.screen
	*
	*  Data used by AJAX to hide / show field groups
	*
	*  @type	object
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	acf.screen = {
		action 			:	'acf/location/match_field_groups_ajax',
		post_id			:	0,
		page_template	:	0,
		page_parent		:	0,
		page_type		:	0,
		post_category	:	0,
		post_format		:	0,
		taxonomy		:	0,
		lang			:	0,
		nonce			:	0
	};
	
	
	/*
	*  Document Ready
	*
	*  Updates acf.screen with more data
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).ready(function(){
		
		
		// update post_id
		acf.screen.post_id = acf.o.post_id;
		acf.screen.nonce = acf.o.nonce;
		
		
		// MPML
		if( $('#icl-als-first').length > 0 )
		{
			var href = $('#icl-als-first').children('a').attr('href'),
				regex = new RegExp( "lang=([^&#]*)" ),
				results = regex.exec( href );
			
			// lang
			acf.screen.lang = results[1];
			
		}
		
	});
	
	
	/*
	*  acf/update_field_groups
	*
	*  finds the new id's for metaboxes and show's hides metaboxes
	*
	*  @type	event
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('acf/update_field_groups', function(){
		
		// Only for a post.
		// This is an attempt to stop the action running on the options page add-on.
		if( ! acf.screen.post_id || ! $.isNumeric(acf.screen.post_id) )
		{
			return false;	
		}
		
		
		$.ajax({
			url: ajaxurl,
			data: acf.screen,
			type: 'post',
			dataType: 'json',
			success: function(result){
				
				// validate
				if( !result )
				{
					return false;
				}
				
				
				// hide all metaboxes
				$('.acf_postbox').addClass('acf-hidden');
				$('.acf_postbox-toggle').addClass('acf-hidden');
		
				
				// dont bother loading style or html for inputs
				if( result.length == 0 )
				{
					return false;
				}
				
				
				// show the new postboxes
				$.each(result, function(k, v) {
					
					
					// vars
					var $el = $('#acf_' + v),
						$toggle = $('#adv-settings .acf_postbox-toggle[for="acf_' + v + '-hide"]');
					
					
					// classes
					$el.removeClass('acf-hidden hide-if-js');
					$toggle.removeClass('acf-hidden');
					$toggle.find('input[type="checkbox"]').attr('checked', 'checked');
					
					
					// load fields if needed
					$el.find('.acf-replace-with-fields').each(function(){
						
						var $replace = $(this);
						
						$.ajax({
							url			:	ajaxurl,
							data		:	{
								action	:	'acf/post/render_fields',
								acf_id	:	v,
								post_id	:	acf.o.post_id,
								nonce	:	acf.o.nonce
							},
							type		:	'post',
							dataType	:	'html',
							success		:	function( html ){
							
								$replace.replaceWith( html );
								
								$(document).trigger('acf/setup_fields', $el);
								
							}
						});
						
					});
				});
				
				
				// load style
				$.ajax({
					url			:	ajaxurl,
					data		:	{
						action	:	'acf/post/get_style',
						acf_id	:	result[0],
						nonce	:	acf.o.nonce
					},
					type		: 'post',
					dataType	: 'html',
					success		: function( result ){
					
						$('#acf_style').html( result );
						
					}
				});
				
				
				
			}
		});
	});

	
	/*
	*  Events
	*
	*  Updates acf.screen with more data and triggers the update event
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('change', '#page_template', function(){
		
		acf.screen.page_template = $(this).val();
		
		$(document).trigger('acf/update_field_groups');
	    
	});
	
	
	$(document).on('change', '#parent_id', function(){
		
		var val = $(this).val();
		
		
		// set page_type / page_parent
		if( val != "" )
		{
			acf.screen.page_type = 'child';
			acf.screen.page_parent = val;
		}
		else
		{
			acf.screen.page_type = 'parent';
			acf.screen.page_parent = 0;
		}
		
		
		$(document).trigger('acf/update_field_groups');
	    
	});

	
	$(document).on('change', '#post-formats-select input[type="radio"]', function(){
		
		var val = $(this).val();
		
		if( val == '0' )
		{
			val = 'standard';
		}
		
		acf.screen.post_format = val;
		
		$(document).trigger('acf/update_field_groups');
		
	});	
	
	
	function _sync_taxonomy_terms() {
		
		// vars
		var values = [];
		
		
		$('.categorychecklist input:checked, .acf-taxonomy-field input:checked, .acf-taxonomy-field option:selected').each(function(){
			
			// validate
			if( $(this).is(':hidden') || $(this).is(':disabled') )
			{
				return;
			}
			
			
			// validate media popup
			if( $(this).closest('.media-frame').exists() )
			{
				return;
			}
			
			
			// validate acf
			if( $(this).closest('.acf-taxonomy-field').exists() )
			{
				if( $(this).closest('.acf-taxonomy-field').attr('data-save') == '0' )
				{
					return;
				}
			}
			
			
			// append
			if( values.indexOf( $(this).val() ) === -1 )
			{
				values.push( $(this).val() );
			}
			
		});

		
		// update screen
		acf.screen.post_category = values;
		acf.screen.taxonomy = values;

		
		// trigger change
		$(document).trigger('acf/update_field_groups');
			
	}
	
	
	$(document).on('change', '.categorychecklist input, .acf-taxonomy-field input, .acf-taxonomy-field select', function(){
		
		// a taxonomy field may trigger this change event, however, the value selected is not
		// actually a term relatinoship, it is meta data
		if( $(this).closest('.acf-taxonomy-field').exists() )
		{
			if( $(this).closest('.acf-taxonomy-field').attr('data-save') == '0' )
			{
				return;
			}
		}
		
		
		// this may be triggered from editing an imgae in a popup. Popup does not support correct metaboxes so ignore this
		if( $(this).closest('.media-frame').exists() )
		{
			return;
		}
		
		
		// set timeout to fix issue with chrome which does not register the change has yet happened
		setTimeout(function(){
			
			_sync_taxonomy_terms();
		
		}, 1);
		
		
	});
	
	
	
	
})(jQuery);