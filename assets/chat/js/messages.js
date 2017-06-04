/* global $ */

import UserFeatures from './features';
import moment from 'moment';

const MessageTypes = {
    STATUS    : 'STATUS',
    ERROR     : 'ERROR',
    INFO      : 'INFO',
    COMMAND   : 'COMMAND',
    BROADCAST : 'BROADCAST',
    UI        : 'UI',
    CHAT      : 'CHAT',
    USER      : 'USER',
    EMOTE     : 'EMOTE'
};
const MessageGlobals = {
    timeformat: 'HH:mm',
    datetimeformat: 'MMMM Do YYYY, h:mm:ss a'
};

function buildFeatures(user){
    const features = [...user.features || []]
        .filter(e => !UserFeatures.SUBSCRIBER.equals(e))
        .sort((a, b) => {
            let a1,a2;

            a1 = UserFeatures.SUBSCRIBERT4.equals(a);
            a2 = UserFeatures.SUBSCRIBERT4.equals(b);
            if (a1 > a2) return -1; if (a1 < a2) return 1;

            a1 = UserFeatures.SUBSCRIBERT3.equals(a);
            a2 = UserFeatures.SUBSCRIBERT3.equals(b);
            if (a1 > a2) return -1; if (a1 < a2) return 1;

            a1 = UserFeatures.SUBSCRIBERT2.equals(a);
            a2 = UserFeatures.SUBSCRIBERT2.equals(b);
            if (a1 > a2) return -1; if (a1 < a2) return 1;

            a1 = UserFeatures.SUBSCRIBERT1.equals(a);
            a2 = UserFeatures.SUBSCRIBERT1.equals(b);
            if (a1 > a2) return -1; if (a1 < a2) return 1;

            a1 = UserFeatures.SUBSCRIBERT0.equals(a);
            a2 = UserFeatures.SUBSCRIBERT0.equals(b);
            if (a1 > a2) return -1; if (a1 < a2) return 1;

            a1 = UserFeatures.BOT2.equals(a) || UserFeatures.BOT.equals(a);
            a2 = UserFeatures.BOT2.equals(a) || UserFeatures.BOT.equals(b);
            if (a1 > a2) return -1; if (a1 < a2) return 1;

            a1 = UserFeatures.VIP.equals(a);
            a2 = UserFeatures.VIP.equals(b);
            if (a1 > a2) return -1; if (a1 < a2) return 1;

            a1 = UserFeatures.CONTRIBUTOR.equals(a) || UserFeatures.TRUSTED.equals(a);
            a2 = UserFeatures.CONTRIBUTOR.equals(b) || UserFeatures.TRUSTED.equals(b);
            if (a1 > a2) return -1; if (a1 < a2) return 1;

            a1 = UserFeatures.NOTABLE.equals(a);
            a2 = UserFeatures.NOTABLE.equals(b);
            if (a1 > a2) return -1; if (a1 < a2) return 1;

            if (a > b) return -1; if (a < b) return 1;
            return 0;
        })
        .map(e => {
            const f = UserFeatures.valueOf(e);
            return `<i class="flair icon-${e.toLowerCase()}" title="${f !== null ? f.label : e}" />`;
        })
        .join('');
    return features.length > 0 ? `<span class="features">${features}</span>` : '';
}
function buildEmoteCount(count){
    return `<i class='count'>${count}</i> <i class="x">X</i> <i class="hit">Hits</i> <i class='combo'>C-C-C-COMBO</i>`;
}
function buildTime(message){
    const datetime = message.timestamp.format(MessageGlobals.datetimeformat);
    const label = message.timestamp.format(message.timeformat);
    return `<time class="time" title="${datetime}">${label}</time>`;
}
function buildMessageTxt(chat, message){
    // TODO we strip off the `/me ` of every message -- must be a better way to do this
    let msg = message.message.substring(0, 4).toLowerCase() === '/me ' ? message.message.substring(4) : message.message;
    chat.formatters.forEach(f => msg = f.format(msg, message.user, message));
    return `<span class="text">${msg}</span>`;
}
function buildWhisperTools(){
    return  '<span>'+
                `<a class="chat-open-whisper"><i class="fa fa-envelope" aria-hidden="true"></i> open</a> ` +
                `<a class="chat-remove-whisper"><i class="fa fa-times" aria-hidden="true"></i> remove</a>`+
            '</span>';
}

class MessageBuilder {

    static element(message, classes=[]){
        return new ChatUIMessage(message, classes)
    }

    static status(message, timestamp = null){
        return new ChatMessage(message, timestamp, MessageTypes.STATUS)
    }

    static error(message, timestamp = null){
        return new ChatMessage(message, timestamp, MessageTypes.ERROR)
    }

