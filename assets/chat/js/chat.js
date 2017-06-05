/* global $, window, document */

import {KEYCODES,getKeyCode,isKeyCode} from "./const";
import debounce from 'debounce';
import moment from 'moment';
import EventEmitter from './emitter';
import ChatSource from './source';
import ChatUser from './user';
import {MessageBuilder, MessageTypes, MessageGlobals} from './messages';
import {ChatMenu, ChatUserMenu, ChatWhisperUsers, ChatEmoteMenu, ChatSettingsMenu} from './menus';
import ChatAutoComplete from './autocomplete';
import ChatInputHistory from './history';
import ChatUserFocus from './focus';
import ChatStore from './store';
import UserFeatures from './features';
import Settings from './settings';
import ChatWindow from './window';

const nickmessageregex = /(?:(?:^|\s)@?)([a-zA-Z0-9_]{3,20})(?=$|\s|[\.\?!,])/g;
const nickregex = /^[a-zA-Z0-9_]{3,20}$/;
const tagcolors = [
    "green",
    "yellow",
    "orange",
    "red",
    "purple",
    "blue",
    "sky",
    "lime",
    "pink",
    "black"
];
const errorstrings = new Map([
    ['unknown', 'Unknown error, this usually indicates an internal problem :('],
    ['nopermission', 'You do not have the required permissions to use that'],
    ['protocolerror', 'Invalid or badly formatted'],
    ['needlogin', 'You have to be logged in to use that'],
    ['invalidmsg', 'The message was invalid'],
    ['throttled', 'Throttled! You were trying to send messages too fast'],
    ['duplicate', 'The message is identical to the last one you sent'],
    ['muted', 'You are muted (subscribing removes mutes). Check your profile for more information.'],
    ['submode', 'The channel is currently in subscriber only mode'],
    ['needbanreason', 'Providing a reason for the ban is mandatory'],
    ['banned', 'You have been banned (subscribing removes non-permanent bans). Check your profile for more information.'],
    ['privmsgbanned', 'Cannot send private messages while banned'],
    ['requiresocket', 'This chat requires WebSockets'],
    ['toomanyconnections', 'Only 5 concurrent connections allowed'],
    ['socketerror', 'Error contacting server'],
    ['privmsgaccounttooyoung', 'Your account is too recent to send private messages'],
    ['notfound', 'The user was not found'],
    ['notconnected', 'You have to be connected to use that']
]);
const hintstrings = new Map([
    ['slashhelp', 'Type in /help for more a list of commands, do advanced things like modify your scroll-back size'],
    ['tabcompletion', 'Use the tab key to auto-complete usernames and emotes (for user only completion prepend a @ or press shift)'],
    ['hoveremotes', 'Hovering your mouse over an emote will show you the emote code'],
    ['highlight', 'Chat messages containing your username will be highlighted'],
    ['notify', 'Use /msg <username> to send a private message to someone'],
    ['ignoreuser', 'Use /ignore <username> to hide messages from pesky chatters'],
    ['mutespermanent', 'Mutes are never persistent, don\'t worry it will pass!'],
    ['stalkmentionshint', 'Use the /stalk <nick> or /mentions <nick> to keep up to date'],
    ['tagshint', `Use the /tag <nick> <color> to highlight users you like. There are preset colors to choose from ${tagcolors.join(', ')}`],
    ['bigscreen', `Bigscreen! Did you know you can have the chat on the left or right side of the stream by clicking the swap icon in the top left?`]
]);
const settingsdefault = new Map([
    ['schemaversion', 1],
    ['showtime', false],
    ['hideflairicons', false],
    ['profilesettings', false],
    ['timestampformat', 'HH:mm'],
    ['maxlines', 250],
    ['notificationwhisper', false],
    ['notificationhighlight', false],
    ['highlight', true], // todo rename this to `highlightself` or something
    ['customhighlight', []],
    ['highlightnicks', []],
    ['taggednicks', []],
    ['showremoved', false],
    ['showhispersinchat', false],
    ['ignorenicks', []],
    ['focusmentioned', false],
    ['notificationtimeout', true],
    ['ignorementions', false],
    ['autocompletehelper', true]
]);
const commandsinfo = new Map([
    ['help', {
        description: 'Helpful information.'
    }],
    ['emotes', {
        description: 'A list of the chats emotes in text form.',
    }],
    ['me', {
        description: 'A normal message, but emotive.',
    }],
    ['message', {
        description: 'Whisper someone',
        alias: ['msg', 'whisper', 'w', 'tell', 't', 'notify']
    }],
    ['ignore', {
        description: 'No longer see user messages, without <nick> to list the nicks ignored'
    }],
    ['unignore', {
        description: 'Remove a user from your ignore list'
    }],
    ['highlight', {
        description: 'Highlights target nicks messages for easier visibility'
    }],
    ['unhighlight', {
        description: 'Unhighlight target nick'
    }],
    ['maxlines', {
        description: 'The maximum number of lines the chat will store'
    }],
    ['mute', {
        description: 'The users messages will be blocked from everyone.',
        admin: true
    }],
    ['unmute', {
        description: 'Unmute the user.',
        admin: true
    }],
    ['subonly', {
        description: 'Subscribers only',
        admin: true
    }],
    ['ban', {
        description: 'User will no longer be able to connect to the chat.',
        admin: true
    }],
    ['unban', {
        description: 'Unban a user',
        admin: true
    }],
    ['timestampformat', {
        description: 'Set the time format of the chat.'
    }],
    ['stalk', {
        description: 'Return a list of messages from <nick>',
        alias: ['s']
    }],
    ['mentions', {
        description: 'Return a list of messages where <nick> is mentioned',
        alias: ['m']
    }],
    ['tag', {
        description: 'Mark a users messages'
    }],
    ['untag', {
        description: 'No longer mark the users messages'
    }],
    ['close', {
        description: 'Exit the conversation'
    }]
]);
const banstruct = {
    id: 0,
    userid: 0,
    username: '',
    targetuserid: '',
    targetusername: '',
    ipaddress: '',
    reason: '',
    starttimestamp: '',
    endtimestamp: ''
};
const debounceFocus = debounce(chat => chat.input.focus(),10);
const extractHostname = url => {
    let hostname;
    if (url.indexOf("://") > -1) {
        hostname = url.split('/')[2];
    } else {
        hostname = url.split('/')[0];
    }
    hostname = hostname.split(':')[0];
    hostname = hostname.split('?')[0];
    return hostname;
};
const focusIfNothingSelected = chat => {
    if(window.getSelection().isCollapsed && !chat.input.is(':focus')) {
        debounceFocus(chat);
    }
};

class Chat {

