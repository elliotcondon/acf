/*
*  Input Actions
*
*  @description: javascript for fields functionality		
*  @author: Elliot Condon
*  @since: 3.1.4
*/

var acf = {
	ajaxurl : '',
	admin_url : '',
	wp_version : '0',
	post_id : 0,
	nonce : '',
	validation : {
		status : true,
		run : function(){},
		text : {
			error : "Validation Failed. One or more fields below are required."
		}
	},
	helpers : {
		version_compare : function(){},
		uniqid : function(){},
		sortable : function(){},
		add_message : function(){},
		is_clone_field : function(){},
		url_to_object : function(){}
	},
	conditional_logic : {},
	media : {
		div : null,
		frame : null,
		clear_frame : function(){},
		type : function(){}
	},
	fields : {
		date_picker : {
			text : {}	
		},
		color_picker : {
			farbtastic : null
		},
		image : {
			add : function(){},
			edit : function(){},
			remove : function(){},
			text : {
				title_add : "Select Image",
				title_edit : "Edit Image"
			}
		},
		file : {
			add : function(){},
			edit : function(){},
			remove : function(){},
			text : {
				title_add : "Select File",
				title_edit : "Edit File"
			}
		},
		wysiwyg : {
			toolbars : {},
			has_tinymce : function(){},
			add_tinymce : function(){},
			add_events : function(){},
			remove_tinymce : function(){}
		},
		gallery : {
			add : function(){},
			edit : function(){},
			update_count : function(){},
			hide_selected_items : function(){},
			text : {
				title_add : "Select Images"
			}
		},
		relationship : {
			timeout : null,
			update_results : function(){},
			hide_results : function(){},
			text : {
				max : "Maximum values reached ( {max} values )"
			}
		}
	}
};

