(function($){
	
	destiny.fn.mCustomScrollbarPlugin = function(chat){
		this.chat = chat;
		return this.init();
	};
	$.extend(destiny.fn.mCustomScrollbarPlugin.prototype, {
		
		scrolledBottom: true,
		scrollLocked: true,
		chat: null,
		
		update: function(){
			this.chat.output.mCustomScrollbar('update');
		},
		
		init: function(){
			var self = this;
			self.chat.output.mCustomScrollbar({
				theme: 'light',
				scrollInertia: 0,
				horizontalScroll: false,
				autoHideScrollbar: false,
				scrollButtons:{
					enable:true
				},
				callbacks: {
					onTotalScrollOffset: 1,
					onTotalScrollBackOffset: 1,
					onScrollStart: function(){
						self.scrolledBottom = false;
					},
					onTotalScrollBack: function(){
						self.scrolledBottom = false;
					},
					onTotalScroll: function(){
						self.scrolledBottom = true;
					}
				}
			});
			self.chat.ui.addClass('chat-custom-scroll');
			return true;
		},
		
		lockScroll: function(lock){
			this.scrollLocked = lock;
			return this;
		},
		
		scrollBottom: function(){
			this.scrolledBottom = true;
			return this.chat.output.mCustomScrollbar('scrollTo','bottom');
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