    constructor(){
        this.uri             = '';
        this.ui              = null;
        this.css             = null;
        this.output          = null;
        this.input           = null;
        this.loginscrn       = null;
        this.loadingscrn     = null;
        this.popoutbtn       = null;
        this.reconnect       = true;
        this.connected       = false;
        this.lastmessage     = null;
        this.showmotd        = true;
        this.authenticated   = false;
        this.backlogloading  = false;
        this.unresolved      = [];
        this.emoticons       = new Set();
        this.twitchemotes    = new Set();
        this.control         = new EventEmitter(this);
        this.source          = new ChatSource();
        this.user            = new ChatUser();
        this.users           = new Map();
        this.whispers        = new Map();
        this.windows         = new Map();
        this.settings        = new Map([...settingsdefault]);
        this.autocomplete    = new ChatAutoComplete();
        this.menus           = new Map();
        this.taggednicks     = new Map();
        this.ignoring        = new Set();

        this.regexhighlightcustom = null;
        this.regexhighlightnicks = null;
        this.regexhighlightself = null;

        this.source.on('PING',             data => this.source.send('PONG', data));
        this.source.on('OPEN',             data => this.connected = true);
        this.source.on('REFRESH',          data => window.location.reload(false));
        this.source.on('CONNECTING',       data => MessageBuilder.status('Connecting...').into(this));
        this.source.on('DISPATCH',         data => this.onDISPATCH(data));
        this.source.on('CLOSE',            data => this.onCLOSE(data));
        this.source.on('NAMES',            data => this.onNAMES(data));
        this.source.on('QUIT',             data => this.onQUIT(data));
        this.source.on('MSG',              data => this.onMSG(data));
        this.source.on('MUTE',             data => this.onMUTE(data));
        this.source.on('UNMUTE',           data => this.onUNMUTE(data));
        this.source.on('BAN',              data => this.onBAN(data));
        this.source.on('UNBAN',            data => this.onUNBAN(data));
        this.source.on('ERR',              data => this.onERR(data));
        this.source.on('SUBONLY',          data => this.onSUBONLY(data));
        this.source.on('BROADCAST',        data => this.onBROADCAST(data));

        this.control.on('SEND',            data => this.cmdSEND(data));
        this.control.on('HINT',            data => this.cmdHINT(data));
        this.control.on('EMOTES',          data => this.cmdEMOTES(data));
        this.control.on('HELP',            data => this.cmdHELP(data));
        this.control.on('IGNORE',          data => this.cmdIGNORE(data));
        this.control.on('UNIGNORE',        data => this.cmdUNIGNORE(data));
        this.control.on('MUTE',            data => this.cmdMUTE(data));
        this.control.on('BAN',             data => this.cmdBAN(data, 'BAN'));
        this.control.on('IPBAN',           data => this.cmdBAN(data, 'IPBAN'));
        this.control.on('UNMUTE',          data => this.cmdUNBAN(data, 'UNMUTE'));
        this.control.on('UNBAN',           data => this.cmdUNBAN(data, 'UNBAN'));
        this.control.on('SUBONLY',         data => this.cmdSUBONLY(data, 'SUBONLY'));
        this.control.on('MAXLINES',        data => this.cmdMAXLINES(data, 'MAXLINES'));
        this.control.on('UNHIGHLIGHT',     data => this.cmdHIGHLIGHT(data, 'UNHIGHLIGHT'));
        this.control.on('HIGHLIGHT',       data => this.cmdHIGHLIGHT(data, 'HIGHLIGHT'));
        this.control.on('TIMESTAMPFORMAT', data => this.cmdTIMESTAMPFORMAT(data));
        this.control.on('BROADCAST',       data => this.cmdBROADCAST(data));
        this.control.on('CONNECT',         data => this.cmdCONNECT(data));
        this.control.on('TAG',             data => this.cmdTAG(data));
        this.control.on('UNTAG',           data => this.cmdUNTAG(data));
        this.control.on('BANINFO',         data => this.cmdBANINFO(data));

        this.source.on('PRIVMSGSENT',      data => this.onPRIVMSGSENT(data));
        this.source.on('PRIVMSG',          data => this.onPRIVMSG(data));

        this.control.on('MESSAGE',         data => this.cmdWHISPER(data));
        this.control.on('MSG',             data => this.cmdWHISPER(data));
        this.control.on('WHISPER',         data => this.cmdWHISPER(data));
        this.control.on('W',               data => this.cmdWHISPER(data));
        this.control.on('TELL',            data => this.cmdWHISPER(data));
        this.control.on('T',               data => this.cmdWHISPER(data));
        this.control.on('NOTIFY',          data => this.cmdWHISPER(data));

        this.control.on('MENTIONS',        data => this.cmdMENTIONS(data));
        this.control.on('M',               data => this.cmdMENTIONS(data));
        this.control.on('STALK',           data => this.cmdSTALK(data));
        this.control.on('S',               data => this.cmdSTALK(data));
    }

    withUserAndSettings(data){
        return this.withUser(data)
            .withSettings(data && data.hasOwnProperty('settings') ? new Map(data.settings) : new Map());
    }

    withUser(user){
        this.user = this.addUser(user || {nick: 'Anonymous'});
        this.authenticated = this.user !== null && this.user.username !== '' && this.user.username !== 'Anonymous';
        return this;
    }

    withSettings(settings){
        // If authed and #settings.profilesettings=true use #settings
        // Else use whats in LocalStorage#chat.settings
        let stored = this.authenticated && settings.get('profilesettings') ? settings : new Map(ChatStore.read('chat.settings') || []);
        // Loop through settings and apply any settings found in the #stored data
        if(stored.size > 0) {
            [...this.settings.keys()]
                .filter(k => stored.get(k) !== undefined && stored.get(k) !== null)
                .forEach(k => this.settings.set(k, stored.get(k)));
        }
        // Upgrade if schema is out of date
        const oldversion = parseInt(stored.get('schemaversion') || -1);
        const newversion = settingsdefault.get('schemaversion');
        if(oldversion !== -1 && newversion !== oldversion) {
            Settings.upgrade(this, oldversion, newversion);
        }

        this.taggednicks = new Map(this.settings.get('taggednicks'));
        this.ignoring = new Set(this.settings.get('ignorenicks'));
        return this;
    }

