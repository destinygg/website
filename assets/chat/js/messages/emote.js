/* global $, destiny */

import ChatMessage from './message.js';

class ChatEmoteMessage extends ChatMessage {

    constructor(emote, timestamp, count=1){
        super(emote, timestamp, 'emote');
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

export default ChatEmoteMessage;