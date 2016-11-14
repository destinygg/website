/* global $, destiny */

import ChatUserMessage from './user.js';

class ChatUserPrivateMessage extends ChatUserMessage {

    constructor(data, user, messageid, timestamp){
        super(data, user, timestamp, 'user');
        this.user = user;
        this.messageid = messageid;
        this.isSlashMe = false; // make sure a private message is never reformatted to /me
    }

    attach(chat){
        super.attach(chat);
        return this.ui.on('click', '.mark-as-read', e => {
            $.ajax({
                type: 'POST',
                url : `/profile/messages/${encodeURIComponent(this.messageid)}/open`
            }).then(data => this.chat.setUnreadMessageCount(data['unread'] || 0));
            this.ui.find('.icon-mail-send').attr('class', 'icon-mail-open-document');
            this.ui.find('.message-actions').remove();
            e.preventDefault();
            e.stopPropagation();
        });
    }

    wrapUser(user){
        return ` <i class="icon-mail-send" title="Received Message"></i> <a class="user">${user.username}</a>`;
    }


    html(){
        const classes = [], args = {};
        classes.push('private-message');
        args['data-messageid'] = this.messageid;
        args['data-username'] = this.user.username.toLowerCase();
        return this.wrap(
            this.wrapTime() + ` <a class="user">${this.user.username}</a> ` + this.wrapMessage() +
            '<span class="message-actions">'+
                '<a href="#" class="mark-as-read">Mark as read <i class="fa fa-check-square-o"></i></a>'+
            '</span>'+
            '<i class="speech-arrow"></i>', classes, args
        );
    }

}

export default ChatUserPrivateMessage;