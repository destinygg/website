
// USER FEATURES
destiny.UserFeatures = {
    PROTECTED     : 'protected',
    SUBSCRIBER    : 'subscriber',
    SUBSCRIBERT2  : 'flair1',
    SUBSCRIBERT3  : 'flair3',
    SUBSCRIBERT4  : 'flair8',
    VIP           : 'vip',
    MODERATOR     : 'moderator',
    ADMIN         : 'admin',
    BOT           : 'bot',
    NOTABLE       : 'flair2',
    TRUSTED       : 'flair4',
    CONTRIBUTOR   : 'flair5',
    COMPCHALLENGE : 'flair6',
    EVENOTABLE    : 'flair7'
};

function chat(element, user, options) {
	this.server             = 'ws://' + options.host + ':' + options.port + '/ws';
	this.connected          = false;
	this.debug              = false;
	this.users              = [];
	this.ignorelist         = {};
	this.controlevents      = ["MUTE", "UNMUTE", "BAN", "UNBAN", "SUBONLY"];
	this.errorstrings       = {
		"nopermission"      : "You do not have the required permissions to use that",
		"protocolerror"     : "Invalid or badly formatted",
		"needlogin"         : "You have to be logged in to use that",
		"invalidmsg"        : "The message was invalid",
		"throttled"         : "Throttled! You were trying to send messages too fast",
		"duplicate"         : "The message is identical to the last one you sent",
		"muted"             : "You are muted (subscribing auto-removes mutes)",
		"submode"           : "The channel is currently in subscriber only mode",
		"needbanreason"     : "Providing a reason for the ban is mandatory",
		"banned"            : "You have been banned (subscribing auto-removes non-permanent bans), disconnecting",
		"requiresocket"     : "This chat requires WebSockets",
		"toomanyconnections": "Only 5 concurrent connections allowed",
		"socketerror"       : "Error contacting server",
		"notfound"          : "The user was not found"
	};
	this.user               = new ChatUser(user);
	this.gui                = new ChatGui(element, this, options);
	this.previousemote      = null;
	this.originemote        = null;
	return this;
};
chat.prototype.start = function(){
	if (window.MozWebSocket)
		window.WebSocket = MozWebSocket;
	
	if (!window.WebSocket)
		return this.gui.push(new ChatErrorMessage(this.errorstrings.requiresocket));
	
	this.gui.onSend = function(str){
		if(this.engine.user == null || !this.engine.user.username)
			return this.push(new ChatErrorMessage(this.engine.errorstrings.requiresocket));
		
		if (str.substring(0, 4) === '/me ')
			var message = str.substring(4);
		else
			var message = str;
		
		// If this is an emoticon spam, emit the message but don't add the line immediately
		if ($.inArray(message, this.emoticons) != -1 && this.engine.previousemote && this.engine.previousemote.message == message)
			return this.engine.emit('MSG', {data: str});
		
		if (str.substring(0, 1) === '/')
			return this.engine.handleCommand(str.substring(1));

		// Normal user message, emit
		this.push(new ChatUserMessage(str, this.engine.user), (!this.engine.connected) ? 'unsent' : 'pending');
		this.engine.emit('MSG', {data: str});
	};

	this.gui.loadBacklog();
	this.gui.loadBroadcasts();
	this.loadIgnoreList();
	this.dispatchBacklog = $.proxy(this.dispatchBacklog, this);
	this.gui.push(new ChatStatusMessage("Connecting..."));
	this.init();
};
chat.prototype.l = function() {
	if (!this.debug)
		return;
	
	var log = Function.prototype.bind.call(console.log, console);
	log.apply(console, arguments);
};
chat.prototype.init = function() {
	this.sock           = new WebSocket(this.server);
	this.sock.onopen    = $.proxy(function() {
		var event = {data: 'OPEN ""'};
		this.parseAndDispatch(event)
	}, this);
	this.sock.onerror   = $.proxy(function() {
		var event = {data: 'ERR "socketerror"'};
		this.parseAndDispatch(event)
	}, this);
	this.sock.onmessage = $.proxy(this.parseAndDispatch, this);
	this.sock.onclose   = $.proxy(function() {
		var event = {data: 'CLOSE ""'};
		this.parseAndDispatch(event)
	}, this);
	
	this.l = $.proxy(this.l, this);
	this.emit = $.proxy(this.emit, this);
};
chat.prototype.loadIgnoreList = function() {
	if (!localStorage)
		return;
	
	this.ignorelist = JSON.parse(localStorage['chatignorelist'] || '{}');
};

