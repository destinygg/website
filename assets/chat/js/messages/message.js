/* global $, destiny */

import moment from 'moment';
import ChatUIMessage from './ui.js';

class ChatMessage extends ChatUIMessage {

    constructor(message, timestamp=null, type='chat'){
        super(message);
        this.type = type;
        this.timestamp = (timestamp) ? moment.utc(timestamp).local() : moment();
    }

    static uiMessage(message){
        return new ChatUIMessage(message);
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

    wrapTime(){
        const datetime = this.timestamp.format('MMMM Do YYYY, h:mm:ss a');
        const label = this.timestamp.format(this.chat.settings.get('timestampformat'));
        return `<time class="time" title="${datetime}">${label}</time>`;
    }

    wrapMessage(){
        let message = this.message;
        this.chat.formatters.forEach(formatter => message = formatter.format(message, this.user));
        return `<span class="text">${message}</span>`;
    }

    html(){
        return this.wrap(this.wrapTime() + ' ' + this.wrapMessage());
    }
}

export default ChatMessage;