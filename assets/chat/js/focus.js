/* global $ */

/**
 * Handles the dimming of the chat when you click on a username
 * within the chat GUI
 */
class ChatUserFocus {

    constructor(chat, css){
        this.chat = chat;
        this.css = css;
        this.focused = [];
        this.chat.output.on('mousedown', e => this.toggleElement(e.target));
    }

    toggleElement(target){
        const t = $(target);
        if(t.hasClass('chat-user')){
            if(!this.chat.settings.get('focusmentioned'))
                this.toggleFocus(t.closest('.msg-user').data('username'), true);
            this.toggleFocus(t.text());
        } else if(t.hasClass('user')){
            this.toggleFocus(t.text());
        } else if(this.focused.length > 0) {
            this.clearFocus();
        }
    }

    toggleFocus(username, bool=null){
        username = (username || '').toLowerCase();
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

    addCssRule(username){
        let rule;
        if(this.chat.settings.get('focusmentioned')) {
            rule = `.msg-user[data-username="${username}"],.msg-user[data-mentioned~="${username}"]{opacity:1 !important;}`;
        } else {
            rule = `.msg-user[data-username="${username}"]{opacity:1 !important;}`;
        }
        this.css.insertRule(rule, this.focused.length); // max 4294967295
        this.focused.push(username);
        this.redraw();
    }

    removeCssRule(index){
        this.css.deleteRule(index);
        this.focused.splice(index, 1);
        this.redraw();
    }

    clearFocus(){
        this.focused.forEach(i => this.css.deleteRule(0));
        this.focused = [];
        this.redraw();
    }

    redraw(){
        this.chat.ui.toggleClass('focus-user', this.focused.length > 0);
    }
}

export default ChatUserFocus;