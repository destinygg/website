/* global $ */

import ChatScrollPlugin from './scroll.js';
import UserFeatures from './features.js';
import EventEmitter from './emitter.js';
import moment from 'moment';
import debounce from 'debounce';

class ChatMenu extends EventEmitter {

    constructor(ui, btn, chat){
        super();
        this.ui      = ui;
        this.btn     = btn;
        this.chat    = chat;
        this.visible = false;
        this.shown   = false;
        this.ui.find('.scrollable').get().forEach(el => this.scrollplugin = new ChatScrollPlugin(el));
        this.ui.on('click', '.close,.menu-close', this.hide.bind(this));
        this.btn.on('click', e => this.toggle());
    }

    show(){
        if(!this.visible){
            this.visible = true;
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

    toggle(){
        const wasVisible = this.visible;
        ChatMenu.closeMenus(this.chat);
        if(!wasVisible) this.show();
    }

    redraw(){
        if(this.visible && this.scrollplugin) this.scrollplugin.reset();
    }

    static closeMenus(chat){
        chat.menus.forEach(m => m.hide());
    }

}

class ChatSettingsMenu extends ChatMenu {

    constructor(ui, btn, chat) {
        super(ui, btn, chat);
        this.notificationEl = this.ui.find('#chat-settings-notification-permissions');
        this.customHighlightEl = this.ui.find('input[name=customhighlight]');
        this.allowNotificationsEl = this.ui.find('input[name="allowNotifications"]');
        this.customHighlightEl.on('keypress blur', e => this.onCustomHighlightChange(e));
        this.ui.on('change', 'input[type="checkbox"]', e => this.onSettingsChange(e));
    }

    onCustomHighlightChange(e){
        if (e.which && e.which !== 13) return; // not Enter
        let data = $(e.target).val().toString().split(',').map(s => s.trim());
        this.chat.settings.set('customhighlight', [...new Set(data)]);
        this.chat.commitSettings();
    }

    onSettingsChange(e){
        let name = $(e.target).attr('name'),
         checked = $(e.target).is(':checked');
        switch(name){
            case 'showremoved':
            case 'showtime':
            case 'hideflairicons':
            case 'highlight':
            case 'showhispersinchat':
            case 'focusmentioned':
                this.chat.settings.set(name, checked);
                break;
            case 'profilesettings':
                if(!checked && this.chat.authenticated) {
                    $.ajax({url: '/chat/settings', method:'delete'});
                }
                this.chat.settings.set(name, checked);
                break;
            case 'allowNotifications':
                if(checked){
                    this.notificationPermission().then(
                        p => this.chat.settings.set(name, true),
                        p => this.chat.settings.set(name, false)
                    );
                } else {
                    this.chat.settings.set(name, false);
                }
                break;
        }
        this.updateNotification();
        this.chat.applySettings(false);
        this.chat.commitSettings();
    }

    show(){
        if(!this.visible){
            [...this.chat.settings].forEach(a => this.ui.find(`input[name=${a[0]}][type="checkbox"]`).prop('checked', this.chat.settings.get(a[0])));
            if(Notification.permission !== 'granted')
                this.allowNotificationsEl.prop('checked', false);
            this.customHighlightEl.val( this.chat.settings.get('customhighlight').join(',') );
            this.updateNotification();
        }
        super.show();
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

    constructor(ui, btn, chat){
        super(ui, btn, chat);
        this.header   = this.ui.find('h5 span');
        this.groupsEl = $('#chat-groups');
        this.group1   = $('<ul id="chat-group1">');
        this.group2   = $('<ul id="chat-group2">');
        this.group3   = $('<ul id="chat-group3">');
        this.group4   = $('<ul id="chat-group4">');
        this.group5   = $('<ul id="chat-group5">');
        this.groups   = [this.group1,this.group2,this.group3,this.group4,this.group5];
        this.groupsEl.on('click', 'li', e => this.chat.userfocus.toggleFocus(e.target.textContent));
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
        return this.groupsEl.find('li[data-username="'+username+'"]').parent().remove();
    }

    addUser(username){
        const user = this.chat.users.get(username);
        const label = !user.username || user.username === '' ? "You" : user.username;
        const elem = `<li data-username="${user.username}"><a class="user ${user.features.join(' ')}">${label}</a></li>`;
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
        return this.groupsEl.find('li[data-username="'+username+'"]').length > 0;
    }

    sort(){
        this.groups.forEach(e => {
            e.children('li').get()
                .sort((a, b) => a.getAttribute('data-username').localeCompare(b.getAttribute('data-username')))
                .forEach(a => a.parentNode.appendChild(a))
        });
    }

}

class ChatEmoteMenu extends ChatMenu {

    constructor(ui, btn, chat) {
        super(ui, btn, chat);
        this.input = $(this.chat.input);
        this.temotes = $('#twitch-emotes');
        this.demotes = $('#destiny-emotes');
        this.demotes.append([...this.chat.emoticons].map(emote => ChatEmoteMenu.buildEmote(emote)).join(''));
        this.temotes.append([...this.chat.twitchemotes].map(emote => ChatEmoteMenu.buildEmote(emote)).join(''));
        this.ui.on('click', '.chat-emote', e => this.selectEmote(e.target.innerText));
    }

    show() {
        if (!this.visible) {
            this.chat.input.focus();
        }
        super.show();
    }

    selectEmote(emote){
        let value = this.input.val().toString().trim();
        this.input.val(value + (value === '' ? '':' ') +  emote + ' ').focus();
    }

    static buildEmote(emote){
        return `<div class="emote"><span title="${emote}" class="chat-emote chat-emote-${emote}">${emote}</span></div>`
    }

}

class ChatWhisperUsers extends ChatMenu {

    constructor(ui, btn, chat){
        super(ui, btn, chat);
        this.unread = 0;
        this.empty = $(`<span class="empty">No new whispers :(</span>`);
        this.notif = $(`<span id="chat-whisper-notif"></span>`);
        this.btn.append(this.notif);
        this.usersEl = ui.find('ul:first');
        this.usersEl.on('click', '.user', e => this.selectConversation(e.target.getAttribute('data-username')));
        this.usersEl.on('click', '.remove', e => this.removeConversation(e.target.getAttribute('data-username')))
    }

    removeConversation(username){
        this.chat.whispers.delete(username);
        this.redraw();
    }

    selectConversation(username){
        ChatMenu.closeMenus(this.chat);
        this.chat.input.focus();
        const menu = this.chat.menus.get('whisper-messages');
        menu.username = username;
        menu.conv = this.chat.whispers.get(username.toLowerCase());
        menu.show();
        this.redraw();
    }

    updateNotification(){
        const wasunread = this.unread;
        this.unread = [...this.chat.whispers.entries()]
            .map(e => parseInt(e[1].unread))
            .reduce((a,b) => a+b, 0);
        if(wasunread < this.unread) {
            this.btn.addClass('pulse-once');
            setTimeout(() => this.btn.removeClass('pulse-once'), 2000);
        }
        this.notif.text(this.unread);
        this.notif.toggle(this.unread > 0);
        try{
            const t = window.parent.document.title.replace(/^\([0-9]+\) /, '');
            window.parent.document.title = this.unread > 0 ? `(${this.unread}) ${t}` : `${t}`;
        }catch(ignored){console.error(ignored)}

    }

    redraw(){
        this.updateNotification();
        if(this.visible){
            this.usersEl.empty();
            if(this.chat.whispers.size === 0) {
                this.usersEl.append(this.empty);
            } else {
                [...this.chat.whispers.entries()]
                .sort((a,b) => {
                    if(a[1].unread === 0){
                        return 1;
                    } else if(b[1].unread === 0){
                        return -1;
                    } else if(a[1] === b[1]){
                        return 0;
                    }
                })
                .forEach(e => this.addConversation(e[0], e[1].unread));
            }
        }
        super.redraw();
    }

    addConversation(nick, unread){
        this.usersEl.append(`
            <li class="conversation unread-${unread}">
                <a data-username="${nick}" title="Hide" class="fa fa-times remove"></a>
                <a data-username="${nick}" class="user">${nick} <span class="badge">${unread}</span></a>
            </li>
        `);
    }

}

class ChatWhisperMessages extends ChatMenu {

    constructor(ui, btn, chat) {
        super(ui, btn, chat);
        this.username = '';
        this.conv = null;
        this.title = ui.find('.toolbar span:first');
        this.el = ui.find('.content:first');
        this.loading = $(
            `<div>
                <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>
                <span>Loading...</span>
             </div>`
        );
    }

    error(msg){
        this.el.append(`<div><span class="label label-danger">${msg}</span></div>`);
        this.scrollplugin.updateAndPin(true);
    }

    show(){
        if(!this.visible){
            this.chat.ui.addClass('focus-user');
        }
        super.show();
    }

    hide(){
        if(this.visible){
            this.chat.ui.removeClass('focus-user');
        }
        super.hide();
    }

    fetchMessages(){
        return $.ajax({url: `/profile/messages/${encodeURIComponent(this.username)}/unread`});
    }

    redraw() {
        if (this.visible) {
            this.el.empty();
            this.conv.unread = 0;
            this.title.text(this.username);
            if(!this.conv.loaded) {
                this.conv.loaded = true;
                this.loading.appendTo(this.el);
                this.fetchMessages()
                    .always(() => this.loading.detach())
                    .done(d => {
                        d.reverse().forEach(e => this.conv.messages.push({data: e.message, timestamp: e.timestamp, nick: e.from}));
                        this.conv.messages.forEach(m => this.addMessage(m));
                    });
            } else {
                this.conv.messages.forEach(m => this.addMessage(m));
            }
        }
        super.redraw();
    }

    addMessage(data){
        let message = data.data;
        const t = moment.utc(data.timestamp).local();
        const label = t.format('MMMM Do YYYY, h:mm:ss a');
        const time = t.format(this.chat.settings.get('timestampformat'));
        const me = this.chat.user.nick.toLowerCase() === data.nick.toLowerCase();
        this.chat.formatters.forEach(formatter => message = formatter.format(message));
        this.el.append(`
            <div class="msg ${me ? 'me' : ''}" title="${label} from ${data.nick}">
                <div class="tri"></div>
                <time>${time}</time>
                <div class="text">${message}</div>
            </div>
        `);
        this.scrollplugin.updateAndPin(true);
    }

}

export {
    ChatMenu,
    ChatSettingsMenu,
    ChatUserMenu,
    ChatEmoteMenu,
    ChatWhisperUsers,
    ChatWhisperMessages
};