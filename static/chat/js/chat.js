$(function() {
	c = new chat();
});

function chat() {

	// Need a better way or loading the user etc
	var userData = $('#destinychat').data('user');
	this.user = null;
	if(userData != null){
		this.user = new ChatUser(userData);
	};
	//

	this.gui = new destiny.fn.Chat({
		ui: '#destinychat',
		engine: this,
		msgQueue: [],
		onSend: function(str){
			if(this.engine.user == null){
				this.push(new ChatMessage("You must be logged in to send messages"));
				return;
			}
			
			if (str.substring(0, 1) === '/')
				return this.engine.handleCommand(str.substring(1));
			
			var message = new ChatUserMessage(str, this.engine.user);
			this.msgQueue.push(message);
			this.push(message);
			message.status(ChatMessageStatus.PENDING);
			this.engine.emit('MSG', {data: str});
		},
		updateMessageStatus: function(data){
			var found = false;
			for(var i in this.msgQueue){
				if(this.msgQueue[i].message == data.data){
					this.msgQueue[i].status();
					this.msgQueue[i] = null;
					delete(this.msgQueue[i]);
					found = true;
				}
			}
			return found;
		}
	});

	if (window.MozWebSocket)
		window.WebSocket = MozWebSocket;
	
	if ( !window.WebSocket ) {
		this.gui.push(new ChatMessage("This chat requires WebSockets."));
		return;
	}
	
	this.debug = true;
	this.sock = new WebSocket('ws://' + location.host + ':9998/ws');
	this.users = {};
	this.init();
}

chat.prototype.l = function() {
	if (!this.debug)
		return;
	console.log(arguments);
};
chat.prototype.init = function() {
	this.sock.onopen    = $.proxy(function() {
		var event = {data: 'OPEN ""'};
		this.parseAndDispatch(event)
	}, this);
	this.sock.onmessage = $.proxy(this.parseAndDispatch, this);
	this.sock.onclose   = $.proxy(function() {
		var event = {data: 'CLOSE ""'};
		this.parseAndDispatch(event)
	}, this);

	if(this.user){
		this.gui.push(new ChatMessage("Connecting as "+this.user.username+"..."));
	}else{
		this.gui.push(new ChatMessage("Connecting..."));
	}
	this.gui.disableInput();
	
	this.l = $.proxy(this.l, this);
	this.emit = $.proxy(this.emit, this);
};

// websocket stuff
chat.prototype.parseAndDispatch = function(e) {
	var eventname = e.data.split(' ', 1)[0],
			handler   = 'on' + eventname,
			obj       = JSON.parse(e.data.substring(eventname.length+1));
	
	this.l(e, handler, obj);
	if (this[handler])
		this[handler](obj, e);
};
chat.prototype.emit = function(eventname, data) {
	this.sock.send(eventname + " " + JSON.stringify(data));
};

// server events
chat.prototype.onPING = function(data) {
	this.emit('PONG', data)
	this.gui.ping();
};
chat.prototype.onOPEN = function() {
	this.gui.push(new ChatMessage("You are now connected"));
	this.gui.enableInput();
	this.loadHistory();
};
chat.prototype.onCLOSE = function() {
	this.gui.push(new ChatMessage("You have been disconnected"));
	this.gui.enableInput();
	this.loadHistory();
};
chat.prototype.onNAMES = function(data) {
	if (!data.users || data.users.length <= 0)
		return;
	// TODO present the connection count in a nice way? is it too much info?
	//this.connectioncount = data.connectioncount;
	for (var i = data.users.length - 1; i >= 0; i--) {
		this.users[data.users[i].username] = new ChatUser(data.users[i]);
	};
};
chat.prototype.onJOIN = function(data) {
	this.users[data.username] = new ChatUser(data);
};
chat.prototype.onQUIT = function(data) {
	this.users[data.nick].connections--;
	if (this.users[data.nick].connections <= 0)
		delete(this.users[data.nick])
};
chat.prototype.onMSG = function(data) {
	if(this.user != null && this.user.username != data.nick || !this.gui.updateMessageStatus(data)){
		this.gui.push(new ChatUserMessage(data.data, this.users[data.nick], data.timestamp));
	}
};
chat.prototype.onDELETE = function(data) {
	// TODO handle this nicer, but definitely do not show "message deleted"
	// maybe just collapse the lines?
	this.gui.removeUserLines(data.data);
};
chat.prototype.onMUTE = function(data) {
	// TODO make these messages distinct along with ban
	// data.data is the nick which has been muted, no info about duration
	this.gui.push(new ChatMessage(data.nick + " muted", data.timestamp));
};
chat.prototype.onUNMUTE = function(data) {
	this.gui.push(new ChatMessage(data.nick + " unmuted", data.timestamp));
};
chat.prototype.onBAN = function(data) {
	// data.data is the nick which has been banned, no info about duration
	this.gui.push(new ChatMessage(data.nick + " banned", data.timestamp));
};
chat.prototype.onUNBAN = function(data) {
	this.gui.push(new ChatMessage(data.nick + " unbanned", data.timestamp));
};
chat.prototype.onERR = function(data) {
	// data is a string now, TODO translate the raw error strings to something
	// human readable
	this.gui.push(new ChatMessage("Error: " + data));
};
chat.prototype.loadHistory = function() {
	if(!this.historyLoaded){
		this.historyLoaded = true;
		this.gui.push(new ChatMessage("Retrieving chat history..."));
		var self = this;
		$.ajax({
			type: 'get',
			url: destiny.baseUrl + 'chat/history.json',
			success: function(data){
				if(data.length > 0){
					data.reverse();
					self.gui.push(new ChatUIMessage('<hr>'));
					for(var i=0; i<data.length; ++i){
						self.gui.push(new ChatUserMessage(data[i].data, new ChatUser({username: data[i].username}), data[i].timestamp));
					}
					self.gui.push(new ChatUIMessage('<hr>'));
				}
				self = null;
			},
			error: function(){
				self.gui.push(new ChatMessage("Error getting history"));
				self = null;
			}
		});
	};
};
chat.prototype.handleCommand = function(str) {
	
	var parts     = str.split(" ");
	    command   = parts[0].toLowerCase(),
	    nickregex = /^[a-zA-Z0-9]{4,20}$/,
	    payload   = {};
	
	switch(command) {
		
		default:
			this.gui.push(new ChatMessage("Error: unknown command"));
			break;
		case "mute":
		case "ban":
			if (!nickregex.test(parts[1])) {
				this.gui.push(new ChatMessage("Error: Invalid nick - /" + command + " nick [time]"));
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
		case "unmute":
		case "unban":
		case "delete":
			if (!nickregex.test(parts[1])) {
				this.gui.push(new ChatMessage("Error: Invalid nick - /" + command + " nick"));
				return;
			}
			
			payload.data = parts[1];
			this.emit(command.toUpperCase(), payload);
			break;
		
		case "subonly":
			if (parts[1] != 'on' && parts[2] != 'off') {
				this.gui.push(new ChatMessage("Error: Invalid argument - /" + command + " on/off"));
				return;
			}
			
			payload.data = parts[1];
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