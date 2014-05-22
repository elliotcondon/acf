(function($){
	
	/*
	*  WYSIWYG
	*
	*  jQuery functionality for this field type
	*
	*  @type	object
	*  @date	20/07/13
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	var _wysiwyg = acf.fields.wysiwyg = {
		
		$el : null,
		$textarea : null,
		
		o : {},
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find textarea
			this.$textarea = this.$el.find('textarea');
			
			
			// get options
			this.o = acf.helpers.get_atts( this.$el );
			
			
			// add ID
			this.o.id = this.$textarea.attr('id');
			
			
			// return this for chaining
			return this;
			
		},
		has_tinymce : function(){
		
			var r = false;
			
			if( typeof(tinyMCE) == "object" )
			{
				r = true;
			}
			
			return r;
			
		},
		
		get_toolbar : function(){
			
			// safely get toolbar
			if( acf.helpers.isset( this, 'toolbars', this.o.toolbar ) ) {
				
				return this.toolbars[ this.o.toolbar ];
				
			}
			
			
			// return
			return false;
			
		},
		
		init : function(){
			
			// is clone field?
			if( acf.helpers.is_clone_field( this.$textarea ) )
			{
				return;
			}
			
			
			// vars
			var toolbar = this.get_toolbar(),
				command = 'mceAddControl',
				setting = 'theme_advanced_buttons{i}';
			
			
			// backup
			var _settings = $.extend( {}, tinyMCE.settings );
			
			
			// v4 settings
			if( tinymce.majorVersion == 4 ) {
				
				command = 'mceAddEditor';
				setting = 'toolbar{i}';
				
			}
			
			
			// add toolbars
			if( toolbar ) {
					
				for( var i = 1; i < 5; i++ ) {
					
					// vars
					var v = '';
					
					
					// load toolbar
					if( acf.helpers.isset( toolbar, 'theme_advanced_buttons' + i ) ) {
						
						v = toolbar['theme_advanced_buttons' + i];
						
					}
					
					
					// update setting
					tinyMCE.settings[ setting.replace('{i}', i) ] = v;
					
				}
				
			}
			
			
			// add editor
			tinyMCE.execCommand( command, false, this.o.id);
			
			
			// events - load
			$(document).trigger('acf/wysiwyg/load', this.o.id);
			
			
			// add events (click, focus, blur) for inserting image into correct editor
			this.add_events();
				
			
			// restore tinyMCE.settings
			tinyMCE.settings = _settings;
			
			
			// set active editor to null
			wpActiveEditor = null;
					
		},
		add_events : function(){
		
			// vars
			var id = this.o.id,
				editor = tinyMCE.get( id );
			
			
			// validate
			if( !editor )
			{
				return;
			}
			
			
			// vars
			var	$container = $('#wp-' + id + '-wrap'),
				$body = $( editor.getBody() );
	
			
			// events
			$container.on('click', function(){
			
				$(document).trigger('acf/wysiwyg/click', id);
				
			});
			
			$body.on('focus', function(){
			
				$(document).trigger('acf/wysiwyg/focus', id);
				
			});
			
			$body.on('blur', function(){
			
				$(document).trigger('acf/wysiwyg/blur', id);
				
			});
			
			
		},
		destroy : function(){
			
			// vars
			var id = this.o.id,
				command = 'mceRemoveControl';
			
			
			// Remove tinymcy functionality.
			// Due to the media popup destroying and creating the field within such a short amount of time,
			// a JS error will be thrown when launching the edit window twice in a row.
			try {
				
				// vars
				var editor = tinyMCE.get( id );
				
				
				// validate
				if( !editor ) {
					
					return;
					
				}
				
				
				// v4 settings
				if( tinymce.majorVersion == 4 ) {
					
					command = 'mceRemoveEditor';
					
				}
				
				
				// store value
				var val = editor.getContent();
				
				
				// remove editor
				tinyMCE.execCommand(command, false, id);
				
				
				// set value
				this.$textarea.val( val );
				
				
			} catch(e) {
				
				//console.log( e );
				
			}
			
			
			// set active editor to null
			wpActiveEditor = null;
			
		}
		
	};
	
	
	/*
	*  acf/setup_fields
	*
	*  run init function on all elements for this field
	*
	*  @type	event
	*  @date	20/07/13
	*
	*  @param	{object}	e		event object
	*  @param	{object}	el		DOM object which may contain new ACF elements
	*  @return	N/A
	*/
	
	$(document).on('acf/setup_fields', function(e, el){
		
		// validate
		if( ! _wysiwyg.has_tinymce() )
		{
			return;
		}
		
		
		// Destory all WYSIWYG fields
		// This hack will fix a problem when the WP popup is created and hidden, then the ACF popup (image/file field) is opened
		$(el).find('.acf_wysiwyg').each(function(){
			
			_wysiwyg.set({ $el : $(this) }).destroy();
			
		});
		
		
		// Add WYSIWYG fields
		setTimeout(function(){
			
			$(el).find('.acf_wysiwyg').each(function(){
			
				_wysiwyg.set({ $el : $(this) }).init();
				
			});
			
		}, 0);
		
	});
	
	
	/*
	*  acf/remove_fields
	*
	*  This action is called when the $el is being removed from the DOM
	*
	*  @type	event
	*  @date	20/07/13
	*
	*  @param	{object}	e		event object
	*  @param	{object}	$el		jQuery element being removed
	*  @return	N/A
	*/
	
	$(document).on('acf/remove_fields', function(e, $el){
		
		// validate
		if( ! _wysiwyg.has_tinymce() )
		{
			return;
		}
		
		
		$el.find('.acf_wysiwyg').each(function(){
			
			_wysiwyg.set({ $el : $(this) }).destroy();
			
		});
		
	});
		
	
	/*
	*  acf/wysiwyg/click
	*
	*  this event is run when a user clicks on a WYSIWYG field
	*
	*  @type	event
	*  @date	17/01/13
	*
	*  @param	{object}	e		event object
	*  @param	{int}		id		WYSIWYG ID
	*  @return	N/A
	*/
	
	$(document).on('acf/wysiwyg/click', function(e, id){
		
		wpActiveEditor = id;
		
		container = $('#wp-' + id + '-wrap').closest('.field').removeClass('error');
		
	});
	
	
	/*
	*  acf/wysiwyg/focus
	*
	*  this event is run when a user focuses on a WYSIWYG field body
	*
	*  @type	event
	*  @date	17/01/13
	*
	*  @param	{object}	e		event object
	*  @param	{int}		id		WYSIWYG ID
	*  @return	N/A
	*/
	
	$(document).on('acf/wysiwyg/focus', function(e, id){
		
		wpActiveEditor = id;
		
		container = $('#wp-' + id + '-wrap').closest('.field').removeClass('error');
		
	});
	
	
	/*
	*  acf/wysiwyg/blur
	*
	*  this event is run when a user loses focus on a WYSIWYG field body
	*
	*  @type	event
	*  @date	17/01/13
	*
	*  @param	{object}	e		event object
	*  @param	{int}		id		WYSIWYG ID
	*  @return	N/A
	*/
	
	$(document).on('acf/wysiwyg/blur', function(e, id){
		
		wpActiveEditor = null;
		
		// update the hidden textarea
		// - This fixes a but when adding a taxonomy term as the form is not posted and the hidden tetarea is never populated!
		var editor = tinyMCE.get( id );
		
		
		// validate
		if( !editor )
		{
			return;
		}
		
		
		var el = editor.getElement();
		
			
		// save to textarea	
		editor.save();
		
		
		// trigger change on textarea
		$( el ).trigger('change');
		
	});

	
	/*
	*  acf/sortable_start
	*
	*  this event is run when a element is being drag / dropped
	*
	*  @type	event
	*  @date	10/11/12
	*
	*  @param	{object}	e		event object
	*  @param	{object}	el		DOM object which may contain new ACF elements
	*  @return	N/A
	*/
	
	$(document).on('acf/sortable_start', function(e, el) {
		
		// validate
		if( ! _wysiwyg.has_tinymce() )
		{
			return;
		}
		
		
		$(el).find('.acf_wysiwyg').each(function(){
			
			_wysiwyg.set({ $el : $(this) }).destroy();
			
		});
		
	});
	
	
	/*
	*  acf/sortable_stop
	*
	*  this event is run when a element has finnished being drag / dropped
	*
	*  @type	event
	*  @date	10/11/12
	*
	*  @param	{object}	e		event object
	*  @param	{object}	el		DOM object which may contain new ACF elements
	*  @return	N/A
	*/
	
	$(document).on('acf/sortable_stop', function(e, el) {
		
		// validate
		if( ! _wysiwyg.has_tinymce() )
		{
			return;
		}
		
		
		$(el).find('.acf_wysiwyg').each(function(){
			
			_wysiwyg.set({ $el : $(this) }).init();
			
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
		
		// validate
		if( ! _wysiwyg.has_tinymce() )
		{
			return;
		}
		
		
		// vars
		var wp_content = $('#wp-content-wrap').exists(),
			wp_acf_settings = $('#wp-acf_settings-wrap').exists()
			mode = 'tmce';
		
		
		// has_editor
		if( wp_acf_settings )
		{
			// html_mode
			if( $('#wp-acf_settings-wrap').hasClass('html-active') )
			{
				mode = 'html';
			}
		}
		
		
		setTimeout(function(){
			
			// trigger click on hidden wysiwyg (to get in HTML mode)
			if( wp_acf_settings && mode == 'html' )
			{
				$('#acf_settings-tmce').trigger('click');
			}
			
		}, 1);
		
		
		setTimeout(function(){
			
			// trigger html mode for people who want to stay in HTML mode
			if( wp_acf_settings && mode == 'html' )
			{
				$('#acf_settings-html').trigger('click');
			}
			
			// Add events to content editor
			if( wp_content )
			{
				_wysiwyg.set({ $el : $('#wp-content-wrap') }).add_events();
			}
			
			
		}, 11);
		
		
	});
	
	
	/*
	*  Full screen
	*
	*  @description: this hack will hide the 'image upload' button in the wysiwyg full screen mode if the field has disabled image uploads!
	*  @since: 3.6
	*  @created: 26/02/13
	*/
	
	$(document).on('click', '.acf_wysiwyg a.mce_fullscreen', function(){
		
		// vars
		var wysiwyg = $(this).closest('.acf_wysiwyg'),
			upload = wysiwyg.attr('data-upload');
		
		if( upload == 'no' )
		{
			$('#mce_fullscreen_container td.mceToolbar .mce_add_media').remove();
		}
		
	});
	
	
})(jQuery);