// websocket stuff
chat.prototype.parseAndDispatch = function(e) {
	var eventname   = e.data.split(' ', 1)[0],
			handler = 'on' + eventname,
			obj     = JSON.parse(e.data.substring(eventname.length+1));
	
	this.l(handler, obj);
	if (eventname == 'PING') { // handle pinging in-line, cant parse 64bit ints
		return this.sock.send('PONG ' + e.data.substring(eventname.length+1));
	}
	
	if (this[handler]) {
		var message = this[handler](obj);
		if (message) {
			
			if ($.inArray(eventname, this.controlevents) >= 0)
				this.gui.push(message, 'control');
			else
				this.gui.push(message);
		}
	}
};
chat.prototype.dispatchBacklog = function(e) {
	var handler = 'on' + e.event,
	    obj     = {
		nick     : e.username,
		data     : e.data || e.target,
		features : e.features,
		timestamp: moment.utc(e.timestamp).valueOf()
	};
	
	if (this[handler])
		return this[handler](obj);
	
};
chat.prototype.emit = function(eventname, data) {
	this.sock.send(eventname + " " + JSON.stringify(data));
};

// server events
chat.prototype.onOPEN = function() {
	this.connected = true;
};
chat.prototype.onCLOSE = function() {
	if (this.dontconnect) return;
	var rand = ((this.connected) ? getRandomInt(501,3000) : getRandomInt(5000,30000));
	setTimeout($.proxy(this.onRECONNECT, this), rand);
	this.connected = false;
	return new ChatStatusMessage("Disconnected... reconnecting in "+ Math.round(rand/1000) +" seconds");
};
chat.prototype.onNAMES = function(data) {
	if (!data.users || data.users.length <= 0)
		return new ChatStatusMessage("Connected");
	
	for (var i = data.users.length - 1; i >= 0; i--) {
		var u = data.users[i];
		this.users[u.nick] = new ChatUser(u);
		this.gui.autoCompletePlugin.addData(u.nick, 1);
	};
	
	this.gui.trigger('names', data);
	return new ChatStatusMessage("Connected. Server connections: " + data.connectioncount);
};
chat.prototype.onJOIN = function(data) {
	this.users[data.nick] = new ChatUser(data);
	this.gui.autoCompletePlugin.addData(data.nick, 1);
	this.gui.trigger('join', data);
};
chat.prototype.onQUIT = function(data) {
	if (this.users[data.nick]) {
		delete(this.users[data.nick]);
		this.gui.trigger('quit', data);
	}
};
chat.prototype.onMSG = function(data) {
	// If we have the same user as the one logged in, update the features
	if(this.user.username == data.nick && $.isArray(data.features))
		this.user.features = data.features;

	// Emote
	if (data.data.substring(0, 4) === '/me ')
		var emoticon = data.data.substring(4);
	else
		var emoticon = data.data;
	
	if ($.inArray(emoticon, this.gui.emoticons) != -1) {
		if (this.previousemote && this.previousemote.message == emoticon) {
			if(this.previousemote.emotecount === 1){
				this.previousemote.emotecount = 2;
				if(this.originemote){
					this.originemote.ui.remove();
					this.originemote = null;
				}
				return this.previousemote;
			}else{
				this.previousemote.incEmoteCount();
				return;
			}
		} else 
			this.previousemote = new ChatEmoteMessage(emoticon, data.timestamp);
	} else
		this.previousemote = null;
	// End emote
	
	var messageui = this.gui.resolveMessage(data);
	
	if(messageui && this.previousemote)
		this.originemote = messageui;
	
	if(this.user.username != data.nick || !messageui){
		if (this.ignorelist[data.nick.toLowerCase()]) // user ignored
			return;
		
		var user = this.users[data.nick];
		if (!user) {
			user = new ChatUser(data);
			if (user.nick == this.user.nick)
				this.user = user;
		} else
			this.gui.autoCompletePlugin.addData(data.nick, data.timestamp);
		
		if (user && user.features.length != data.features.length)
			this.users[data.nick] = user;

		var usermessage = new ChatUserMessage(data.data, user, data.timestamp);
		
		if(this.previousemote)
			this.originemote = usermessage;

		// Returned message gets appended to GUI
		return usermessage;
	}
};
chat.prototype.onMUTE = function(data) {
	var suppressednick = data.data;
	if (this.user.username.toLowerCase() == data.data.toLowerCase())
		suppressednick = 'You have been';
    else if (
        $.inArray(destiny.UserFeatures.SUBSCRIBERT3, this.user.features) == -1 &&
        $.inArray(destiny.UserFeatures.SUBSCRIBERT4, this.user.features) == -1 &&
        $.inArray(destiny.UserFeatures.SUBSCRIBERT2, this.user.features) == -1 &&
        $.inArray(destiny.UserFeatures.ADMIN, this.user.features) == -1 &&
        $.inArray(destiny.UserFeatures.MODERATOR, this.user.features) == -1 
    )
		this.gui.removeUserMessages(data.data);
	
	return new ChatCommandMessage(suppressednick + " muted by " + data.nick, data.timestamp);
};
chat.prototype.onUNMUTE = function(data) {
	var suppressednick = data.data;
	if (this.user.username.toLowerCase() == data.data.toLowerCase())
		suppressednick = 'You have been';
	
	return new ChatCommandMessage(suppressednick + " unmuted by " + data.nick, data.timestamp);
};
chat.prototype.onBAN = function(data) {
	// data.data is the nick which has been banned, no info about duration
	var suppressednick = data.data;
	if (this.user.username.toLowerCase() == data.data.toLowerCase()) {
		suppressednick = 'You have been';
		if(!this.gui.backlogLoading){
			setTimeout(function() {
				window.location.href = "/banned";
			}, 1500);
		}
    } else if(
        $.inArray(destiny.UserFeatures.SUBSCRIBERT3, this.user.features) == -1 &&
        $.inArray(destiny.UserFeatures.SUBSCRIBERT4, this.user.features) == -1 &&
        $.inArray(destiny.UserFeatures.SUBSCRIBERT2, this.user.features) == -1 &&
        $.inArray(destiny.UserFeatures.ADMIN, this.user.features) == -1 &&
        $.inArray(destiny.UserFeatures.MODERATOR, this.user.features) == -1 
    )
		this.gui.removeUserMessages(data.data);

	return new ChatCommandMessage(suppressednick + " banned by " + data.nick, data.timestamp);
};
chat.prototype.onUNBAN = function(data) {
	var suppressednick = data.data;
	if (this.user.username.toLowerCase() == data.data.toLowerCase())
		suppressednick = 'You have been';
	
	return new ChatCommandMessage(suppressednick + " unbanned by " + data.nick, data.timestamp);
};
chat.prototype.onERR = function(data) {
	if (data == "toomanyconnections" || data == "banned")
		this.dontconnect = true;

	if (data == "banned" && !this.gui.backlogLoading)
		window.location.href = "/banned";

	return new ChatErrorMessage(this.errorstrings[data]);
};
chat.prototype.onREFRESH = function() {
	window.location.href = window.location.href;
};
chat.prototype.onRECONNECT = function() {
	this.init();
};
chat.prototype.onSUBONLY = function(data) {
	var submode = data.data == 'on'? 'enabled': 'disabled';
	return new ChatCommandMessage("Subscriber only mode "+submode+" by " + data.nick, data.timestamp);
};
chat.prototype.onBROADCAST = function(data) {
	if (data.data.substring(0, 9) == 'redirect:') {
		var url = data.data.substring(9);
		var message = new ChatBroadcastMessage("Redirecting in 5 seconds to " + url, data.timestamp);
		setTimeout(function() {
			// try redirecting the parent window too if possible
			if (window.parent)
				window.parent.location = url;

			window.location = url;
		}, 5000 );
	} else
		var message = new ChatBroadcastMessage(data.data, data.timestamp);

	message.onAPPEND = function(gui){
		gui.addBroadcastUI(message.message);
	};
	return message;
};