    withGui(){
        this.ui             = $('#chat');
        this.css            = $('#chat-styles')[0]['sheet'];
        this.output         = this.ui.find('#chat-output-frame');
        this.input          = this.ui.find('#chat-input-control');
        this.loginscrn      = this.ui.find('#chat-login-screen');
        this.loadingscrn    = this.ui.find('#chat-loading');
        this.popoutbtn      = this.ui.find('#chat-popout-btn');
        this.windowselect   = this.ui.find('#chat-windows-thumbnails');
        this.inputhistory   = new ChatInputHistory(this);
        this.userfocus      = new ChatUserFocus(this, this.css);
        this.mainwindow     = new ChatWindow('main').into(this);

        this.menus.set('settings',
            new ChatSettingsMenu(this.ui.find('#chat-settings'), this.ui.find('#chat-settings-btn'), this));
        this.menus.set('emotes',
            new ChatEmoteMenu(this.ui.find('#chat-emote-list'), this.ui.find('#chat-emoticon-btn'), this));
        this.menus.set('users',
            new ChatUserMenu(this.ui.find('#chat-user-list'), this.ui.find('#chat-users-btn'), this));
        this.menus.set('whisper-users',
            new ChatWhisperUsers(this.ui.find('#chat-whisper-users'), this.ui.find('#chat-whisper-btn'), this));

        commandsinfo.forEach((a, k) => {
            this.autocomplete.add(`/${k}`);
            (a['alias'] || []).forEach(k => this.autocomplete.add(`/${k}`));
        });
        this.emoticons.forEach(e => this.autocomplete.add(e, true));
        this.twitchemotes.forEach(e => this.autocomplete.add(e, true));
        this.autocomplete.bind(this);
        this.applySettings(false);

        // Chat input
        this.input.on('keypress', e => {
            if(isKeyCode(e, KEYCODES.ENTER) && !e.shiftKey && !e.ctrlKey) {
                e.preventDefault();
                e.stopPropagation();
                if(!this.authenticated) {
                    this.loginscrn.show();
                    this.input.blur();
                } else {
                    this.control.emit('SEND', this.input.val().toString().trim());
                    this.input.val('').focus();
                }
            }
        });

        // Chat focus / menu close when clicking on some areas
        this.output.on('mouseup', () => {
            ChatMenu.closeMenus(this);
            focusIfNothingSelected(this);
        });

        // ESC
        document.addEventListener('keydown', e => {
            if(isKeyCode(e, KEYCODES.ESC)) ChatMenu.closeMenus(this); // ESC key
        });

        // Visibility
        document.addEventListener('visibilitychange', debounce(() => {
            const visibility = document['visibilityState'] || 'visible';
            if(visibility === 'visible') {
                focusIfNothingSelected(this);
            } else {
                ChatMenu.closeMenus(this);
            }
        },100), true);

        // Resize
        let resizing = false;
        const onresizecomplete = debounce(() => {
            resizing = false;
            this.getActiveWindow().unlock();
            focusIfNothingSelected(this);
        }, 100);
        const onresize = () => {
            if(!resizing) {
                resizing = true;
                ChatMenu.closeMenus(this);
                this.getActiveWindow().lock();
            }
            onresizecomplete();
        };
        window.addEventListener('resize', onresize, false);

        // Chat window selectors
        this.windowselect.on('click', '.ctrl', e => {
            ChatMenu.closeMenus(this);
            const el = $(e.currentTarget);
            if(!el.hasClass('active')) {
                this.windowToFront(e.target.getAttribute('data-name').toLowerCase());
            } else {
                if(!el.hasClass('win-main')) {
                    if(!el.hasClass('select')) {
                        el.addClass('select')
                    } else {
                        this.removeWindow(e.target.getAttribute('data-name').toLowerCase());
                    }
                }
            }
            this.input.focus();
            return false;
        });

        // Censored
        this.output.on('click', '.censored', e => {
            $(e.currentTarget).removeClass('censored');
            return false;
        });

        // Login
        this.loginscrn.on('click', '#chat-btn-login', () => {
            const uri = location.protocol+'//'+location.hostname+(location.port ? ':'+location.port: '');
            try {
                if(window.self !== window.top){
                    window.parent.location.href = uri + '/login?follow=' + encodeURIComponent(window.parent.location.pathname);
                    return;
                }
            } catch(ignored){}
            window.location.href = uri + '/login?follow=' + encodeURIComponent(window.location.pathname);
            this.loginscrn.hide();
        });
        this.loginscrn.on('click', '#chat-btn-cancel', () => this.loginscrn.hide());

        // Whisper
        this.output.on('click', '.chat-open-whisper', e => {
            const msg = $(e.target).closest('.msg-chat'),
                normalized = msg.data('username').toString().toLowerCase();
            if(this.whispers.has(normalized)) {
                this.menus.get('whisper-users').selectConversation(normalized);
            }
            return false;
        });
        this.output.on('click', '.chat-remove-whisper', e => {
            this.mainwindow.lock();
            const msg  = $(e.target).closest('.msg-chat'),
            normalized = msg.data('username').toString().toLowerCase(),
                    id = msg.data('id');
            if(id !== null && this.whispers.has(normalized)) {
                const conv = this.whispers.get(normalized);
                const result = conv.messages.filter(m => m.id === id);
                if(result.length > 0) {
                    $.ajax({url: `/api/messages/${encodeURIComponent(id)}/open`, method:'get'});
                    msg.remove();
                    this.menus.get('whisper-users').redraw();
                }
            }
            this.mainwindow.unlock();
            return false;
        });

        // If we are in an iframe from a different host, add the popout button.
        try {
            const referrer = extractHostname(document.referrer);
            if(window.self !== window.top && referrer.localeCompare(window.location.hostname) !== 0) {
                this.popoutbtn.on('click', () => {
                    window.open('/embed/chat', '_blank', `height=500,width=420,scrollbars=0,toolbar=0,location=0,status:no,menubar:0,resizable:0,dependent:0`);
                    MessageBuilder.info('Disconnecting... Good bye.').into(this);
                    this.reconnect = false;
                    this.source.disconnect();
                    return false;
                }).show();
            } else {
                this.popoutbtn.remove();
            }
        } catch (e) {
            this.popoutbtn.remove();
            console.error(e);
        }

        // Keep the website session alive.
        setInterval(() => $.ajax({url: '/ping'}), 10*60*1000);

        MessageBuilder.broadcast('SOmeone has subscribed.').into(this);

        this.loadingscrn.fadeOut(500, () => this.loadingscrn.remove());
        this.mainwindow.updateAndPin();
        this.input.focus();
        return this;
    }

    withEmotes(emotes) {
        this.emoticons = new Set(emotes['destiny']);
        this.twitchemotes = new Set(emotes['twitch']);
        return this;
    }

    withHistory(history) {
        if(history && history.length > 0) {
            this.backlogloading = true;
            history.forEach(line => this.source.parseAndDispatch({data: line}));
            this.backlogloading = false;
            MessageBuilder.element('<hr/>').into(this);
            this.mainwindow.updateAndPin();
        }
        return this;
    }