(function($){
	
	
	/**
	 * Simply compares two string version values.
	 * 
	 * Example:
	 * versionCompare('1.1', '1.2') => -1
	 * versionCompare('1.1', '1.1') =>  0
	 * versionCompare('1.2', '1.1') =>  1
	 * versionCompare('2.23.3', '2.22.3') => 1
	 * 
	 * Returns:
	 * -1 = left is LOWER than right
	 *  0 = they are equal
	 *  1 = left is GREATER = right is LOWER
	 *  And FALSE if one of input versions are not valid
	 *
	 * @function
	 * @param {String} left  Version #1
	 * @param {String} right Version #2
	 * @return {Integer|Boolean}
	 * @author Alexey Bass (albass)
	 * @since 2011-07-14
	 */
	 
	acf.helpers.version_compare = function(left, right)
	{
	    if (typeof left + typeof right != 'stringstring')
	        return false;
	    
	    var a = left.split('.')
	    ,   b = right.split('.')
	    ,   i = 0, len = Math.max(a.length, b.length);
	        
	    for (; i < len; i++) {
	        if ((a[i] && !b[i] && parseInt(a[i]) > 0) || (parseInt(a[i]) > parseInt(b[i]))) {
	            return 1;
	        } else if ((b[i] && !a[i] && parseInt(b[i]) > 0) || (parseInt(a[i]) < parseInt(b[i]))) {
	            return -1;
	        }
	    }
	    
	    return 0;
	};
	
	
	/*
	*  Helper: uniqid
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	acf.helpers.uniqid = function()
    {
    	var newDate = new Date;
    	return newDate.getTime();
    };
    
    
    /*
	*  Helper: url_to_object
	*
	*  @description: 
	*  @since: 4.0.0
	*  @created: 17/01/13
	*/
	
    acf.helpers.url_to_object = function( url ){
	    
	    // vars
	    var obj = {},
	    	pairs = url.split('&');
	    
	    
		for( i in pairs )
		{
		    var split = pairs[i].split('=');
		    obj[decodeURIComponent(split[0])] = decodeURIComponent(split[1]);
		}
		
		return obj;
	    
    };
    
	
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
	*  3.5 Media
	*
	*  @description: 
	*  @since: 3.5.7
	*  @created: 16/01/13
	*/
	
	acf.media.clear_frame = function()
	{
		// validate
		if( !acf.media.frame )
		{
			return;
		}
		
		
		acf.media.frame.detach();
		acf.media.frame.dispose();
		acf.media.frame = null;
		
	};
	
	acf.media.type = function(){
		
		var type = 'thickbox';
		
		if( typeof(wp) == "object" )
		{
			type = 'backbone';
		}
		
		return type;
		
	};
	
	
	
	/*
	*  Document Ready
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	$(document).ready(function(){
		
		// add classes
		$('#poststuff .postbox[id*="acf_"]').addClass('acf_postbox');
		$('#adv-settings label[for*="acf_"]').addClass('acf_hide_label');
		
		// hide acf stuff
		$('#poststuff .acf_postbox').addClass('acf-hidden');
		$('#adv-settings .acf_hide_label').hide();
		
		// loop through acf metaboxes
		$('#poststuff .postbox.acf_postbox').each(function(){
			
			// vars
			var options = $(this).find('> .inside > .options'),
				show = options.attr('data-show'),
				layout = options.attr('data-layout'),
				id = $(this).attr('id').replace('acf_', '');
			
			// layout
			$(this).addClass(layout);
			
			// show / hide
			if( show == "1" )
			{
				$(this).removeClass('acf-hidden');
				$('#adv-settings .acf_hide_label[for="acf_' + id + '-hide"]').show();
			}
			
		});
	
	});

	
	/*
	*  Save Draft
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	var save_post = false;
	$('#save-post').live('click', function(){
		
		save_post = true;
		
	});
	
	
	/*
	*  Submit form
	*
	*  @description: does validation, deletes all hidden metaboxes (otherwise, post data will be overriden by hidden inputs)
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	$('form#post').live('submit', function(){
			
		if( ! save_post )
		{
			// do validation
			acf.validation.run();
			
			
			if( ! acf.validation.status )
			{
				// show message
				$(this).siblings('#message').remove();
				$(this).before('<div id="message" class="error"><p>' + acf.validation.text.error + '</p></div>');
				
				
				// hide ajax stuff on submit button
				$('#publish').removeClass('button-primary-disabled');
				$('#ajax-loading').attr('style','');
				$('#publishing-action .spinner').hide();
				
				return false;
			}
		}

		
		// remove hidden postboxes
		$('.acf_postbox.acf-hidden').remove();
		

		// submit the form
		return true;
		
	});
	

	/*
	*  Sortable Helper
	*
	*  @description: keeps widths of td's inside a tr
	*  @since 3.5.1
	*  @created: 10/11/12
	*/
	
	acf.helpers.sortable = function(e, ui)
	{
		ui.children().each(function(){
			$(this).width($(this).width());
		});
		return ui;
	};
	
	
	/*
	*  is_clone_field
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	acf.helpers.is_clone_field = function( input )
	{
		if( input.attr('name') && input.attr('name').indexOf('[acfcloneindex]') != -1 )
		{
			return true;
		}
		
		return false;
	}
	
	
	/*
	*  acf.helpers.add_message
	*
	*  @description: 
	*  @since: 3.2.7
	*  @created: 10/07/2012
	*/
	
	acf.helpers.add_message = function( message, div ){
		
		var message = $('<div class="acf-message-wrapper"><div class="message updated"><p>' + message + '</p></div></div>');
		
		div.prepend( message );
		
		setTimeout(function(){
			
			message.animate({
				opacity : 0
			}, 250, function(){
				message.remove();
			});
			
		}, 1500);
			
	};
	
	
	
	/*
	*  Conditional Logic Calculate
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 15/10/12
	*/
	
	acf.conditional_logic.calculate = function( options )
	{
		// vars
		var field = $('.field_key-' + options.field),
			toggle = $('.field_key-' + options.toggle),
			r = false;
		
		
		// compare values
		if( toggle.hasClass('field_type-true_false') || toggle.hasClass('field_type-checkbox') || toggle.hasClass('field_type-radio') )
		{
			if( options.operator == "==" )
			{
				if( toggle.find('input[value="' + options.value + '"]:checked').exists() )
				{
					r = true;
				}
			}
			else
			{
				if( !toggle.find('input[value="' + options.value + '"]:checked').exists() )
				{
					r = true;
				}
			}
			
		}
		else
		{
			// get val and make sure it is an array
			var val = toggle.find('*[name]:last').val();
			if( !$.isArray(val) )
			{
				val = [ val ];
			}
			
			
			if( options.operator == "==" )
			{
				if( $.inArray(options.value, val) > -1 )
				{
					r = true;
				}
			}
			else
			{
				if( $.inArray(options.value, val) < 0 )
				{
					r = true;
				}
			}
			
		}
		
		return r;
	}
	
	
	/*
	*  window load
	*
	*  @description: 
	*  @since: 3.5.5
	*  @created: 22/12/12
	*/
	
	$(window).load(function(){
		
		setTimeout(function(){
			
			// Hack for CPT without a content editor
			try
			{
				wp.media.view.settings.post.id = acf.post_id;	
			} 
			catch(e)
			{
				// one of the objects was 'undefined'...
			}
			

			
			// setup fields
			$(document).trigger('acf/setup_fields', $('#poststuff'));
			
		}, 10);
		
	});
	
	
})(jQuery);