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
	
})();