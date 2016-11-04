/* global $, destiny */

import ChatFormatter from '../formatter.js';

class MentionedUserFormatter extends ChatFormatter {

    constructor(chat){
        super(chat);
        this.users = chat.engine.users;
        this.userregex = /((?:^|\s)@?)([a-zA-Z0-9_]{3,20})(?=$|\s|[\.\?!,])/g;
    }

    format(str, user){
        const users = this.users;
        return str.replace(this.userregex, function(match, prefix, nick) {
            if (users.propertyIsEnumerable(nick)) {
                return `${prefix}<span class="chat-user">${nick}</span>`;
            } else {
                return match;
            }
        });
    }

}

export default MentionedUserFormatter;