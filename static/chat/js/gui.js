(function($){

	// Base Chat
	destiny.fn.Chat = function(props){
		props.ui = $(props.ui).get(0);
		$.extend(this, props);
		return this.init();
	};
	
	// Base User
	destiny.fn.ChatUser = function(args){
		this.username = args.username;
		this.userId = args.userId;
		this.roles = [];
		$.extend(this, args);
		return this;
	};

	// Base Message
	destiny.fn.ChatMessage = function(message){
		var self = this;
		self.message = message;
		self.timestamp = null;
		self.state = '';
		self.status = function(state){
			$(this).triggerHandler('status', [state]);
			this.state = state;
			return this;
		};
		self.wrap = function(){
			return $('<li class="line"/>');
		};
		return this;
	};
	
	$.extend(destiny.fn.Chat.prototype, {

		maxLines: 50,
		scrollPlugin: null,
		options: null,
		ui: null,
		lines: null,
		output: null,
		input: null,
		
		init: function(){
			var self = this;
			// Optional params passed in via the data-options="{}" attribute
			self.options = $(self.ui).data('options');
			// local elements stored in vars to not have to get the elements via query each time
			self.lines = $(self.ui).find('.chat-lines').get(0);
			self.output = $(self.ui).find('.chat-output').get(0);
			self.inputwrap = $(self.ui).find('.chat-input').get(0);
			self.input = $(self.inputwrap).find('.input:first').get(0);
			// Set the elements data 'chat' var - should prob remove this - used to reference this in the UI
			$(self.ui).data('chat', self);
			// Bind to user input submit
			$(self.ui).find('.chat-input form').on('submit', function(e){
				self.send();
				return false;
			});
			// Scrollbars and scroll locking
			if(self.scrollPlugin == null){
				//self.scrollPlugin = new destiny.fn.ChatScrollPlugin(self);
				self.scrollPlugin = new destiny.fn.mCustomScrollbarPlugin(self);
				self.scrollPlugin.lockScroll(true);
			};
			// Bind to window resize event
			$(window).on('resize.chat',function(){
				self.resize();
			});
			self.show();
			self.resize();
			return self;
		},
		
		lineCount: function(){
			return $(this.lines).find('.line').length;
		},
		
		// API
		purge: function(){
			$(this.lines).empty();
			$(this).triggerHandler('purge');
			return this;
		},
		
		push: function(message){
			var isScrolledBottom = this.scrollPlugin.isScrolledBottom();
			var line = message.html().appendTo(this.lines);
			$(message).on('status', function(e, state){
				line.removeClass(this.state).addClass(state);
			});
			if(this.lineCount() >= this.maxLines){
				$(this.lines).find('.line:first').remove();
			}
			if(isScrolledBottom && this.scrollPlugin.isScrollLocked()){
				this.scrollPlugin.update();
				this.scrollPlugin.scrollBottom();
			}else if(this.scrollPlugin.isScrollable()){
				this.scrollPlugin.update()
			}
			$(this).triggerHandler('push', [message, line]);
			return message;
		},
		
		send: function(){
			var str = $(this.input).val();
			if(str != ''){
				$(this.input).focus().val('');
				$(this).triggerHandler('send', [str, this.input]);
			};
			return this;
		},
		
		// UI
		resize: function(){
			var bg = $(this.ui).height(), offset = $(this.inputwrap).outerHeight();
			$(this.output).height(bg-offset);
			//this.scrollPlugin.update();
			$(this).triggerHandler('resize');
			return this;
		},
		
		show: function(){
			$(this.ui).show();
			$(this).triggerHandler('show');
			return this;
		},
		
		hide: function(){
			$(this.ui).hide();
			$(this).triggerHandler('hide');
			return this;
		}
		
	});
	
	
})(jQuery);