    withWhispers(){
        if(this.authenticated) {
            $.ajax({url: '/api/messages/inbox'})
                .done(d => d.forEach(e => this.whispers.set(e.username.toLowerCase(), {
                    id: e['messageid'],
                    nick: e['username'],
                    unread: e['unread'],
                    messages: [],
                    loaded: false
                })))
                .always(e => this.menus.get('whisper-users').redraw());
        }
        return this;
    }

    connect(uri) {
        this.uri = uri;
        this.source.connect(uri);
        return this;
    }

    saveSettings(){
        if(this.authenticated){
            if(this.settings.get('profilesettings')) {
                $.ajax({url: '/api/chat/me/settings', method:'post', data: JSON.stringify([...this.settings])});
            } else {
                ChatStore.write('chat.settings', this.settings);
            }
        } else {
            ChatStore.write('chat.settings', this.settings);
        }
    }

    // De-bounced saveSettings
    commitSettings(){
        if(!this.debouncedsave) {
            this.debouncedsave = debounce(() => this.saveSettings(), 1000, false);
        }
        this.debouncedsave();
    }

    // Save settings if save=true then apply current settings to chat
    applySettings(save=true){
        if(save) this.saveSettings();

        // Formats
        MessageGlobals.timeformat = this.settings.get('timestampformat');

        // Ignore Regex
        const ignores = Array.from(this.ignoring.values()).map(Chat.makeSafeForRegex);
        this.ignoreregex = ignores.length > 0 ? new RegExp(`\\b(?:${ignores.join('|')})\\b`, 'i') : null;

        // Highlight Regex
        const cust = [...(this.settings.get('customhighlight') || [])].filter(a => a !== '');
        const nicks = [...(this.settings.get('highlightnicks') || [])].filter(a => a !== '');
        this.regexhighlightself = this.user.nick ? new RegExp(`\\b(?:${this.user.nick})\\b`, 'i') : null;
        this.regexhighlightcustom = cust.length > 0 ? new RegExp(`\\b(?:${cust.join('|')})\\b`, 'i') : null;
        this.regexhighlightnicks = nicks.length > 0 ? new RegExp(`\\b(?:${nicks.join('|')})\\b`, 'i') : null;

        // Settings Css
        Array.from(this.settings.keys())
            .filter(key => typeof this.settings.get(key) === 'boolean')
            .forEach(key => this.ui.toggleClass(`pref-${key}`, this.settings.get(key)));

        // Update maxlines
        [...this.windows].forEach(w => w.maxlines = this.settings.get('maxlines'));
    }

    addUser(data){
        if(!data)
            return null;
        const normalized = data.nick.toLowerCase();
        let user = this.users.get(normalized);
        if (!user) {
            user = new ChatUser(data);
            this.users.set(normalized, user);
        } else if (data.hasOwnProperty('features') && !Chat.isArraysEqual(data.features, user.features)) {
            user.features = data.features;
        }
        return user;
    }

    addMessage(message, win=null){
        // Dont add the gui if user is ignored
        if (message.type === MessageTypes.USER && this.ignored(message.user.nick, message.message))
            return;

        if(win === null) {
            win = this.mainwindow;
        }

        win.lock();

        // Break the current combo if this message is not an emote
        // We dont need to check what type the current message is, we just know that its a new message, so the combo is invalid.
        if(this.lastmessage && this.lastmessage.type === MessageTypes.EMOTE && this.lastmessage.emotecount > 1)
            this.lastmessage.completeCombo();

        if(this.lastmessage && this.lastmessage.type === message.type && [MessageTypes.ERROR,MessageTypes.INFO,MessageTypes.COMMAND,MessageTypes.STATUS].indexOf(message.type)){
            message.continued = true;
        }

        // Populate the tag, mentioned users and highlight for this $message.
        if(message.type === MessageTypes.USER){
            // strip off `/` if message starts with `//`
            message.message = message.message.substring(0, 2) === '//' ? message.message.substring(1) : message.message;
            // check if message is `/me `
            message.slashme = message.message.substring(0, 4).toLowerCase() === '/me ';
            // check if this is the current users message
            message.isown = message.user.username.toLowerCase() === this.user.username.toLowerCase();
            // check if the last message was from the same user
            message.continued = this.lastmessage && this.lastmessage.user && this.lastmessage.user.username.toLowerCase() === message.user.username.toLowerCase();
            // get mentions from message
            message.mentioned = Chat.extractNicks(message.message).filter(a => this.users.has(a.toLowerCase()));
            // set tagged state
            message.tag = this.taggednicks.get(message.user.nick.toLowerCase());
            // set highlighted state if this is not the current users message or a bot, as well as other highlight criteria
            message.highlighted = !message.isown && !message.user.hasFeature(UserFeatures.BOT) && (
                // Check current user nick against msg.message (if highlight setting is on)
                (this.regexhighlightself && this.settings.get('highlight') && this.regexhighlightself.test(message.message)) ||
                // Check /highlight nicks against msg.nick
                (this.regexhighlightnicks && this.regexhighlightnicks.test(message.user.username)) ||
                // Check custom highlight against msg.nick and msg.message
                (this.regexhighlightcustom && this.regexhighlightcustom.test(message.user.username + ' ' + message.message))
            );
        }

        // The point where we actually add the message dom
        message.ui = $(message.html(this));
        win.addline(message.ui);
        win.cleanup();

        // Show desktop notification
        if(!this.backlogloading && message.highlighted && this.settings.get('notificationhighlight') && !this.input.is(':focus')) {
            Chat.showNotification(`${message.user.username} said ...`, message.message, message.timestamp.valueOf(), this.settings.get('notificationtimeout'));
        }

        // Cache the last message for interrogation
        this.lastmessage = message;
        win.unlock(); // unlockdebounce
        return message;
    }

    resolveMessage(nick, str){
        for(const message of this.unresolved){
            if(this.user.username.toLowerCase() === nick.toLowerCase() && message.message === str){
                this.unresolved.splice(this.unresolved.indexOf(message), 1);
                return true;
            }
        }
        return false;
    }

    removeMessageByNick(nick){
        this.mainwindow.lock();
        this.mainwindow.removelines(`.msg-chat[data-username="${nick.toLowerCase()}"]`);
        this.mainwindow.unlock();
    }


    windowToFront(name){
        this.windows.forEach(win => {
            if(win.visible) {
                if(!win.locked()) win.lock();
                win.hide();
            }
        });
        const win = this.windows.get(name);
        win.show();
        if(win.locked()) win.unlock();
        this.redrawWindowIndicators();
    }

    getActiveWindow(){
        return [...this.windows.values()].filter(win => win.visible)[0];
    }

    getWindow(name){
        return this.windows.get(name);
    }

