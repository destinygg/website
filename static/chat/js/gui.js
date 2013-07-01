(function($){
//https://developer.mozilla.org/en-US/docs/Web/Guide/User_experience/Displaying_notifications
//http://blog.teamtreehouse.com/adding-desktop-notifications-to-your-web-applications
	
	destiny.fn.Chat = function(props){
		$.extend(this, props);
		this.ui = $(this.ui);
		return this.init();
	};
	$.extend(destiny.fn.Chat.prototype, {

		maxlines: 50,
		lineCount: 0,
		scrollPlugin: null,
		ui: null,
		lines: null,
		output: null,
		input: null,
		onSend: $.noop,
		
		init: function(){
			// Optional params passed in via the data-options="{}" attribute
			$.extend(this, this.ui.data('options'));
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
			if(this.lineCount() >= this.maxlines){
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
				this.onSend(str, this.input[0]);
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
		},
		
		enableInput: function(){
			this.input.removeAttr('disabled', true);
		},
		
		disableInput: function(){
			this.input.attr('disabled', true);
		},
		
		ping: function(){
			
		}
		
	});
	
	// should be moved somewhere better
	$(window).on('resize.chat',function(){
		$('.chat.chat-frame').each(function(){
			$(this).data('chat').resize();
		});
	});
	
	
})(jQuery);

// USER FEATURES
var UserFeatures = {
	PROTECTED 	: 1,
	SUBSCRIBER	: 2,
	VIP			: 3,
	MODERATOR	: 4,
	ADMIN		: 5
};

var ChatMessageStatus = {
	SENT		: 'sent',
	PENDING		: 'pending',
	FAILED		: 'failed'
};

//CHAT USER
function ChatUser(args){
	this.username = args.username;
	this.userId = args.userId;
	this.features = [];
	this.connections = 0;
	this.color = '#efefef';
	$.extend(this, args);
	return this;
};
ChatUser.prototype.getFeatureHTML = function(){
	var icons = '';
	for (var i = this.features.length - 1; i >= 0; i--) {
		switch(parseInt(this.features[i])){
			case UserFeatures.PROTECTED :
				icons += '<i class="icon-eye-close" title="Protected"/>';
				break;
			case UserFeatures.SUBSCRIBER :
				icons += '<i class="icon-star" title="Subscriber"/>';
				break;
			case UserFeatures.VIP :
				icons += '<i class="icon-film" title="VIP"/>';
				break;
			case UserFeatures.MODERATOR :
				icons += '<i class="icon-leaf" title="Moderator"/>';
				break;
			case UserFeatures.ADMIN :
				icons += '<i class="icon-fire" title="Administrator"/>';
				break;
		}
	}
	return icons;
};

//UI MESSAGE
function ChatUIMessage(html){
	this.init(html);
	return this;
};
ChatUIMessage.prototype.init = function(html){
	this.message = html;
	return this;
};
ChatUIMessage.prototype.html = function(){
	return this.wrap(this.wrapMessage());
};
ChatUIMessage.prototype.wrap = function(content){
	return '<div>'+content+'</div>';
};
ChatUIMessage.prototype.wrapMessage = function(css){
	return $('<span'+ ((css==undefined) ? '':' class="'+css+'"') +' />').html(this.message).html();
};

//BASE MESSAGE
function ChatMessage(message, timestamp){
	this.init(message, timestamp);
	return this;
};
ChatMessage.prototype.init = function(message, timestamp){
	this.message = message;
	this.timestamp = moment(timestamp);
	this.state = null;
	return this;
};
ChatMessage.prototype.status = function(state){
	$(this).triggerHandler('status', [state]);
	this.state = state;
	return this;
};
ChatMessage.prototype.wrapTime = function(){
	return '<time datetime="'+this.timestamp.format('MMMM Do YYYY, h:mm:ss a')+'">'+this.timestamp.format('HH:mm')+' </time>';
};
ChatMessage.prototype.wrapMessage = function(css){
	return $('<span'+ ((css==undefined) ? '':' class="'+css+'"') +' />').text(this.message).html();
};
ChatMessage.prototype.html = function(){
	return this.wrap(this.wrapTime() + this.wrapMessage());
};
ChatMessage.prototype.wrap = function(content){
	return '<div>'+content+'</div>';
};
// USER MESSAGE
function ChatUserMessage(message, user, timestamp){
	this.init(message, timestamp);
	this.user = user;
	return this;
};
$.extend(ChatUserMessage.prototype, ChatMessage.prototype);
ChatUserMessage.prototype.wrapUser = function(user){
	return user.getFeatureHTML() +' <a style="color:'+user.color+'">'+user.username+'</a>';
};
ChatUserMessage.prototype.wrapMessage = function(css){
	return $('<span'+ ((css==undefined) ? '':' class="'+css+'"') +' />').text(': '+this.message).html();
};
ChatUserMessage.prototype.html = function(){
	return this.wrap(this.wrapTime() + this.wrapUser(this.user) + this.wrapMessage());
};