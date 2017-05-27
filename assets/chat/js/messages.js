/* global $ */

import UserFeatures from './features.js';
import moment from 'moment';

const MessageTypes = {
    status    : 'status',
    error     : 'error',
    info      : 'info',
    command   : 'command',
    broadcast : 'broadcast',
    ui        : 'ui',
    chat      : 'chat',
    user      : 'user',
    emote     : 'emote'
};

class MessageBuilder {

    static uiMessage(message){
        return new ChatUIMessage(message)
    }

    static statusMessage(message, timestamp = null){
        return new ChatMessage(message, timestamp, MessageTypes.status)
    }

    static errorMessage(message, timestamp = null){
        return new ChatMessage(message, timestamp, MessageTypes.error)
    }

    static infoMessage(message, timestamp = null){
        return new ChatMessage(message, timestamp, MessageTypes.info)
    }

    static broadcastMessage(message, timestamp = null){
        return new ChatMessage(message, timestamp, MessageTypes.broadcast)
    }

    static commandMessage(message, timestamp = null){
        return new ChatMessage(message, timestamp, MessageTypes.command)
    }

    static userMessage(message, user, timestamp = null){
        return new ChatUserMessage(message, user, timestamp)
    }

    static emoteMessage(emote, timestamp, count=1){
        return new ChatEmoteMessage(emote, timestamp, count);
    }

}

class ChatUIMessage {

    constructor(str){
        this.ui      = null;
        this.chat    = null;
        this.message = str;
        this.type    = MessageTypes.ui;
    }

    attach(chat){
        this.chat = chat;
        this.ui = $(this.html());
        return this.ui;
    }

    wrap(content, classes=[], attr={}){
        classes.unshift(`msg-${this.type}`);
        attr['class'] = classes.join(' ');
        return $('<div>', attr).html(content)[0].outerHTML;
    }

    html(){
        return this.wrap(this.message);
    }

}

class ChatMessage extends ChatUIMessage {

    constructor(message, timestamp=null, type=MessageTypes.chat){
        super(message);
        this.type = type;
        this.timestamp = (timestamp) ? moment.utc(timestamp).local() : moment();
    }

    wrapTime(){
        const datetime = this.timestamp.format('MMMM Do YYYY, h:mm:ss a');
        const label = this.timestamp.format(this.chat.settings.get('timestampformat'));
        return `<time class="time" title="${datetime}">${label}</time>`;
    }

    wrapMessage(){
        let msg = this.message;
        this.chat.formatters.forEach(f => msg = f.format(msg, this.user));
        return `<span class="text">${msg}</span>`;
    }

    html(){
        return this.wrap(this.wrapTime() + ' ' + this.wrapMessage());
    }
}

class ChatUserMessage extends ChatMessage {

    constructor(message, user, timestamp=null) {
        super(message, timestamp, MessageTypes.user);
        this.user = user;
        this.highlighted = false;
        this.prepareMessage();
    }

    prepareMessage(){
        this.isSlashMe = false;
        if (this.message.substring(0, 4) === '/me ') {
            this.isSlashMe = true;
            this.message = this.message.substring(4);
        } else if (this.message.substring(0, 2) === '//') {
            this.message = this.message.substring(1);
        }
    }

    wrapUser(user){
        const features = (user.features.length > 0) ? `<span class="features">${this.getFeatureHTML()}</span>` : '';
        return `${features} <a class="user ${user.features.join(' ')}">${user.username}</a>`;
    }

    html(){
        const classes = [], attr = {};
        if (this.user && this.user.username)
            attr['data-username'] = this.user.username.toLowerCase();
        if(this.chat.user && this.chat.user.username === this.user.username)
            classes.push('msg-own');
        if(this.isSlashMe)
            classes.push('msg-emote');
        if(this.highlighted)
            classes.push('msg-highlight');
        if(this.chat.lastmessage && this.chat.lastmessage.user && this.user && this.chat.lastmessage.user.username === this.user.username)
            classes.push('msg-continue');
        return this.wrap(this.wrapTime() + ' ' + this.wrapUser(this.user) + ' <span class="ctrl"></span> ' + this.wrapMessage(), classes, attr);
    }

