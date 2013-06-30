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
	this.maxlines = 150;
	this.sock     = new WebSocket('ws://' + location.host + ':9998/ws');
	this.users    = {};
	this.features = {
		'subscriber': '<i class="icon-star" title="Subscriber"/>',
		'admin'     : '<i class="icon-fire" title="Administrator"/>',
		'moderator' : '<i class="icon-leaf" title="Moderator"/>',
		'protected' : '<i class="icon-leaf" title="Protected"/>',
		'vip'       : '<i class="icon-leaf" title="VIP"/>'
	}
	
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
	
	this.setupUIHandlers();
};
chat.prototype.setupUIHandlers = function() {
	
	var self = this;
	$('form.chat-input-wrap').submit(function(e) {
		e.preventDefault();
		var input = $('form.chat-input-wrap .input');
		self.emit('MSG', {data: input.val()});
		input.val('');
	});
	// TODO delete, mute needs a way to specify the length,
	// ban needs a ui for entering a reason + specify a length
};
chat.prototype.writeMessage = function(timestamp, nick, message) {
	var html = $('<div class="line">' +
			'<time class="p-time" datetime=""></time>&nbsp;' +
			'<span class="p-user"></span><span class="p-userpostfix">:&nbsp;</span>' +
			'<span class="p-message"></span>' +
		'</div>');
	
	var ts = html.find('.p-time'),
	    u  = html.find('.p-user'),
	    m  = html.find('.p-message');
	
	if (timestamp) {
		var t = moment.utc(timestamp);
		ts.attr('datetime', t.format('MMMM Do YYYY, h:mm:ss a') );
		ts.attr('title', ts.attr('datetime') );
		ts.text(t.format('HH:mm'));
	} else
		ts.remove();
	
	if (nick) {
		var icons = '';
		for (var i = this.users[nick].length - 1; i >= 0; i--) {
			var feature = this.users[nick][i];
			if (this.features[feature])
				icons += this.features[feature];
		};
		u.text(nick);
		html.addClass('nick-' + nick);
	} else {
		u.remove();
		html.find('.p-userpostfix').remove();
	}
	
	m.text(message);
	
	html.appendTo('.chat-lines');
	var lines = $('.chat-lines .line');
	if (lines.length > this.maxlines)
		lines.eq(0).remove();
	
	// TODO scroll
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
	this.writeMessage((new Date).getTime(), null, "Connected!")
};
chat.prototype.onCLOSE = function() {
	this.writeMessage((new Date).getTime(), null, 'Disconnected');
};
chat.prototype.onNAMES = function(data) {
	if (!data.users || data.users.length <= 0)
		return;
	
	// TODO present the connection count in a nice way? is it too much info?
	//this.connectioncount = data.connectioncount;
	for (var i = data.users.length - 1; i >= 0; i--) {
		var nick     = data.users[i].nick,
				features = data.users[i].features || [];
		
		this.users[nick] = features;
	};
	
};
chat.prototype.onJOIN = function(data) {
	delete(data.timestamp);
	this.users[data.nick] = data.features || [];
};
chat.prototype.onQUIT = function(data) {
	// TODO handle it when a user joins from multiple browsers, need info from the
	// server side too
};
chat.prototype.onMSG = function(data) {
	this.writeMessage(data.timestamp, data.nick, data.data);
};
chat.prototype.onDELETE = function(data) {
	// TODO handle this nicer, but definitely do not show "message deleted"
	// maybe just collapse the lines?
	$('.chat-lines nick-' + data.data).remove();
};
chat.prototype.onMUTE = function(data) {
	// TODO make these messages distinct along with ban
	// data.data is the nick which has been muted, no info about duration
	this.writeMessage(data.timestamp, data.nick, "MUTED: ")
};
chat.prototype.onUNMUTE = function(data) {
	this.writeMessage(data.timestamp, data.nick, "UNMUTED: ")
};
chat.prototype.onBAN = function(data) {
	// data.data is the nick which has been banned, no info about duration
	this.writeMessage(data.timestamp, data.nick, "BANNED: ")
};
chat.prototype.onUNBAN = function(data) {
	this.writeMessage(data.timestamp, data.nick, "UNBANNED: ")
};
chat.prototype.onERROR = function(data) {
	// data is a string now, TODO translate the raw error strings to something
	// human readable
	this.writeMessage((new Date).getTime(), null, "ERROR: " + data)
};