(function($){
//https://developer.mozilla.org/en-US/docs/Web/Guide/User_experience/Displaying_notifications
//http://blog.teamtreehouse.com/adding-desktop-notifications-to-your-web-applications
//
	
	// Base User
	destiny.fn.ChatUser = function(args){
		this.username = args.username;
		this.userId = args.userId;
		this.roles = [];
		$.extend(this, args);
		return this;
	};
	
	// Base Message
	destiny.fn.ChatMessage = function(message, timestamp){
		return this.init(message, timestamp);
	};
	$.extend(destiny.fn.ChatMessage.prototype, {
		timestamp: null,
		state: null,
		init: function(message, timestamp){
			this.timestamp = moment(timestamp);
			this.message = message;
			this.state = '';
			return this;
		},
		status: function(state){
			$(this).triggerHandler('status', [state]);
			this.state = state;
			return this;
		},
		wrap: function(content){
			return '<div>'+content+'</div>';
		}
	});


	// Base Chat
	destiny.fn.Chat = function(props){
		$.extend(this, props);
		this.ui = $(this.ui);
		return this.init();
	};
	$.extend(destiny.fn.Chat.prototype, {

		maxLines: 50,
		lineCount: 0,
		scrollPlugin: null,
		options: null,
		ui: null,
		lines: null,
		output: null,
		input: null,
		
		init: function(){
			// Optional params passed in via the data-options="{}" attribute
			this.options = this.ui.data('options');
			// local elements stored in vars to not have to get the elements via query each time
			this.lines = $(this.ui.find('.chat-lines:first')[0]);
			this.output = $(this.ui.find('.chat-output:first')[0]);
			this.inputwrap = $(this.ui.find('.chat-input:first')[0]);
			this.input = $(this.inputwrap.find('.input:first:first')[0]);
			
			// Set the elements data 'chat' var - should prob remove this - used to reference this in the UI
			this.ui.data('chat', this);

			// Bind to user input submit
			this.ui.on('submit', '.chat-input form', function(e){
				e.preventDefault();
				$(this).closest('.chat.chat-frame').data('chat').send();
			});
			
			// Scrollbars and scroll locking
			if(this.scrollPlugin == null){
				//this.scrollPlugin = new destiny.fn.ChatScrollPlugin(this);
				this.scrollPlugin = new destiny.fn.mCustomScrollbarPlugin(this);
				this.scrollPlugin.lockScroll(true);
			};
			this.show();
			this.resize();
			return this;
		},
		
		lineCount: function(){
			return this.lines.children().length;
		},
		
		// API
		purge: function(){
			this.lines.empty();
			$(this).triggerHandler('purge');
			return this;
		},
		
		push: function(message){
			var isScrolledBottom = this.scrollPlugin.isScrolledBottom();
			message.ui = $(message.html()).appendTo(this.lines);
			$(message).on('status', function(e, state){
				if(state != undefined){
					this.ui.attr('class', state);
				}else{
					this.ui.removeAttr('class');
				}
			});
			if(this.lineCount() >= this.maxLines){
				$(this.lines.children()[0]).remove();
			}else if(isScrolledBottom && this.scrollPlugin.isScrollLocked()){
				this.scrollPlugin.update();
				this.scrollPlugin.scrollBottom();
			}else if(this.scrollPlugin.isScrollable()){
				this.scrollPlugin.update()
			}
			$(this).triggerHandler('push', [message]);
			return message;
		},
		
		send: function(){
			var str = this.input.val();
			if(str != ''){
				this.input.val('').focus();
				$(this).triggerHandler('send', [str, this.input[0]]);
			};
			str = null;
			return this;
		},
		
		// UI
		resize: function(){
			this.output.height(this.ui.height()-this.inputwrap.outerHeight());
			$(this).triggerHandler('resize');
			return this;
		},
		
		show: function(){
			this.ui.show();
			$(this).triggerHandler('show');
			return this;
		},
		
		hide: function(){
			this.ui.hide();
			$(this).triggerHandler('hide');
			return this;
		},
		
		removeUserLines: function(user){
			//
		}
		
	});
	
	// should be moved somewhere better
	$(window).on('resize.chat',function(){
		$('.chat.chat-frame').each(function(){
			$(this).data('chat').resize();
		});
	});
	
	
})(jQuery);


function ChatMessage(message, timestamp){
	this.init(message, timestamp);
	return this;
};
$.extend(ChatMessage.prototype, destiny.fn.ChatMessage.prototype, {
	wrapTime: function(){
		return '<time datetime="'+this.timestamp.format('MMMM Do YYYY, h:mm:ss a')+'">'+this.timestamp.format('HH:mm')+' </time>';
	},
	wrapMessage: function(css){
		return '<span'+ ((css==undefined) ? '':' class="'+css+'"') +'>'+this.message+'</span>';
	},
	html: function(){
		return this.wrap(this.wrapTime() + this.wrapMessage());
	}
});
function ChatUserMessage(message, user, timestamp){
	this.init(message, timestamp);
	this.user = user;
	return this;
};
$.extend(ChatUserMessage.prototype, ChatMessage.prototype, {
	wrapUser: function(user){
		var features = {
			'subscriber': '<i class="icon-star" title="Subscriber"/>',
			'admin'     : '<i class="icon-fire" title="Administrator"/>',
			'moderator' : '<i class="icon-leaf" title="Moderator"/>',
			'protected' : '<i class="icon-eye-close" title="Protected"/>',
			'vip'       : '<i class="icon-film" title="VIP"/>'
		};
		var icons = '';
		for (var i = user.features.length - 1; i >= 0; i--) {
			var feature = user.features[i];
			if (features[feature])
				icons += features[feature];
		}
		return icons+' <a style="color:'+user.color+'">'+user.username+'</a>';
	},
	wrapMessage: function(css){
		return '<span'+ ((css==undefined) ? '':' class="'+css+'"') +'>: '+this.message+'</span>';
	},
	html: function(){
		return this.wrap(this.wrapTime() + this.wrapUser(this.user) + this.wrapMessage());
	}
});