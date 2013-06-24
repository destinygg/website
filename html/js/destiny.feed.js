(function($){
	
	var DestinyFeedConsumer = function(args){
		this.args = $.extend({
			polling: null,
			init: $.noop(),
			success: $.noop(), 
			error: $.noop(), 
			pollexecute: $.noop(),
			type: 'GET', 
			dataType: 'json',
			start: true,
			ui: null,
		}, args);
		return this.init();
	};
	
	$.extend(DestinyFeedConsumer.prototype, {

		polling: null,
		args: null,
		
		init: function(){
			if($.isFunction(this.args.init)){
				this.args.init.call(this, this.args);
			};
			// Exit operation if the ui element isnt found. This is temp measure.
			if(this.args.ui != null){
				if($(this.args.ui).get(0) == null){
					return;
				};
				// If the php didnt load the html, load the feed immediatly
				if($(this.args.ui).find('.loading').get(0) != null){
					this.args.start = true;
				};
			};
			if(this.args.start){
				this.load();
			};
			this.args.start = true;
			this.setupPoll();
			return this;
		},
		
		load: function(){
			$.ajax(this.args);
			return this;
		},
		
		setupPoll: function(){
			var self = this;
			if(self.polling != null){
				self.stopPolling();
			};
			if(self.args.polling != null && typeof self.args.polling == 'number'){
				self.polling = setInterval(function(){
					if($.isFunction(self.args.pollexecute) && false === self.args.pollexecute.call(self, self.args)){
						return;
					};
					self.load();
				}, self.args.polling * 1000);
			};
		},
		
		stopPolling: function(){
			clearInterval(this.polling);
		},
		
		resetPoll: function(){
			this.setupPoll();
		}
		
	});
	
	window.DestinyFeedConsumer = DestinyFeedConsumer;
	
})(jQuery);