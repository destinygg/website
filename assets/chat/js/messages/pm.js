/* global $, destiny */

import ChatUserMessage from './user.js';

class ChatUserPrivateMessage extends ChatUserMessage {

    constructor(data, user, messageid, timestamp){
        super(data, user, timestamp, 'user');
        this.user = user;
        this.messageid = messageid;
        this.prepareMessage();
        this.isSlashMe = false; // make sure a private message is never reformatted to /me
    }

    wrap(html, css){
        return '' +
        '<div class="'+this.type+'-msg'+((css) ? ' '+css:'')+' private-message" data-messageid="'+this.messageid+'" data-username="'+this.user.username.toLowerCase()+'">'+
            html+
            '<span class="message-actions">'+
            '<a href="#" class="mark-as-read">Mark as read <i class="fa fa-check-square-o"></i></a>'+
            '</span>'+
            '<i class="speech-arrow"></i>'+
        '</div>';
    }

    wrapUser(user){
        return ` <i class="icon-mail-send" title="Received Message"></i> <a class="user">${user.username}</a>`;
    }

}

export default ChatUserPrivateMessage;