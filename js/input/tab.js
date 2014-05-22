(function($){

	acf.fields.tab = {
		
		add_group : function( $wrap ){
			
			// vars
			var html = '';
			
			
			// generate html
			if( $wrap.is('tbody') )
			{
				html = '<tr class="acf-tab-wrap"><td colspan="2"><ul class="hl clearfix acf-tab-group"></ul></td></tr>';
			}
			else
			{
				html = '<div class="acf-tab-wrap"><ul class="hl clearfix acf-tab-group"></ul></div>';
			}
			
			
			// append html
			$wrap.children('.field_type-tab:first').before( html );
			
		},
		
		add_tab : function( $tab ){
			
			// vars
			var $field	= $tab.closest('.field'),
				$wrap	= $field.parent(),
				
				key		= $field.attr('data-field_key'),
				label 	= $tab.text();
				
				
			// create tab group if it doesnt exist
			if( ! $wrap.children('.acf-tab-wrap').exists() )
			{
				this.add_group( $wrap );
			}
			
			// add tab
			$wrap.children('.acf-tab-wrap').find('.acf-tab-group').append('<li><a class="acf-tab-button" href="#" data-key="' + key + '">' + label + '</a></li>');
			
		},
		
		toggle : function( $a ){
			
			// reference
			var _this = this;
				
				
			//console.log( 'toggle %o ', $a);
			// vars
			var $wrap	= $a.closest('.acf-tab-wrap').parent(),
				key		= $a.attr('data-key');
			
			
			// classes
			$a.parent('li').addClass('active').siblings('li').removeClass('active');
			
			
			// hide / show
			$wrap.children('.field_type-tab').each(function(){
			
				
				// vars
				var $tab = $(this);
					
				
				if( $tab.attr('data-field_key') == key  )
				{
					_this.show_tab_fields( $(this) );
				}
				else
				{
					_this.hide_tab_fields( $(this) );
				}
				
				
			});
			
		},
		
		show_tab_fields : function( $field ) {
			
			//console.log('show tab fields %o', $field);
			$field.nextUntil('.field_type-tab').each(function(){
				
				$(this).removeClass('acf-tab_group-hide').addClass('acf-tab_group-show');
				$(document).trigger('acf/fields/tab/show', [ $(this) ]);
				
			});
		},
		
		hide_tab_fields : function( $field ) {
			
			$field.nextUntil('.field_type-tab').each(function(){
				
				$(this).removeClass('acf-tab_group-show').addClass('acf-tab_group-hide');
				$(document).trigger('acf/fields/tab/hide', [ $(this) ]);
				
			});
		},
		
		refresh : function( $el ){
			
			// reference
			var _this = this;
			
			
			// trigger
			$el.find('.acf-tab-group').each(function(){
				
				$(this).find('.acf-tab-button:first').each(function(){
					
					_this.toggle( $(this) );
					
				});
				
			});

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
		
		// add tabs
		$(el).find('.acf-tab').each(function(){
			
			acf.fields.tab.add_tab( $(this) );
			
		});
		
		
		// activate first tab
		acf.fields.tab.refresh( $(el) );
		
		
		// NOTE: this code is defined BEFORE the acf.conditional_logic action. This is becuase the 'acf/setup_fields' listener is defined INSIDE the conditional_logic.init() function which is run on doc.ready
		
		// trigger conditional logic
		// this code ( acf/setup_fields ) is run after the main acf.conditional_logic.init();
		//console.log('acf/setup_fields (after tab refresh) calling acf.conditional_logic.refresh()');
		//acf.conditional_logic.refresh();
		
	});
	
	
		
	
	/*
	*  Events
	*
	*  jQuery events for this field
	*
	*  @type	function
	*  @date	1/03/2011
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	$(document).on('click', '.acf-tab-button', function( e ){
		
		e.preventDefault();
		
		acf.fields.tab.toggle( $(this) );
		
		$(this).trigger('blur');
		
	});
	
	
	$(document).on('acf/conditional_logic/hide', function( e, $target, item ){
		
		// validate
		if( $target.attr('data-field_type') != 'tab' )
		{
			return;
		}
		
		//console.log('conditional_logic/hide tab %o', $target);
		
		
		// vars
		var $tab = $target.siblings('.acf-tab-wrap').find('a[data-key="' + $target.attr('data-field_key') + '"]');
		
		
		// if tab is already hidden, then ignore the following functiolnality
		if( $tab.is(':hidden') )
		{
			return;
		}
		
		
		// visibility
		$tab.parent().hide();
		
		
		// if 
		if( $tab.parent().siblings(':visible').exists() )
		{
			// if the $target to be hidden is a tab button, lets toggle a sibling tab button
			$tab.parent().siblings(':visible').first().children('a').trigger('click');
		}
		else
		{
			// no onther tabs
			acf.fields.tab.hide_tab_fields( $target );
		}
		
	});
	
	
	$(document).on('acf/conditional_logic/show', function( e, $target, item ){
		
		// validate
		if( $target.attr('data-field_type') != 'tab' )
		{
			return;
		}
		
		
		//console.log('conditional_logic/show tab %o', $target);
		
		
		// vars
		var $tab = $target.siblings('.acf-tab-wrap').find('a[data-key="' + $target.attr('data-field_key') + '"]');
		
		
		// if tab is already visible, then ignore the following functiolnality
		if( $tab.is(':visible') )
		{
			return;
		}
		
		
		// visibility
		$tab.parent().show();
		
		
		// if this is the active tab
		if( $tab.parent().hasClass('active') )
		{
			$tab.trigger('click');
			return;
		}
		
		
		// if the sibling active tab is actually hidden by conditional logic, take ownership of tabs
		if( $tab.parent().siblings('.active').is(':hidden') )
		{
			// show this tab group
			$tab.trigger('click');
			return;
		}
		

	});
	
	

})(jQuery);