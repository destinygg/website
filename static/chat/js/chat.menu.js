(function(){

	cMenu = function(){
		return this;
	};
	
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
	
	cMenu.addMenu = function(chat, e){
		e.on('click', '.close', function(){
			cMenu.closeMenus(chat);
			return false;
		});
		cMenu.prototype.scrollable.apply(e);
		chat.menus.push(e);
		return this;
	};
	cMenu.closeMenus = function(chat){
		for(var i=0;i<chat.menus.length; ++i){
			if(chat.menus[i].visible){
				this.prototype.hideMenu.call(chat.menus[i], chat);
			}
		}
	};
	cMenu.prototype.showMenu = function(chat){
		showMenuUI(this);
		this.visible = true;
		this.btn.addClass('active');
		this.scrollable.mCustomScrollbar('update');
		++chat.menuOpenCount;
	};
	cMenu.prototype.hideMenu = function(chat){
		hideMenuUI(this);
		this.visible = false;
		this.btn.removeClass('active');
		--chat.menuOpenCount;
	};
	cMenu.prototype.scrollable = function(){
		this.scrollable.mCustomScrollbar({
			theme: 'light-thin',
			scrollInertia: 0,
			horizontalScroll: false,
			autoHideScrollbar: true,
			scrollButtons:{enable:false}
		});
	};
	
	cUserTools = function(chat){
		var self = this;
		this.chat = chat;
		this.visible = false;
		this.label = '';
		this.user = null;
		this.username = '';
		this.ui = chat.ui.find('.user-tools');
		this.ui.user = this.ui.find('.user-tools-user');
		this.ui.muteForm = this.ui.find('#user-mute-form');
		this.ui.muteForm.on('submit', function(){
			var time = $(this).find('#banTimeLength');
			chat.engine.handleCommand('mute ' + self.username + ' ' + time.val() + 'm');
			time.val('0');
			self.hide();
			return false;
		});
		this.ui.banForm = this.ui.find("#user-ban-form");
		this.ui.banForm.on('submit', function(){
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
		
		
		this.ui.on('click', 'a.close', $.proxy(this.hide, this));
		this.ui.on('click', 'a#ignoreuser,a#unignoreuser', function(){
			var cmd = $(this).attr('href').substring(1);
			chat.engine.handleCommand(cmd + ' ' + self.username);
			self.hide();
			self.show(self.label, self.username, self.user);
			return false;
		});
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
		return false;
	};
	
	
})();