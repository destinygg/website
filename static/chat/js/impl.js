(function($){
	
	// First we setup the basic types and UI
	// Then we initialize the chat
	// Then we start communicating
	
	// Simple chat roles
	var ChatUserRoles = {
		USER		:0,
		ADMIN		:1,
		MODERATOR	:2,
		SUBSCRIBER	:3,
		BROADCASTER	:5
	};
	
	// Simple chat user
	var ChatUser = function(args){
		this.username = args.username;
		this.userId = args.userId;
		this.roles = [];
		$.extend(this, args);
		return this;
	};

	// Simple chat message
	var ChatMessage = function(message){
		var self = this;
		self.timestamp = moment();
		self.message = message;
		self.wrap = function(){
			return $('<chat class="h-chat" />');
		};
		self.wrapTime = function(){
			return $('<time class="p-time"/>').text(self.timestamp.format('HH:mm')+' ').attr('datetime', self.timestamp.format('MMMM Do YYYY, h:mm:ss a'));
		};
		self.wrapMessage = function(){
			return $('<span class="p-message"/>').text(' ' + this.message);
		};
		self.html = function(){
			return this.wrap()
				.append(this.wrapTime())
				.append(this.wrapMessage());
		};
		return this;
	};

	// Simple chat message with a user
	var ChatUserMessage = function(message, user){
		$.extend(this, new ChatMessage(message));
		var self = this;
		self.user = user;
		self.wrapUser = function(user){
			var usr = $('<span class="p-user"/>').text(' '+user.username).css('color', user.color);
			if($.inArray(ChatUserRoles.MODERATOR, user.roles)>-1){
				usr.prepend('<i class="icon-leaf" title="Moderator">[MODERATOR]</i>');
			}
			if($.inArray(ChatUserRoles.ADMIN, user.roles)>-1){
				usr.prepend('<i class="icon-fire" title="Administrator">[ADMIN]</i>');
			}
			if($.inArray(ChatUserRoles.SUBSCRIBER, user.roles)>-1){
				usr.prepend('<i class="icon-star" title="Subscriber">[SUBSCRIBER]</i>');
			}
			if($.inArray(ChatUserRoles.BROADCASTER, user.roles)>-1){
				usr.prepend('<i class="icon-facetime-video" title="Broadcaster">[BROADCASTER]</i>');
			}
			return usr;
		}
		self.html = function(){
			return this.wrap()
				.append(this.wrapTime())
				.append(this.wrapUser(this.user))
				.append(': ')
				.append(this.wrapMessage());
		}
		return this;
	};

	// Whisper chat message
	var ChatWhisperMessage = function(message, user, fromUser){
		$.extend(this, new ChatUserMessage(message));
		var self = this;
		self.fromUser = fromUser;
		self.html = function(){
			return this.wrap()
				.append(this.wrapTime())
				.append(this.wrapUser(this.fromUser))
				.append(' [w] ')
				.append(this.wrapMessage().addClass('p-whisper'));
		};
		return this;
	};

	
	
	//--- INITIALIZE ---
	
	var chat = new destiny.chat({
		// The id of the chat element
		ui: '#destinyChat',
		// Maximum chat lines to keep
		maxLines: 100,
		// This currently logged in user - you need to set this
		user: null
	});
	
	// Bind to window resize event
	$(window).on('resize',function(){
		chat.resize();
	});
	
	// Demonstration binding to purge event
	$(chat).on('purge', function(){
		chat.push(new ChatMessage('Chat purged by ' + chat.user.username));
	});
	
	// The ... send event, used when a person sends a message to the chat
	// Probably will send an a request to the chat server
	$(chat).on('send', function(e, str, input){
		// Push the message to the UI immediately, but add a pending class
		var message = chat.push(new ChatUserMessage(str, chat.user)).addClass('pending');
		// Simulate 300ms ping
		window.setTimeout(function(){ message.removeClass('pending').addClass('sent') }, 300);
	});
	
	// Gotta init meng
	chat.init(); 
	// End chat setup
	
	
	
	//--- COMMUNICATING ---
	
	// Global messages
	chat.push(new ChatMessage('Welcome to destiny.gg'));
	chat.push(new ChatMessage('Retrieving user info...'));
	
	// Temp way of getting user info
	$.ajax({
		url: destiny.baseUrl + 'profile/info.json',
		async: false,
		success: function(data){
			chat.user = data;
			chat.push(new ChatMessage('User '+ data.username + ' entered the room'));
		}
	});
	
	// Mock users
	var RandomColor = {letters:'0123456789ABCDEF'.split(''), gen: function(){ for (var c='',i=0; i<6; i++) c += this.letters[Math.round(Math.random() * 15)]; return '#'+c;}};
	var StevenBonnell = new ChatUser({username: 'StevenBonnell', userId: 312, roles: [ChatUserRoles.USER,ChatUserRoles.BROADCASTER], color: 'black'});
	var Thomas = new ChatUser({username: 'Thomas', userId: 5432, roles: [ChatUserRoles.USER,ChatUserRoles.ADMIN], color: 'red'});
	var Jeff = new ChatUser({username: 'Jeff', userId: 12312, roles: [ChatUserRoles.USER,ChatUserRoles.SUBSCRIBER], color: RandomColor.gen()});
	var Pleb = new ChatUser({username: 'Pleb', userId: 323, roles: [ChatUserRoles.USER], color: RandomColor.gen()});
	var Gay4Steve = new ChatUser({username: 'Gay4Steve', userId: 452, roles: [ChatUserRoles.USER,ChatUserRoles.SUBSCRIBER,ChatUserRoles.MODERATOR], color: RandomColor.gen()});
	
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
	
	
	// Socket stub
	var ws = new WebSocket('ws://localhost:8000');
	ws.onmessage = function(e) {
		// standard
		var data = {
			type: 'push',
			response: {time: 0},
			message: {text: '',user: null}
		};
		// merge to socket message
		$.extend(data, JSON.parse(e.data));
		// Check the type, push the message
		if(data.type == 'push'){
			if(data.message.user != null){
				chat.push(new ChatUserMessage(data.message.text, data.message.user));
			}else{
				chat.push(new ChatMessage(data.message.text));
			}
		}
	};
	//
	
})(jQuery);