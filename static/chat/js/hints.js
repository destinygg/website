(function(){
	
	if(!localStorage)
		localStorage = {};
	
	hintPopup = function(chat){
		
		this.hints = {
			'slashhelp'      : 'Type in /help for more advanced features, like modifying the scrollback size',
			'tabcompletion'  : 'Use the tab key to auto-complete usernames and emotes',
			'hoveremotes'    : 'Hovering your mouse over an emote will show you the emote code',
			'highlight'      : 'Chat messages containing your username will be highlighted in blue',
			'ignoreuser'     : 'Ignore other users by clicking their name and selecting ignore',
			'moreinfo'       : 'See the <a href="/chat/faq" target="_blank">chat FAQ</a> for more information',
			'emotewiki'      : 'For the list of available emotes type /emotes or <a href="https://github.com/destinygg/website/wiki/Emotes" target="_blank">click here</a>',
			'mutespermanent' : 'Mutes are never persistent, don\'t worry it will pass!'
		};

		this.popupInterval   = 3600000;
		this.readingInterval = 30000;
		this.paused          = false;
		this.visible         = false;
		this.enabled         = true;
		this.hiddenhints     = JSON.parse(localStorage['hiddenhints'] || '[]');
		this.lasthinttime    = (localStorage['lasthinttime'] || null);
		this.currenthint     = '';
		this.hintindex       = [];
		
		for(var i in this.hints)
			this.hintindex.push(i);
		
		this.ui = chat.ui.find('.hint-popup');
		this.ui.hintmessage = this.ui.find('.hint-message');
		this.ui.on('click', '.hidehint', $.proxy(this.hideHint, this));
		this.ui.on('click', '.nexthint', $.proxy(this.nextHint, this));
		
		if(this.enabled)
			this.listenForInput(chat);
		
	};
	hintPopup.prototype.listenForInput = function(chat){
		chat.input.unbind('.hintpopup');
		chat.input.on('click.hintpopup keydown.hintpopup', $.proxy(function(e){
			if(chat.loaded && e.keyCode != 116 /* F5 */){
				chat.input.unbind('.hintpopup');
				this.invoke();
			};
		}, this));
	};
	hintPopup.prototype.invoke = function(chat){
		if(this.visible || !this.enabled)
			return false;
		if(!this.lasthinttime || (new Date().getTime() - this.lasthinttime)  >= this.popupInterval){
			this.currenthint = this.getRandomHint();
			if(!this.currenthint)
				return false;
			this.show();
			return true;
		};
		return false;
	};
	hintPopup.prototype.enable = function(enabled){
		this.enabled = enabled;
	};
	hintPopup.prototype.reset = function(chat){
		this.hiddenhints = [];
		this.updateHiddenHints();
		this.updateLastHintTime(0);
		this.listenForInput(chat);
	};
	hintPopup.prototype.getRandomHint = function(){
		var hint = null, i = 0;
		while(++i){
			var id = this.hintindex[Math.floor(Math.random()*this.hintindex.length)];
			if(this.hiddenhints.indexOf(id) == -1)
				hint = id;
			if(i == this.hintindex.length || hint)
				break;
		}
		return hint;
	};
	hintPopup.prototype.hideHint = function(){
		this.hiddenhints.push(this.currenthint);
		this.updateHiddenHints();
		this.hide();
		return false;
	};
	hintPopup.prototype.nextHint = function(){
		if(!this.currenthint)
			return;
		this.currenthint = (this.hintindex[this.hintindex.indexOf(this.currenthint) + 1] || this.hintindex[0]);
		this.hiddenhints.push(this.currenthint);
		this.updateHiddenHints();
		this.show();
		return false;
	};
	hintPopup.prototype.show = function(){
		clearTimeout(this.ui.data('hide-timeout'));
		clearTimeout(this.hideTimeoutId);
		this.hideTimeoutId = setTimeout($.proxy(this.hide, this), this.readingInterval);
		this.ui.hintmessage.html(this.hints[this.currenthint]);
		this.updateLastHintTime(new Date().getTime());
		this.ui.addClass('active').css('visibility', 'visible');
		this.visible = true;
	};
	hintPopup.prototype.hide = function(){
		clearTimeout(this.ui.data('hide-timeout'));
		clearTimeout(this.hideTimeoutId);
		this.currenthint = '';
		this.visible = false;
		this.ui.removeClass('active').data('hide-timeout', setTimeout($.proxy(function(){
			this.ui.css('visibility', 'hidden');
		}, this), 250));
	};
	hintPopup.prototype.updateHiddenHints = function(){
		localStorage['hiddenhints'] = JSON.stringify(this.hiddenhints);
	};
	hintPopup.prototype.updateLastHintTime = function(time){
		this.lasthinttime = time;
		localStorage['lasthinttime'] = this.lasthinttime;
	};
	
})();