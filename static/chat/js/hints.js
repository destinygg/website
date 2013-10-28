(function(){
	
	var showMenuUI = function(ui){
		clearTimeout(ui.data('hide-timeout'));
		ui.addClass('active').css('visibility', 'visible');
	};
	var hideMenuUI = function(ui){
		ui.removeClass('active');
		ui.data('hide-timeout', setTimeout(function(){
			ui.css('visibility', 'hidden');
		}, 250));
	};
	
	hintPopup = function(chat, enabled){
		
		this.hints = {
			'tabcompletion'  : 'Use the tab key to auto-complete usernames and emotes',
			'hoveremotes'    : 'Hovering your mouse over an emote will show you the emote code',
			'highlight'      : 'Chat messages containing your username will highlight blue',
			'ignoreuser'     : 'Ignore other users by clicking their name and selecting ignore',
			'localstorage'   : 'Chat settings can be cleared in your browser',
			'hidehints'      : 'You can hide these hints at any time',
			'moreinfo'       : 'See the <a href="/chat/faq" target="_blank">chat FAQ</a> for more information'
		};
		
		this.paused          = false;
		this.visible         = false;
		this.enabled         = enabled;
		this.hiddenhints     = [];
		this.popupInterval   = 60000;
		this.readingInterval = 10000;
		this.lasthint        = '';
		this.currenthint     = '';
		this.hintindex       = [];
		
		for(var i in this.hints)
			this.hintindex.push(i);
		
		this.ui = chat.ui.find('.hint-popup');
		this.ui.hintmessage = this.ui.find('.hint-message');
		this.ui.on('click', 'a.close', $.proxy(this.hide, this));
		this.ui.on('click', 'a.hidehint', $.proxy(this.hideHint, this));
		this.ui.on('mouseover', $.proxy(this.pause, this));
		this.ui.on('mouseout', $.proxy(this.unpause, this));
		this.load();
		this.rotate();
	};
	hintPopup.prototype.pause = function(){
		this.paused = true;
		clearTimeout(this.showTimeoutId);
		clearTimeout(this.hideTimeoutId);
	};
	hintPopup.prototype.unpause = function(){
		this.paused = false;
		this.rotate();
	};
	hintPopup.prototype.enable = function(enabled){
		this.enabled = enabled;
	};
	hintPopup.prototype.load = function(){
		if(!localStorage)
			return;
		this.hiddenhints = JSON.parse(localStorage['hiddenhints'] || '[]');
	};
	hintPopup.prototype.save = function(){
		if(!localStorage)
			return;
		localStorage['hiddenhints'] = JSON.stringify(this.hiddenhints);
	};
	hintPopup.prototype.reset = function(){
		if(!localStorage)
			return;
		this.hiddenhints = [];
		this.save();
	};
	hintPopup.prototype.hideHint = function(){
		this.hiddenhints.push(this.currenthint);
		this.save();
		this.hide();
	};
	hintPopup.prototype.getRandomHint = function(){
		var hint = null, i = 0;
		while(++i){
			var id = this.hintindex[Math.floor(Math.random()*this.hintindex.length)];
			if(this.hiddenhints.indexOf(id) == -1 && id != this.lasthint)
				hint = id;
			if(i == this.hintindex.length || hint)
				break;
		}
		return hint;
	};
	hintPopup.prototype.show = function(){
		this.currenthint = this.getRandomHint();
		if(!this.currenthint)
			return;
		this.ui.hintmessage.html(this.hints[this.currenthint]);
		showMenuUI(this.ui);
		this.visible = true;
	};
	hintPopup.prototype.hide = function(){
		this.lasthint = this.currenthint;
		this.currenthint = '';
		hideMenuUI(this.ui);
		this.visible = false;
	};
	hintPopup.prototype.rotateOut = function(){
		var self = this;
		self.hideTimeoutId = setTimeout(function(){
			self.hide();
			self.rotateIn();
		}, self.readingInterval);
	};
	hintPopup.prototype.rotateIn = function(){
		var self = this;
		self.showTimeoutId = setTimeout(function(){
			if(self.enabled)
				self.show();
			self.rotateOut();
		}, self.popupInterval);
	};
	hintPopup.prototype.rotate = function(){
		var self = this;
		if(!self.visible)
			self.rotateIn();
		else
			self.rotateOut();
	};
})();