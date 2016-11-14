/* global $, destiny */

import ChatFormatter from './formatter.js';

class MentionedUserFormatter extends ChatFormatter {

    constructor(chat){
        super(chat);
        this.userregex = /((?:^|\s)@?)([a-zA-Z0-9_]{3,20})(?=$|\s|[\.\?!,])/g;
    }

    format(str, user){
        return str.replace(this.userregex, (match, prefix, nick) => this.chat.users.has(nick) ? `${prefix}<span class="chat-user">${nick}</span>` : match);
    }

}

export default MentionedUserFormatter;