/* global $ */

import {EmoteFormatter, GreenTextFormatter, HtmlTextFormatter, MentionedUserFormatter,UrlFormatter} from './formatters'
import {DATE_FORMATS} from './const'
import UserFeatures from './features'
import throttle from 'throttle-debounce/throttle'
import moment from 'moment'

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
}
const formatters = new Map()
formatters.set('html', new HtmlTextFormatter())
formatters.set('url', new UrlFormatter())
formatters.set('emote', new EmoteFormatter())
formatters.set('mentioned', new MentionedUserFormatter())
formatters.set('green', new GreenTextFormatter())

function buildMessageTxt(chat, message){
    // TODO we strip off the `/me ` of every message -- must be a better way to do this
    let msg = message.message.substring(0, 4).toLowerCase() === '/me ' ? message.message.substring(4) : message.message
    formatters.forEach(f => msg = f.format(chat, msg, message))
    return `<span class="text">${msg}</span>`
}
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
function buildTime(message){
    const datetime = message.timestamp.format(DATE_FORMATS.FULL);
    const label = message.timestamp.format(DATE_FORMATS.TIME);
    return `<time class="time" title="${datetime}">${label}</time>`;
}
function buildWhisperTools(){
    return  '<span>'+
                `<a class="chat-open-whisper"><i class="fa fa-envelope"></i> open</a> ` +
                `<a class="chat-remove-whisper"><i class="fa fa-times" ></i> remove</a>`+
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
        /** @type String */
        this.type = MessageTypes.UI
        /** @type String */
        this.message = message
        /** @type Array */
        this.classes = classes
        /** @type JQuery */
        this.ui = null
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

    afterRender(chat=null){}

}

class ChatMessage extends ChatUIMessage {

    constructor(message, timestamp=null, type=MessageTypes.CHAT){
        super(message);
        this.user = null;
        this.type = type;
        this.continued = false;
        this.timestamp = timestamp ? moment.utc(timestamp).local() : moment();
    }

    html(chat=null){
        const classes = [], attr = {};
        if(this.continued)
            classes.push('msg-continue');
        return this.wrap(`${buildTime(this)} ${buildMessageTxt(chat, this)}`, classes, attr);
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
            classes.push('msg-me');
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

function ChatEmoteMessageCount(message){
    if(!message || !message._combo)
        return;
    let stepClass = ''
    if(message.emotecount >= 50)
        stepClass = ' x50'
    else if(message.emotecount >= 30)
        stepClass = ' x30'
    else if(message.emotecount >= 20)
        stepClass = ' x20'
    else if(message.emotecount >= 10)
        stepClass = ' x10'
    else if(message.emotecount >= 5)
        stepClass = ' x5'
    if(!message._combo)
        console.error('no combo', message._combo)
    message._combo.attr('class', 'chat-combo' + stepClass)
    message._combo_count.text(`${message.emotecount}`)
    message.ui.append(message._text.detach(), message._combo.detach())
}
const ChatEmoteMessageCountThrottle = throttle(63, ChatEmoteMessageCount)

class ChatEmoteMessage extends ChatMessage {

    constructor(emote, timestamp, count=1){
        super(emote, timestamp, MessageTypes.EMOTE)
        this.emotecount = count
    }

    html(chat=null){
        this._text          = $(`<span class="text">${formatters.get('emote').format(chat, this.message, this)}</span>`)
        this._combo         = $(`<span class="chat-combo" />`)
        this._combo_count   = $(`<i class="count">${this.emotecount}</i>`)
        this._combo_x       = $(`<i class="x">X</i>`)
        this._combo_hits    = $(`<i class="hit">Hits</i>`)
        this._combo_txt     = $(`<i class="combo">C-C-C-COMBO</i>`)
        return this.wrap(buildTime(this))
    }

    afterRender(chat=null){
        this._combo.append(this._combo_count, ' ', this._combo_x, ' ', this._combo_hits, ' ', this._combo_txt)
        this.ui.append(this._text, this._combo)
    }

    incEmoteCount(){
        ++this.emotecount
        ChatEmoteMessageCountThrottle(this)
    }

    completeCombo(){
        ChatEmoteMessageCount(this)
        this._combo.attr('class', this._combo.attr('class') + ' combo-complete')
        this._combo = this._combo_count = this._combo_x = this._combo_hits = this._combo_txt = null
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