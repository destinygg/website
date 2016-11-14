/* global $ */

import Logger from './log.js';

class ChatUserFocus {

    constructor(chat, css){
        this.log     = Logger.make(this);
        this.chat    = chat;
        this.css     = css;
        this.focused = [];
        this.chat.lines.on('mousedown', e => this.toggleElement(e.target));
    }

    toggleElement(target){
        const t = $(target);
        if(t.hasClass('chat-user')){
            this.toggleFocus(t.closest('.msg-user').data('username'), true)
                .toggleFocus(t.text().toLowerCase());
        } else if(t.hasClass('user')){
            this.toggleFocus(t.closest('.msg-user').data('username'));
        } else if(this.focused.length > 0) {
            this.clearFocus();
        }
    }

    addCssRule(username){
        this.log.debug('Add focus user', username);
        this.css.insertRule(`.msg-user[data-username="${username}"]{opacity:1 !important;}`, this.focused.length); // max 4294967295
        this.focused.push(username);
        this.redraw();
    }

    removeCssRule(index){
        this.log.debug('Remove focus user', index);
        this.css.deleteRule(index);
        this.focused.splice(index, 1);
        this.redraw();
    }

    clearFocus(){
        this.log.debug('Clearing focus', this.focused.length);
        this.focused.forEach(i => this.css.deleteRule(0));
        this.focused = [];
        this.redraw();
    }

    redraw(){
        this.chat.ui.toggleClass('focus-user', this.focused.length > 0);
    }

    toggleFocus(username, bool=null){
        const index = this.focused.indexOf(username.toLowerCase()),
            focused = index !== -1;
        if(bool === null)
            bool = !focused;
        if(bool && !focused)
            this.addCssRule(username);
        else if(!bool && focused)
            this.removeCssRule(index);
        return this;
    }
}

export default ChatUserFocus;