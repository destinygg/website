(function(){

	cMenu = function(){
		this.scrollPlugin = null;
		return this;
	};
	
	var showMenuUI = function(ui){
		clearTimeout(ui.data('hide-timeout'));
		ui.addClass('active');
	};
	var hideMenuUI = function(ui){
		ui.removeClass('active');
	};
	
	cMenu.addMenu = function(chat, el){
		el.on('click', '.close', function(){
			cMenu.closeMenus(chat);
			return false;
		});
		chat.menus.push(el);
		return this;
	};
	cMenu.closeMenus = function(chat){
		for(var i=0;i<chat.menus.length; ++i){
			if(chat.menus[i].visible)
				this.prototype.hideMenu.call(chat.menus[i], chat);
		}
	};
	cMenu.prototype.showMenu = function(chat){
		showMenuUI(this);
		this.visible = true;
		this.btn.addClass('active');
		
		// Can only init the scrollbar when the item is visible
		if(this.scrollable){
			if(!this.scrollPlugin)
				this.scrollPlugin = this.scrollable.nanoScroller({disableResize: true, preventPageScrolling: true})[0].nanoscroller;
			else
				this.scrollPlugin.reset();
		}
		
		++chat.menuOpenCount;
	};
	
	
	cMenu.prototype.hideMenu = function(chat){
		hideMenuUI(this);
		this.visible = false;
		this.btn.removeClass('active');
		--chat.menuOpenCount;
	};
	
	
	cUserTools = function(chat){
		var self = this;
		
		self.chat = chat;
		self.visible = false;
		self.label = '';
		self.user = null;
		self.username = '';
		
		return this;
	};
	
	
	cUserTools.prototype.hide = function(){
		if(!this.visible)
			return;
		this.chat.lines.find('.focused').removeClass('focused');
		this.chat.ui.removeClass('focus-user');
		hideMenuUI(this.ui);
		this.visible = false;
		$(this).triggerHandler('hide');
		return false;
	};
	cUserTools.prototype.show = function(label, username, user){
		if(this.visible && username == this.username)
			return this.hide();
		if(this.visible)
			this.hide();

		this.label = label;
		this.user = user;
		this.username = username;

		this.chat.lines.find('div[data-username="'+this.username+'"]').addClass('focused');
		this.chat.ui.addClass('focus-user');
		this.visible = true;
		$(this).triggerHandler('show');
		return false;
	};
	
})();