chat.prototype.handleCommand = function(str) {

	var parts     = str.split(" ");
	    command   = parts[0].toLowerCase(),
	    nickregex = /^[a-zA-Z0-9_]{3,20}$/,
	    payload   = {};
	
	if (str.substring(0, 1) === '/') {
		payload.data = "/" + str;
		this.emit("MSG", payload);
		return;
	}
	
	this.l(command, parts);
	
	switch(command) {
	
		default:
			this.gui.push(new ChatErrorMessage("Unknown command"));
			break;
			
		case "emotes":
			this.gui.push(new ChatInfoMessage("Available emoticons: "+this.gui.emoticons.join(", ")+" (www.destiny.gg/emotes)"));
			break;
			
		case "help":
			this.gui.push(new ChatInfoMessage("Available commands: /emotes /me /ignore (without arguments to list the nicks ignored) /unignore /highlight (highlights target nicks messages for easier visibility) /unhighlight /maxlines /mute /unmute /subonly /ban /ipban /unban (also unbans ip bans) /timestampformat"));
			break;
			
		case "me":
			payload.data = "/" + str;
			this.emit("MSG", payload);
			break;
			
		case "ignore":
			if (!localStorage) {
				this.gui.push(new ChatErrorMessage("Ignore is unavailable, no localStorage"));
				return;
			}
			
			if (!parts[1]) {
				var nicks = [];
				$.each(this.ignorelist, function(key) {
					nicks.push(key);
				});
				if (nicks.length == 0) {
					this.gui.push(new ChatInfoMessage("Your ignore list is empty"));
					return;
				}
				this.gui.push(new ChatInfoMessage("Ignoring the following people: "+nicks.join(', ')));
				return
			}
			
			var nick = parts[1].toLowerCase();
			if (!nickregex.test(nick)) {
				this.gui.push(new ChatErrorMessage("Invalid nick - /ignore nick"));
				return;
			}
			
			this.ignorelist[nick] = true;
			this.gui.removeUserMessages(nick);
			this.gui.push(new ChatStatusMessage("Ignoring "+nick));
			
			localStorage['chatignorelist'] = JSON.stringify(this.ignorelist);
			this.loadIgnoreList();
			break;
			
		case "unignore":
			if (!localStorage) {
				this.gui.push(new ChatErrorMessage("Ignore is unavailable, no localStorage"));
				return;
			}
			
			if (!parts[1] || !nickregex.test(parts[1].toLowerCase())) {
				this.gui.push(new ChatErrorMessage("Invalid nick - /ignore nick"));
				return;
			}
			var nick = parts[1].toLowerCase();
			
			delete(this.ignorelist[nick]);
			this.gui.push(new ChatStatusMessage(""+nick+" has been removed from your ignore list"));
			
			localStorage['chatignorelist'] = JSON.stringify(this.ignorelist);
			this.loadIgnoreList();
			break;
			
		case "mute":
			if (parts.length == 1) {
				this.gui.push(new ChatInfoMessage("Usage: /" + command + " nick[ time]"));
				return;
			}
			
			// TODO bans are a little more involved, requiring a reason + ip bans + permbans
			if (!nickregex.test(parts[1])) {
				this.gui.push(new ChatErrorMessage("Invalid nick - /" + command + " nick[ time]"));
				return;
			}
			
			var duration = null;
			if (parts[2])
				duration = this.parseTimeInterval(parts[2])
			
			payload.data = parts[1];
			if (duration && duration > 0)
				payload.duration = duration;
			
			this.emit(command.toUpperCase(), payload);
			break;
			
		case "ban":
		case "ipban":
			if (parts.length < 4) {
				this.gui.push(new ChatInfoMessage("Usage: /" + command + " nick time reason (time can be 'permanent')"));
				return;
			}
			
			if (!nickregex.test(parts[1])) {
				this.gui.push(new ChatErrorMessage("Invalid nick"));
				return;
			}
			
			payload.nick = parts[1];
			if (command == "ipban")
				payload.banip = true;
			
			if (/^perm/i.test(parts[2]))
				payload.ispermanent = true;
			else
				payload.duration = this.parseTimeInterval(parts[2]);
			
			payload.reason = parts.slice(3, parts.length).join(' ');
			if (!payload.reason) {
				this.gui.push(new ChatErrorMessage("Providing a reason is mandatory"));
				return;
			}
			
			this.emit("BAN", payload);
			break;
			
		case "unmute":
		case "unban":
			if (parts.length == 1) {
				this.gui.push(new ChatInfoMessage("Usage: /" + command + " nick"));
				return;
			}
			
			if (!nickregex.test(parts[1])) {
				this.gui.push(new ChatErrorMessage("Invalid nick - /" + command + " nick"));
				return;
			}
			
			payload.data = parts[1];
			this.emit(command.toUpperCase(), payload);
			break;
		
		case "subonly":
			if (parts[1] != 'on' && parts[1] != 'off') {
				this.gui.push(new ChatErrorMessage("Invalid argument - /" + command + " on/off"));
				return;
			}
			
			payload.data = parts[1];
			this.emit(command.toUpperCase(), payload);
			break;
			
		case "maxlines":
			if (!parts[1]) {
				this.gui.push(new ChatInfoMessage("Current number of lines shown: " + this.gui.maxlines));
				return;
			}
			
			var newmaxlines = Math.abs(parseInt(parts[1], 10));
			if (!newmaxlines) {
				this.gui.push(new ChatErrorMessage("Invalid argument - /maxlines is expecting a number"));
				return;
			}
			
			this.gui.saveChatOption('maxlines', newmaxlines);
			this.gui.maxlines = newmaxlines;
			this.gui.push(new ChatInfoMessage("Current number of lines shown: " + this.gui.maxlines));
			break;
			
		case "unhighlight":
		case "highlight":
			if (!parts[1]) {
				var nicks = [];
				$.each(this.gui.highlightnicks, function(k, v) {
					nicks.push(k);
				});
				
				this.gui.push(new ChatInfoMessage("Currenty highlighted users: " + nicks.join(', ')));
				return;
			}
			
			if (!nickregex.test(parts[1])) {
				this.gui.push(new ChatErrorMessage("Invalid nick - /" + command + " nick"));
				return;
			}
			
			var nick = parts[1].toLowerCase();
			if (command == "unhighlight") {
				delete(this.gui.highlightnicks[nick]);
				this.gui.push(new ChatInfoMessage("No longer highlighting: " + nick));
			} else {
				this.gui.highlightnicks[nick] = true;
				this.gui.push(new ChatInfoMessage("Now highlighting: " + nick));
			}
			
			this.gui.saveChatOption('highlightnicks', this.gui.highlightnicks);
			break;
			
		case "timestampformat":
			if (!parts[1]) {
				this.gui.push(new ChatInfoMessage("Current format: " + this.gui.timestampformat + " (the default is 'HH:mm', for more info: http://momentjs.com/docs/#/displaying/format/)"));
				return;
			}
			
			var format = str.substring(command.length);
			if ( !/^[a-z :.,-\\*]+$/i.test(format)) {
				this.gui.push(new ChatErrorMessage("Invalid format, see: http://momentjs.com/docs/#/displaying/format/"));
				return;
			}
			
			this.gui.timestampformat = format;
			this.gui.saveChatOption('timestampformat', format);
			this.gui.push(new ChatInfoMessage("New format: " + this.gui.timestampformat));
			break;
			
		case "broadcast":
			payload.data = str.substring(command.length+1);
			this.emit(command.toUpperCase(), payload);
			break;
			
	};
};
chat.prototype.parseTimeInterval = function(str) {
	var nanoseconds = 0,
		units   = {
		s: 1000000000,
		sec: 1000000000, secs: 1000000000,
		second: 1000000000, seconds: 1000000000,
		
		m: 60000000000,
		min: 60000000000, mins: 60000000000,
		minute: 60000000000, minutes: 60000000000,

		h: 3600000000000,
		hr: 3600000000000, hrs: 3600000000000,
		hour: 3600000000000, hours: 3600000000000,

		d: 86400000000000,
		day: 86400000000000, days: 86400000000000,
	};
	str.replace(/(\d+(?:\.\d*)?)([a-z]+)?/ig, function($0, number, unit) {
		if (unit)
			number *= units[unit.toLowerCase()] || units.s;
		else
			number *= units.s;
		
		nanoseconds += +number;
	});
	return nanoseconds;
};