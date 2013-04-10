/*
*  Image
*
*  @description: 
*  @since: 3.5.8
*  @created: 17/01/13
*/

(function($){
	
	var _image = acf.fields.image,
		_media = acf.media;
		
	
	/*
	*  Add
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	_image.add = function( image )
	{
		// vars
		var div = _media.div;
		
		// set atts
		div.find('.acf-image-value').val( image.id ).trigger('change');
	 	div.find('img').attr( 'src', image.src );
	 	
	 	
	 	// set div class
	 	div.addClass('active');
	 	
	 	
	 	// validation
		div.closest('.field').removeClass('error');
	
	};
	
	
	/*
	*  Edit
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	_image.edit = function(){
		
		// vars
		var div = _media.div,
			id = div.find('.acf-image-value').val();
		
		
		// show edit attachment
		tb_show( _image.text.title_edit , acf.admin_url + 'media.php?attachment_id=' + id + '&action=edit&acf_action=edit_attachment&acf_field=image&TB_iframe=1');
		
	};
	
	
	/*
	*  Remove
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	_image.remove = function()
	{
		// vars
		var div = _media.div;
		
		
		// remove atts
		div.find('.acf-image-value').val('').trigger('change');
		div.find('img').attr('src', '');
		
		
		// remove class
		div.removeClass('active');
		
	};
	
	
	/*
	*  Add Button
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	$('.acf-image-uploader .add-image').live('click', function(){
				
		// vars
		var div = _media.div = $(this).closest('.acf-image-uploader'),
			preview_size = div.attr('data-preview_size'),
			multiple = div.closest('.repeater').exists() ? true : false;
			
			
		// show the thickbox
		if( _media.type() == 'backbone' )
		{
			// clear the frame
			_media.clear_frame();
			
			
		    // Create the media frame. Leave options blank for defaults
			_media.frame = wp.media({
				title : _image.text.title_add,
				multiple : multiple,
				library : {
					type : 'image'
				},
			});

			
			// add filter by overriding the option when the title is being created. This is an evet fired before the rendering / creating of the library content so it works but is a bit of a hack. In the future, this should be changed to an init / options event
			_media.frame.on('title:create', function(){
				var state = _media.frame.state();
				state.set('filterable', 'uploaded');
			});
			
			
			// When an image is selected, run a callback.
			_media.frame.on( 'select', function() {
				
				// get selected images
				selection = _media.frame.state().get('selection');
				
				if( selection )
				{
					var i = 0;
					
					selection.each(function(attachment){
	
				    	// counter
				    	i++;
				    	
				    	
				    	// select / add another file field?
				    	if( i > 1 )
						{
							var tr = _media.div.closest('tr'),
								repeater = tr.closest('.repeater');
							
							
							if( tr.next('.row').exists() )
							{
								_media.div = tr.next('.row').find('.acf-image-uploader');
							}
							else
							{
								// add row 
				 				repeater.find('.add-row-end').trigger('click'); 
				 			 
				 				// set acf_div to new row file 
				 				_media.div = repeater.find('> table > tbody > tr.row:last .acf-image-uploader');
							}
						}
						
						
				    	// vars
				    	var image = {
					    	id : attachment.id,
					    	src : attachment.attributes.url
				    	};
				    	
				
				    	// is preview size available?
				    	if( attachment.attributes.sizes && attachment.attributes.sizes[ preview_size ] )
				    	{
					    	image.src = attachment.attributes.sizes[ preview_size ].url;
				    	}
				    	
				    	
				    	// add image to field
				        _image.add( image );
				        
						
				    });
				    // selection.each(function(attachment){
				}
				// if( selection )
			});
			// _media.frame.on( 'select', function() {
					 
				
			// Finally, open the modal
			_media.frame.open();
				
		}
		else
		{
			tb_show( _image.text.title_add , acf.admin_url + 'media-upload.php?post_id=' + acf.post_id + '&post_ID=' + acf.post_id + '&type=image&acf_type=image&acf_preview_size=' + preview_size + 'TB_iframe=1');
		}
		
	
		return false;
	});
	
	
	/*
	*  Edit Button
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	$('.acf-image-uploader .acf-button-edit').live('click', function(){
		
		// vars
		_media.div = $(this).closest('.acf-image-uploader');
				
		_image.edit();
		
		return false;
			
	});
	
	
	/*
	*  Remove Button
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	$('.acf-image-uploader .acf-button-delete').live('click', function(){
		
		// vars
		_media.div = $(this).closest('.acf-image-uploader');
				
		_image.remove();
		
		return false;
			
	});
	

})(jQuery);