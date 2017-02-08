/* global $, document */

import ChatMenu from './menu.js';
import ChatStore from '../store.js';

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
        this.chat.scrollPlugin.updateAndPin(this.chat.scrollPlugin.isPinned());
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

export default ChatSettingsMenu;