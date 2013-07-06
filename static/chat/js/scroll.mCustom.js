(function(w) {
    "use strict";
    // most browsers have an implementation
    w.requestAnimationFrame = w.requestAnimationFrame ||
            w.mozRequestAnimationFrame || w.webkitRequestAnimationFrame ||
            w.msRequestAnimationFrame;
    w.cancelAnimationFrame = w.cancelAnimationFrame ||
            w.mozCancelAnimationFrame || w.webkitCancelAnimationFrame ||
            w.msCancelAnimationFrame;
 
    // polyfill, when necessary
    if (!w.requestAnimationFrame) {
        var aAnimQueue = [],
            iRequestId = 0,
            iIntervalId;
 
        // create a mock requestAnimationFrame function
        w.requestAnimationFrameLegacy = function(callback) {
            aAnimQueue.push([++iRequestId, callback]);
 
            if (!iIntervalId) {
                iIntervalId = setInterval(function() {
                    if (aAnimQueue.length) {
                        aAnimQueue.shift()[1](+new Date());
                    }
                    else {
                        // don't continue the interval, if unnecessary
                        clearInterval(iIntervalId);
                        iIntervalId = undefined;
                    }
                }, 1000 / 50);  // estimating support for 50 frames per second
            }
 
            return iRequestId;
        };
 
        // create a mock cancelAnimationFrame function
        w.cancelAnimationFrameLegacy = function(requestId) {
            // find the request ID and remove it
            for (var i = 0, j = aAnimQueue.length; i < j; i += 1) {
                if (aAnimQueue[i][0] === requestId) {
                    aAnimQueue.splice(i, 1);
                    return;
                }
            }
        };
    }
})(window);


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
				theme: 'light-thin',
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