    addWindow(name, win){
        this.windows.set(name, win);
        this.redrawWindowIndicators();
    }

    removeWindow(name){
        const win = this.windows.get(name);
        if(win) {
            const visible = win.visible;
            this.windows.delete(name);
            win.destroy();
            if(visible) {
                const keys = [...this.windows.keys()];
                this.windowToFront(this.windows.get(keys[keys.length-1]).name);
            } else {
                this.redrawWindowIndicators();
            }
        }
    }

    redrawWindowIndicators(){
        this.windowselect.empty();
        if(this.windows.size > 1) {
            this.windows.forEach(w => {
                this.windowselect.append(
                    `<span title="${w.label}" data-name="${w.name}" class="ctrl win-${w.name} tag-${w.tag} ${w.visible ? 'active' : ''}">\
                        <i class="fa fa-times" data-name="${w.name}"></i>
                     </span>`
                )
            });
        }
    }


    censor(nick){
        this.mainwindow.lock();
        const c = this.mainwindow.getlines(`.msg-chat[data-username="${nick.toLowerCase()}"]`);
        if(this.settings.get('showremoved')) {
            c.addClass('censored');
        } else {
            c.remove();
        }
        this.mainwindow.unlock();
    }

    ignored(nick, text=null){
        return this.ignoring.has(nick.toLowerCase()) || (this.ignoreregex && text !== null && this.settings.get('ignorementions') && this.ignoreregex.test(text));
    }

    ignore(nick, ignore=true){
        nick = nick.toLowerCase();
        const exists = this.ignoring.has(nick);
        if(ignore && !exists){
            this.ignoring.add(nick);
        } else if(!ignore && exists) {
            this.ignoring.delete(nick);
        }
        this.settings.set('ignorenicks', [...this.ignoring]);
        this.applySettings();
    }


    onDISPATCH({data}){
        if (typeof data === 'object'){
            let users = [];
            const now = Date.now();
            if(data.hasOwnProperty('nick'))
                users.push(this.addUser(data));
            if(data.hasOwnProperty('users'))
                users = users.concat(data.users.map(d => this.addUser(d)));
            users.forEach(u => this.autocomplete.weight(u.nick, now));
        }
    }

    onCLOSE(){
        const wasconnected = this.connected;
        this.connected = false;
        if (this.reconnect){
            const rand = ((wasconnected) ? Math.floor(Math.random() * (3000 - 501 + 1)) + 501 : Math.floor(Math.random() * (30000 - 5000 + 1)) + 5000);
            setTimeout(() => {
                if(!this.connected) this.connect(this.uri)
            }, rand);
            MessageBuilder.status(`Disconnected... reconnecting in ${Math.round(rand/1000)} seconds`).into(this);
        }
    }

    onNAMES(data){
        MessageBuilder.status(`Connected. Server connections: ${data['connectioncount']}`).into(this);
        if(this.showmotd) {
            this.cmdHINT([Math.floor(Math.random() * hintstrings.size)]);
            this.showmotd = false;
        }
    }

    onQUIT(data){
        const normalized = data.nick.toLowerCase();
        if (this.users.has(normalized)){
            this.users.delete(normalized);
            this.autocomplete.remove(data.nick);
        }
    }

    onMSG(data){
        let textonly = Chat.extractTextOnly(data.data);
        const isemote = this.emoticons.has(textonly) || this.twitchemotes.has(textonly);
        if(isemote && this.lastmessage !== null && Chat.extractTextOnly(this.lastmessage.message) === textonly){
            if(this.lastmessage.type === MessageTypes.EMOTE) {
                this.mainwindow.lock();
                this.lastmessage.incEmoteCount();
                this.mainwindow.unlock();
            } else {
                this.lastmessage.ui.remove();
                MessageBuilder.emote(textonly, data.timestamp, 2).into(this);
            }
        } else if(!this.resolveMessage(data.nick, data.data)){
            MessageBuilder.message(data.data, this.users.get(data.nick.toLowerCase()), data.timestamp).into(this);
        }
    }

    onMUTE(data){
        // data.data is the nick which has been banned, no info about duration
        this.censor(data.data);
        if(this.user.username.toLowerCase() === data.data.toLowerCase()) {
            MessageBuilder.command(`You have been muted by ${data.nick}.`, data.timestamp).into(this);
        } else {
            MessageBuilder.command(`${data.data} muted by ${data.nick}.`, data.timestamp).into(this);
        }
    }

    onUNMUTE(data){
        if(this.user.username.toLowerCase() === data.data.toLowerCase()) {
            MessageBuilder.command(`You have been unmuted by ${data.nick}.`, data.timestamp).into(this);
        } else {
            MessageBuilder.command(`${data.data} unmuted by ${data.nick}.`, data.timestamp).into(this);
        }
    }

    onBAN(data){
        // data.data is the nick which has been banned, no info about duration
        if(this.user.username.toLowerCase() === data.data.toLowerCase()) {
            MessageBuilder.command(`You have been banned by ${data.nick}. Check your profile for more information.`, data.timestamp).into(this);
            this.cmdBANINFO();
        } else {
            MessageBuilder.command(`${data.data} banned by ${data.nick}.`, data.timestamp).into(this);
        }
        this.censor(data.data);
    }

    onUNBAN(data){
        if(this.user.username.toLowerCase() === data.data.toLowerCase()) {
            MessageBuilder.command(`You have been unbanned by ${data.nick}.`, data.timestamp).into(this);
        } else {
            MessageBuilder.command(`${data.data} unbanned by ${data.nick}.`, data.timestamp).into(this);
        }
    }

    onERR(data){
        this.reconnect = data !== 'toomanyconnections' && data !== 'banned';
        const errorString = errorstrings.has(data) ? errorstrings.get(data) : data;
        const front = this.getActiveWindow();
        MessageBuilder.error(errorString).into(this, front);
        if(front !== this.mainwindow) {
            MessageBuilder.error(errorString).into(this);
        }
    }

    onSUBONLY(data){
        const submode = data.data === 'on' ? 'enabled': 'disabled';
        MessageBuilder.command(`Subscriber only mode ${submode} by ${data.nick}`, data.timestamp).into(this);
    }

    onBROADCAST(data){
        MessageBuilder.broadcast(data.data, data.timestamp).into(this);
    }

    onPRIVMSGSENT(){
        if(this.mainwindow.visible) {
            MessageBuilder.info('Your message has been sent.').into(this);
        }
    }

