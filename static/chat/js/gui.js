
// Need a better place for these
var emoticons = ["Dravewin", "INFESTINY", "FIDGETLOL", "Hhhehhehe", "GameOfThrows", "WORTH", "FeedNathan", "Abathur", "LUL", "Heimerdonger", "ASLAN", "DJAslan", "SoSad", "DURRSTINY", "Kappa", "Klappa", "DappaKappa", "BibleThump", "BloodTrail", "BrainSlug", "FrankerZ", "Kreygasm", "PJSalt", "SSSsss", "FailFish", "BasedGod"];
var emoteregex = new RegExp('\\b(?:'+emoticons.join('|')+')\\b');
var linkregex = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;

(function($){
	
	destiny.fn.Chat = function(engine, options){
		$.extend(this, options);
		this.ui = $(this.ui);
		this.engine = engine;
		return this.init();
	};
	$.extend(destiny.fn.Chat.prototype, {

		theme: 'dark',
		maxlines: 500,
		lineCount: 0,
		scrollPlugin: null,
		autoCompletePlugin: null,
		ui: null,
		lines: null,
		output: null,
		input: null,
		onSend: $.noop,
		userMessages: [],
		backlog: backlog,
		highlightregex: {},
		highlightnicks: {},
		notifications: true,
		lastMessage: null,
		menus: [],
		menuOpenCount: 0,
		timestampformat: null,
		
		init: function(){
			// Set the elements data 'chat' var - should prob remove this - used to reference this in the UI
			this.ui.data('chat', this);
			
			// local elements stored in vars to not have to get the elements via query each time
			this.lines = this.ui.find('.chat-lines:first').eq(0);
			this.output = this.ui.find('.chat-output:first').eq(0);
			this.inputwrap = this.ui.find('.chat-input:first').eq(0);
			this.input = this.inputwrap.find('.input:first:first').eq(0);
			
			this.inputwrap.removeClass('hidden');
			
			// Scrollbars and scroll locking
			if(this.scrollPlugin == null){
				this.scrollPlugin = new destiny.fn.mCustomScrollbarPlugin(this);
				this.scrollPlugin.lockScroll(true);
			};
			
			// Input history
			this.currenthistoryline = -1;
			this.storedinputline = null;
			if (window.localStorage) {
				this.setupInputHistory();
			}

			this.setupNotifications();
			if(this.engine.user.username){
				this.highlightregex.user = new RegExp("\\b"+this.engine.user.username+"\\b", "i");
			};
			
			// Auto complete
			this.autoCompletePlugin = this.input.mAutoComplete({
				minWordLength: 2,
				maxResults: 10
			}).data('mAutoComplete');

			this.autoCompletePlugin.addData(emoticons, 2);
			
			// Chat settings
			this.chatsettings = this.ui.find('#chat-settings:first').eq(0);
			this.chatsettings.btn = this.ui.find('.chat-settings-btn:first').eq(0);
			this.chatsettings.list = this.chatsettings.find('ul:first').eq(0);
			this.chatsettings.visible = false;
			this.chatsettings.scrollable = this.chatsettings.find('.scrollable:first');
			this.loadSettings();
			
			this.chatsettings.btn.on('click', function(e){
				e.preventDefault();
				var chat = $(this).closest('.chat.chat-frame').data('chat');
				//chat.chatsettings.detach();
				if(chat.chatsettings.visible){
					return cMenu.prototype.hideMenu.call(chat.chatsettings, chat);
				}
				cMenu.closeMenus(chat);
				//chat.chatsettings.appendTo(chat.ui);
				return cMenu.prototype.showMenu.call(chat.chatsettings, chat);
			});
			this.chatsettings.on('keypress blur', 'input[name=customhighlight]', function(e) {
				var keycode = e.keyCode ? e.keyCode : e.which;
				if (keycode && keycode != 13) // not enter
					return;
				
				e.preventDefault();
				var data = $(this).val().trim().split(',');
				for (var i = data.length - 1; i >= 0; i--) {
					data[i] = data[i].trim();
					if (!data[i])
						data.splice(i, 1)
				};
				
				var chat = $(this).closest('.chat.chat-frame').data('chat');
				chat.saveChatOption('customhighlight', data );
				chat.loadCustomHighlights();
			});
			this.chatsettings.on('change', 'input[type="checkbox"]', function(){
				var chat    = $(this).closest('.chat.chat-frame').data('chat'),
				    name    = $(this).attr('name'),
				    checked = $(this).is(':checked');
				switch(name){
				
					case 'showtime':
						chat.saveChatOption(name, checked);
						chat.ui.toggleClass('chat-time', checked);
						chat.resize();
						break;
					
					case 'hideflairicons':
						chat.saveChatOption(name, checked);
						chat.ui.toggleClass('chat-icons', (!checked));
						chat.resize();
						break;
					
					case 'highlight':
						chat.saveChatOption(name, checked);
						break;
					
					case 'notifications':
						var permission = notifications.checkPermission();
						if (permission == 1) // not yet allowed
							notifications.requestPermission(function(){});
						else if (permission == 2) {
							chat.notifications = false;
							break;
						}
						chat.notifications = checked;
						chat.saveChatOption(name, checked);
						break;
				}
				chat = null;
			});
			
			// User list
			this.userslist = this.ui.find('#chat-user-list:first').eq(0);
			this.userslist.btn = this.ui.find('.chat-users-btn:first').eq(0);
			this.userslist.visible = false;
			this.userslist.scrollable = this.userslist.find('.scrollable:first');
			this.userslist.btn.on('click', function(e){
				e.preventDefault();
				var chat = $(this).closest('.chat.chat-frame').data('chat');
				//chat.userslist.detach();
				if(chat.userslist.visible){
					return cMenu.prototype.hideMenu.call(chat.userslist, chat);
				}
				cMenu.closeMenus(chat);
				var lists  = chat.userslist.find('ul'),
				    admins = [], vips = [], mods = [], bots = [], subs = [], plebs = [],
				    elems  = {},
				    usercount = 0;

				for(var username in chat.engine.users){
					var u    = chat.engine.users[username],
					    elem = $('<li><a class="user '+ u.features.join(' ') +'">'+u.username+'</a></li>');
					
					usercount++;
					elems[username.toLowerCase()] = elem;
					if($.inArray('bot', u.features) >= 0)
						bots.push(username.toLowerCase());
					else if ($.inArray('admin', u.features) >= 0)
						admins.push(username.toLowerCase());
					else if($.inArray('vip', u.features) >= 0)
						vips.push(username.toLowerCase());
					else if($.inArray('moderator', u.features) >= 0)
						mods.push(username.toLowerCase());
					else if($.inArray('subscriber', u.features) >= 0)
						subs.push(username.toLowerCase());
					else
						plebs.push(username.toLowerCase());
					
				}
				
				chat.userslist.find('h5 span').text(usercount);
				var appendUsers = function(users, elem) {
					if (users.length == 0) {
						elem.prev().hide().prev().hide();
						return;
					}
					elem.prev().show().prev().show();
					users.sort()
					for (var i = 0; i < users.length; i++) {
						elem.append(elems[users[i]]);
					};
				};

				lists.empty();
				appendUsers(admins, lists.filter('.admins'));
				appendUsers(vips, lists.filter('.vips'));
				appendUsers(mods, lists.filter('.moderators'));
				appendUsers(bots, lists.filter('.bots'));
				appendUsers(subs, lists.filter('.subs'));
				appendUsers(plebs, lists.filter('.plebs'));
				//chat.userslist.appendTo(chat.ui);
				return cMenu.prototype.showMenu.call(chat.userslist, chat);
			});
			
			cMenu.addMenu(this, this.chatsettings);
			cMenu.addMenu(this, this.userslist);
			
			// The tools for when you click on a user
			this.cUserTools = new cUserTools(this);
			this.userslist.on('click', '.user', function(){
				var chat = $(this).closest('.chat.chat-frame').data('chat');
				var username = $(this).text().toLowerCase();
				chat.cUserTools.show($(this).text(), username, chat.engine.users[username]);
			});
			this.lines.on('mousedown', 'div.user-msg a.user', function(){
				var chat = $(this).closest('.chat.chat-frame').data('chat');
				var username = $(this).closest('.user-msg').data('username');
				chat.cUserTools.show($(this).text(), username, chat.engine.users[username]);
				return false;
			});

			// Bind to user input submit
			this.ui.on('submit', 'form.chat-input', function(e){
				e.preventDefault();
				$(this).closest('.chat.chat-frame').data('chat').send();
			});
			this.ui.on('click', '.chat-send-btn', function(e){
				e.preventDefault();
				$(this).closest('.chat.chat-frame').data('chat').send();
			});

			// Close the menu, or perform a scroll
			this.input.on('keydown mousedown', $.proxy(function(e){
				if(this.menuOpenCount > 0)
					cMenu.closeMenus(this);
				
				if(e.keyCode == 33 /*PGUP*/){
					this.scrollPlugin.scroll('up');
					return false;
				}
				if(e.keyCode == 34 /*PGDOWN*/){
					this.scrollPlugin.scroll('down');
					return false;
				}
			}, this));
			
			this.output.on('mousedown', $.proxy(function(e){
				if(this.cUserTools.visible)
					this.cUserTools.hide();
				if(this.menuOpenCount > 0)
					cMenu.closeMenus(this);
			}, this));

			// Enable toolbar
			this.ui.find('.chat-tools-wrap button').removeAttr('disabled');
			return this.resize();
		},
		
		loadSettings: function() {
			var self     = this,
			    defaults = {
					showtime      : false,
					hideflairicons: false,
					highlight     : true,
					notifications : false,
					maxlines      : this.maxlines,
					timestampformat: 'HH:mm'
			};
			
			this.timestampformat = self.getChatOption('timestampformat', defaults['timestampformat']);
			this.maxlines = self.getChatOption('maxlines', defaults['maxlines']);
			customhighlight = self.getChatOption('customhighlight', []);
			this.chatsettings.find('input[name=customhighlight]').val( customhighlight.join(', ') );
			this.loadCustomHighlights();
			this.chatsettings.find('input[type="checkbox"]').each(function() {
				var name  = $(this).attr('name'),
				    value = self.getChatOption(name, defaults[name]);
				
				$(this).attr('checked', value);
				switch(name){
					case 'showtime':
						self.ui.toggleClass('chat-time', value);
						self.resize();
						break;
					case 'hideflairicons':
						self.ui.toggleClass('chat-icons', (!value));
						self.resize();
						break;
				};
			});
		},
		
		loadBacklog: function() {
			if(this.backlog.length > 0){
				for (var i = this.backlog.length - 1; i >= 0; i--) {
					var line    = this.backlog[i],
					    message = this.engine.dispatchBacklog(line);
					
					if (!message)
						continue;
					
					if ($.inArray(line.event, this.engine.controlevents) >= 0)
						this.put(message);
					else {
						var m = this.put(message);
						this.handleHighlight(m, true);
					}
				}
				this.put(new ChatUIMessage('<hr/>'));
				this.scrollPlugin.update();
			};
		},
		
		lineCount: function(){
			return this.lines.children().length;
		},
		
		push: function(message, state){
			var isScrolledBottom = this.scrollPlugin.isScrolledBottom();
			this.userMessages.push(message);
			this.put(message, state);
			this.scrollPlugin.update();
			if(isScrolledBottom && this.scrollPlugin.isScrollLocked()){
				if(this.lineCount() >= this.maxlines){
					this.lines.children().slice(0,2+Math.floor(((this.lineCount()-this.maxlines) / this.maxlines)*100)).remove();
				}
				this.scrollPlugin.scrollBottom();
			}
			this.handleHighlight(message);
			return message;
		},

		put: function(message, state){
			if(message instanceof ChatUserMessage){
				if(this.lastMessage && this.lastMessage.user && message.user && this.lastMessage.user.username == message.user.username){
					//same person consecutively
					message.ui = $(message.addonHtml());
				}else{
					//different person
					message.ui = $(message.html());
				}
				if(message.user && this.engine.user && this.engine.user.username == message.user.username){
					message.ui.addClass('own-msg');
				}
			}else{
				message.ui = $(message.html());
			};
			message.ui.appendTo(this.lines);
			
			if(state != undefined)
				message.status(state);
			this.lastMessage = message;
			return message;
		},
		
		send: function(){
			var str = this.input.val().trim();
			if(str != ''){
				this.input.val('').focus();
				this.onSend(str, this.input[0]);
				this.insertInputHistory(str);
				this.currenthistoryline = -1;
			};
			str = null;
			return this;
		},
		
		resize: function(){
			this.output.height(this.ui.height()-(this.inputwrap.height() + parseInt(this.inputwrap.css('margin-top'))));
			this.scrollPlugin.update();
			this.scrollPlugin.scrollBottom();
			return this;
		},
		
		setupInputHistory: function(){
			var modifierpressed = false;
			$(this.input).on('keyup', function(e) {
				
				if (e.shiftKey || e.metaKey || e.ctrlKey)
					modifierpressed = true;
				
				if ((e.keyCode != 38 && e.keyCode != 40) || modifierpressed) {
					modifierpressed = false;
					return;
				}
				
				var self = $(this).closest('.chat.chat-frame').data('chat'),
				    num  = e.keyCode == 38? -1: 1; // if uparrow we subtract otherwise add
				
				// if uparrow and we are not currently showing any lines from the history
				if (self.currenthistoryline < 0 && e.keyCode == 38) {
					// set the current line to the end if the history, do not subtract 1
					// thats done later
					self.currenthistoryline = self.getInputHistory().length;
					// store the typed in message so that we can go back to it
					self.storedinputline = $(self.input).val();
					
					if (self.currenthistoryline <= 0) // nothing in the history, bail out
						return;
					
				} else if (self.currenthistoryline < 0 && e.keyCode == 40)
					return; // down arrow, but nothing to show
				
				var index = self.currenthistoryline + num;
				// out of bounds
				if (index >= self.getInputHistory().length || index < 0) {
					
					// down arrow was pressed to get back to the original line, reset
					if (index >= self.getInputHistory().length) {
						$(self.input).val(self.storedinputline);
						self.currenthistoryline = -1;
					}
					
					return;
				}
				
				self.currenthistoryline = index;
				$(self.input).val(self.getInputHistory()[index]);
				
			});
		},
		
		getInputHistory: function(){
			return JSON.parse(localStorage['inputhistory'] || '[]');
		},
		
		setInputHistory: function(arr){
			localStorage['inputhistory'] = JSON.stringify(arr);
		},
		
		insertInputHistory: function(message){
			if (!window.localStorage)
				return;
			
			var history = this.getInputHistory();
			history.push(message);
			if (history.length > 20)
				history.shift();
			
			this.setInputHistory(history);
		},
		
		resolveMessage: function(data){
			var found = false;
			for(var i in this.userMessages){
				if(this.userMessages[i].message == data.data){
					this.userMessages[i].status();
					this.userMessages[i] = null;
					delete(this.userMessages[i]);
					found = true;
				}
			}
			return found;
		},
		
		setupNotifications: function() {
			window.notifications = window.webkitNotifications || window.mozNotifications || window.oNotifications || window.msNotifications || window.notifications;
			if(!notifications)
				$('#chat-settings input[name=notifications]').closest('label').text('Notifications are not supported by your browser');
			
			if(!notifications || !this.engine.user.username || !this.getChatOption('notifications', false)){
				this.notifications = false;
			}
		},
		
		showNotification: function(message) {
			var msg = message.message, title = (message.user) ? ''+message.user.username+' said ...' : 'Highlight ...';
			if (msg.length >= 30)
				msg = msg.substring(0, 30) + '...';

			var notif = notifications.createNotification(destiny.cdn+'/chat/img/notifyicon.png', title, msg);
			
			// only ever show a single notification
			if (this.currentnotification)
				this.currentnotification.cancel();
			
			this.currentnotification = notif;
			notif.show();
			var self = this;
			setTimeout(function() {
				notif.cancel();
				self.currentnotification = null;
				self = null;
			}, 5000);
		},
		
		loadCustomHighlights: function() {
			this.highlightnicks = this.getChatOption('highlightnicks', {});
			
			var highlights = this.getChatOption('customhighlight', []);
			if (highlights.length == 0)
				return;
			
			for (var i = highlights.length - 1; i >= 0; i--) {
				highlights[i] = highlights[i].replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")
			};
			this.highlightregex.custom = new RegExp("\\b(?:"+highlights.join("|")+")\\b", "i")
		},
		
		handleHighlight: function(message, skipnotify){
			
			if (!message.user || message.user.username == this.engine.user.username)
				return;
			
			var u = message.user.username.toLowerCase();
			if (this.highlightnicks[u]) {
				message.ui.addClass('highlight');
			}
			
			if (!this.highlightregex.user || !this.getChatOption('highlight', true))
				return;
			
			if (this.highlightregex.user.test(message.message) || (this.highlightregex.custom && this.highlightregex.custom.test(message.message))) {
				message.ui.addClass('highlight');
				if(!skipnotify && this.notifications){
					this.showNotification(message);
				}
			}
			
		},
		
		getChatOption: function(option, defaultvalue) {
			var options = JSON.parse(localStorage['chatoptions'] || '{}');
			return (options[option] == undefined)? defaultvalue: options[option];
		},
		
		saveChatOption: function(option, value) {
			var options     = JSON.parse(localStorage['chatoptions'] || '{}');
			options[option] = value;
			localStorage['chatoptions'] = JSON.stringify(options);
		},
		
		removeUserMessages: function(username) {
			$('.chat-lines > div[data-username="'+username.toLowerCase()+'"]').remove();
			$('.chat.chat-frame').data('chat').resize();
		}
		
	});
	
	// should be moved somewhere better
	$(window).on('resize.chat',function(){
		$('.chat.chat-frame').each(function(){
			$(this).data('chat').resize();
		});
	});
	
	// USER FEATURES
	UserFeatures = {
		PROTECTED 	: 'protected',
		SUBSCRIBER	: 'subscriber',
		VIP			: 'vip',
		MODERATOR	: 'moderator',
		ADMIN		: 'admin',
		BOT			: 'bot',
	};

	//CHAT USER
	ChatUser = function(args){
		args = args || {};
		this.username = args.nick || '';
		this.features = [];
		this.connections = 0;
		args.features = args.features || [];
		$.extend(this, args);
		return this;
	};
	ChatUser.prototype.getFeatureHTML = function(){
		var icons = '';
		for (var i = 0; i < this.features.length; i++) {
			switch(this.features[i]){
				case UserFeatures.SUBSCRIBER :
					icons += '<i class="icon-subscriber" title="Subscriber"/>';
					break;
				case UserFeatures.VIP :
					icons += '<i class="icon-vip" title="VIP"/>';
					break;
				case UserFeatures.MODERATOR :
					icons += '<i class="icon-moderator" title="Moderator"/>';
					break;
				case UserFeatures.ADMIN :
					icons += '<i class="icon-administrator" title="Administrator"/>';
					break;
				case UserFeatures.BOT :
					icons += '<i class="icon-bot" title="Bot"/>';
					break;
			}
		}
		return icons;
	};

	//UI MESSAGE
	ChatUIMessage = function(html){
		return this.init(html);
	};
	ChatUIMessage.prototype.init = function(html){
		this.message = html;
		return this;
	};
	ChatUIMessage.prototype.html = function(){
		return this.wrap(this.wrapMessage());
	};
	ChatUIMessage.prototype.wrap = function(html){
		return '<div class="ui-msg">'+html+'</div>';
	};
	ChatUIMessage.prototype.wrapMessage = function(){
		return this.message;
	};

	//BASE MESSAGE
	ChatMessage = function(message, timestamp){
		return this.init(message, timestamp);
	};
	ChatMessage.prototype.init = function(message, timestamp){
		this.message = message;
		this.timestamp = moment.utc(timestamp).local();
		this.state = null;
		this.type = 'chat';
		this.timestampformat = $('.chat.chat-frame').data('chat').timestampformat;
		return this;
	};
	ChatMessage.prototype.status = function(state){
		if(this.ui){
			if(state){
				this.ui.addClass(state);
			}else{
				this.ui.removeClass(this.state);
			}
		}
		this.state = state;
		return this;
	};
	ChatMessage.prototype.wrapTime = function(){
		return '<time datetime="'+this.timestamp.format('MMMM Do YYYY, h:mm:ss a')+'">'+this.timestamp.format(this.timestampformat)+'</time>';
	};
	ChatMessage.prototype.wrapMessage = function(){
		return $('<span/>').text(this.message).html();
	};
	ChatMessage.prototype.html = function(){
		return this.wrap(this.wrapTime() + ' ' + this.wrapMessage());
	};
	ChatMessage.prototype.wrap = function(content){
		return '<div class="'+this.type+'-msg">'+content+'</div>';
	};
	// ERROR MESSAGE
	ChatErrorMessage = function(error, timestamp){
		this.init(error, timestamp);
		this.type = 'error';
		return this;
	};
	$.extend(ChatErrorMessage.prototype, ChatMessage.prototype);
	ChatErrorMessage.prototype.html = function(){
		return this.wrap(this.wrapTime() + ' <i class="icon-error"></i> ' + this.wrapMessage());
	};
	// INFO / HELP MESSAGE
	ChatInfoMessage = function(message, timestamp){
		this.init(message, timestamp);
		this.type = 'info';
		return this;
	};
	$.extend(ChatInfoMessage.prototype, ChatMessage.prototype);
	ChatInfoMessage.prototype.html = function(){
		return this.wrap(this.wrapTime() + ' <i class="icon-info"></i> ' + this.wrapMessage());
	};
	// COMMAND MESSAGE
	ChatCommandMessage = function(message, timestamp){
		this.init(message, timestamp);
		this.type = 'command';
		return this;
	};
	$.extend(ChatCommandMessage.prototype, ChatMessage.prototype);
	ChatCommandMessage.prototype.html = function(){
		return this.wrap(this.wrapTime() + ' <i class="icon-command"></i> ' + this.wrapMessage());
	};
	// STATUS MESSAGE
	ChatStatusMessage = function(message, timestamp){
		this.init(message, timestamp);
		this.type = 'status';
		return this;
	};
	$.extend(ChatStatusMessage.prototype, ChatMessage.prototype);
	ChatStatusMessage.prototype.html = function(){
		return this.wrap(this.wrapTime() + ' <i class="icon-status"></i> ' + this.wrapMessage());
	};
	// USER MESSAGE
	ChatUserMessage = function(message, user, timestamp){
		this.init(message, timestamp);
		this.type = 'user';
		// strip the /me
		this.isEmote = false;
		if (this.message.substring(0, 4) === '/me ') {
			this.isEmote = true;
			this.message = this.message.substring(4);
		} else if (this.message.substring(0, 2) === '//')
			this.message = this.message.substring(1);
		this.isAddon = false;
		this.user = user;
		return this;
	};
	$.extend(ChatUserMessage.prototype, ChatMessage.prototype);
	ChatUserMessage.prototype.wrap = function(html, css) {
		if (this.user && this.user.username) {
			return '<div class="'+this.type+'-msg'+((css) ? ' '+css:'')+'" data-username="'+this.user.username.toLowerCase()+'">'+html+'</div>';
		} else
			return '<div class="'+this.type+'-msg'+((css) ? ' '+css:'')+'">'+html+'</div>';
	};
	ChatUserMessage.prototype.wrapUser = function(user){
		return ((this.isEmote) ? '':user.getFeatureHTML()) +' <a class="user '+ user.features.join(' ') +'">' +user.username+'</a>';
	};
	ChatUserMessage.prototype.wrapMessage = function(){
		var elem = $('<msg/>').text(this.message),
		encoded  = elem.html();
		
		var emoticon = emoteregex.exec(encoded);
		if (emoticon)
			encoded = encoded.replace(emoticon[0], '<div title="'+emoticon[0]+'" class="chat-emote chat-emote-' + emoticon[0] +'"></div>');
		
		encoded = encoded.replace(linkregex, '<a href="$1" target="_blank" class="externallink">$1</a>');
		if(this.isEmote)
			elem.addClass('emote');
		return elem.html(encoded)[0].outerHTML;
	};
	ChatUserMessage.prototype.html = function(){
		return this.wrap(this.wrapTime() + ' ' + ((!this.isEmote) ? '' : '*') + this.wrapUser(this.user) + ((!this.isEmote) ? ': ' : ' ') + this.wrapMessage());
	};
	ChatUserMessage.prototype.addonHtml = function(){
		if(this.isEmote)
			return this.wrap(this.wrapTime() + ' *' + this.wrapUser(this.user) + ' ' + this.wrapMessage());
		return this.wrap(this.wrapTime() + ' <span class="continue">&gt;</span> ' + this.wrapMessage(), 'continue');
	};
	
	
})(jQuery);
