/* global $, destiny */

import ChatFormatter from './formatter.js';
import UserFeatures from '../features.js';

class EmoteFormatter extends ChatFormatter {

    constructor(chat){
        super(chat);
        const emoticons = [...chat.emoticons].join('|');
        const twitchemotes = [...chat.twitchemotes].join('|');
        this.emoteregex = new RegExp(`(^|\\s)(${emoticons})(?=$|\\s)`);
        this.gemoteregex = new RegExp(`(^|\\s)(${emoticons})(?=$|\\s)`, 'gm');
        this.twitchemoteregex = new RegExp(`(^|\\s)(${emoticons}|${twitchemotes})(?=$|\\s)`, 'gm');
    }

    format(str, user){
        const regex = (user && user.features.length > 0) ? ((user.hasFeature(UserFeatures.SUBSCRIBERT0)) ? this.twitchemoteregex : this.gemoteregex) : this.emoteregex;
        return str.replace(regex, '$1<div title="$2" class="chat-emote chat-emote-$2">$2 </div>');
    }

}

export default EmoteFormatter;