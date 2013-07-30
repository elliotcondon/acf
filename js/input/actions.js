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
		get_atts : function(){},
		version_compare : function(){},
		uniqid : function(){},
		sortable : function(){},
		add_message : function(){},
		is_clone_field : function(){},
		url_to_object : function(){}
	},
	conditional_logic : {},
	media : {},
	fields : {
		date_picker : {},
		color_picker : {},
		image : {},
		file : {},
		wysiwyg : {},
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
	
	
	/*
	*  acf.helpers.isset
	*
	*  http://phpjs.org/functions/isset
	*
	*  @type	function
	*  @date	20/07/13
	*
	*  @param	{mixed}		arguments
	*  @return	{boolean}	
	*/
	
	acf.helpers.isset = function(){
		
		var a = arguments,
	        l = a.length,
	        i = 0,
	        undef;
	
	    if (l === 0) {
	        throw new Error('Empty isset');
	    }
	
	    while (i !== l) {
	        if (a[i] === undef || a[i] === null) {
	            return false;
	        }
	        i++;
	    }
	    return true;
			
	};
	
	
	/*
	*  acf.helpers.get_atts
	*
	*  description
	*
	*  @type	function
	*  @date	1/06/13
	*
	*  @param	{el}		$el
	*  @return	{object}	atts
	*/
	
	acf.helpers.get_atts = function( $el ){
		
		var atts = {};
		
		$.each( $el[0].attributes, function( index, attr ) {
        	
        	if( attr.name.substr(0, 5) == 'data-' )
        	{
	        	atts[ attr.name.replace('data-', '') ] = attr.value;
        	}
        });
        
        return atts;
			
	};
        
           
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
	
	acf.media = {
	
		div : null,
		frame : null,
		
		clear_frame : function(){
			
			// validate
			if( !this.frame )
			{
				return;
			}
			
			
			// detach
			this.frame.detach();
			this.frame.dispose();
			
			
			// reset var
			this.frame = null;
			
		},
		type : function(){
			
			// default
			var type = 'thickbox';
			
			
			// if wp exists
			if( typeof(wp) == "object" )
			{
				type = 'backbone';
			}
			
			
			// return
			return type;
			
		},
		init : function(){
			
			// vars
			var _prototype = wp.media.view.AttachmentCompat.prototype;
			
			
			// orig
			_prototype.orig_render = _prototype.render;
			
			
			// update class
			_prototype.className = 'compat-item acf_postbox no_box';
			
			
			// modify render
			_prototype.render = function() {
				
				// vars
				var _this = this;
				
				
				if( _this.ignore_render )
				{
					return this;	
				}
				
				
				// run the old render function
				this.orig_render();
				
				
				// add button
				setTimeout(function(){
					
					// vars
					var $media_model = _this.$el.closest('.media-modal');
					
					
					// is this an edit only modal?
					if( $media_model.hasClass('acf-media-modal') )
					{
						return;	
					}
					
					
					// does button already exist?
					if( $media_model.find('.media-frame-router .acf-expand-details').exists() )
					{
						return;	
					}
					
					
					// create button
					var button = $([
						'<a href="#" class="acf-expand-details">',
							'<span class="icon"></span>',
							'<span class="is-closed">' + acf.l10n.core.expand_details +  '</span>',
							'<span class="is-open">' + acf.l10n.core.collapse_details +  '</span>',
						'</a>'
					].join('')); 
					
					
					// add events
					button.on('click', function( e ){
						
						e.preventDefault();
						
						if( $media_model.hasClass('acf-expanded') )
						{
							$media_model.removeClass('acf-expanded');
						}
						else
						{
							$media_model.addClass('acf-expanded');
						}
						
					});
					
					
					// append
					$media_model.find('.media-frame-router').append( button );
						
				
				}, 0);
 
				// add in ACF render!
				// + WP must be caching the HTML to be rendered. When you select an image, select a different image, then seelct the origional image again, the same ID is found on the WYSIWYG and it doesn't render...
				// + Failed: Edit the wysiwyg.js file and use mceAddControl before adding it... perhaps some sort of destroy instead?
				setTimeout(function(){
					$(document).trigger( 'acf/setup_fields', _this.$el );
				}, 50);
				
				
				
				// return based on the origional render function
				return this;
			};
			
			
			// override save
			_prototype.save = function( event ) {
			
				var data = {},
					names = {};
				
				if ( event )
					event.preventDefault();
					
					
				_.each( this.$el.serializeArray(), function( pair ) {
				
					// initiate name
					if( pair.name.slice(-2) === '[]' )
					{
						// remove []
						pair.name = pair.name.replace('[]', '');
						
						
						// initiate counter
						if( typeof names[ pair.name ] === 'undefined'){
							
							names[ pair.name ] = -1;
							//console.log( names[ pair.name ] );
							
						}
						
						
						names[ pair.name ]++
						
						pair.name += '[' + names[ pair.name ] +']';
						
						
					}
 
					data[ pair.name ] = pair.value;
				});
 
				this.ignore_render = true;
				this.model.saveCompat( data );
				
			};
		}
	};
	
	
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
	};
	
	
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
	
	acf.conditional_logic = {
		
		items : [],
		
		init : function(){
			
			// reference
			var _this = this;
			
			
			// events
			$(document).on('change', '.field input, .field textarea, .field select', function(){
				
				_this.change();
				
			});
			
			
			_this.change();
			
		},
		change : function(){
			
			// reference
			var _this = this;
			
			
			// vars
			//var $el		=	$( e.target ),
			//	$field	=	$el.closest('.field');
			
			
			// loop through items
			$.each(this.items, function( k, item ){
				
				// vars
				var show	=	true,
					$field	=	$('.field_key-' + item.field);
				
				
				// if 'any' was selected, start of as false and any match will result in show = true
				if( item.allorany == 'any' )
				{
					show = false;
				}
				
				
				// loop through rules
				$.each(item.rules, function( k2, rule ){
					
					var calculate = _this.calculate( rule );
					
					if( item.allorany == 'all' )
					{
						if( calculate == false )
						{
							show = false;
							
							// end loop
							return false;
						}
					}
					else
					{
						if( calculate == true )
						{
							show = true;
							
							// end loop
							return false;
						}
					}
					
				});
				
				
				// hide / show field
				if( show )
				{
					// remove "disabled"
					$field.find('input, textarea, select').removeAttr('disabled');
					
					
					$field.removeClass('acf-conditional_logic-hide').addClass('acf-conditional_logic-show');
				}
				else
				{
					// add "disabled"
					$field.find('input, textarea, select').attr('disabled', 'disabled');
					
					
					$field.removeClass('acf-conditional_logic-show').addClass('acf-conditional_logic-hide');
				}
				
				
			});
			
		},
		calculate : function( rule ){
			
			// vars
			var $field	=	$('.field_key-' + rule.field),
				r		=	false;
			
			
			// compare values
			if( $field.hasClass('field_type-true_false') || $field.hasClass('field_type-checkbox') || $field.hasClass('field_type-radio') )
			{
				var exists = $field.find('input[value="' + rule.value + '"]:checked').exists();
				
				
				if( rule.operator == "==" )
				{
					if( exists )
					{
						r = true;
					}
				}
				else
				{
					if( ! exists )
					{
						r = true;
					}
				}
				
			}
			else
			{
				// get val and make sure it is an array
				var val = $field.find('input, textarea, select').last().val();
				
				if( ! $.isArray(val) )
				{
					val = [ val ];
				}
				
				
				if( rule.operator == "==" )
				{
					if( $.inArray(rule.value, val) > -1 )
					{
						r = true;
					}
				}
				else
				{
					if( $.inArray(rule.value, val) < 0 )
					{
						r = true;
					}
				}
				
			}
			
			
			// return
			return r;
			
		}
		
	};
	
	
	
	
		
	/*
	*  Document Ready
	*
	*  @description: 
	*  @since: 3.5.8
	*  @created: 17/01/13
	*/
	
	$(document).ready(function(){
		
		
		// conditional logic
		acf.conditional_logic.init();
		
		
		// fix for older options page add-on
		$('.acf_postbox > .inside > .options').each(function(){
			
			$(this).closest('.acf_postbox').addClass( $(this).attr('data-layout') );
			
		});
		
	
	});
	
	
	/*
	*  window load
	*
	*  @description: 
	*  @since: 3.5.5
	*  @created: 22/12/12
	*/
	
	$(window).load(function(){
		
		// init
		acf.media.init();
		
		
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