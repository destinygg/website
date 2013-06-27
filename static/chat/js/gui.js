(function($){

	destiny.chat = function(options){
		options.ui = $(options.ui).get(0);
		$.extend(this, options);
		return this;
	};
	
	$.extend(destiny.chat.prototype, {

		maxLines: 50,
		isScrollLocked: true,
		
		ui: null,
		lines: null,
		output: null,
		input: null,

		init: function(){
			var self = this;
			$(self.ui).data('chat', self);
			// Vars
			self.lines		= $(self.ui).find('.chat-lines').get(0);
			self.output		= $(self.ui).find('.chat-output').get(0);
			self.inputwrap	= $(self.ui).find('.chat-input').get(0);
			self.input		= $(self.inputwrap).find('.input:first').get(0);
			// Bind to user input submit
			$(self.ui).find('.chat-input form').on('submit', function(e){
				self.send();
				return false;
			});
			// Scroll locking / output events
			$(self.output).on({
				mousewheel: function(e){
					// If the user scrolls up at any time and we are locked, the lock is released
					if(self.isScrollLocked && self.isScrollable() && self.isScrolledBottom()){
						if(e['originalEvent'] != null && e.originalEvent['wheelDelta'] != undefined && e.originalEvent.wheelDelta/120 > 0){
							self.lockScroll(false);
						}
					}
				},
				mousedown: function(){
					if(self.isScrollLocked && self.isScrolledBottom()){
						self.lockScroll(false);
					}
				},
				mouseup: function(){
					if(!self.isScrollLocked && self.isScrolledBottom()) {
						self.lockScroll(true);
					}
				},
				scroll: function(){
					if(!self.isScrollLocked && self.isScrolledBottom()) {
						self.lockScroll(true);
					};
				}
			});
			self.lockScroll(true);
			//
			self.show();
			self.resize();
		},
		
		resize: function(){
			var bg = $(this.ui).height(), offset = $(this.inputwrap).outerHeight();
			$(this.output).height(bg-offset);
			$(this).triggerHandler('resize');
		},
		
		show: function(){
			$(this.ui).show();
			$(this).triggerHandler('show');
		},
		
		hide: function(){
			$(this.ui).hide();
			$(this).triggerHandler('hide');
		},
		
		send: function(){
			var self = this, str = $(self.input).val();
			if(str != ''){
				$(self.input).focus().val('');
				$(this).triggerHandler('send', [str, self.input]);
			};
		},
		
		lineCount: function(){
			return $(this.lines).find('.line').length;
		},
		
		lockScroll: function(lock){
			this.isScrollLocked = lock; 
			$(this).triggerHandler('lockScroll', [lock]);
		},
		
		purge: function(){
			$(this.lines).empty();
			$(this).triggerHandler('purge');
		},
		
		push: function(message){
			var line = $('<li class="line"/>').append(message.html()).appendTo(this.lines);
			line.data('message', message);
			if(this.lineCount() >= this.maxLines){
				$(this.lines).find('.line:first').remove();
			}
			if(this.isScrollLocked){
				this.scrollBottom();
			}
			$(this).triggerHandler('push', [message, line]);
			return line;
		},
		
		scrollBottom: function(){
			$(this.output).scrollTop(this.output.scrollHeight);
		},
		
		isScrolledBottom: function(){
			return (!this.isScrollable() || ($(this.output).scrollTop() + $(this.output).height() == $(this.lines).height()));
		},
		
		isScrollable: function(){
			return ($(this.output).height() < $(this.lines).height());
		}
		
	});
	
})(jQuery);