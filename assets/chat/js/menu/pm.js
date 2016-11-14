/* global $ */

import ChatMenu from './menu.js';

class ChatPmMenu extends ChatMenu {

    constructor(ui, chat){
        super(ui, chat);
        this.ui.on('click', '#user-list-link', () => {
            ChatMenu.closeMenus(this.chat);
            this.chat.menus.get('users').show(this.btn);
        });
        this.ui.on('click', '#markread-privmsg', () => {
            this.chat.setUnreadMessageCount(0);
            ChatMenu.closeMenus(this.chat);
            $.ajax({
                type: 'POST',
                url: '/profile/messages/openall'
            });
        });
        this.ui.on('click', '#inbox-privmsg', () => {
            this.chat.setUnreadMessageCount(0);
            ChatMenu.closeMenus(this.chat);
        });
    }

}

export default ChatPmMenu;