    onPRIVMSG(data) {
        const normalized = data.nick.toLowerCase();
        if (!this.ignoring.has(normalized)){
            const user = this.users.get(normalized) || new ChatUser({nick: data.nick});
            const messageid = data.hasOwnProperty('messageid') ? data['messageid'] : null;
            const message = {data: data.data, timestamp: data.timestamp, read: false, nick: data.nick, id: messageid};
            const conv = this.whispers.get(normalized) || {nick:data.nick, unread:0, messages:[], loaded:true};
            this.whispers.set(normalized, conv);
            if(!message.read)
                conv.unread++;
            if(conv.loaded)
                conv.messages.push(message);
            const win = this.getWindow(normalized);
            if(win) {
                MessageBuilder.historical(data.data, user, data.timestamp).into(this, win);
            }
            if(this.settings.get('showhispersinchat')){
                MessageBuilder.whisper(data.data, user, this.user.username, data.timestamp, messageid).into(this);
            }
            if(this.settings.get('notificationwhisper') && !this.input.is(':focus')) {
                Chat.showNotification(`${data.nick} whispered ...`, data.data, data.timestamp, this.settings.get('notificationtimeout'));
            }
            this.menus.get('whisper-users').redraw();
        }
    }

    cmdSEND(str) {
        if(str !== ''){
            const isme = str.substring(0, 4).toLowerCase() === '/me ';
            const iscommand = str.substring(0, 1) === '/' && str.substring(0, 2) !== '//';
            const command = iscommand ? str.split(' ', 1)[0] : '';

            if(!this.mainwindow.visible) {
                const win = this.getActiveWindow();
                // If we have a /close command, and we
                if(/^\/close/i.test(command) && this.mainwindow !== win)
                {
                    this.removeWindow(win.name);
                    this.inputhistory.add(str);
                }
                else if(!isme && iscommand) {
                    MessageBuilder.error(`No commands in private channels yet.`).into(this, win);
                }
                else {
                    if(this.source.connected()) {
                        this.source.send('PRIVMSG', {nick: win.name, data: str});
                        MessageBuilder.message(str, this.user).into(this, win);
                    } else {
                        MessageBuilder.error(errorstrings.get('notconnected')).into(this, win);
                    }
                }
            }

            // Run a command e.g. /me
             else if (!isme && iscommand)
            {
                const parts = (str.substring(command.length+1) || '').match(/([^ ]+)/g);
                const normalized = command.substring(1).toUpperCase(); // remove the leading slash
                if(this.control.listeners.has(normalized)) {
                    this.control.emit(
                        normalized,
                        parts || []
                    );
                } else {
                    MessageBuilder.error(`Unknown command. Try /help`).into(this);
                }
                this.inputhistory.add(str);
            }

            // Normal chat message or emote
            else
            {
                const textonly = (isme ? str.substring(4) : str).trim(); // strip off /me and check if its an emote
                if (this.connected && !this.emoticons.has(textonly) && !this.twitchemotes.has(textonly)){
                    // Normal text message
                    // We add the message to the gui immediately
                    // But we will also get the MSG event, so we need to make sure we dont add the message to the gui again.
                    // We do this by storing the message in the unresolved array
                    // The onMSG then looks in the unresolved array for the message using the nick + message
                    // If found, the message is not added to the gui, its removed from the unresolved array and the message.resolve method is run on the message
                    const message = MessageBuilder.message(str, this.user).into(this);
                    this.unresolved.unshift(message);
                }
                this.source.send('MSG', {data: str});
                this.inputhistory.add(str);
            }
        }
    }

    cmdEMOTES(){
        MessageBuilder.info(`Available emoticons: ${this.emoticons.join(', ')} (www.destiny.gg/emotes)`).into(this);
    }

    cmdHELP(){
        let str = `Available commands: \r`;
        commandsinfo.forEach((a, k) => {
            str += ` /${k} - ${a.description} \r`;
        });
        MessageBuilder.info(str).into(this);
    }

    cmdHINT(parts){
        const arr = [...hintstrings];
        const i = parts && parts[0] ? parseInt(parts[0])-1 : -1;
        if(i > 0 && i < hintstrings.size){
            MessageBuilder.info(arr[i][1]).into(this);
        } else {
            if(this.lasthintindex === undefined || this.lasthintindex === arr.length - 1) {
                this.lasthintindex = 0;
            } else  {
                this.lasthintindex++;
            }
            MessageBuilder.info(arr[this.lasthintindex][1]).into(this);
        }
    }

    cmdIGNORE(parts){
        const username = parts[0] || null;
        if (!username) {
            if (this.ignoring.size <= 0) {
                MessageBuilder.info('Your ignore list is empty').into(this);
            } else {
                MessageBuilder.info(`Ignoring the following people: ${Array.from(this.ignoring.values()).join(', ')}`).into(this);
            }
        } else if (!nickregex.test(username)) {
            MessageBuilder.info('Invalid nick - /ignore <nick>').into(this);
        } else {
            this.ignore(username, true);
            this.removeMessageByNick(username);
            MessageBuilder.status(`Ignoring ${username}`).into(this);
        }
    }

    cmdUNIGNORE(parts){
        const username = parts[0] || null;
        if (!username || !nickregex.test(username)) {
            MessageBuilder.error('Invalid nick - /ignore <nick>').into(this);
        } else {
            this.ignore(username, false);
            MessageBuilder.status(`${username} has been removed from your ignore list`).into(this);
        }
    }

    cmdMUTE(parts){
        if (parts.length === 0) {
            MessageBuilder.info(`Usage: /mute <nick>[ <time>]`).into(this);
        } else if (!nickregex.test(parts[0])) {
            MessageBuilder.info(`Invalid nick - /mute <nick>[ <time>]`).into(this);
        } else {
            const duration = (parts[1]) ? Chat.parseTimeInterval(parts[1]) : null;
            if (duration && duration > 0){
                this.source.send('MUTE', {data: parts[0], duration: duration});
            } else {
                this.source.send('MUTE', {data: parts[0]});
            }
        }
    }

    cmdBAN(parts, command){
        if (parts.length === 0 || parts.length < 3) {
            MessageBuilder.info(`Usage: /${command} <nick> <time> <reason> (time can be 'permanent')`).into(this);
        } else if (!nickregex.test(parts[0])) {
            MessageBuilder.info('Invalid nick').into(this);
        } else if (!parts[2]) {
            MessageBuilder.error('Providing a reason is mandatory').into(this);
        } else {
            let payload = {
                nick   : parts[0],
                reason : parts.slice(2, parts.length).join(' ')
            };
            if(command === 'IPBAN' || /^perm/i.test(parts[1]))
                payload.ispermanent = (command === 'IPBAN' || /^perm/i.test(parts[1]));
            else
                payload.duration = Chat.parseTimeInterval(parts[1]);
            this.source.send('BAN', payload);
        }
    }

