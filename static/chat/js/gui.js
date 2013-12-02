(function($){
	
	// Global chat instance
	destiny.chat;
	
	//ChatGUI $('#element) shortcut
	$.fn.ChatGui = function(user, options){
		destiny.chat = new chat(this, user, options);
		destiny.chat.start();
	};
	
	// ChatGUI class
	ChatGui = function(element, engine, options){
		this.ui = $(element);
		this.engine = engine;
		$.extend(this, options);
		return this.init();
	};
	$.extend(ChatGui.prototype, {

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
		emoticons: [],
		formatters: [],
		hintPopup: null,
		loaded: false,
		
		init: function(){
			
			// local var for this instance
			var chat = this;
			
			// local elements stored in vars to not have to get the elements via query each time
			this.lines = this.ui.find('.chat-lines:first').eq(0);
			this.output = this.ui.find('.chat-output:first').eq(0);
			this.inputwrap = this.ui.find('.chat-input:first').eq(0);
			this.input = this.inputwrap.find('.input:first').eq(0);
			this.inputwrap.removeClass('hidden');
			
			// Message formatters
			this.formatters.push(new destiny.fn.EmoteFormatter(this));
			this.formatters.push(new destiny.fn.UrlFormatter(this));
			
			// Input history
			this.currenthistoryline = -1;
			this.storedinputline = null;
			if (window.localStorage) {
				this.setupInputHistory();
			}

			this.setupNotifications();
			if (this.engine.user.username) {
				this.highlightregex.user = new RegExp("\\b"+this.engine.user.username+"\\b", "i");
			};
			
			// Auto complete
			this.autoCompletePlugin = this.input.mAutoComplete({
				minWordLength: 2,
				maxResults: 10
			}).data('mAutoComplete');
			
			for (var i = this.emoticons.length - 1; i >= 0; i--) {
				// let the emotes have more weight than someone who never spoke
				this.autoCompletePlugin.addData(this.emoticons[i], 2);
			};
			
			// Chat settings
			this.chatsettings = this.ui.find('#chat-settings:first').eq(0);
			this.chatsettings.btn = this.ui.find('.chat-settings-btn:first').eq(0);
			this.chatsettings.list = this.chatsettings.find('ul:first').eq(0);
			this.chatsettings.visible = false;
			this.loadSettings();
			
			this.chatsettings.btn.on('click', function(e){
				e.preventDefault();
				if(chat.chatsettings.visible){
					return cMenu.prototype.hideMenu.call(chat.chatsettings, chat);
				}
				cMenu.closeMenus(chat);
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
				chat.saveChatOption('customhighlight', data );
				chat.loadCustomHighlights();
			});
			this.chatsettings.on('change', 'input[type="checkbox"]', function(){
				var name    = $(this).attr('name'),
				    checked = $(this).is(':checked');
				switch(name){
				
					case 'showtime':
						chat.saveChatOption(name, checked);
						chat.ui.toggleClass('chat-time', checked);
						this.scrollPlugin.updateAndScroll();
						break;
					
					case 'hideflairicons':
						chat.saveChatOption(name, checked);
						chat.ui.toggleClass('chat-icons', (!checked));
						this.scrollPlugin.updateAndScroll();
						break;
					
					case 'highlight':
						chat.saveChatOption(name, checked);
						break;
					
					case 'notifications':
						if (!notifications)
							break;
						
						var permission;
						if (notifications.checkPermission)
							permission = notifications.checkPermission();
						else if (notifications.permission) {
							switch(notifications.permission) {
								case "default": permission = 1; break;
								case "denied":  permission = 2; break;
								case "granted": permission = 3; break;
							}
						}
						
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
			});
			
			// User list
			this.userslist = this.ui.find('#chat-user-list:first').eq(0);
			this.userslist.btn = this.ui.find('.chat-users-btn:first').eq(0);
			this.userslist.visible = false;
			this.userslist.scrollable = this.userslist.find('.scrollable:first');
			this.userslist.btn.on('click', function(e){
				e.preventDefault();
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

				return cMenu.prototype.showMenu.call(chat.userslist, chat);
			});
			
			cMenu.addMenu(this, this.chatsettings);
			cMenu.addMenu(this, this.userslist);
			
			// The tools for when you click on a user
			this.cUserTools = new cUserTools(this);
			this.userslist.on('click', '.user', function(){
				var username = $(this).text().toLowerCase();
				chat.cUserTools.show($(this).text(), username, chat.engine.users[username]);
			});
			this.lines.on('mousedown', 'div.user-msg a.user', function(){
				var username = $(this).closest('.user-msg').data('username');
				chat.cUserTools.show($(this).text(), username, chat.engine.users[username]);
				return false;
			});
			
			// Hints
			this.hintPopup = new hintPopup(this);
			
			// Reset event
			this.chatsettings.find('#resethints').on('click', $.proxy(function(e){
				e.preventDefault();
				this.reset(chat);
			}, this.hintPopup));
			
			// Hide hint when someone clicks the user tools
			$(chat.cUserTools).on('show', $.proxy(function(e){
				this.hide();
			}, this.hintPopup));

			// Bind to user input submit
			this.ui.on('submit', 'form.chat-input', function(e){
				e.preventDefault();
				chat.send();
			});
			this.ui.on('click', '.chat-send-btn', function(e){
				e.preventDefault();
				chat.send();
			});

			// Close all menus and perform a scroll
			this.input.on('keydown mousedown', $.proxy(function(e){
				if(this.menuOpenCount > 0)
					cMenu.closeMenus(this);
			}, this));
			// Close all menus if someone clicks on any messages
			this.output.on('mousedown', $.proxy(function(e){
				if(this.cUserTools.visible)
					this.cUserTools.hide();
				if(this.menuOpenCount > 0)
					cMenu.closeMenus(this);
			}, this));

			// Scrollbars and scroll locking
			this.lines.addClass('content');
			this.scrollPlugin = this.output.nanoScroller({
				disableResize: true,
				preventPageScrolling: true,
				contentClass: 'chat-lines',
				sliderMinHeight: 40,
				tabIndex: 1
			})[0].nanoscroller;
			this.scrollPlugin.isScrolledToBottom = $.proxy(function(){
				return (this.contentScrollTop >= this.maxScrollTop);
			}, this.scrollPlugin);
			this.scrollPlugin.updateAndScroll = $.proxy(function(scrollbottom){
				if(!this.isActive) 
					return;
				scrollbottom = (scrollbottom == undefined) ? this.isScrolledToBottom() : scrollbottom;
				if(scrollbottom)
					this.scrollBottom(0);
				else
					this.reset();
			}, this.scrollPlugin);
			
			// Enable toolbar
			this.ui.find('.chat-tools-wrap button').removeAttr('disabled');
			return this;
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
						break;
					case 'hideflairicons':
						self.ui.toggleClass('chat-icons', (!value));
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
					else
						this.handleHighlight(this.put(message), true);
				}
				this.put(new ChatUIMessage('<hr/>'));
			};
			this.scrollPlugin.updateAndScroll();
			return;
		},
		
		getLineCount: function(){
			return this.lines.children().length;
		},

		// Add a message to the UI
		put: function(message, state){
			if(message instanceof ChatUserMessage){
				if(this.lastMessage && this.lastMessage.user && message.user && this.lastMessage.user.username == message.user.username)
					message.ui = $(message.addonHtml()); //same person consecutively
				else
					message.ui = $(message.html()); //different person
				
				if(message.user && this.engine.user && this.engine.user.username == message.user.username)
					message.ui.addClass('own-msg');
			}else
				message.ui = $(message.html());
				
			message.ui.appendTo(this.lines);
			
			if(state != undefined)
				message.status(state);
			
			this.lastMessage = message;
			return message;
		},
		
		// Add a message
		push: function(message, state){
			
			// Get the scroll position before adding the new line / removing old lines
			var wasScrolledBottom = this.scrollPlugin.isScrolledToBottom();
			
			// Rid excess lines if the user is scrolled to the bottom
			var lineCount = this.getLineCount();
			if(wasScrolledBottom && lineCount >= this.maxlines)
				this.lines.children().slice(0,2+Math.floor(((lineCount-this.maxlines)/this.maxlines)*100)).remove();
			
			this.userMessages.push(message);
			this.put(message, state);

			// Make sure a reset has been called at least once when the scroll should be enabled, but isnt yet
			if(this.scrollPlugin.content.scrollHeight > this.scrollPlugin.el.clientHeight && !this.scrollPlugin.isActive)
				this.scrollPlugin.reset();
			
			// Reset and or scroll bottom
			this.scrollPlugin.updateAndScroll(wasScrolledBottom);
			
			this.handleHighlight(message);
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
			this.scrollPlugin.updateAndScroll();
			return this;
		},
		
		setupInputHistory: function(){
			var modifierpressed = false,
				chat = this;
			$(this.input).on('keyup', function(e) {
				
				if (e.shiftKey || e.metaKey || e.ctrlKey)
					modifierpressed = true;
				
				if ((e.keyCode != 38 && e.keyCode != 40) || modifierpressed) {
					modifierpressed = false;
					return;
				}
				
				var num  = e.keyCode == 38? -1: 1; // if uparrow we subtract otherwise add
				
				// if uparrow and we are not currently showing any lines from the history
				if (chat.currenthistoryline < 0 && e.keyCode == 38) {
					// set the current line to the end if the history, do not subtract 1
					// thats done later
					chat.currenthistoryline = chat.getInputHistory().length;
					// store the typed in message so that we can go back to it
					chat.storedinputline = chat.input.val();
					
					if (chat.currenthistoryline <= 0) // nothing in the history, bail out
						return;
					
				} else if (chat.currenthistoryline < 0 && e.keyCode == 40)
					return; // down arrow, but nothing to show
				
				var index = chat.currenthistoryline + num;
				// out of bounds
				if (index >= chat.getInputHistory().length || index < 0) {
					
					// down arrow was pressed to get back to the original line, reset
					if (index >= chat.getInputHistory().length) {
						chat.input.val(chat.storedinputline);
						chat.currenthistoryline = -1;
					}
					return;
				}
				
				chat.currenthistoryline = index;
				chat.input.val(chat.getInputHistory()[index]);
				
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
					
					var message = this.userMessages[i];
					message.status();
					
					this.userMessages[i] = null;
					delete(this.userMessages[i]);
					
					return message;
				}
			}
			return null;
		},
		
		setupNotifications: function() {
			window.notifications = window.webkitNotifications || window.mozNotifications || window.oNotifications || window.msNotifications || window.notifications || window.Notification;
			if(!notifications)
				$('#chat-settings input[name=notifications]').closest('label').text('Notifications are not supported by your browser');
			
			if(!notifications || !this.engine.user.username || !this.getChatOption('notifications', false)){
				this.notifications = false;
			}
		},
		
		showNotification: function(message) {
			if (!this.notifications)
				return;
			
			var msg   = message.message,
			    title = (message.user) ? ''+message.user.username+' said ...' : 'Highlight ...',
			    notif = null,
			    self  = this;
			
			if (msg.length >= 30)
				msg = msg.substring(0, 30) + '...';
			
			// only ever show a single notification
			if (this.currentnotification)
				this.currentnotification.close();
			
			if (notifications.createNotification){
				// Try more widely supported webkit API first
				notif = notifications.createNotification(destiny.cdn+'/chat/img/notifyicon.png', title, msg);
				this.currentnotification = notif;
				notif.show();
				
			} else {
				// Fallback to standard HTML5 notifications if needed
				notif =  new notifications(title, {
					icon: destiny.cdn+'/chat/img/notifyicon.png',
					body: msg,
					tag : message.timestamp.unix(),
					dir : "auto"
				});
				this.currentnotification = notif;
			}
			
			// If a notification was created and shown, close it after 5 seconds
			setTimeout(function() {
				notif.close();
				// only null out our own notification so that we can still cancel
				// the previous notification
				if (notif === self.currentnotification)
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
			this.lines.children('div[data-username="'+username.toLowerCase()+'"]').remove();
			this.scrollPlugin.reset();
		}
		
	});
	
	// should be moved somewhere better
	$(window).on({
		'resize.chat': function(){
			destiny.chat.gui.resize();
		},
		'focus.chat': function(){
			destiny.chat.gui.input.focus();
		},
		'load.chat': function(){
			destiny.chat.gui.input.focus();
			destiny.chat.gui.loaded = true;
		}
	});
	
	// USER FEATURES
	UserFeatures = {
		PROTECTED    : 'protected',
		SUBSCRIBER   : 'subscriber',
		SUBSCRIBERT2 : 'flair1',
		SUBSCRIBERT3 : 'flair3',
		VIP          : 'vip',
		MODERATOR    : 'moderator',
		ADMIN        : 'admin',
		BOT          : 'bot',
		NOTABLE      : 'flair2',
		TRUSTED      : 'flair4',
	};

	//CHAT USER
	ChatUser = function(args){
		args = args || {};
		this.username = args.nick || '';
		this.connections = 0;
		this.features = [];
		$.extend(this, args);
		return this;
	};
	ChatUser.prototype.getFeatureHTML = function(){
		var icons = '';
		for (var i = 0; i < this.features.length; i++) {
			switch(this.features[i]){
				case UserFeatures.VIP :
					icons += '<i class="icon-vip" title="VIP"/>';
					break;
				case UserFeatures.MODERATOR :
					icons += '<i class="icon-moderator" title="Moderator"/>';
					break;
				case UserFeatures.ADMIN :
					icons += '<i class="icon-admin" title="Administrator"/>';
					break;
				case UserFeatures.BOT :
					icons += '<i class="icon-bot" title="Bot"/>';
					break;
				case UserFeatures.NOTABLE :
					icons += '<i class="icon-notable" title="Notable"/>';
					break;
				case UserFeatures.TRUSTED :
					icons += '<i class="icon-trusted" title="Trusted"/>';
					break;
			}
		}
		if($.inArray(UserFeatures.SUBSCRIBERT3, this.features) >= 0){
			icons += '<i class="icon-subscribert3" title="Subscriber (T3)"/>';
		}else if($.inArray(UserFeatures.SUBSCRIBERT2, this.features) >= 0){
			icons += '<i class="icon-subscribert2" title="Subscriber (T2)"/>';
		}else if($.inArray(UserFeatures.SUBSCRIBER, this.features) >= 0){
			icons += '<i class="icon-subscriber" title="Subscriber (T1)"/>';
		}
		return icons;
	};

	//UI MESSAGE - ability to send HTML markup to the chat
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
		this.timestampformat = destiny.chat.gui.timestampformat;
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
		return '<time title="'+this.timestamp.format('MMMM Do YYYY, h:mm:ss a')+'" datetime="'+this.timestamp.format('MMMM Do YYYY, h:mm:ss a')+'">'+this.timestamp.format(this.timestampformat)+'</time>';
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
	
	// BROADCAST MESSAGE
	ChatBroadcastMessage = function(message, timestamp){
		this.init(message, timestamp);
		this.type = 'broadcast';
		return this;
	};
	$.extend(ChatBroadcastMessage.prototype, ChatMessage.prototype);
	ChatBroadcastMessage.prototype.html = function(){
		return this.wrap(this.wrapTime() + ' ' + this.wrapMessage());
	};
	ChatBroadcastMessage.prototype.wrapMessage = function(){
		var elem     = $('<span class="msg"/>').text(this.message),
		    encoded  = elem.html();
		
		for(var i=0; i<destiny.chat.gui.formatters.length; ++i)
			encoded = destiny.chat.gui.formatters[i].format(encoded);
		
		elem.html(encoded);
		return elem.get(0).outerHTML;
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
		var elem     = $('<span class="msg"/>').text(this.message),
		    encoded  = elem.html();
		
		if(this.isEmote)
			elem.addClass('emote');
		
		for(var i=0; i<destiny.chat.gui.formatters.length; ++i)
			encoded = destiny.chat.gui.formatters[i].format(encoded, this.user);
		
		elem.html(encoded);
		return elem.get(0).outerHTML;
	};
	ChatUserMessage.prototype.html = function(){
		return this.wrap(this.wrapTime() + ' ' + ((!this.isEmote) ? '' : '*') + this.wrapUser(this.user) + ((!this.isEmote) ? ': ' : ' ') + this.wrapMessage());
	};
	ChatUserMessage.prototype.addonHtml = function(){
		if(this.isEmote)
			return this.wrap(this.wrapTime() + ' *' + this.wrapUser(this.user) + ' ' + this.wrapMessage());
		return this.wrap(this.wrapTime() + ' <span class="continue">&gt;</span> ' + this.wrapMessage(), 'continue');
	};
	
	// Emote count
	ChatEmoteMessage = function(emote, timestamp){
		this.init(emote, timestamp);
		this.emotecount = 1;
		return this;
	};
	$.extend(ChatEmoteMessage.prototype, ChatMessage.prototype);
	ChatEmoteMessage.prototype.getEmoteCountLabel = function(){
		return "C-C-C-COMBO: " + this.emotecount + "x";
	};
	ChatEmoteMessage.prototype.html = function(){
		return this.wrap(this.wrapTime() + ' ' + this.wrapMessage() + '<span class="emotecount">'+ this.getEmoteCountLabel() +'<span>');
	};
	ChatEmoteMessage.prototype.wrapMessage = function(){
		var elem     = $('<span class="msg"/>').text(this.message),
		    encoded  = elem.html();
		
		for(var i=0; i<destiny.chat.gui.formatters.length; ++i)
			encoded = destiny.chat.gui.formatters[i].format(encoded);
		
		elem.html(encoded);
		return elem.get(0).outerHTML;
	};
	ChatEmoteMessage.prototype.incEmoteCount = function(){
		++this.emotecount;
		this.ui.find('.emotecount').detach().text(this.getEmoteCountLabel()).appendTo(this.ui);
	};
	
})(jQuery);
