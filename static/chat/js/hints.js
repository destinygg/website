(function(){
	
	if(!localStorage)
		localStorage = {};
	
	hintPopup = function(chat){
		
		this.hints = {
			'tabcompletion'  : 'Use the tab key to auto-complete usernames and emotes',
			'hoveremotes'    : 'Hovering your mouse over an emote will show you the emote code',
			'highlight'      : 'Chat messages containing your username will highlight blue',
			'ignoreuser'     : 'Ignore other users by clicking their name and selecting ignore',
			'localstorage'   : 'Chat settings can be cleared in your browser',
			'hidehints'      : 'You can hide these types of hints in the options menu',
			'moreinfo'       : 'See the <a href="/chat/faq" target="_blank">chat FAQ</a> for more information'
		};

		this.popupInterval   = 3600000;
		this.readingInterval = 30000;
		this.paused          = false;
		this.visible         = false;
		this.enabled         = !chat.getChatOption('hidehints', false);
		this.hiddenhints     = JSON.parse(localStorage['hiddenhints'] || '[]');
		this.lasthinttime    = (localStorage['lasthinttime'] || null);
		this.currenthint     = '';
		this.hintindex       = [];
		
		for(var i in this.hints)
			this.hintindex.push(i);
		
		this.ui = chat.ui.find('.hint-popup');
		this.ui.hintmessage = this.ui.find('.hint-message');
		this.ui.on('click', '.close', $.proxy(this.hideHint, this));
		this.ui.on('click', '.nexthint', $.proxy(this.nextHint, this));
	};
	hintPopup.prototype.invoke = function(){
		if(this.visible || !this.enabled)
			return;
		if(!this.lasthinttime || (new Date().getTime() - this.lasthinttime)  >= this.popupInterval){
			this.currenthint = this.getRandomHint();
			if(!this.currenthint)
				return;
			this.show();
		};
	};
	hintPopup.prototype.enable = function(enabled){
		this.enabled = enabled;
	};
	hintPopup.prototype.reset = function(){
		this.hiddenhints = [];
		this.updateHiddenHints();
		this.updateLastHintTime(0);
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