    cmdUNBAN(parts, command){
        if (parts.length === 0) {
            MessageBuilder.info(`Usage: /${command} nick`).into(this);
        } else if (!nickregex.test(parts[0])) {
            MessageBuilder.info('Invalid nick').into(this);
        } else {
            this.source.send(command, {data: parts[0]});
        }
    }

    cmdSUBONLY(parts, command){
        if (/on|off/i.test(parts[0])) {
            this.source.send(command.toUpperCase(), {data: parts[0].toLowerCase()});
        } else {
            MessageBuilder.error(`Invalid argument - /${command.toLowerCase()} on | off`).into(this);
        }
    }

    cmdMAXLINES(parts, command){
        if (parts.length === 0) {
            MessageBuilder.info(`Maximum lines stored: ${this.settings.get('maxlines')}`).into(this);
            return;
        }
        const newmaxlines = Math.abs(parseInt(parts[0], 10));
        if (!newmaxlines) {
            MessageBuilder.info(`Invalid argument - /${command} is expecting a number`).into(this);
        } else {
            MessageBuilder.info(`Current number of lines shown: ${this.settings.get('maxlines')}`).into(this);
            this.settings.set('maxlines', newmaxlines);
            this.applySettings();
        }
    }

    cmdHIGHLIGHT(parts, command){
        const highlights = this.settings.get('highlightnicks');
        if (parts.length === 0) {
            if (highlights.length > 0)
                MessageBuilder.info('Currently highlighted users: ' + highlights.join(',')).into(this);
            else
                MessageBuilder.info(`No highlighted users`).into(this);
            return;
        }
        if (!nickregex.test(parts[0])) {
            MessageBuilder.error(`Invalid nick - /${command} nick`).into(this);
        }
        const nick = parts[0].toLowerCase();
        const i = highlights.indexOf(nick);
        switch(command) {
            case 'UNHIGHLIGHT':
                if(i !== -1) highlights.splice(i, 1);
                break;
            default:
            case 'HIGHLIGHT':
                if(i === -1) highlights.push(nick);
                break;
        }
        MessageBuilder.info(command.toUpperCase() === 'HIGHLIGHT' ? `Highlighting ${nick}` : `No longer highlighting ${nick}}`).into(this);
        this.settings.set('highlightnicks', highlights);
        this.applySettings();
    }

    cmdTIMESTAMPFORMAT(parts){
        if (parts.length === 0) {
            MessageBuilder.info(`Current format: ${this.settings.get('timestampformat')} (the default is 'HH:mm', for more info: http://momentjs.com/docs/#/displaying/format/)`).into(this);
        } else {
            const format = parts.slice(1, parts.length);
            if ( !/^[a-z :.,-\\*]+$/i.test(format)) {
                MessageBuilder.error('Invalid format, see: http://momentjs.com/docs/#/displaying/format/').into(this);
            } else {
                MessageBuilder.info(`New format: ${this.settings.get('timestampformat')}`).into(this);
                this.settings.set('timestampformat', format);
                this.applySettings();
            }
        }
    }

    cmdBROADCAST(parts){
        this.source.send('BROADCAST', {data: parts.join(' ')});
    }

    cmdWHISPER(parts){
        if (!parts[0] || !nickregex.test(parts[0])) {
            MessageBuilder.error('Invalid nick - /msg nick message').into(this);
        } else if (parts[0].toLowerCase() === this.user.username.toLowerCase()) {
            MessageBuilder.error('Cannot send a message to yourself').into(this);
        } else {
            const data = parts.slice(1, parts.length).join(' ');
            this.source.send('PRIVMSG', {nick: parts[0], data: data});
        }
    }

    cmdCONNECT(parts){
        this.reconnect = false;
        this.uri = parts[0];
        this.source.disconnect();
        this.source.connect(this.uri);
    }

    cmdSTALK(parts){
        if (parts[0] && /^\d+$/.test(parts[0])){
            parts[1] = parts[0];
            parts[0] = this.user.username;
        }
        if (!parts[0] || !nickregex.test(parts[0].toLowerCase())) {
            MessageBuilder.error('Invalid nick - /stalk <nick> <limit>').into(this);
            return;
        }
        if(this.busystalk){
            MessageBuilder.error('Still busy stalking').into(this);
            return;
        }
        if(this.nextallowedstalk && this.nextallowedstalk.isAfter(new Date())){
            MessageBuilder.error(`Next allowed stalk ${this.nextallowedstalk.fromNow()}`).into(this);
            return;
        }
        this.busystalk = true;
        const limit = parts[1] ? parseInt(parts[1]) : 3;
        MessageBuilder.info(`Getting messages for ${[parts[0]]} ...`).into(this);
        $.ajax({timeout:5000, url: `/api/chat/stalk?username=${encodeURIComponent(parts[0])}&limit=${limit}`})
            .always(() => {
                this.nextallowedstalk = moment().add(10, 'seconds');
                this.busystalk = false;
            })
            .done(d => {
                if(d.lines.length === 0) {
                    MessageBuilder.info(`No messages for ${parts[0]}`).into(this);
                } else {
                    const date = moment.utc(d.lines[d.lines.length-1]['timestamp']*1000).local().format('MMMM Do YYYY, h:mm:ss a');
                    MessageBuilder.info(`Stalked ${parts[0]} last seen ${date}`).into(this);
                    d.lines.forEach(a => MessageBuilder.historical(a.text, new ChatUser({nick: d.nick}), a.timestamp*1000).into(this));
                    MessageBuilder.info(`End of stalk (https://dgg.overrustlelogs.net/${parts[0]})`).into(this);
                }
            })
            .fail(e => MessageBuilder.error(`No messages for ${parts[0]} received. Try again later`).into(this));
    }

    cmdMENTIONS(parts){
        if (parts[0] && /^\d+$/.test(parts[0])){
            parts[1] = parts[0];
            parts[0] = this.user.username;
        }
        if (!parts[0]) parts[0] = this.user.username;
        if (!parts[0] || !nickregex.test(parts[0].toLowerCase())) {
            MessageBuilder.error('Invalid nick - /mentions <nick> <limit>').into(this);
            return;
        }
        if(this.busymentions){
            MessageBuilder.error('Still busy getting mentions').into(this);
            return;
        }
        if(this.nextallowedmentions && this.nextallowedmentions.isAfter(new Date())){
            MessageBuilder.error(`Next allowed mentions ${this.nextallowedmentions.fromNow()}`).into(this);
            return;
        }
        this.busymentions = true;
        const limit = parts[1] ? parseInt(parts[1]) : 3;
        MessageBuilder.info(`Getting mentions for ${[parts[0]]} ...`).into(this);
        $.ajax({timeout:5000, url: `/api/chat/mentions?username=${encodeURIComponent(parts[0])}&limit=${limit}`})
            .always(() => {
                this.nextallowedmentions = moment().add(10, 'seconds');
                this.busymentions = false;
            })
            .done(d => {
                if(d.length === 0) {
                    MessageBuilder.info(`No mentions for ${parts[0]}`).into(this);
                } else {
                    const date = moment.utc(d[d.length-1].date*1000).local().format('MMMM Do YYYY, h:mm:ss a');
                    MessageBuilder.info(`Mentions for ${parts[0]} last seen ${date}`).into(this);
                    d.forEach(a => MessageBuilder.historical(a.text, new ChatUser({nick: a.nick}), a.date*1000).into(this));
                    MessageBuilder.info(`End of stalk (https://dgg.overrustlelogs.net/${parts[0]})`).into(this);
                }
            })
            .fail(e => MessageBuilder.error(`No mentions for ${parts[0]} received. Try again later`).into(this));
    }

