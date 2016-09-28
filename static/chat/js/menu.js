ChatMenu = (function(){

    /**
     * @type {Array<ChatMenu>}
     */
    var menus = [];

    /**
     * @param ui
     * @param chat
     */
    function cls(ui, chat) {

        var scrollPlugin = null;

        this.ui      = ui;
        this.chat    = chat;
        this.btn     = null;
        this.visible = false;
        this.shown   = false;

        this.on = function(name, fn){
            $(this).on(name, fn);
        };

        this.show = function(btn){
            if(this.visible) return;
            if(!this.shown) $(this).triggerHandler('init');

            this.visible = true;
            this.shown = true;
            this.btn = btn;

            $(this.btn).addClass('active');
            $(this.ui).addClass('active');
            $(this).triggerHandler('show');

            $(this.ui).find('.scrollable').each(function(){
                if(!scrollPlugin)
                    scrollPlugin = $(this).nanoScroller({
                        disableResize: true,
                        preventPageScrolling: true
                    })[0].nanoscroller;
                else
                    scrollPlugin.reset();
            });
        };

        this.hide = function(){
            if(this.visible){
                this.visible = false;
                $(this.btn).removeClass('active');
                $(this.ui).removeClass('active');
                $(this).triggerHandler('hide');
            }
        };

        this.toggle = function(btn){
            var wasVisible = this.visible;
            ChatMenu.closeMenus(null);
            if(!wasVisible) this.show(btn);
        };

        this.redraw = function(){
            if(scrollPlugin) scrollPlugin.reset();
        };

        this.ui.on('click', '.close', this.hide.bind(this));
        menus.push(this);
    }

    cls.closeMenus = function(){
        for(var i=0; i<menus.length; ++i)
            menus[i].hide();
    };

    return cls;

})();