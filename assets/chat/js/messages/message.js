/* global $, destiny */

import moment from 'moment';

class ChatMessage {

    constructor(message, timestamp = null, type = 'chat'){
        this.ui = null;
        this.message = message;
        this.state = null;
        this.type = type;
        this.timestamp = (timestamp) ? moment.utc(timestamp).local() : moment();
    }

    static statusMessage(message, timestamp = null){
        return new ChatMessage(message, timestamp, 'status');
    }

    static errorMessage(message, timestamp = null){
        return new ChatMessage(message, timestamp, 'error');
    }

    static infoMessage(message, timestamp = null){
        return new ChatMessage(message, timestamp, 'info');
    }

    static broadcastMessage(message, timestamp = null){
        return new ChatMessage(message, timestamp, 'broadcast');
    }

    static commandMessage(message, timestamp = null){
        return new ChatMessage(message, timestamp, 'command');
    }

    status(state){
        if(this.ui){
            if(state)
                this.ui.addClass(state);
            else
                this.ui.removeClass(this.state);
        }
        this.state = state;
    }

    wrapTime(){
        const datetime = this.timestamp.format('MMMM Do YYYY, h:mm:ss a');
        const label = this.timestamp.format(destiny.chat.gui.getPreference('timestampformat'));
        return `<time title="${datetime}" datetime="${datetime}">${label}</time>`;
    }

    wrapMessage(){
        const el = $('<span class="msg" />');
        let message = el.text(this.message)[0].innerHTML;
        for(const formatter of destiny.chat.gui.formatters){
            message = formatter.format(message, this.user, this.message);
        }
        return el.html(message).get(0).outerHTML;
    }

    html(){
        return this.wrap(this.wrapTime() + ' ' + this.wrapMessage());
    }

    wrap(content){
        return `<div class="${this.type}-msg">${content}</div>`;
    }
}

export default ChatMessage;