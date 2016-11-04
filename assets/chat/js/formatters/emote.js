/* global $, destiny */

import ChatFormatter from '../formatter.js';

class EmoteFormatter extends ChatFormatter {

    constructor(chat){
        super(chat);
        this.emoteregex = new RegExp('(^|\\s)('+chat.emoticons.join('|')+')(?=$|\\s)');
        this.gemoteregex = new RegExp('(^|\\s)('+chat.emoticons.join('|')+')(?=$|\\s)', 'gm');
        this.twitchemoteregex = new RegExp('(^|\\s)('+chat.emoticons.join('|')+'|'+chat.twitchemotes.join('|')+')(?=$|\\s)', 'gm');
    }

    format(str, user){
        let emoteregex = this.emoteregex;
        if (user && user.features.length > 0) {
            if (user.hasFeature(destiny.UserFeatures.SUBSCRIBERT0))
                emoteregex = this.twitchemoteregex;
            else
                emoteregex = this.gemoteregex;
        }
        return str.replace(emoteregex, '$1<div title="$2" class="chat-emote chat-emote-$2">$2 </div>');
    }

}

export default EmoteFormatter;