    getFeatureHTML(){
        let icons = '';
        let user = this.user;

        if(user.hasFeature(UserFeatures.SUBSCRIBERT4))
            icons += '<i class="icon-subscribert4" title="Subscriber (T4)"/>';
        else if(user.hasFeature(UserFeatures.SUBSCRIBERT3))
            icons += '<i class="icon-subscribert3" title="Subscriber (T3)"/>';
        else if(user.hasFeature(UserFeatures.SUBSCRIBERT2))
            icons += '<i class="icon-subscribert2" title="Subscriber (T2)"/>';
        else if(user.hasFeature(UserFeatures.SUBSCRIBERT1))
            icons += '<i class="icon-subscriber" title="Subscriber (T1)"/>';
        else if(!user.hasFeature(UserFeatures.SUBSCRIBERT0) && user.hasFeature(UserFeatures.SUBSCRIBER))
            icons += '<i class="icon-subscriber" title="Subscriber (T1)"/>';

        for(const feature of user.features){
            switch(feature){
                case UserFeatures.SUBSCRIBERT0 :
                    icons += '<i class="icon-minitwitch" title="Twitch subscriber"/>';
                    break;
                case UserFeatures.BOT :
                    icons += '<i class="icon-bot" title="Bot"/>';
                    break;
                case UserFeatures.BOT2 :
                    icons += '<i class="icon-bot2" title="Bot"/>';
                    break;
                case UserFeatures.NOTABLE :
                    icons += '<i class="icon-notable" title="Notable"/>';
                    break;
                case UserFeatures.TRUSTED :
                    icons += '<i class="icon-trusted" title="Trusted"/>';
                    break;
                case UserFeatures.CONTRIBUTOR :
                    icons += '<i class="icon-contributor" title="Contributor"/>';
                    break;
                case UserFeatures.COMPCHALLENGE :
                    icons += '<i class="icon-compchallenge" title="Composition Winner"/>';
                    break;
                case UserFeatures.EVE :
                    icons += '<i class="icon-eve" title="Eve"/>';
                    break;
                case UserFeatures.SC2 :
                    icons += '<i class="icon-sc2" title="Starcraft 2"/>';
                    break;
                case UserFeatures.BROADCASTER :
                    icons += '<i class="icon-broadcaster" title="Broadcaster"/>';
                    break;
            }
        }
        return icons;
    }

}

class ChatEmoteMessage extends ChatMessage {

    constructor(emote, timestamp, count=1){
        super(emote, timestamp, MessageTypes.emote);
        this.emotecount   = count;
        this.emotecountui = null;
    }

    getEmoteCountLabel(){
        return `<i class='count'>${this.emotecount}</i><i class='x'>X</i> C-C-C-COMBO`;
    }

    html(){
        return this.wrap(`${this.wrapTime()} ${this.wrapMessage()} <span class="emotecount">${this.getEmoteCountLabel()}<span>`);
    }

    incEmoteCount(){
        ++this.emotecount;

        let stepClass = '';
        if(this.emotecount >= 50)
            stepClass = ' x50';
        else if(this.emotecount >= 30)
            stepClass = ' x30';
        else if(this.emotecount >= 20)
            stepClass = ' x20';
        else if(this.emotecount >= 10)
            stepClass = ' x10';
        else if(this.emotecount >= 5)
            stepClass = ' x5';

        if(!this.emotecountui)
            this.emotecountui = this.ui.find('.emotecount');

        this.emotecountui.detach().attr('class', 'emotecount' + stepClass).html(this.getEmoteCountLabel()).appendTo(this.ui);
    }

}

export {
    MessageBuilder,
    ChatUIMessage,
    ChatMessage,
    ChatUserMessage,
    ChatEmoteMessage,
    MessageTypes
};