    cmdTAG(parts){
        if (parts.length === 0){
            if(this.taggednicks.size > 0) {
                MessageBuilder.info(`Tagged nicks: ${[...this.taggednicks.keys()].join(',')}. Available colors: ${tagcolors.join(',')}`).into(this);
            } else {
                MessageBuilder.info(`No tagged nicks. Available colors: ${tagcolors.join(',')}`).into(this);
            }
            return;
        }
        const nicks = parts.filter(a => a !== null && a !== '' && nickregex.test(a));
        if(nicks.length === 0) {
            MessageBuilder.error('Invalid nick - /tag <nick> <color>').into(this);
            return;
        }
        nicks.forEach(u => {
            const n = u.toLowerCase();
            if(n === this.user.username.toLowerCase()){
                MessageBuilder.error('Cannot tag yourself').into(this);
                return;
            }
            const color = parts[1] && tagcolors.indexOf(parts[1]) !== -1 ? parts[1] : tagcolors[Math.floor(Math.random()*tagcolors.length)];
            this.mainwindow.getlines(`.msg-user[data-username="${n}"]`)
                .removeClass(Chat.removeClasses('msg-tagged'))
                .addClass(`msg-tagged msg-tagged-${color}`);
            this.taggednicks.set(n, color);
            MessageBuilder.info(`Tagged ${u} as ${color}`).into(this);
        });
        this.settings.set('taggednicks', [...this.taggednicks]);
        this.applySettings();
    }

    cmdUNTAG(parts){
        if (parts.length === 0){
            if(this.taggednicks.size > 0) {
                MessageBuilder.info(`Tagged nicks: ${[...this.taggednicks.keys()].join(',')}. Available colors: ${tagcolors.join(',')}`).into(this);
            } else {
                MessageBuilder.info(`No tagged nicks. Available colors: ${tagcolors.join(',')}`).into(this);
            }
            return;
        }
        const nicks = parts.filter(a => a !== null && a !== '' && nickregex.test(a));
        if(nicks.length === 0) {
            MessageBuilder.error('Invalid nick - /untag <nick>').into(this);
            return;
        }
        nicks.forEach(u => {
            const n = u.toLowerCase();
            this.taggednicks.delete(n);
            this.mainwindow.getlines(`.msg-chat[data-username="${n}"]`)
                .removeClass(Chat.removeClasses('msg-tagged'));
        });
        MessageBuilder.info(`Un-tagged ${nicks.join(',')}`).into(this);
        this.settings.set('taggednicks', [...this.taggednicks]);
        this.applySettings();
    }

    cmdBANINFO(){
        MessageBuilder.info('Loading ban info ...').into(this);
        $.ajax({url:`/api/chat/me/ban`})
            .done(d => {
                if(d === 'bannotfound') {
                    MessageBuilder.info(`You have no active bans. Thank you.`).into(this);
                    return;
                }
                const b = $.extend({}, banstruct, d);
                const by = b.username ? b.username : 'Chat';
                const start = moment(b.starttimestamp).format('MMMM Do YYYY, h:mm:ss a');
                if(!b.endtimestamp){
                    MessageBuilder.info(`Permanent ban by ${by} starting ${start}.`).into(this);
                } else {
                    const end = moment(b.endtimestamp).calendar();
                    MessageBuilder.info(`Temporary ban by ${by} starting ${start} and ending ${end}`).into(this);
                }
                if(b.reason){
                    const m = MessageBuilder.message(b.reason, new ChatUser({nick: b.username}, b.starttimestamp));
                    m.historical = true;
                    m.into(this);
                }
                MessageBuilder.info(`End of ban information`).into(this);
            })
            .fail(e => MessageBuilder.error('Error loading ban info. Check your profile.').into(this));
    }

    static extractTextOnly(msg){
        return (msg.toLowerCase().substring(0, 4) === '/me ' ? msg.substring(4) : msg).trim();
    }

    static extractNicks(text){
        let match, nicks = new Set();
        while (match = nickmessageregex.exec(text)) {
            nicks.add(match[1]);
        }
        return [...nicks];
    }

    static removeClasses(search){
        return function(i, c) {
            return (c.match(new RegExp(`\\b${search}(?:[A-z-]+)?\\b`, 'g')) || []).join(' ');
        }
    }

    static isArraysEqual(a, b){
        return (!a || !b) ? (a.length !== b.length || a.sort().toString() !== b.sort().toString()) : false;
    }

    static showNotification(title, message, timestamp, timeout=false){
        if(Notification.permission === 'granted'){
            const n = new Notification(title, {
                body : message,
                tag  : `dgg${timestamp}`,
                icon : '/notifyicon.png',
                dir  : 'auto'
            });
            n.onclick = function(){}; // todo open chat at specific line
            if(timeout) setTimeout(() => n.close(), 8000);
        }
    }

    static makeSafeForRegex(str){
        return str.trim().replace(/[\-\[\]\/{}()*+?.\\\^$|]/g, "\\$&");
    }

    static parseTimeInterval(str){
        let nanoseconds = 0,
            units = {
                s: 1000000000,
                sec: 1000000000, secs: 1000000000,
                second: 1000000000, seconds: 1000000000,

                m: 60000000000,
                min: 60000000000, mins: 60000000000,
                minute: 60000000000, minutes: 60000000000,

                h: 3600000000000,
                hr: 3600000000000, hrs: 3600000000000,
                hour: 3600000000000, hours: 3600000000000,

                d: 86400000000000,
                day: 86400000000000, days: 86400000000000
            };
        str.replace(/(\d+(?:\.\d*)?)([a-z]+)?/ig, function($0, number, unit) {
            number *= (unit) ? units[unit.toLowerCase()] || units.s : units.s;
            nanoseconds += +number;
        });
        return nanoseconds;
    }

}

export default Chat;