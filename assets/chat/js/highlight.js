/* global */

import Chat from './chat.js';
import ChatUserMessage from './messages/user.js';
import UserFeatures from './features.js';

class ChatHighlighter {

    constructor(chat){
        this.chat = chat;
        this.customregex = null;
        this.userregex = null;
        this.highlightnicks = null;
        this.loadHighlighters();
    }

    loadHighlighters(){
        const highlights = this.chat.settings.get('customhighlight').map(Chat.makeSafeForRegex).join('|');
        if (highlights !== '')
            this.customregex = new RegExp(`\\b(?:${highlights})\\b`, 'i');
        if (this.chat.user && this.chat.user.username)
            this.userregex = new RegExp(`\\b@?(?:${this.chat.user.username})\\b`, 'i');
        this.highlightnicks = Object.keys(this.chat.settings.get('highlightnicks'));
    }

    mustHighlight(message){
        if (!this.chat.user || !(message instanceof ChatUserMessage) || !this.chat.settings.get('highlight') || message.user.hasFeature(UserFeatures.BOT) || message.user.username === this.chat.user.username)
            return false;
        return Boolean(
            this.highlightnicks.find(nick => message.user.username.toLowerCase() === nick.toLowerCase()) ||
            (this.userregex && this.userregex.test(message.message)) ||
            (this.customregex && this.customregex.test(message.message))
        );
    }

    redraw(){}
}

export default ChatHighlighter;