$(function() {
	c = new chat(user);
});

function chat(user) {
	if (window.MozWebSocket)
		window.WebSocket = MozWebSocket;
	
	if ( !window.WebSocket ) {
		// TODO print warning
		return
	}
	
	this.sock  = new WebSocket('ws://' + location.host + ':9998/ws');
	this.user  = user;
	this.debug = false;
	this.init();
	
}

chat.prototype.l = function() {
	if (!this.debug)
		return;
	
	console.log.apply(null, arguments);
};
chat.prototype.init = function() {
	this.sock.onopen    = $.proxy(function() {
		var event = {data: "OPEN "};
		this.parseAndDispatch(event)
	}, this);
	this.sock.onmessage = $.proxy(this.parseAndDispatch, this);
	this.sock.onclose   = $.proxy(function() {
		var event = {data: "CLOSE "};
		this.parseAndDispatch(event)
	}, this);
	
	this.l = $.proxy(this.l, this);
	this.emit = $.proxy(this.emit, this);
};
chat.prototype.parseAndDispatch = function(event) {
	var data    = event.data.split(' ', 1),
			handler = 'on' . data[0],
			obj     = JSON.parse(data[1]);
	
	this.l(event, handler, obj);
	if (this[handler])
		this[handler](obj);
};
chat.prototype.emit = function(event, data) {
	
};
