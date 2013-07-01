$(function() {
	c = new chat();
});

function chat() {

	this.gui = new destiny.fn.Chat({
		ui: '#destinychat',
		engine: this,
		onSend: function(str){
			var message = chat.push(new ChatUserMessage(str, chat.user));
			message.status(ChatMessageStatus.PENDING);
			this.engine.emit('MSG', {data: str});
			//message.status();
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

	this.gui.push(new ChatMessage("Connecting..."));
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
		data.users[i].username = data.users[i].nick;
		this.users[nick] = new ChatUser(data.users[i]);
	};
};
chat.prototype.onJOIN = function(data) {
	data.username = data.nick;
	this.users[data.nick] = new ChatUser(data);
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