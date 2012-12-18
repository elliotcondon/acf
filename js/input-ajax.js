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
		action 			:	'get_input_metabox_ids',
		post_id			:	0,
		page_template	:	false,
		page_parent		:	0,
		page_type		:	false,
		page			:	0,
		post			:	0,
		post_category	:	false,
		post_format		:	false,
		post_template	:	false,
		taxonomy		:	false
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
		acf.data.page = acf.post_id;
		acf.data.post = acf.post_id;
		
		
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
	*  update_fields
	*
	*  @description: finds the new id's for metaboxes and show's hides metaboxes
	*  @created: 1/03/2011
	*/
	
	function update_fields()
	{

		$.ajax({
			url: ajaxurl,
			data: acf.data,
			type: 'post',
			dataType: 'json',
			success: function(result){
				
				// hide all metaboxes
				$('#poststuff .acf_postbox').addClass('acf-hidden');
				$('#adv-settings .acf_hide_label').hide();
				
				
				// dont bother loading style or html for inputs
				if(result.length == 0)
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
								action : 'acf_input',
								acf_id : v,
								post_id : acf.post_id
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
						action : 'get_input_style',
						acf_id : result[0]
					},
					type: 'post',
					dataType: 'html',
					success: function(result){
					
						$('#acf_style').html(result);
						
					}
				});
				
			}
		});
	}

	
	/*
	*  update_fields (Live change events)
	*
	*  @description: call the update_fields function on live events
	*  @created: 1/03/2011
	*/
		
	$('#page_template').live('change', function(){
		
		acf.data.page_template = $(this).val();
		update_fields();
	    
	});
	
	$('#custom_post_template').live('change', function(){
		
		acf.data.post_template = $(this).val();
		update_fields();
		
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
		
		update_fields();
	    
	});

	
	$('#post-formats-select input[type="radio"]').live('change', function(){
		
		acf.data.post_format = $(this).val();
		update_fields();
		
	});	
	
	
	// taxonomy / category
	$('.categorychecklist input[type="checkbox"]').live('change', function(){
		
		
		// vars
		var values = ['0'];
		
		
		$('.categorychecklist input[type="checkbox"]:checked').each(function(){
			values.push($(this).val());
		});

		
		acf.data.post_category = values;
		acf.data.taxonomy = values;


		update_fields();
		
	});
	
	
	
})(jQuery);