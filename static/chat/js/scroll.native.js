(function($){

	destiny.fn.ChatScrollPlugin = function(chat){
		this.chat = chat;
		return this.init();
	};
	$.extend(destiny.fn.ChatScrollPlugin.prototype, {
		
		scrollLocked: true,
		chat: null,
		update: $.noop,
		
		init: function(){
			var self = this;
			$(self.chat.output).on({
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
			$(self.chat.ui).addClass('chat-native-scroll');
			return self;
		},
		
		lockScroll: function(lock){
			this.scrollLocked = lock; 
			$(this).triggerHandler('lockScroll');
			return this;
		},
		
		scrollBottom: function(){
			$(this.chat.output).scrollTop(this.chat.output.scrollHeight);
			$(this).triggerHandler('scrollBottom');
			return this;
		},
		
		isScrolledBottom: function(){
			return (!this.isScrollable() || $(this.chat.output).scrollTop() + $(this.chat.output).height() == $(this.chat.lines).height());
		},
		
		isScrollable: function(){
			return ($(this.chat.output).height() < $(this.chat.lines).height());
		},
		
		isScrollLocked: function(){
			return this.scrollLocked;
		}
	});
	
})(jQuery);