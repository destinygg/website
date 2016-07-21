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
	
	
})();