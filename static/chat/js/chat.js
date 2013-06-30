$(function() {
	c = new chat();
});

function chat() {
	if (window.MozWebSocket)
		window.WebSocket = MozWebSocket;
	
	if ( !window.WebSocket ) {
		// TODO print warning
		return
	}
	
	this.debug    = true;
	this.sock     = new WebSocket('ws://' + location.host + ':9998/ws');
	this.users    = {};

	this.gui = new destiny.fn.Chat({
		ui: '#destinychat',
		maxLines: 150,
		user: null,
		engine: this,
		onSend: function(str, input){
			//var message = chat.push(new ChatUserMessage(str, chat.user));
			//message.status(ChatMessageStatus.PENDING);
			this.engine.emit('MSG', {data: str});
			//message.status();
		}
	});
	
	this.gui.push(new ChatMessage("Connecting..."));
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
};
chat.prototype.onOPEN = function() {
	this.gui.push(new ChatMessage("Connected"));
};
chat.prototype.onCLOSE = function() {
	this.gui.push(new ChatMessage("Disconnected"));
};
chat.prototype.onNAMES = function(data) {
	if (!data.users || data.users.length <= 0)
		return;
	
	// TODO present the connection count in a nice way? is it too much info?
	//this.connectioncount = data.connectioncount;
	for (var i = data.users.length - 1; i >= 0; i--) {
		var nick        = data.users[i].nick,
		    features    = data.users[i].features || [],
		    connections = data.users[i].connections,
		    color		= '#efefef';
		
		this.users[nick] = {username: nick, connections: connections, features: features, color: color};
	};
	
};
chat.prototype.onJOIN = function(data) {
	var features    = data.features || [],
	    connections = data.connections;
	
	this.users[data.nick] = {connections: connections, features: features};
};
chat.prototype.onQUIT = function(data) {
	this.users[data.nick].connections--;
	if (this.users[data.nick].connections <= 0)
		delete(this.users[data.nick])
};
chat.prototype.onMSG = function(data) {
	this.gui.push(new ChatUserMessage(data.data, this.users[data.nick], data.timestamp));
};
chat.prototype.onDELETE = function(data) {
	// TODO handle this nicer, but definitely do not show "message deleted"
	// maybe just collapse the lines?
	this.gui.removeUserLines(data.data);
};
chat.prototype.onMUTE = function(data) {
	// TODO make these messages distinct along with ban
	// data.data is the nick which has been muted, no info about duration
	this.gui.push(new ChatMessage(data.nick + " has been muted", data.timestamp));
};
chat.prototype.onUNMUTE = function(data) {
	this.gui.push(new ChatMessage(data.nick + " has been unmuted", data.timestamp));
};
chat.prototype.onBAN = function(data) {
	// data.data is the nick which has been banned, no info about duration
	this.gui.push(new ChatMessage(data.nick + " has been banned", data.timestamp));
};
chat.prototype.onUNBAN = function(data) {
	this.gui.push(new ChatMessage(data.nick + " has been unbanned", data.timestamp));
};
chat.prototype.onERR = function(data) {
	// data is a string now, TODO translate the raw error strings to something
	// human readable
	this.gui.push(new ChatMessage("Error: " + data));
};