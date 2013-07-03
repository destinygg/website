(function($){
//https://developer.mozilla.org/en-US/docs/Web/Guide/User_experience/Displaying_notifications
//http://blog.teamtreehouse.com/adding-desktop-notifications-to-your-web-applications
	
	destiny.fn.Chat = function(props){
		$.extend(this, props);
		this.ui = $(this.ui);
		return this.init();
	};
	$.extend(destiny.fn.Chat.prototype, {

		maxlines: 50,
		lineCount: 0,
		scrollPlugin: null,
		ui: null,
		lines: null,
		output: null,
		input: null,
		onSend: $.noop,
		userMessages: [],
		
		init: function(){
			// Optional params passed in via the data-options="{}" attribute
			$.extend(this, this.ui.data('options'));
			// local elements stored in vars to not have to get the elements via query each time
			this.lines = $(this.ui.find('.chat-lines:first')[0]);
			this.output = $(this.ui.find('.chat-output:first')[0]);
			this.inputwrap = $(this.ui.find('.chat-input:first')[0]);
			this.input = $(this.inputwrap.find('.input:first:first')[0]);
			
			this.currenthistoryline = -1;
			this.storedinputline = null;
			if (window.localStorage)
				this.setupInputHistory();
			
			// Set the elements data 'chat' var - should prob remove this - used to reference this in the UI
			this.ui.data('chat', this);

			// Bind to user input submit
			this.ui.on('submit', '.chat-input form', function(e){
				e.preventDefault();
				$(this).closest('.chat.chat-frame').data('chat').send();
			});
			
			// Scrollbars and scroll locking
			if(this.scrollPlugin == null){
				//this.scrollPlugin = new destiny.fn.ChatScrollPlugin(this);
				this.scrollPlugin = new destiny.fn.mCustomScrollbarPlugin(this);
				this.scrollPlugin.lockScroll(true);
			};
			this.show();
			this.resize();
			return this;
		},
		
		lineCount: function(){
			return this.lines.children().length;
		},
		
		// API
		purge: function(){
			this.lines.empty();
			$(this).triggerHandler('purge');
			return this;
		},
		
		push: function(message, state){
			var isScrolledBottom = this.scrollPlugin.isScrolledBottom();
			
			// Attach message ui
			message.ui = $(message.html()).appendTo(this.lines);
			if(state != undefined){
				message.status(state);
			}
			this.userMessages.push(message);
			//
			
			if(this.lineCount() >= this.maxlines){
				$(this.lines.children()[0]).remove();
			}else if(isScrolledBottom && this.scrollPlugin.isScrollLocked()){
				this.scrollPlugin.update();
				this.scrollPlugin.scrollBottom();
			}else if(this.scrollPlugin.isScrollable()){
				this.scrollPlugin.update()
			}
			$(this).triggerHandler('push', [message]);
			return message;
		},
		
		send: function(){
			var str = this.input.val();
			if(str != ''){
				this.input.val('').focus();
				this.onSend(str, this.input[0]);
				this.insertInputHistory(str);
				this.currenthistoryline = -1;
			};
			str = null;
			return this;
		},
		
		// UI
		resize: function(){
			this.output.height(this.ui.height()-this.inputwrap.outerHeight());
			$(this).triggerHandler('resize');
			return this;
		},
		
		show: function(){
			this.ui.show();
			$(this).triggerHandler('show');
			return this;
		},
		
		hide: function(){
			this.ui.hide();
			$(this).triggerHandler('hide');
			return this;
		},
		
		enableInput: function(){
			this.input.removeAttr('disabled', true);
		},
		
		disableInput: function(){
			this.input.attr('disabled', true);
		},
		
		ping: function(){
			//  stub
		},
		
		removeUserLines: function(user){
			// stub
		},
		
		setupInputHistory: function(){
			$(this.input).on('keyup', function(e) {
				
				if (e.keyCode != 38 && e.keyCode != 40)
					return;
				
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
		}
	});
	
	// should be moved somewhere better
	$(window).on('resize.chat',function(){
		$('.chat.chat-frame').each(function(){
			$(this).data('chat').resize();
		});
	});
	
	
})(jQuery);

// USER FEATURES
var UserFeatures = {
	PROTECTED 	: 'protected',
	SUBSCRIBER	: 'subscriber',
	VIP			: 'vip',
	MODERATOR	: 'moderator',
	ADMIN		: 'admin'
};

var ChatMessageStatus = {
	SENT		: 'sent',
	PENDING		: 'pending',
	FAILED		: 'failed'
};

//CHAT USER
function ChatUser(args){
	this.username = args.nick || '';
	this.userId = '';
	this.features = [];
	this.connections = 0;
	this.color = '#efefef';
	$.extend(this, args);
	return this;
};
ChatUser.prototype.getFeatureHTML = function(){
	var icons = '';
	for (var i = this.features.length - 1; i >= 0; i--) {
		switch(this.features[i]){
			case UserFeatures.PROTECTED :
				icons += '<i class="icon-eye-close" title="Protected"/>';
				break;
			case UserFeatures.SUBSCRIBER :
				icons += '<i class="icon-star" title="Subscriber"/>';
				break;
			case UserFeatures.VIP :
				icons += '<i class="icon-film" title="VIP"/>';
				break;
			case UserFeatures.MODERATOR :
				icons += '<i class="icon-leaf" title="Moderator"/>';
				break;
			case UserFeatures.ADMIN :
				icons += '<i class="icon-fire" title="Administrator"/>';
				break;
		}
	}
	return icons;
};

//UI MESSAGE
function ChatUIMessage(html){
	this.init(html);
	return this;
};
ChatUIMessage.prototype.init = function(html){
	this.message = html;
	return this;
};
ChatUIMessage.prototype.html = function(){
	return this.wrap(this.wrapMessage());
};
ChatUIMessage.prototype.wrap = function(content){
	return '<div>'+content+'</div>';
};
ChatUIMessage.prototype.wrapMessage = function(css){
	return $('<span'+ ((css==undefined) ? '':' class="'+css+'"') +' />').html(this.message).html();
};

//BASE MESSAGE
function ChatMessage(message, timestamp){
	this.init(message, timestamp);
	return this;
};
ChatMessage.prototype.init = function(message, timestamp){
	this.message = message;
	this.timestamp = moment(timestamp);
	this.state = null;
	return this;
};
ChatMessage.prototype.status = function(state){
	if(this.ui){
		if(state){
			this.ui.attr('class', state);
		}else{
			this.ui.removeAttr('class');
		}
	}
	this.state = state;
	return this;
};
ChatMessage.prototype.wrapTime = function(){
	return '<time datetime="'+this.timestamp.format('MMMM Do YYYY, h:mm:ss a')+'">'+this.timestamp.format('HH:mm')+' </time>';
};
ChatMessage.prototype.wrapMessage = function(){
	return $('<span/>').text(this.message).html();
};
ChatMessage.prototype.html = function(){
	return this.wrap(this.wrapTime() + this.wrapMessage());
};
ChatMessage.prototype.wrap = function(content){
	return '<div>'+content+'</div>';
};
// USER MESSAGE
function ChatUserMessage(message, user, timestamp){
	this.init(message, timestamp);
	this.user = user;
	this.emoteregex = /ArsonNoSexy|AsianGlow|BCWarrior|BORT|BibleThump|BionicBunion|BlargNaut|BloodTrail|BrainSlug|BrokeBack|CougarHunt|DAESuppy|DBstyle|DansGame|DatSheffy|EagleEye|EvilFetus|FPSMarksman|FUNgineer|FailFish|FrankerZ|FreakinStinkin|FuzzyOtterOO|GingerPower|HassanChop|HotPokket|ItsBoshyTime|JKanStyle|Jebaited|JonCarnage|Kappa|KevinTurtle|Kreygasm|MVGame|MrDestructoid|NinjaTroll|NoNoSpot|OMGScoots|OneHand|OpieOP|OptimizePrime|PJSalt|PMSTwin|PazPazowitz|PicoMause|PogChamp|Poooound|PunchTrees|RedCoat|ResidentSleeper|RuleFive|SMOrc|SMSkull|SSSsss|ShazBotstix|SoBayed|SoonerLater|StoneLightning|StrawBeary|SuperVinlin|SwiftRage|TehFunrun|TheRinger|TheTarFu|TinyFace|TooSpicy|TriHard|UleetBackup|UnSane|Volcania|WinWaker/;
	this.linkregex = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
	return this;
};
$.extend(ChatUserMessage.prototype, ChatMessage.prototype);
ChatUserMessage.prototype.wrapUser = function(user){
	return user.getFeatureHTML() +' <a style="color:'+user.color+'">'+user.username+'</a>';
};
ChatUserMessage.prototype.wrapMessage = function(){
	var elem  = $('<span/>').text(': '+this.message),
	    emote = this.emoteregex.exec(elem.text());
	
	elem.html(elem.text().replace(this.linkregex, '<a href="$1" target="_blank" class="externallink">$1</a>'));
	
	if (emote) {
		var emoteelem = $('<div class="twitch-emote"/>');
		emoteelem.addClass('twitch-emote-' + emote[0]);
		emoteelem.attr('title', emote[0]);
		
		var html = elem.text().replace(emote[0], emoteelem.get(0).outerHTML);
		elem.html(html);
	}
	
	return elem.html();
};
ChatUserMessage.prototype.html = function(){
	return this.wrap(this.wrapTime() + this.wrapUser(this.user) + this.wrapMessage());
};