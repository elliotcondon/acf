/*
*  Input Ajax
*
*  @description: show / hide metaboxes from changing category / tempalte / etc		
*  @author: Elliot Condon
*  @since: 3.1.4
*/

(function($){
	
		
	/*
	*  Exists
	*
	*  @description: returns true / false		
	*  @created: 1/03/2011
	*/
	
	$.fn.exists = function()
	{
		return $(this).length>0;
	};
	
	
	/*
	*  Vars
	*
	*  @description: 
	*  @created: 3/09/12
	*/
	
	acf.data = {
		'action' 			:	'acf/location/match_field_groups_ajax',
		'post_id'			:	0,
		'page_template'		:	0,
		'page_parent'		:	0,
		'page_type'			:	0,
		'post_category'		:	0,
		'post_format'		:	0,
		'taxonomy'			:	0,
		'lang'				:	0,
		'nonce'				:	0,
		'return'			:	'json'
	};
	
		
	/*
	*  Document Ready
	*
	*  @description: adds ajax data		
	*  @created: 1/03/2011
	*/
	
	$(document).ready(function(){
		
		
		// update post_id
		acf.data.post_id = acf.post_id;
		acf.data.nonce = acf.nonce;
		
		
		// MPML
		if( $('#icl-als-first').exists() )
		{
			var href = $('#icl-als-first').children('a').attr('href'),
				regex = new RegExp( "lang=([^&#]*)" ),
				results = regex.exec( href );
			
			// lang
			acf.data.lang = results[1];
			
		}
		
	});
	
	
	/*
	*  update_field_groups
	*
	*  @description: finds the new id's for metaboxes and show's hides metaboxes
	*  @created: 1/03/2011
	*/
	
	$(document).live('acf/update_field_groups', function(){
		
		$.ajax({
			url: ajaxurl,
			data: acf.data,
			type: 'post',
			dataType: 'json',
			success: function(result){
				
				// validate
				if( !result )
				{
					return false;
				}
				
				
				// hide all metaboxes
				$('#poststuff .acf_postbox').addClass('acf-hidden');
				$('#adv-settings .acf_hide_label').hide();
				
				
				// dont bother loading style or html for inputs
				if( result.length == 0 )
				{
					return false;
				}
				
				
				// show the new postboxes
				$.each(result, function(k, v) {
					
					
					var postbox = $('#poststuff #acf_' + v);
					
					postbox.removeClass('acf-hidden');
					$('#adv-settings .acf_hide_label[for="acf_' + v + '-hide"]').show();
					
					// load fields if needed
					postbox.find('.acf-replace-with-fields').each(function(){
						
						var div = $(this);
						
						$.ajax({
							url: ajaxurl,
							data: {
								action : 'acf/input/render_fields',
								acf_id : v,
								post_id : acf.post_id,
								nonce : acf.nonce
							},
							type: 'post',
							dataType: 'html',
							success: function(html){
							
								div.replaceWith(html);
								
								$(document).trigger('acf/setup_fields', postbox);
								
							}
						});
						
					});
				});
				
				// load style
				$.ajax({
					url: ajaxurl,
					data: {
						action : 'acf/input/get_style',
						acf_id : result[0],
						nonce : acf.nonce
					},
					type: 'post',
					dataType: 'html',
					success: function(result){
					
						$('#acf_style').html(result);
						
					}
				});
				
			}
		});
	});

	
	/*
	*  $(document).trigger('acf/update_field_groups'); (Live change events)
	*
	*  @description: call the $(document).trigger('acf/update_field_groups'); event on live events
	*  @created: 1/03/2011
	*/
		
	$('#page_template').live('change', function(){
		
		acf.data.page_template = $(this).val();
		
		$(document).trigger('acf/update_field_groups');
	    
	});
	
	
	$('#parent_id').live('change', function(){
		
		var val = $(this).val();
		
		
		// set page_type / page_parent
		if( val != "" )
		{
			acf.data.page_type = 'child';
			acf.data.page_parent = val;
		}
		else
		{
			acf.data.page_type = 'parent';
			acf.data.page_parent = 0;
		}
		
		
		$(document).trigger('acf/update_field_groups');
	    
	});

	
	$('#post-formats-select input[type="radio"]').live('change', function(){
		
		var val = $(this).val();
		
		if( val == '0' )
		{
			val = 'standard';
		}
		
		acf.data.post_format = val;
		
		$(document).trigger('acf/update_field_groups');
		
	});	
	
	
	// taxonomy / category
	$('.categorychecklist input[type="checkbox"]').live('change', function(){
		
		
		// vars
		var values = [];
		
		
		$('.categorychecklist input[type="checkbox"]:checked').each(function(){
			values.push( $(this).val() );
		});

		
		acf.data.post_category = values;
		acf.data.taxonomy = values;


		$(document).trigger('acf/update_field_groups');
		
	});
	
	
	
})(jQuery);