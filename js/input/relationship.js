(function($){
	
	/*
	*  Relationship
	*
	*  static model for this field
	*
	*  @type	event
	*  @date	1/06/13
	*
	*/
	
	acf.fields.relationship = {
		
		$el : null,
		$input : null,
		$left : null,
		$right : null,
				
		o : {},
		
		timeout : null,
		
		set : function( o ){
			
			// merge in new option
			$.extend( this, o );
			
			
			// find elements
			this.$input = this.$el.children('input[type="hidden"]');
			this.$left = this.$el.find('.relationship_left'),
			this.$right = this.$el.find('.relationship_right');
			
			
			// get options
			this.o = acf.helpers.get_atts( this.$el );
			
			
			// return this for chaining
			return this;
			
		},
		init : function(){
			
			// reference
			var _this = this;
			
			
			// is clone field?
			if( acf.helpers.is_clone_field(this.$input) )
			{
				return;
			}
			
			
			// set height of right column
			this.$right.find('.relationship_list').height( this.$left.height() -2 );
			
			
			// right sortable
			this.$right.find('.relationship_list').sortable({
				axis					:	'y',
				items					:	'> li',
				forceHelperSize			:	true,
				forcePlaceholderSize	:	true,
				scroll					:	true,
				update					:	function(){
					
					_this.$input.trigger('change');
					
				}
			});
			
			
			// load more
			var $el = this.$el;
			
			this.$left.find('.relationship_list').scrollTop( 0 ).on('scroll', function(e){
				
				// validate
				if( $el.hasClass('loading') || $el.hasClass('no-results') )
				{
					return;
				}
				
				
				// Scrolled to bottom
				if( $(this).scrollTop() + $(this).innerHeight() >= $(this).get(0).scrollHeight )
				{
					var paged = parseInt( $el.attr('data-paged') );
					
					// update paged
					$el.attr('data-paged', (paged + 1) );
					
					// fetch
					_this.set({ $el : $el }).fetch();
				}
				
			});
			
			
			// ajax fetch values for left side
			this.fetch();
					
		},
		fetch : function(){
			
			// reference
			var _this = this,
				$el = this.$el;
			
			
			// add loading class, stops scroll loading
			$el.addClass('loading');
			
			
			// get results
		    $.ajax({
				url				:	acf.o.ajaxurl,
				type			:	'post',
				dataType		:	'json',
				data			:	$.extend({ 
					action		:	'acf/fields/relationship/query_posts', 
					post_id		:	acf.o.post_id,
					nonce		:	acf.o.nonce
				}, this.o ),
				success			:	function( json ){
					
					
					// render
					_this.set({ $el : $el }).render( json );
					
				}
			});
			
		},
		render : function( json ){
			
			// reference
			var _this = this;
			
			
			// update classes
			this.$el.removeClass('no-results').removeClass('loading');
			
			
			// new search?
			if( this.o.paged == 1 )
			{
				this.$el.find('.relationship_left li:not(.load-more)').remove();
			}
			
			
			// no results?
			if( ! json || ! json.html )
			{
				this.$el.addClass('no-results');
				return;
			}
			
			
			// append new results
			this.$el.find('.relationship_left .load-more').before( json.html );
			
			
			// next page?
			if( ! json.next_page_exists )
			{
				this.$el.addClass('no-results');
			}
							
			
			// apply .hide to left li's
			this.$left.find('a').each(function(){
				
				var id = $(this).attr('data-post_id');
				
				if( _this.$right.find('a[data-post_id="' + id + '"]').exists() )
				{
					$(this).parent().addClass('hide');
				}
				
			});
			
		},
		add : function( $a ){
			
			// vars
			var id = $a.attr('data-post_id'),
				title = $a.html();
			
			
			// max posts
			if( this.$right.find('a').length >= this.o.max )
			{
				alert( acf.l10n.relationship.max.replace('{max}', this.o.max) );
				return false;
			}
			
			
			// can be added?
			if( $a.parent().hasClass('hide') )
			{
				return false;
			}
			
			
			// hide
			$a.parent().addClass('hide');
			
			
			// template
			var html = [
				'<li>',
					'<a href="#" data-post_id="' + $a.attr('data-post_id') + '">',
						$a.html() + '<span class="acf-button-remove"></span>',
					'</a>',
					'<input type="hidden" name="' + this.$input.attr('name') + '[]" value="' + $a.attr('data-post_id') + '" />',
				'</li>'].join('');
			
	
			// add new li
			this.$right.find('.relationship_list').append( html )
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
			
			// validation
			this.$el.closest('.field').removeClass('error');

			
		},
		remove : function( $a ){
			
			// remove
			$a.parent().remove();
			
			
			// show
			this.$left.find('a[data-post_id="' + $a.attr('data-post_id') + '"]').parent('li').removeClass('hide');
			
			
			// trigger change on new_li
			this.$input.trigger('change');
			
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
		
		$(el).find('.acf_relationship').each(function(){
			
			acf.fields.relationship.set({ $el : $(this) }).init();
			
		});
		
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
	
	$(document).on('change', '.acf_relationship .select-post_type', function(e){
		
		// vars
		var val = $(this).val(),
			$el = $(this).closest('.acf_relationship');
			
		
		// update attr
	    $el.attr('data-post_type', val);
	    $el.attr('data-paged', 1);
	    
	    
	    // fetch
	    acf.fields.relationship.set({ $el : $el }).fetch();
		
	});

	
	$(document).on('click', '.acf_relationship .relationship_left .relationship_list a', function( e ){
		
		e.preventDefault();
		
		acf.fields.relationship.set({ $el : $(this).closest('.acf_relationship') }).add( $(this) );
		
		$(this).blur();
		
	});
	
	$(document).on('click', '.acf_relationship .relationship_right .relationship_list a', function( e ){
		
		e.preventDefault();
		
		acf.fields.relationship.set({ $el : $(this).closest('.acf_relationship') }).remove( $(this) );
		
		$(this).blur();
		
	});
	
	$(document).on('keyup', '.acf_relationship input.relationship_search', function( e ){
		
		// vars
		var val = $(this).val(),
			$el = $(this).closest('.acf_relationship');
			
		
		// update attr
	    $el.attr('data-s', val);
	    $el.attr('data-paged', 1);
	    
	    
	    // fetch
	    clearTimeout( acf.fields.relationship.timeout );
	    acf.fields.relationship.timeout = setTimeout(function(){
	    
	    	 acf.fields.relationship.set({ $el : $el }).fetch();
	    	
	    }, 500);
		
	});
	
	$(document).on('keypress', '.acf_relationship input.relationship_search', function( e ){
		
		// don't submit form
		if( e.which == 13 )
		{
			e.preventDefault();
		}
		
	});
	

})(jQuery);