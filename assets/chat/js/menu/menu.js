/* global $ */

import ChatScrollPlugin from '../scroll.js';
import EventEmitter from '../emitter.js';

class ChatMenu extends EventEmitter {

    constructor(ui, chat){
        super();
        this.ui      = $(ui);
        this.chat    = chat;
        this.btn     = null;
        this.visible = false;
        this.shown   = false;
        this.ui.find('.scrollable').get().forEach(el => this.scrollPlugin = new ChatScrollPlugin(el));
        this.ui.on('click', '.close', this.hide.bind(this));
    }

    show(btn){
        if(!this.visible){
            this.visible = true;
            this.btn = $(btn);
            this.shown = true;
            this.btn.addClass('active');
            this.ui.addClass('active');
            this.redraw();
            this.emit('show');
        }
    }

    hide(){
        if(this.visible){
            this.visible = false;
            this.btn.removeClass('active');
            this.ui.removeClass('active');
            this.emit('hide');
        }
    }

    toggle(btn){
        const wasVisible = this.visible;
        ChatMenu.closeMenus(this.chat);
        if(!wasVisible) this.show(btn);
    }

    redraw(){
        if(this.scrollPlugin) this.scrollPlugin.reset();
    }

    static closeMenus(chat){
        chat.menus.forEach(m => m.hide());
    }

}

export default ChatMenu;