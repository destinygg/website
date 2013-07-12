(function(){

	cMenu = function(){
		return this;
	};
	cMenu.addMenu = function(chat, e){
		e.on('click', 'button.close', function(){
			cMenu.closeMenus(chat);
		});
		cMenu.prototype.scrollable.apply(e);
		chat.menus.push(e);
		return this;
	};
	cMenu.update = function(chat){
		if(chat.menuOpenCount > 0){
			chat.ui.addClass('active-menu');
		}else{
			chat.ui.removeClass('active-menu');
		}
	};
	cMenu.closeMenus = function(chat){
		for(var i=0;i<chat.menus.length; ++i){
			if(chat.menus[i].visible){
				this.prototype.hideMenu.call(chat.menus[i], chat);
			}
		}
	};
	cMenu.prototype.showMenu = function(chat){
		this.stop().slideDown(50);
		this.visible = true;
		this.btn.addClass('active');
		this.scrollable.mCustomScrollbar('update');
		++chat.menuOpenCount;
		cMenu.update(chat);
	};
	cMenu.prototype.hideMenu = function(chat){
		this.stop().hide();
		this.visible = false;
		this.btn.removeClass('active');
		--chat.menuOpenCount;
		cMenu.update(chat);
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
			var reason = $(this).find('#banReason');
			chat.engine.handleCommand('/mute ' + self.username + ' ' + time.val() + 'm ' + '"' + htmlEncode(reason.val()) + '"');
			time.val('');
			reason.val('');
			self.hide();
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
		this.ui.removeClass('active user-ignored');
		this.visible = false;
		return false;
	};
	cUserTools.prototype.show = function(label, username, user){
		if(this.visible && username == this.username)
			return this.hide();
		if(this.visible)
			this.hide();
		
		this.ui.muteForm.hide();
		
		this.label = label;
		this.user = user;
		this.username = username;

		if(this.chat.engine.ignorelist[this.username])
			this.ui.addClass('user-ignored');
		
		this.ui.user.text(this.label);
		this.chat.lines.find('div[data-username="'+this.username+'"]').addClass('focused');
		this.chat.ui.addClass('focus-user');
		this.ui.addClass('active');
		this.visible = true;
		return false;
	};
	
	
})();