    static info(message, timestamp = null){
        return new ChatMessage(message, timestamp, MessageTypes.INFO)
    }

    static broadcast(message, timestamp = null){
        return new ChatMessage(message, timestamp, MessageTypes.BROADCAST)
    }

    static command(message, timestamp = null){
        return new ChatMessage(message, timestamp, MessageTypes.COMMAND)
    }

    static message(message, user, timestamp = null){
        return new ChatUserMessage(message, user, timestamp)
    }

    static emote(emote, timestamp, count=1){
        return new ChatEmoteMessage(emote, timestamp, count);
    }

    static whisper(message, user, target, timestamp = null, id = null){
        const m = new ChatUserMessage(message, user, timestamp);
        m.id = id;
        m.target = target;
        return m;
    }

    static historical(message, user, timestamp = null){
        const m = new ChatUserMessage(message, user, timestamp);
        m.historical = true;
        return m;
    }

}

class ChatUIMessage {

    constructor(message, classes=[]){
        this.type = MessageTypes.UI;
        this.message = message;
        this.classes = classes;
    }

    into(chat, window=null){
        chat.addMessage(this, window);
        return this;
    }

    wrap(content, classes=[], attr={}){
        classes.push(this.classes);
        classes.unshift(`msg-${this.type.toLowerCase()}`);
        classes.unshift(`msg-chat`);
        attr['class'] = classes.join(' ');
        return $('<div />', attr).html(content)[0].outerHTML;
    }

    html(chat=null){
        return this.wrap(this.message);
    }

}

class ChatMessage extends ChatUIMessage {

    constructor(message, timestamp=null, type=MessageTypes.CHAT){
        super(message);
        this.user = null;
        this.type = type;
        this.continued = false;
        this.timestamp = timestamp ? moment.utc(timestamp).local() : moment();
        this.timeformat = MessageGlobals.timeformat;
    }

    html(chat=null){
        const classes = [], attr = {};
        if(this.continued)
            classes.push('msg-continue');
        return this.wrap(buildTime(this) + ' ' + buildMessageTxt(chat, this), classes, attr);
    }
}

class ChatUserMessage extends ChatMessage {

    constructor(message, user, timestamp=null) {
        super(message, timestamp, MessageTypes.USER);
        this.user = user;
        this.id = null;
        this.isown = false;
        this.highlighted = false;
        this.historical = false;
        this.target = null;
        this.tag = null;
        this.slashme = false;
        this.mentioned = [];
    }

    html(chat=null){
        const classes = [], attr = {};

        if(this.id)
            attr['data-id'] = this.id;
        if(this.user && this.user.username)
            attr['data-username'] = this.user.username.toLowerCase();
        if(this.mentioned && this.mentioned.length > 0)
            attr['data-mentioned'] = this.mentioned.join(' ').toLowerCase();

        if(this.isown)
            classes.push('msg-own');
        if(this.slashme)
            classes.push('msg-emote');
        if(this.historical)
            classes.push('msg-historical');
        if(this.highlighted)
            classes.push('msg-highlight');
        if(this.continued && !this.target)
            classes.push('msg-continue');
        if(this.tag)
            classes.push(`msg-tagged msg-tagged-${this.tag}`);
        if(this.target)
            classes.push(`msg-whisper`);

        let ctrl = ': ';
        if(this.target)
            ctrl = ` whispered you ... ` + buildWhisperTools();
        else if(this.slashme)
            ctrl = '';
        else if(this.continued)
            ctrl = '';

        const user = buildFeatures(this.user) + ` <a class="user ${this.user.features.join(' ')}">${this.user.username}</a>`;
        return this.wrap(buildTime(this) + ` ${user}<span class="ctrl">${ctrl}</span> ` + buildMessageTxt(chat, this), classes, attr);
    }

}

class ChatEmoteMessage extends ChatMessage {

    constructor(emote, timestamp, count=1){
        super(emote, timestamp, MessageTypes.EMOTE);
        this.emotecount = count;
        this.emotecountui = null;
    }

    html(chat=null){
        return this.wrap(`${buildTime(this)} ${buildMessageTxt(chat, this)} <span class="chat-combo">${buildEmoteCount(this.emotecount)}<span>`);
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
            this.emotecountui = this.ui.find('.chat-combo');
        this.emotecountui
            .detach()
            .attr('class', 'chat-combo' + stepClass)
            .html(buildEmoteCount(this.emotecount))
            .appendTo(this.ui);
    }

    completeCombo(){
        if(!this.emotecountui)
            this.emotecountui = this.ui.find('.chat-combo');
        this.emotecountui.addClass('combo-complete');
    }

}

export {
    MessageBuilder,
    MessageGlobals,
    ChatUIMessage,
    ChatMessage,
    ChatUserMessage,
    ChatEmoteMessage,
    MessageTypes
};