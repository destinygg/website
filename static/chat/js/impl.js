(function($){
	
	// First we setup the basic types and UI
	// Then we initialize the chat
	// Then we start communicating
	
	var ChatUserRoles = {
		USER		: 0,
		ADMIN		: 1,
		MODERATOR	: 2,
		SUBSCRIBER	: 3,
		BROADCASTER	: 5
	};
	var ChatMessageStatus = {
		SENT		: 'sent',
		PENDING		: 'pending',
		FAILED		: 'failed'
	};

	// Simple chat user
	var ChatUser = function(args){
		$.extend(this, new destiny.fn.ChatUser(args));
		return this;
	}

	// Simple chat message
	var ChatMessage = function(message){
		this.init(message);
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

	// Simple chat message with a user
	var ChatUserMessage = function(message, user){
		this.init(message);
		this.user = user;
		return this;
	};
	$.extend(ChatUserMessage.prototype, ChatMessage.prototype, {
		wrapUser: function(user){
			var icon = '';
			if($.inArray(ChatUserRoles.MODERATOR, user.roles)>-1){
				icon += '<i class="icon-leaf" title="Moderator" />';
			}
			if($.inArray(ChatUserRoles.ADMIN, user.roles)>-1){
				icon += '<i class="icon-fire" title="Administrator" />';
			}
			if($.inArray(ChatUserRoles.SUBSCRIBER, user.roles)>-1){
				icon += '<i class="icon-star" title="Subscriber" />';
			}
			if($.inArray(ChatUserRoles.BROADCASTER, user.roles)>-1){
				icon += '<i class="icon-facetime-video" title="Broadcaster" />';
			};
			return icon+' <a style="color:'+user.color+'">'+user.username+'</a>';
		},
		wrapMessage: function(css){
			return '<span'+ ((css==undefined) ? '':' class="'+css+'"') +'>: '+this.message+'</span>';
		},
		html: function(){
			return this.wrap(this.wrapTime() + this.wrapUser(this.user) + this.wrapMessage());
		}
	});

	// Whisper chat message
	var ChatWhisperMessage = function(message, user, fromUser){
		this.init(message);
		this.fromUser = fromUser;
		this.user = user;
		return this;
	};
	$.extend(ChatWhisperMessage.prototype, ChatUserMessage.prototype, {
		html: function(){
			return this.wrap(this.wrapTime() + this.wrapUser(this.fromUser) + ' [w] ' + this.wrapMessage('p-whisper'));
		}
	});
	
	
	//--- INITIALIZE ---
	var chat = new destiny.fn.Chat({
		// The selector of the chat element $(selector)
		ui: '#destinychat',
		// Maximum chat lines to keep
		maxLines: 250,
		// This currently logged in user - you need to set this
		user: null
	});
	// Bind to window resize event - this will resize all chats
	$(window).on('resize.chat',function(){
		$('.chat.chat-frame').each(function(){
			$(this).data('chat').resize();
		});
	});
	
	
	// The ... send event, used when a person sends a message to the chat
	// Probably will send an a request to the chat server
	$(chat).on('send', function(e, str){
		// Push the message to the UI immediately, all new messages have the "pending" state
		var message = chat.push(new ChatUserMessage(str, chat.user));
		message.status(ChatMessageStatus.PENDING);
		// Simulate 300ms ping
		window.setTimeout(function(){ 
			//message.status(ChatMessageStatus.FAILED);
			message.status();
			message = null;
		}, 300);
	});
	// End chat setup
	
	// Demonstration binding to purge event
	$(chat).on('purge', function(){
		chat.push(new ChatMessage('Chat purged by ' + chat.user.username));
	});
	
	
	//--- COMMUNICATING ---
	var RandomColor = {letters:'0123456789ABCDEF'.split(''), gen: function(){ for (var c='',i=0; i<6; i++) c += this.letters[Math.round(Math.random() * 15)]; return '#'+c;}};
	
	// Global messages
	chat.push(new ChatMessage('Welcome to destiny.gg'));
	chat.push(new ChatMessage('Retrieving user info...'));
	
	var users = [];
	
	// Temp way of getting user info
	$.ajax({
		url: destiny.baseUrl + 'profile/info.json',
		async: false,
		success: function(data){
			chat.user = data;
			chat.user.color = RandomColor.gen();
			chat.push(new ChatMessage('User '+ data.username + ' entered the room'));
			users.push(chat.user);
		}
	});
	
	// Mock users
	var StevenBonnell = new ChatUser({username: 'StevenBonnell', userId: 312, roles: [ChatUserRoles.USER,ChatUserRoles.BROADCASTER], color: 'red'});
	var Thomas = new ChatUser({username: 'Thomas', userId: 5432, roles: [ChatUserRoles.USER,ChatUserRoles.ADMIN], color: 'red'});
	var Jeff = new ChatUser({username: 'Jeff', userId: 12312, roles: [ChatUserRoles.USER,ChatUserRoles.SUBSCRIBER], color: RandomColor.gen()});
	var Pleb = new ChatUser({username: 'Pleb', userId: 323, roles: [ChatUserRoles.USER], color: RandomColor.gen()});
	var Gay4Steve = new ChatUser({username: 'Gay4Steve', userId: 452, roles: [ChatUserRoles.USER,ChatUserRoles.SUBSCRIBER,ChatUserRoles.MODERATOR], color: RandomColor.gen()});
	
	users.push(StevenBonnell);
	users.push(Thomas);
	users.push(Jeff);
	users.push(Pleb);
	users.push(Gay4Steve);
	
	chat.push(new ChatWhisperMessage('This is what a whisper from ' + Pleb.username + ' to ' + chat.user.username + ' looks like', chat.user, Pleb));
	chat.push(new ChatUserMessage('Hello '+Thomas.username+'....', Jeff));
	chat.push(new ChatUserMessage('Oh, hello '+Jeff.username+'!', Thomas));
	chat.push(new ChatUserMessage('IS STEBEN DONE? :)', Pleb));
	chat.push(new ChatUserMessage('IS STEBEN DONE? :) :) :)', Pleb));
	chat.push(new ChatUserMessage('IS STEBEN DONE?', Pleb));
	chat.push(new ChatUserMessage('IS STEBEN DONE?', Gay4Steve));
	chat.push(new ChatUserMessage('IS STEBEN DONE? ^@#!@#$^$%& $%&$%^', Pleb));
	chat.push(new ChatUserMessage('IS STEBEN DONE?', Pleb));
	chat.push(new ChatUserMessage('IS STEBEN DONE?', Pleb));
	chat.push(new ChatUserMessage('IS STEBEN DONE?', Pleb));
	chat.push(new ChatUserMessage('IS STEBEN DONE?', Pleb));
	chat.push(new ChatUserMessage('STFU!', Thomas));
	chat.push(new ChatUserMessage('I TYPE IN ALL CAPS!', StevenBonnell));
	
	/**
	window.setInterval(function(){
		var user = users[Math.floor(Math.random()*users.length)];
		chat.push(new ChatUserMessage('IS STEBEN DONE?', user));
	},1);**/
	
})(jQuery);