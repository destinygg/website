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
		
		self.ui = chat.ui.find('.user-tools');
		self.ui.user = self.ui.find('.user-tools-user');
		
		self.ui.muteForm = this.ui.find('#user-mute-form');
		self.ui.banForm = this.ui.find("#user-ban-form");
		
		self.ui.muteForm.on('submit', function(){
			var time = $(this).find('#banTimeLength');
			chat.engine.handleCommand('mute ' + self.username + ' ' + time.val() + 'm');
			time.val('0');
			self.hide();
			return false;
		});
		self.ui.muteForm.find('button#cancelmute').on('click', function(){
			self.ui.muteForm.hide();
			return false;
		});
		
		
		self.ui.banForm.on('submit', function(){
			var time = $(this).find('#banTimeLength'),
				reason = $(this).find('#banReason'),
				ipBan = $(this).find('#ipBan'),
				cmd = (ipBan.val() == '1') ? 'ipban' : 'ban';
			chat.engine.handleCommand(cmd + ' ' + self.username + ' ' + time.val() + 'm' + ' ' + htmlEncode(reason.val()));
			time.val('0');
			reason.val('');
			ipBan.val('');
			self.hide();
			return false;
		});
		
		self.ui.banForm.find('select#banTimeLength').on('change', function(){
			self.ui.banForm.find('#banReason').focus();
			return false;
		});
		self.ui.banForm.find('button#ipbanuser').on('click', function(){
			self.ui.banForm.find('input[name="ipBan"]').val('1');
			self.ui.banForm.submit();
			return false;
		});
		self.ui.banForm.find('button#cancelban').on('click', function(){
			self.ui.banForm.hide();
			return false;
		});
		
		
		self.ui.on('click', 'a#ignoreuser,a#unignoreuser', function(){
			var cmd = $(this).attr('href').substring(1);
			chat.engine.handleCommand(cmd + ' ' + self.username);
			self.hide();
			self.show(self.label, self.username, self.user);
			return false;
		});
		
		self.ui.on('click', 'a[href="#clearmessages"]', function(){
			chat.engine.handleCommand('mute ' + self.username + ' 1ms');
			self.hide();
			return false;
		});

		self.ui.on('click', 'a[href="#togglemute"]', function(){
			self.ui.muteForm.toggle();
			self.ui.banForm.hide();
			return false;
		});
		
		self.ui.on('click', 'a[href="#toggleban"]', function(){
			self.ui.banForm.toggle();
			self.ui.banForm.hide();
			return false;
		});
		
		self.ui.on('click', '.close', $.proxy(self.hide, self));
		
		return this;
	};
	
	
	cUserTools.prototype.hide = function(){
		if(!this.visible)
			return;
		this.chat.lines.find('.focused').removeClass('focused');
		this.chat.ui.removeClass('focus-user');
		this.ui.removeClass('user-ignored');
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

		this.ui.muteForm.hide();
		this.ui.banForm.hide();
		
		this.label = label;
		this.user = user;
		this.username = username;

		if(this.chat.engine.ignorelist[this.username])
			this.ui.addClass('user-ignored');
		
		this.ui.user.text(this.label);
		this.chat.lines.find('div[data-username="'+this.username+'"]').addClass('focused');
		this.chat.ui.addClass('focus-user');
		showMenuUI(this.ui);
		this.visible = true;
		$(this).triggerHandler('show');
		return false;
	};
	
})();