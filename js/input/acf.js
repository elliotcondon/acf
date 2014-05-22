/*
*  input.js
*
*  All javascript needed for ACF to work
*
*  @type	awesome
*  @date	1/08/13
*
*  @param	N/A
*  @return	N/A
*/ 

var acf = {
	
	// vars
	ajaxurl				:	'',
	admin_url			:	'',
	wp_version			:	'',
	post_id				:	0,
	nonce				:	'',
	l10n				:	null,
	o					:	null,
	
	// helper functions
	helpers				:	{
		get_atts		: 	null,
		version_compare	:	null,
		uniqid			:	null,
		sortable		:	null,
		add_message		:	null,
		is_clone_field	:	null,
		url_to_object	:	null
	},
	
	
	// modules
	validation			:	null,
	conditional_logic	:	null,
	media				:	null,
	
	
	// fields
	fields				:	{
		date_picker		:	null,
		color_picker	:	null,
		Image			:	null,
		file			:	null,
		wysiwyg			:	null,
		gallery			:	null,
		relationship	:	null
	}
};

(function($){
	
	
	/*
	*  acf.helpers.isset
	*
	*  description
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
	        c = null,
	        undef;
		
	    if (l === 0) {
	        throw new Error('Empty isset');
	    }
		
		c = a[0];
		
	    for (i = 1; i < l; i++) {
	    	
	        if (a[i] === undef || c[ a[i] ] === undef) {
	            return false;
	        }
	        
	        c = c[ a[i] ];
	        
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
		render_timout : null,
		
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
			if( typeof wp !== 'undefined' )
			{
				type = 'backbone';
			}
			
			
			// return
			return type;
			
		},
		init : function(){
			
			// validate
			if( this.type() !== 'backbone' )
			{
				return false;
			}
			
			
			// validate prototype
			if( ! acf.helpers.isset(wp, 'media', 'view', 'AttachmentCompat', 'prototype') )
			{
				return false;	
			}
			
			
			// vars
			var _prototype = wp.media.view.AttachmentCompat.prototype;
			
			
			// orig
			_prototype.orig_render = _prototype.render;
			_prototype.orig_dispose = _prototype.dispose;
			
			
			// update class
			_prototype.className = 'compat-item acf_postbox no_box';
			
			
			// modify render
			_prototype.render = function() {
				
				// reference
				var _this = this;
				
				
				// validate
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
				
				
				// setup fields
				// The clearTimout is needed to prevent many setup functions from running at the same time
				clearTimeout( acf.media.render_timout );
				acf.media.render_timout = setTimeout(function(){

					$(document).trigger( 'acf/setup_fields', [ _this.$el ] );
					
				}, 50);

				
				// return based on the origional render function
				return this;
			};
			
			
			// modify dispose
			_prototype.dispose = function() {
				
				// remove
				$(document).trigger('acf/remove_fields', [ this.$el ]);
				
				
				// run the old render function
				this.orig_dispose();
				
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
				
				// preview hack
				if( $('#acf-has-changed').exists() )
				{
					$('#acf-has-changed').val(1);
				}
				
				_this.change( $(this) );
				
			});
			
			
			$(document).on('acf/setup_fields', function(e, el){
				
				//console.log('acf/setup_fields calling acf.conditional_logic.refresh()');
				_this.refresh( $(el) );
				
			});
			
			//console.log('acf.conditional_logic.init() calling acf.conditional_logic.refresh()');
			_this.refresh();
			
		},
		change : function( $el ){
			
			//console.log('change %o', $el);
			// reference
			var _this = this;
			
			
			// vars
			var $field = $el.closest('.field'),
				key = $field.attr('data-field_key');
			
			
			// loop through items and find rules where this field key is a trigger
			$.each(this.items, function( k, item ){
				
				$.each(item.rules, function( k2, rule ){
					
					// compare rule against the changed $field
					if( rule.field == key )
					{
						_this.refresh_field( item );
					}
					
				});
				
			});
			
		},
		
		refresh_field : function( item ){
			
			//console.log( 'refresh_field: %o ', item );
			// reference
			var _this = this;
			
			
			// vars
			var $targets	=	$('.field_key-' + item.field);

			
			// may be multiple targets (sub fields)
			$targets.each(function(){
				
				//console.log('target %o', $(this));
				
				// vars
				var show = true;
				
				
				// if 'any' was selected, start of as false and any match will result in show = true
				if( item.allorany == 'any' )
				{
					show = false;
				}
				
				
				// vars
				var $target		=	$(this),
					hide_all	=	true;
				
				
				// loop through rules
				$.each(item.rules, function( k2, rule ){
					
					// vars
					var $toggle = $('.field_key-' + rule.field);
					
					
					// are any of $toggle a sub field?
					if( $toggle.hasClass('sub_field') )
					{
						// toggle may be a sibling sub field.
						// if so ,show an empty td but keep the column
						$toggle = $target.siblings('.field_key-' + rule.field);
						hide_all = false;
						
						
						// if no toggle was found, we need to look at parent sub fields.
						// if so, hide the entire column
						if( ! $toggle.exists() )
						{
							// loop through all the parents that could contain sub fields
							$target.parents('tr').each(function(){
								
								// attempt to update $toggle to this parent sub field
								$toggle = $(this).find('.field_key-' + rule.field)
								
								// if the parent sub field actuallly exists, great! Stop the loop
								if( $toggle.exists() )
								{
									return false;
								}
								
							});

							hide_all = true;
						}
						
					}
					
					
					// if this sub field is within a flexible content layout, hide the entire column because 
					// there will never be another row added to this table
					var parent = $target.parent('tr').parent().parent('table').parent('.layout');
					if( parent.exists() )
					{
						hide_all = true;
						
						if( $target.is('th') && $toggle.is('th') )
						{
							$toggle = $target.closest('.layout').find('td.field_key-' + rule.field);
						}

					}
					
					// if this sub field is within a repeater field which has a max row of 1, hide the entire column because 
					// there will never be another row added to this table
					var parent = $target.parent('tr').parent().parent('table').parent('.repeater');
					if( parent.exists() && parent.attr('data-max_rows') == '1' )
					{
						hide_all = true;
						
						if( $target.is('th') && $toggle.is('th') )
						{
							$toggle = $target.closest('table').find('td.field_key-' + rule.field);
						}

					}
					
					
					var calculate = _this.calculate( rule, $toggle, $target );
					
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
				// $.each(item.rules, function( k2, rule ){
				
				
				// clear classes
				$target.removeClass('acf-conditional_logic-hide acf-conditional_logic-show acf-show-blank');
				
				
				// hide / show field
				if( show )
				{
					// remove "disabled"
					$target.find('input, textarea, select').removeAttr('disabled');
					
					$target.addClass('acf-conditional_logic-show');
					
					// hook
					$(document).trigger('acf/conditional_logic/show', [ $target, item ]);
					
				}
				else
				{
					// add "disabled"
					$target.find('input, textarea, select').attr('disabled', 'disabled');
					
					$target.addClass('acf-conditional_logic-hide');
					
					if( !hide_all )
					{
						$target.addClass('acf-show-blank');
					}
					
					// hook
					$(document).trigger('acf/conditional_logic/hide', [ $target, item ]);
				}
				
				
			});
			
		},
		
		refresh : function( $el ){
			
			// defaults
			$el = $el || $('body');
			
			
			// reference
			var _this = this;
			
			
			// loop through items and find rules where this field key is a trigger
			$.each(this.items, function( k, item ){
				
				$.each(item.rules, function( k2, rule ){
					
					// is this field within the $el
					// this will increase performance by ignoring conditional logic outside of this newly appended element ($el)
					if( ! $el.find('.field[data-field_key="' + item.field + '"]').exists() )
					{
						return;
					}
					
					_this.refresh_field( item );
					
				});
				
			});
			
		},
		
		calculate : function( rule, $toggle, $target ){
			
			// vars
			var r = false;
			

			// compare values
			if( $toggle.hasClass('field_type-true_false') || $toggle.hasClass('field_type-checkbox') || $toggle.hasClass('field_type-radio') )
			{
				var exists = $toggle.find('input[value="' + rule.value + '"]:checked').exists();
				
				
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
				var val = $toggle.find('input, textarea, select').last().val();
				
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
		
		
		// Remove 'field_123' from native custom field metabox
		$('#metakeyselect option[value^="field_"]').remove();
		
	
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
				// post_id may be string (user_1) and therefore, the uploaded image cannot be attached to the post
				if( $.isNumeric(acf.o.post_id) )
				{
					wp.media.view.settings.post.id = acf.o.post_id;
				}
				
			} 
			catch(e)
			{
				// one of the objects was 'undefined'...
			}
			
			
			// setup fields
			$(document).trigger('acf/setup_fields', [ $('#poststuff') ]);
			
		}, 10);
		
	});
	
	
	/*
	*  Gallery field Add-on Fix
	*
	*  Gallery field v1.0.0 required some data in the acf object.
	*  Now not required, but older versions of gallery field need this.
	*
	*  @type	object
	*  @date	1/08/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	acf.fields.gallery = {
		add : function(){},
		edit : function(){},
		update_count : function(){},
		hide_selected_items : function(){},
		text : {
			title_add : "Select Images"
		}
	};
	
		
	
})(jQuery);