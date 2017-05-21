/* global $ */

import ChatScrollPlugin from './scroll.js';
import UserFeatures from './features.js';
import EventEmitter from './emitter.js';

class ChatMenu extends EventEmitter {

    constructor(ui, chat){
        super();
        this.ui      = $(ui);
        this.chat    = chat;
        this.btn     = null;
        this.visible = false;
        this.shown   = false;
        this.ui.find('.scrollable').get().forEach(el => this.scrollplugin = new ChatScrollPlugin(el));
        this.ui.on('click', '.close', this.hide.bind(this));
    }

    show(btn){
        if(!this.visible){
            this.visible = true;
            this.btn = $(btn);
            this.shown = true;
            this.btn.addClass('active');
            this.ui.addClass('active');
            this.redraw();
            this.emit('show');
        }
    }

    hide(){
        if(this.visible){
            this.visible = false;
            this.btn.removeClass('active');
            this.ui.removeClass('active');
            this.emit('hide');
        }
    }

    toggle(btn){
        const wasVisible = this.visible;
        ChatMenu.closeMenus(this.chat);
        if(!wasVisible) this.show(btn);
    }

    redraw(){
        if(this.scrollplugin) this.scrollplugin.reset();
    }

    static closeMenus(chat){
        chat.menus.forEach(m => m.hide());
    }

}

class ChatSettingsMenu extends ChatMenu {

    constructor(ui, chat) {
        super(ui, chat);
        this.notificationEl = this.ui.find('#chat-settings-notification-permissions');
        this.customHighlightEl = this.ui.find('input[name=customhighlight]');
        this.allowNotificationsEl = this.ui.find('input[name="allowNotifications"]');
        this.customHighlightEl.on('keypress blur', e => {
            if (e.which && e.which !== 13) return; // not enter
            let data = $(e.target).val().toString().split(',').map(s => s.trim());
            this.chat.settings.set('customhighlight', [...new Set(data)]);
            this.chat.highlighter.loadHighlighters();
            this.chat.highlighter.redraw();
        });
        this.ui.on('change', 'input[type="checkbox"]', e => {
            let name    = $(e.target).attr('name'),
                checked = $(e.target).is(':checked');
            switch(name){
                case 'showtime':
                    this.updateSetting(name, checked);
                    break;
                case 'hideflairicons':
                    this.updateSetting(name, checked);
                    break;
                case 'highlight':
                    this.updateSetting(name, checked);
                    break;
                case 'allowNotifications':
                    if(checked){
                        this.notificationPermission().then(
                            p => this.updateSetting(name, true),
                            p => this.updateSetting(name, false)
                        );
                    } else {
                        this.updateSetting(name, false);
                    }
                    break;
            }
        });
    }

    show(btn){
        super.show(btn);
        Object.keys(this.chat.settings).forEach(key => {
            this.ui.find(`input[name=${key}][type="checkbox"]`).prop('checked', this.chat.settings.get(key));
        });
        if(Notification.permission !== 'granted')
            this.allowNotificationsEl.prop('checked', false);
        this.customHighlightEl.val( this.chat.settings.get('customhighlight').join(',') );
        this.updateNotification();
    }

    updateSetting(name, value){
        this.updateNotification();
        this.chat.settings.set(name, value);
        ChatStore.write('chat.settings', this.chat.settings);
        this.chat.updateSettingsCss();
        this.chat.scrollplugin.updateAndPin(this.chat.scrollplugin.isPinned());
    }

    updateNotification(){
        const perm = Notification.permission === 'default' ? 'required' : Notification.permission;
        this.notificationEl.text(`(Permission ${perm})`);
    }

    notificationPermission(){
        return new Promise((resolve, reject) => {
            switch(Notification.permission) {
                case 'default':
                    Notification.requestPermission(permission => {
                        switch(permission) {
                            case 'granted':
                                resolve(permission);
                                break;
                            default:
                                reject(permission);
                        }
                    });
                    break;
                case 'granted':
                    resolve(Notification.permission);
                    break;
                case 'denied':
                default:
                    reject(Notification.permission);
                    break;
            }
        });
    }
}

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

class ChatEmoteMenu extends ChatMenu {

    constructor(ui, chat) {
        super(ui, chat);
        this.input = $(this.chat.input);
        this.temotes = $('#twitch-emotes');
        this.demotes = $('#destiny-emotes');
        this.demotes.append([...this.chat.emoticons].map(emote => ChatEmoteMenu.buildEmote(emote)).join(''));
        this.temotes.append([...this.chat.twitchemotes].map(emote => ChatEmoteMenu.buildEmote(emote)).join(''));
        this.ui.on('click', '.chat-emote', e => this.selectEmote(e.target.innerText));
    }

    selectEmote(emote){
        let value = this.input.val().toString().trim();
        this.input.val(value + (value === '' ? '':' ') +  emote + ' ').focus();
    }

    static buildEmote(emote){
        return `<div class="emote"><span title="${emote}" class="chat-emote chat-emote-${emote}">${emote}</span></div>`
    }

}

export {
    ChatMenu,
    ChatSettingsMenu,
    ChatUserMenu,
    ChatPmMenu,
    ChatEmoteMenu
};