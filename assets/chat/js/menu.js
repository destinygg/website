/* global $, destiny */

class ChatMenu {

    constructor(ui, chat){
        this.ui      = ui;
        this.chat    = chat;
        this.btn     = null;
        this.visible = false;
        this.shown   = false;
        this.scrollPlugin = null;
        this.ui.on('click', '.close', this.hide.bind(this));
        this.chat.menus.push(this);
    }

    on(name, fn){
        $(this).on(name, fn);
    }

    show(btn){
        if(this.visible) return;
        if(!this.shown) $(this).triggerHandler('init');

        this.visible = true;
        this.shown = true;
        this.btn = btn;

        $(this.btn).addClass('active');
        $(this.ui).addClass('active');
        $(this).triggerHandler('show');

        $(this.ui).find('.scrollable').each(function(){
            if(!this.scrollPlugin)
                this.scrollPlugin = $(this).nanoScroller({
                    disableResize: true,
                    preventPageScrolling: true
                })[0].nanoscroller;
            else
                this.scrollPlugin.reset();
        });
    }

    hide(){
        if(this.visible){
            this.visible = false;
            $(this.btn).removeClass('active');
            $(this.ui).removeClass('active');
            $(this).triggerHandler('hide');
        }
    }

    toggle(btn){
        const wasVisible = this.visible;
        ChatMenu.closeMenus(this.chat);
        if(!wasVisible) this.show(btn);
    }

    redraw(){
        if(this.scrollPlugin && this.scrollPlugin.isActive)
            this.scrollPlugin.reset();
    }

    static closeMenus(chat){
        for(var i=0; i<chat.menus.length; ++i)
            chat.menus[i].hide();
    }

}

export default ChatMenu;