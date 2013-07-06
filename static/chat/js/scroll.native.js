(function($){

	destiny.fn.ChatScrollPlugin = function(chat){
		this.chat = chat;
		return this.init();
	};
	$.extend(destiny.fn.ChatScrollPlugin.prototype, {
		
		scrollLocked: true,
		scrolledBottom: true,
		chat: null,
		update: $.noop,
		
		init: function(){
			var self = this;
			$(self.chat.output).on({
				scroll: function(e){
					self.scrolledBottom = (self.chat.output.scrollTop() + self.chat.output.height() == self.chat.lines.height());
				},
				mousedown: function(e){
					if(self.isScrollLocked() && !self.isScrolledBottom()){
						self.lockScroll(false);
					}
				},
				mouseup: function(e){
					if(!self.isScrollLocked()) {
						self.lockScroll(true);
					}
				},
				mousewheel: function(e){
					// If the user scrolls up at any time and we are locked, the lock is released
					if(!self.isScrollLocked() && self.isScrollable() && self.isScrolledBottom()){
						if(e['originalEvent'] != null && e.originalEvent['wheelDelta'] != undefined && e.originalEvent.wheelDelta/120 > 0){
							self.lockScroll(false);
						}
					}
				}
			});

			
			self.chat.ui.addClass('chat-native-scroll');
			return self;
		},
		
		lockScroll: function(lock){
			this.scrollLocked = lock; 
			return this;
		},
		
		scrollBottom: function(){
			this.scrolledBottom = true;
			return this.chat.output.scrollTop(this.chat.output.prop('scrollHeight'));
		},
		
		isScrolledBottom: function(){
			return (!this.isScrollable() || this.scrolledBottom);
		},
		
		isScrollable: function(){
			return (this.chat.output.height() < this.chat.lines.height());
		},
		
		isScrollLocked: function(){
			return this.scrollLocked;
		}
	});
	
})(jQuery);