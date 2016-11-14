/* global $ */

import ChatMenu from './menu.js';
import UserFeatures from '../features.js';

class ChatUserMenu extends ChatMenu {

    constructor(ui, chat){
        super(ui, chat);
        this.header   = this.ui.find('h5 span');
        this.groupsEl = $('#chat-groups');
        this.group1   = $('<ul id="chat-group1">');
        this.group2   = $('<ul id="chat-group2">');
        this.group3   = $('<ul id="chat-group3">');
        this.group4   = $('<ul id="chat-group4">');
        this.group5   = $('<ul id="chat-group5">');
        this.groups   = [this.group1,this.group2,this.group3,this.group4,this.group5];
        this.groupsEl.on('click', '.user', e => this.chat.userfocus.toggleFocus(e.target.textContent));
        this.chat.source.on('JOIN', data => this.addAndRedraw(data.nick));
        this.chat.source.on('QUIT', data => this.removeAndRedraw(data.nick));
        this.chat.source.on('NAMES', data => this.redraw());
    }

    redraw(){
        if(this.visible){
            this.groups.forEach(e => e.detach().children('li').remove());
            this.chat.users.forEach(({username}) => this.addUser(username));
            this.sort();
            this.groupsEl.append(this.groups);
            this.header.text(this.chat.users.size);
        }
        super.redraw();
    }

    addAndRedraw(username){
        if(this.visible && !this.hasUser(username)){
            this.addUser(username);
            this.sort();
            this.redraw();
        }
    }

    removeAndRedraw(username){
        if(this.visible && this.hasUser(username)){
            this.removeUser(username);
            this.redraw();
        }
    }

    removeUser(username){
        return this.groupsEl.find('.user[data-username="'+username+'"]').parent().remove();
    }

    addUser(username){
        const user = this.chat.users.get(username),
              elem = `<li><a data-username="${user.username}" class="user ${user.features.join(' ')}">${user.username}</a></li>`;
        if(user.hasFeature(UserFeatures.BOT) || user.hasFeature(UserFeatures.BOT2))
            this.group5.append(elem);
        else if (user.hasFeature(UserFeatures.ADMIN) || user.hasFeature(UserFeatures.VIP))
            this.group1.append(elem);
        else if(user.hasFeature(UserFeatures.BROADCASTER))
            this.group2.append(elem);
        else if(user.hasFeature(UserFeatures.SUBSCRIBER))
            this.group3.append(elem);
        else
            this.group4.append(elem);
    }

    hasUser(username){
        return this.groupsEl.find('.user[data-username="'+username+'"]').length > 0;
    }

    sort(){
        this.groups.forEach(e => {
            e.children('.user').get()
            .sort((a, b) => a.getAttribute('data-username').localeCompare(b.getAttribute('data-username')))
            .forEach(a => a.parentNode.appendChild(a))
        });
    }

}

export default ChatUserMenu;