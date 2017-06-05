import ChatStore from './store';

function upgradeSettings(chat, oldversion, newversion){
    // DGG 1.x -> 2.x
    if(oldversion <= 0){
        let arr = ChatStore.read('chatoptions');
        if(arr){
            Object.keys(arr).forEach(k => {
                switch (k) {
                    case 'highlightnicks':
                        chat.settings.set('highlightnicks', Object.keys(arr[k]));
                        break;
                    default:
                        chat.settings.set(k, arr[k]);
                        break;
                }
            });
        }
        arr = ChatStore.read('chatignorelist');
        if(arr) chat.settings.set('ignorenicks', Object.keys(arr) || []);

        arr = ChatStore.read('chat.ignoring');
        if(arr) chat.settings.set('ignorenicks', arr);

        arr = ChatStore.read('inputhistory');
        if(arr) ChatStore.write('chat.history', arr);

        arr = chat.settings.get('allowNotifications');
        if(arr !== undefined && arr !== null){
            chat.settings.set('notificationwhisper', arr);
            chat.settings.set('notificationhighlight', arr);
            chat.settings.delete('allowNotifications');
        }

        arr = chat.settings.get('notificationtimeout');
        chat.settings.set('notificationtimeout', arr !== -1);

        arr = null;
        ChatStore.remove('chatoptions');
        ChatStore.remove('inputhistory');
        ChatStore.remove('chatignorelist');
        ChatStore.remove('hiddenhints');
        ChatStore.remove('lasthinttime');
        ChatStore.remove('unreadMessageCount');
        ChatStore.remove('chat.shownhints');
        ChatStore.remove('chat.ignoring');
        chat.saveSettings();
    }
}

export default {
    upgrade: upgradeSettings
};