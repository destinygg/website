/* global $, window, document */

import debounce from 'debounce';
import moment from 'moment';
import EventEmitter from './emitter.js';
import ChatSource from './source.js';
import ChatUser from './user.js';
import {MessageBuilder, MessageTypes} from './messages.js';
import {ChatMenu, ChatUserMenu, ChatWhisperUsers, ChatWhisperMessages, ChatEmoteMenu, ChatSettingsMenu} from './menus.js';
import {EmoteFormatter, GreenTextFormatter, HtmlTextFormatter, MentionedUserFormatter, UrlFormatter} from './formatters.js';
import ChatAutoComplete from './autocomplete.js';
import ChatInputHistory from './history.js';
import ChatScrollPlugin from './scroll.js';
import ChatUserFocus from './focus.js';
import ChatStore from './store.js';
import UserFeatures from "./features";

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
    ['muted', 'You are muted (subscribing auto-removes mutes). Check your profile for more information.'],
    ['submode', 'The channel is currently in subscriber only mode'],
    ['needbanreason', 'Providing a reason for the ban is mandatory'],
    ['banned', 'You have been banned (subscribing auto-removes non-permanent bans), disconnecting. Check your profile for more information.'],
    ['requiresocket', 'This chat requires WebSockets'],
    ['toomanyconnections', 'Only 5 concurrent connections allowed'],
    ['socketerror', 'Error contacting server'],
    ['privmsgbanned', 'Cannot send private messages while banned'],
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
    ['tagshint', `Use the /tag <nick> <color> to highlight users you like. There are preset colors to choose from ${tagcolors.join(', ')}`]
]);
const settings = new Map([
    ['showtime', false],
    ['hideflairicons', false],
    ['profilesettings', false],
    ['timestampformat', 'HH:mm'],
    ['maxlines', 250],
    ['allowNotifications', false],
    ['highlight', true],
    ['customhighlight', []],
    ['highlightnicks', []],
    ['taggednicks', []],
    ['showremoved', false],
    ['showhispersinchat', false],
    ['ignorenicks', []]
]);
const commandsinfo = new Map([
    ['help', ''],
    ['emotes', ''],
    ['me', ''],
    ['msg', ''],
    ['ignore', 'without arguments to list the nicks ignored'],
    ['unignore', ''],
    ['highlight', 'highlights target nicks messages for easier visibility'],
    ['unhighlight', ''],
    ['maxlines', ''],
    ['mute', ''],
    ['unmute', ''],
    ['subonly', ''],
    ['ban', ''],
    ['unban', 'also unbans ip bans'],
    ['timestampformat', ''],
    ['stalk', ''],
    ['mentions', ''],
    ['tag', ''],
    ['untag', '']
]);

class Chat {

    constructor(){
        this.uri             = '';
        this.ui              = $('#chat');
        this.css             = $('#chat-styles')[0]['sheet'];
        this.output          = this.ui.find('#chat-output');
        this.lines           = this.ui.find('#chat-lines');
        this.input           = this.ui.find('#chat-input-control');
        this.scrollnotify    = this.ui.find('#chat-scroll-notify');
        this.loginscrn       = this.ui.find('#chat-login-screen');
        this.loadingscrn     = this.ui.find('#chat-loading');
        this.control         = new EventEmitter(this);
        this.source          = new ChatSource();
        this.user            = new ChatUser();
        this.users           = new Map();
        this.whispers        = new Map();
        this.reconnect       = true;
        this.connected       = false;
        this.lastmessage     = null;
        this.unresolved      = [];
        this.formatters      = [];
        this.emoticons       = new Set();
        this.twitchemotes    = new Set();
        this.showstarthint   = true;
        this.authenticated   = true;
        this.backlogloading  = false;
        this.autocomplete    = new ChatAutoComplete(this);
        this.settings        = new Map([...settings]);
        this.taggednicks     = new Map();
        this.ignoring        = new Set();
        this.ignoreregex     = null;
        this.highlightregex  = null;

        this.source.on('PING',             data => this.source.send('PONG', data));
        this.source.on('OPEN',             data => this.connected = true);
        this.source.on('REFRESH',          data => window.location.reload(false));
        this.source.on('CONNECTING',       data => this.onCONNECTING(data));
        this.source.on('DISPATCH',         data => this.onDISPATCH(data));
        this.source.on('CLOSE',            data => this.onCLOSE(data));
        this.source.on('NAMES',            data => this.onNAMES(data));
        this.source.on('JOIN',             data => this.onJOIN(data));
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
        this.control.on('UNBAN',           data => this.cmdUNBAN(data, 'UNBAN'));
        this.control.on('SUBONLY',         data => this.cmdSUBONLY(data, 'SUBONLY'));
        this.control.on('MAXLINES',        data => this.cmdMAXLINES(data, 'MAXLINES'));
        this.control.on('UNHIGHLIGHT',     data => this.cmdTOGGLEHIGHLIGHT(data, 'UNHIGHLIGHT'));
        this.control.on('HIGHLIGHT',       data => this.cmdTOGGLEHIGHLIGHT(data, 'HIGHLIGHT'));
        this.control.on('TIMESTAMPFORMAT', data => this.cmdTIMESTAMPFORMAT(data));
        this.control.on('BROADCAST',       data => this.cmdBROADCAST(data));
        this.control.on('CONNECT',         data => this.cmdCONNECT(data));
        this.control.on('TAG',             data => this.cmdTAG(data));
        this.control.on('UNTAG',           data => this.cmdUNTAG(data));

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
                   .withSettings(data && data.hasOwnProperty('settings') ? data.settings : []);
    }

    withUser(user){
        this.user = this.addUser(user || {});
        this.authenticated = this.user !== null && this.user.username !== '';
        return this;
    }

    withSettings(data){
        if(this.authenticated && new Map([...data]).get('profilesettings')){
            this.settings = new Map([...settings, ...data]);
        } else {
            this.settings = new Map([...settings, ...(ChatStore.read('chat.settings') || []), ['profilesettings', false]]);
        }

        // Convert old style settings
        let save = false;
        let arr = ChatStore.read('chatoptions');
        if(arr){
            save = true;
            Object.keys(arr).forEach(k => {
                switch (k) {
                    case 'highlightnicks':
                        this.settings.set('highlightnicks', Object.keys(arr[k]));
                        break;
                    default:
                        this.settings.set(k, arr[k]);
                        break;
                }
            });
        }

        arr = ChatStore.read('chatignorelist');
        if(arr) {
            save = true;
            this.settings.set('ignorenicks', Object.keys(arr) || []);
        }

        arr = ChatStore.read('chat.ignoring');
        if(arr) {
            save = true;
            this.settings.set('ignorenicks', arr);
        }

        arr = ChatStore.read('inputhistory');
        if(arr)
            ChatStore.write('chat.history', arr);

        arr = null;
        ChatStore.remove('chatoptions');
        ChatStore.remove('inputhistory');
        ChatStore.remove('chatignorelist');
        ChatStore.remove('hiddenhints');
        ChatStore.remove('lasthinttime');
        ChatStore.remove('unreadMessageCount');
        ChatStore.remove('chat.shownhints');
        ChatStore.remove('chat.ignoring');
        // end of clean up

        // If the save flag is one, we save the settings (can result in an api call)
        if(save) this.saveSettings();

        return this;
    }

    withGui(){
        [...commandsinfo.entries()].forEach(a => this.autocomplete.addToBucket(`/${a[0]}`, 1, false, 0));
        this.taggednicks    = new Map(this.settings.get('taggednicks'));
        this.ignoring       = new Set(this.settings.get('ignorenicks'));
        this.scrollplugin   = new ChatScrollPlugin(this.output);
        this.inputhistory   = new ChatInputHistory(this);
        this.userfocus      = new ChatUserFocus(this, this.css);
        this.menus          = new Map([
            ['settings',            new ChatSettingsMenu(this.ui.find('#chat-settings'), this.ui.find('#chat-settings-btn'), this)],
            ['emotes',              new ChatEmoteMenu(this.ui.find('#chat-emote-list'), this.ui.find('#chat-emoticon-btn'), this)],
            ['users',               new ChatUserMenu(this.ui.find('#chat-user-list'), this.ui.find('#chat-users-btn'), this)],
            ['whisper-users',       new ChatWhisperUsers(this.ui.find('#chat-whisper-users'), this.ui.find('#chat-whisper-btn'), this)],
            ['whisper-messages',    new ChatWhisperMessages(this.ui.find('#chat-whisper-messages'), $.fn, this)]
        ]);

        this.applySettings(false);
        this.loadingscrn.fadeOut();

        // The user input field
        this.input.on('keypress', e => {
            if(e.keyCode === 13 && !e.shiftKey && !e.ctrlKey) {
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

        this.lines.on('click', '.censored .ctrl', e => {
            $(e.target).closest('.censored').removeClass('censored');
            return false;
        });

        // On update show/hide the scroll notify ui
        this.output.on('update', debounce(() => this.scrollnotify.toggle(!this.scrollplugin.isPinned()), 100));

        // When you click the scroll notify ui pin the scroll
        this.scrollnotify.on('mousedown', () => false);
        this.scrollnotify.on('mouseup', () => {
            this.scrollplugin.updateAndPin(true);
            return false;
        });

        // Interaction with the mouse down in the chat lines
        // Chat stops scrolling if the mouse is down
        // resumes scrolling after X ms, remembers if the chat was pinned.
        this.mousedown = false;
        this.waspinned = true;
        this.waitingtopin = false;
        const delayedpin = debounce(() => {
            if(this.waspinned && !this.mousedown){
                this.scrollplugin.updateAndPin(true);
            }
            this.waitingtopin = false;
            }, 750);
        this.output.on('mouseup', () => {
            this.mousedown = false;
            this.waitingtopin = true;
            delayedpin();
        });
        this.output.on('mousedown', () => {
            this.mousedown = true;
            if(!this.waitingtopin){
                this.waspinned = this.scrollplugin.isPinned();
            }
            ChatMenu.closeMenus(this);
        });

        this.input.on('mousedown', () => {
            const m = this.menus.get('whisper-messages');
            if(!m || !m.visible)
                ChatMenu.closeMenus(this)
        });

        // On window resize, update scroll
        let waspinnedbeforeresize = null;
        let isresizing = false;
        const delayedresizepin = debounce(() => {
            this.scrollplugin.updateAndPin(waspinnedbeforeresize);
            isresizing = false;
        }, 300);
        $(window).on('resize', () => {
            if(!isresizing){
                waspinnedbeforeresize = this.scrollplugin.isPinned();
                isresizing = true;
            }
            delayedresizepin();
        });

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
        this.loginscrn.on('click', '#chat-btn-cancel', () => {
            this.loginscrn.hide();
        });

        // whispers
        this.lines.on('click', '.chat-open-whisper', e => {
            const nick = $(e.target).data('username');
            if(this.whispers.has(nick)) {
                this.menus.get('whisper-users').selectConversation(nick);
            }
            return false;
        });
        this.lines.on('click', '.chat-remove-whisper', e => {
            const nick = $(e.target).data('username');
            if(this.whispers.has(nick)) {
                $.ajax({url: `/profile/messages/${encodeURIComponent(nick)}/unread`, method:'delete'});
                const pinned = this.scrollplugin.isPinned();
                this.whispers.delete(nick);
                this.menus.get('whisper-users').redraw();
                $(e.target).closest('.msg-whisper').remove();
                this.scrollplugin.updateAndPin(pinned);
            }
            return false;
        });

        // Close menus when esc is pressed
        $(document).on('keydown', ({keyCode}) => {
            if(keyCode === 27) ChatMenu.closeMenus(this);
        });

        this.scrollplugin.updateAndPin(true);
        this.input.attr('disabled', false);
        this.input.focus();
        return this;
    }

    withFormatters(){
        this.formatters.push(new HtmlTextFormatter(this));
        this.formatters.push(new UrlFormatter(this));
        this.formatters.push(new EmoteFormatter(this));
        this.formatters.push(new MentionedUserFormatter(this));
        this.formatters.push(new GreenTextFormatter(this));
        return this;
    }

    withEmotes(emotes) {
        this.emoticons = new Set(emotes['destiny']);
        this.twitchemotes = new Set(emotes['twitch']);
        this.emoticons.forEach(e => this.autocomplete.addToBucket(e, 1, true, 0));
        this.twitchemotes.forEach(e => this.autocomplete.addToBucket(e, 1, true, 0));
        return this;
    }

    withHistory(history) {
        if(history && history.length > 0) {
            this.backlogloading = true;
            history.forEach(line => this.source.parseAndDispatch({data: line}));
            this.backlogloading = false;
            this.push(MessageBuilder.uiMessage('<hr/>'));
            this.scrollplugin.updateAndPin(true);
        }
        return this;
    }

    withMessages(){
        if(this.authenticated) {
            $.ajax({url: "/profile/conversations/unread"})
                .done(d => d.forEach(e => this.whispers.set(e.username, {
                    nick: e.username,
                    unread: e.unread,
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
                $.ajax({url: '/chat/settings', method:'post', data: JSON.stringify([...this.settings])});
            } else {
                ChatStore.write('chat.settings', this.settings);
            }
        } else {
            ChatStore.write('chat.settings', this.settings);
        }
    }

    commitSettings(){
        if(!this.debouncedsave) {
            this.debouncedsave = debounce(() => this.saveSettings(), 1000, false);
        }
        this.debouncedsave();
    }

    applySettings(save=true){
        if(save)
            this.saveSettings();
        this.updateHighlightRegex();
        this.updateIgnoreRegex();
        this.updateSettingsCss();
    }

    sendCommand(command, payload=null){
        const parts = (payload || '').match(/([^ ]+)/g);
        this.control.emit(command, parts || []);
    }

    addUser(data){
        let user = this.users.get(data.nick);
        if (!user) {
            user = new ChatUser(data);
            this.users.set(user.nick, user);
        } else if (data.hasOwnProperty('features') && !Chat.isArraysEqual(data.features, user.features)) {
            user.features = data.features;
        }
        return user;
    }

    onDISPATCH({data}){
        if (typeof data === 'object'){
            if(data.hasOwnProperty('nick')){
                this.autocomplete.updateNick(this.addUser(data).nick);
            }
            if(data.hasOwnProperty('users')){
                data.users.forEach(u => {
                    this.autocomplete.updateNick(this.addUser(u).nick);
                });
            }
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
            this.push(MessageBuilder.statusMessage(`Disconnected... reconnecting in ${Math.round(rand/1000)} seconds`));
        }
    }

    onCONNECTING(){
        this.push(MessageBuilder.statusMessage('Connecting...'));
    }

    onNAMES(data){
        this.push(MessageBuilder.statusMessage(`Connected. Server connections: ${data['connectioncount']}`));
        if(this.showstarthint) {
            this.cmdHINT([Math.floor(Math.random() * hintstrings.size)]);
            this.showstarthint = false;
        }
    }

    onJOIN(data){}

    onQUIT(data){
        if (this.users.has(data.nick)){
            delete this.users.delete(data.nick);
            this.autocomplete.removeNick(data.nick);
        }
    }

    onMSG(data){
        const str = data.data;
        let text = (str.substring(0, 4) === '/me ' ? str.substring(4) : str).trim();

        // todo refactor this
        // the Message class manipulates the raw string like below.
        if (text.substring(0, 4) === '/me ') {
            text = text.substring(4);
        } else if (text.substring(0, 2) === '//') {
            text = text.substring(1);
        }
        text = text.trim();
        //

        const isemote = this.emoticons.has(text) || this.twitchemotes.has(text);
        if(isemote && this.lastmessage !== null && this.lastmessage.message === text){
            if(this.lastmessage.type === MessageTypes.emote) {
                const pinned = this.scrollplugin.isPinned();
                this.lastmessage.incEmoteCount();
                this.scrollplugin.updateAndPin(pinned);
            } else {
                this.lastmessage.ui.remove();
                this.push(MessageBuilder.emoteMessage(text, data.timestamp, 2));
            }
        } else if(!this.resolveMessage(data.nick, text)){
            this.push(MessageBuilder.userMessage(data.data, this.users.get(data.nick), data.timestamp));
        }
    }

    onMUTE(data){
        let suppressednick = data.data;
        if (this.user.username.toLowerCase() === data.data.toLowerCase())
            suppressednick = 'You have been';
        this.censorMessageByUsername(data.data);
        this.push(MessageBuilder.commandMessage(`${suppressednick} muted by ${data.nick}`, data.timestamp));
    }

    onUNMUTE(data){
        let suppressednick = data.data;
        if (this.user.username.toLowerCase() === data.data.toLowerCase())
            suppressednick = 'You have been';
        this.push(MessageBuilder.commandMessage(`${suppressednick} unmuted by ${data.nick}`, data.timestamp));
    }

    onBAN(data){
        // data.data is the nick which has been banned, no info about duration
        let suppressednick = data.data;
        if (this.user.username.toLowerCase() === suppressednick.toLowerCase())
            suppressednick = 'You have been';
        this.censorMessageByUsername(data.data);
        this.push(MessageBuilder.commandMessage(`${suppressednick} banned by ${data.nick}`, data.timestamp));
    }

    onUNBAN(data){
        let suppressednick = data.data;
        if (this.user.username.toLowerCase() === data.data.toLowerCase())
            suppressednick = 'You have been';
        this.push(MessageBuilder.commandMessage(`${suppressednick} unbanned by ${data.nick}`, data.timestamp));
    }

    onERR(data){
        this.reconnect = (data !== 'toomanyconnections' && data !== 'banned');
        const errorString = errorstrings.has(data) ? errorstrings.get(data) : data;
        const convmenu = this.menus.get('whisper-messages');
        if(convmenu && convmenu.visible){
            convmenu.error(errorString);
        } else {
            this.push(MessageBuilder.errorMessage(errorString));
        }
    }

    onSUBONLY(data){
        const submode = data.data === 'on' ? 'enabled': 'disabled';
        this.push(MessageBuilder.commandMessage(`Subscriber only mode ${submode} by ${data.nick}`, data.timestamp));
    }

    onBROADCAST(data){
        this.push(MessageBuilder.broadcastMessage(data.data, data.timestamp));
    }

    cmdSEND(str) {
        if(str !== ''){
            const isme = str.substring(0, 4).toLowerCase() === '/me ';
            const iscommand = str.substring(0, 1) === '/' && str.substring(0, 2) !== '//';

            // If we have the whisper window open, send a whisper instead
            // If its a command close the window, and continue
            const convmenu = this.menus.get('whisper-messages');
            if(iscommand && convmenu && convmenu.visible)
                ChatMenu.closeMenus(this);

            if(convmenu && convmenu.visible) {
                this.cmdWHISPER([convmenu.username, str]);
                return; // todo, rather than prevent the history here -- read from a different history
            }
            // Run a command e.g. /me
             else if (!isme && iscommand)
            {
                const command = str.split(' ', 1)[0];
                this.sendCommand(
                    command.substring(1).toUpperCase(), // remove the leading /
                    str.substring(command.length+1)     // the rest of the string
                );
            }

            // Normal chat message or emote
            else
            {
                const text = (isme ? str.substring(4) : str).trim();
                if (!this.emoticons.has(text) && !this.twitchemotes.has(text) && this.connected){
                    // Normal text message
                    // We add the message to the gui immediately
                    // But we will also get the MSG event, so we need to make sure we dont add the message to the gui again.
                    // We do this by storing the message in the unresolved array
                    // The onMSG then looks in the unresolved array for the message using the nick + message
                    // If found, the message is not added to the gui, its removed from the unresolved array and the message.resolve method is run on the message
                    const message = MessageBuilder.userMessage(str, this.user);
                    this.push(message);
                    this.unresolved.unshift(message);
                }
                this.source.send('MSG', {data: str});
            }
            this.inputhistory.add(str);
            this.autocomplete.markLastComplete();
        }
    }

    cmdEMOTES(){
        this.push(MessageBuilder.infoMessage(`Available emoticons: ${this.emoticons.join(', ')} (www.destiny.gg/emotes)`));
    }

    cmdHELP(){
        let str = 'Available commands: ';
        [...commandsinfo].forEach(a => {
            const s = a[1] !== '' ? `(${a[1]})` : '';
            str += `/${a[0]} ${s} `;
        });
        this.push(MessageBuilder.infoMessage(str));
    }

    cmdHINT(parts){
        const arr = [...hintstrings];
        const i = parts && parts[0] ? parseInt(parts[0])-1 : -1;
        if(i > 0 && i < hintstrings.size){
            this.push(MessageBuilder.infoMessage(arr[i][1]));
        } else {
            if(this.lasthintindex === undefined || this.lasthintindex === arr.length - 1) {
                this.lasthintindex = 0;
            } else  {
                this.lasthintindex++;
            }
            this.push(MessageBuilder.infoMessage(arr[this.lasthintindex][1]));
        }
    }

    cmdIGNORE(parts){
        const username = parts[0] || null;
        if (!username) {
            if (this.ignoring.size <= 0) {
                this.push(MessageBuilder.infoMessage('Your ignore list is empty'));
            } else {
                this.push(MessageBuilder.infoMessage(`Ignoring the following people: ${Array.from(this.ignoring.values()).join(', ')}`));
            }
        } else if (!nickregex.test(username)) {
            this.push(MessageBuilder.infoMessage('Invalid nick - /ignore nick'));
        } else {
            this.ignoreNick(username, true);
            this.removeMessageByUsername(username);
            this.push(MessageBuilder.statusMessage(`Ignoring ${username}`));
        }
    }

    cmdUNIGNORE(parts){
        const username = parts[0] || null;
        if (!username || !nickregex.test(username)) {
            this.push(MessageBuilder.errorMessage('Invalid nick - /ignore nick'));
        } else {
            this.ignoreNick(username, false);
            this.push(MessageBuilder.statusMessage(`${username} has been removed from your ignore list`));
        }
    }

    cmdMUTE(parts){
        if (!parts[0]) {
            this.push(MessageBuilder.infoMessage(`Usage: /mute nick[ time]`));
        } else if (!nickregex.test(parts[0])) {
            this.push(MessageBuilder.infoMessage(`Invalid nick - /mute nick[ time]`));
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
        if (parts.length < 3) {
            this.push(MessageBuilder.infoMessage(`Usage: /${command} nick time reason (time can be 'permanent')`));
        } else if (!nickregex.test(parts[0])) {
            this.push(MessageBuilder.infoMessage('Invalid nick'));
        } else if (!parts[2]) {
            this.push(MessageBuilder.errorMessage('Providing a reason is mandatory'));
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
        if (!parts[0]) {
            this.push(MessageBuilder.infoMessage(`Usage: /${command} nick`));
        } else if (!nickregex.test(parts[0])) {
            this.push(MessageBuilder.infoMessage(`Invalid nick - /${command} nick`));
        } else {
            this.source.send(command, {data: parts[0]});
        }
    }

    cmdSUBONLY(parts, command){
        if (parts[0] !== 'on' && parts[0] !== 'off') {
            this.push(MessageBuilder.errorMessage(`Invalid argument - /${command} on/off`));
        } else {
            this.source.send(command.toUpperCase(), {data: parts[0]});
        }
    }

    cmdMAXLINES(parts, command){
        if (!parts[0]) {
            this.push(MessageBuilder.infoMessage(`Maximum lines stored: ${this.settings.get('maxlines')}`));
        } else {
            const newmaxlines = Math.abs(parseInt(parts[0], 10));
            if (!newmaxlines) {
                this.push(MessageBuilder.infoMessage(`Invalid argument - /${command} is expecting a number`));
            } else {
                this.settings.set('maxlines', newmaxlines);
                this.applySettings();
                this.push(MessageBuilder.infoMessage(`Current number of lines shown: ${this.settings.get('maxlines')}`));
            }
        }
    }

    cmdTOGGLEHIGHLIGHT(parts, command){
        const highlights = this.settings.get('highlightnicks');
        if (!parts[0]) {
            if(highlights.length > 0)
                this.push(MessageBuilder.infoMessage('Currently highlighted users: ' + highlights.join(', ')));
            else
                this.push(MessageBuilder.infoMessage(`No highlighted users`));

        } else if (!nickregex.test(parts[1])) {
            this.push(MessageBuilder.errorMessage(`Invalid nick - /${command} nick`));
        } else {
            const nick = parts[0].toLowerCase();
            const i = highlights.indexOf(nick);
            switch(command) {
                case 'UNHIGHLIGHT':
                    if(i !== -1) highlights.splice(i, 1);
                    this.push(MessageBuilder.infoMessage(`No longer highlighting ${nick}`));
                    break;
                default:
                case 'HIGHLIGHT':
                    if(i === -1) highlights.push(nick);
                    this.push(MessageBuilder.infoMessage(`Highlighting ${nick}`));
                    break;
            }
            this.settings.set('highlightnicks', highlights);
            this.applySettings();
        }
    }

    cmdTIMESTAMPFORMAT(parts){
        if (!parts[0]) {
            this.push(MessageBuilder.infoMessage(`Current format: ${this.settings.get('timestampformat')} (the default is 'HH:mm', for more info: http://momentjs.com/docs/#/displaying/format/)`));
        } else {
            const format = parts.slice(1, parts.length);
            if ( !/^[a-z :.,-\\*]+$/i.test(format)) {
                this.push(MessageBuilder.errorMessage('Invalid format, see: http://momentjs.com/docs/#/displaying/format/'));
            } else {
                this.settings.set('timestampformat', format);
                this.applySettings();
                this.push(MessageBuilder.infoMessage(`New format: ${this.settings.get('timestampformat')}`));
            }
        }
    }

    cmdBROADCAST(parts){
        this.source.send('BROADCAST', {data: parts.join(' ')});
    }

    onPRIVMSGSENT(){
        const menu = this.menus.get('whisper-messages');
        if(!menu || !menu.visible) {
            this.push(MessageBuilder.infoMessage('Your message has been sent.'));
        }
    }

    onPRIVMSG(data) {
        if (!this.shouldIgnoreUser(data.nick)){
            this.addWhisper(data.nick, {data: data.data, timestamp: data.timestamp, read: false, nick: data.nick});
            if(this.settings.get('showhispersinchat')){
                let user = this.users.has(data.nick) ? this.users.get(data.nick) : new ChatUser({nick: data.nick});
                this.push(MessageBuilder.whisperMessage(data.data, user, this.user.username, data.timestamp));
            }
            if(this.settings.get('allowNotifications') && !this.input.is(':focus')) {
                Chat.showNotification(`${data.nick} whispered ...`, data.data, data.timestamp);
            }
        }
    }

    cmdWHISPER(parts){
        if (!parts[0] || !nickregex.test(parts[0].toLowerCase())) {
            this.push(MessageBuilder.errorMessage('Invalid nick - /msg nick message'));
        } else if (parts[0].toLowerCase() === this.user.username.toLowerCase()) {
            this.push(MessageBuilder.errorMessage('Cannot send a message to yourself'));
        } else {
            const data = parts.slice(1, parts.length).join(' ');
            if(this.whispers.has(parts[0])){
                // Only add the whisper gui for this message, if the whisper user already exists (we already have gui for it)
                // Because the request/response for whispers is not a single transaction, we cannot really tell if the pm was successful
                this.addWhisper(parts[0], {data: data, timestamp: Date.now(), read: true, nick: this.user.username});
            }
            const payload = {nick: parts[0], data: data};
            this.source.send('PRIVMSG', payload);
        }
    }

    cmdCONNECT(parts){
        this.reconnect = false;
        this.uri = parts[0];
        this.source.disconnect();
        this.source.connect(this.uri);
    }

    cmdSTALK(parts){
        if (!parts[0] || !nickregex.test(parts[0].toLowerCase())) {
            this.push(MessageBuilder.errorMessage('Invalid nick - /stalk <nick>'));
            return;
        }
        if(this.busystalk){
            this.push(MessageBuilder.errorMessage('Still busy stalking'));
            return;
        }
        if(this.nextallowedstalk && this.nextallowedstalk.isAfter(new Date())){
            this.push(MessageBuilder.errorMessage(`Next allowed stalk ${this.nextallowedstalk.fromNow()}`));
            return;
        }
        this.busystalk = true;
        this.push(MessageBuilder.infoMessage(`Getting messages for ${[parts[0]]} ...`));
        $.ajax({timeout:5000, url: `/chat/api/v1/${encodeURIComponent(parts[0])}/stalk`})
            .always(() => {
                this.nextallowedstalk = moment().add(10, 'seconds');
                this.busystalk = false;
            })
            .done(d => {
                if(d.lines.length === 0) {
                    this.push(MessageBuilder.infoMessage(`No messages for ${parts[0]}`));
                } else {
                    const date = moment.utc(d.lines[d.lines.length-1]['timestamp']*1000).local().format('MMMM Do YYYY, h:mm:ss a');
                    this.push(MessageBuilder.uiMessage(`Stalked ${parts[0]} ... <a href="https://dgg.overrustlelogs.net/${parts[0]}" target="_blank">overrustlelogs.net</a><br />last seen ${date}`, [`h-start`]));
                    d.lines.forEach(a => {
                        const m = MessageBuilder.userMessage(a.text, new ChatUser({nick: d.nick}), a.timestamp*1000);
                        m.historical = true;
                        this.push(m);
                    });
                    this.push(MessageBuilder.cssMessage([`h-end`]));
                }
            })
            .fail(e => this.push(MessageBuilder.errorMessage(`Error stalking ${parts[0]}. Try again later`)));
    }

    cmdMENTIONS(parts){
        if (!parts[0]) parts[0] = this.user.username;
        if (!parts[0] || !nickregex.test(parts[0].toLowerCase())) {
            this.push(MessageBuilder.errorMessage('Invalid nick - /mentions <nick>'));
            return;
        }
        if(this.busymentions){
            this.push(MessageBuilder.errorMessage('Still busy getting mentions'));
            return;
        }
        if(this.nextallowedmentions && this.nextallowedmentions.isAfter(new Date())){
            this.push(MessageBuilder.errorMessage(`Next allowed mentions ${this.nextallowedmentions.fromNow()}`));
            return;
        }
        this.busymentions = true;
        this.push(MessageBuilder.infoMessage(`Getting mentions for ${[parts[0]]} ...`));
        $.ajax({timeout:5000, url: `/chat/api/v1/${encodeURIComponent(parts[0])}/mentions`})
            .always(() => {
                this.nextallowedmentions = moment().add(10, 'seconds');
                this.busymentions = false;
            })
            .done(d => {
                if(d.length === 0) {
                    this.push(MessageBuilder.infoMessage(`No mentions for ${parts[0]}`));
                } else {
                    const date = moment.utc(d[d.length-1].date*1000).local().format('MMMM Do YYYY, h:mm:ss a');
                    this.push(MessageBuilder.uiMessage(`Mentions for ${parts[0]} ... <a href="https://dgg.overrustlelogs.net/${parts[0]}" target="_blank">overrustlelogs.net</a><br /> last message ${date}`, [`h-start`]));
                    d.forEach(a => {
                        const m = MessageBuilder.userMessage(a.text, new ChatUser({nick: a.nick}), a.date*1000);
                        m.historical = true;
                        this.push(m);
                    });
                    this.push(MessageBuilder.cssMessage([`h-end`]));
                }
            })
            .fail(e => this.push(MessageBuilder.errorMessage(`Error retrieving ${parts[0]} mentions. Try again later`)));
    }

    cmdTAG(parts){
        if (!parts[0]){
            this.push(MessageBuilder.infoMessage(`Tagged nicks: ${[...this.taggednicks].map(a => a[0]).join(', ')}. Available colors: ${tagcolors.join(', ')}`));
            return;
        }
        if (!parts[0] || !nickregex.test(parts[0])) {
            this.push(MessageBuilder.errorMessage('Invalid nick - /tag <nick> <color>'));
            return;
        }
        const normalized = parts[0].toLowerCase();
        if(normalized === this.user.username.toLowerCase()){
            this.push(MessageBuilder.errorMessage('Cannot tag yourself'));
            return;
        }
        const color = parts[1] && tagcolors.indexOf(parts[1]) !== -1 ? parts[1] : tagcolors[Math.floor(Math.random()*tagcolors.length)];
        this.taggednicks.set(normalized, color);
        this.settings.set('taggednicks', [...this.taggednicks]);
        this.applySettings();

        this.lines.children(`div.msg-tagged`).removeClass(Chat.removeTagClasses());
        this.lines.children(`div.msg-user`).get().forEach(e => {
            const el = $(e);
            if(this.taggednicks.has(el.attr('data-username'))){
                el.addClass(`msg-tagged msg-tagged-${color}`);
            }
        });
        this.push(MessageBuilder.infoMessage(`Tagged ${parts[0]} AYYYLMAO with ${color}`));
    }

    cmdUNTAG(parts){
        if (!parts[0]){
            this.push(MessageBuilder.infoMessage(`Tagged nicks: ${[...this.taggednicks].join(',')}`));
            return;
        }
        if (!parts[0] || !nickregex.test(parts[0])) {
            this.push(MessageBuilder.errorMessage('Invalid nick - /untag <nick>'));
            return;
        }
        const normalized = parts[0].toLowerCase();
        this.taggednicks.delete(normalized);
        this.settings.set('taggednicks', [...this.taggednicks]);
        this.applySettings();

        this.lines.children(`div[data-username="${normalized}"]`).removeClass(Chat.removeTagClasses());
        this.push(MessageBuilder.infoMessage(`Un-tagged ${parts[0]} `));
    }

    static removeTagClasses(){
        return function(i, c) {
            return (c.match(/(^|\s)msg-tagged?\S+/g) || []).join(' ');
        }
    }

    push(message){
        // Dont add the gui if user is ignored
        if (message.type === MessageTypes.user && this.shouldIgnoreMessage(message.message))
            return;

        // Get the scroll position before adding the new line / removing old lines
        const maxlines = this.settings.get('maxlines'),
                 lines = this.lines.children(),
                   pin = this.scrollplugin.isPinned() && !this.mousedown && !this.waitingtopin;

        // Rid excess lines if the user is scrolled to the bottom
        if(pin && lines.length >= maxlines)
            lines.slice(0, lines.length - maxlines).remove();

        // Break the current combo
        if(this.lastmessage && this.lastmessage.type === MessageTypes.emote && this.lastmessage.emotecount > 1)
            this.lastmessage.completeCombo();

        if(message.type === MessageTypes.user){
            const normalized = message.user.nick.toLowerCase();
            if(this.taggednicks.has(normalized)) {
                message.tag = this.taggednicks.get(normalized);
            }
        }

        // Highlight and append to the chat gui
        message.highlighted =
            this.settings.get('highlight') &&
            message.type === MessageTypes.user &&
            !message.user.hasFeature(UserFeatures.BOT) &&
            message.user.username !== this.user.username &&
            this.highlightregex !== null &&
            (Boolean(this.highlightregex.test(message.message) || this.highlightregex.test(message.username)));
        this.lines.append(message.attach(this));

        if(!this.backlogloading){
            // Pin the chat scroll
            //console.debug(`Update scroll Pinned: ${pin} contentScrollTop: ${this.scrollplugin.scroller.contentScrollTop} maxScrollTop: ${this.scrollplugin.scroller.maxScrollTop}`);
            this.scrollplugin.updateAndPin(pin);

            // Show desktop notification
            if(message.highlighted && this.settings.get('allowNotifications') && !this.input.is(':focus'))
                Chat.showNotification(`${message.user.username} said ...`, message.message, message.timestamp.valueOf());
        }

        // Cache the last message for interrogation
        this.lastmessage = message;
        return message;
    }

    resolveMessage(username, str){
        for(const message of this.unresolved){
            if(this.user.username === username && message.message === str){
                this.unresolved.splice(this.unresolved.indexOf(message), 1);
                return true;
            }
        }
        return false;
    }

    censorMessageByUsername(username){
        const c = this.lines.children(`div[data-username="${username.toLowerCase()}"]`);
        if(this.settings.get('showremoved')) {
            c.addClass('censored');
        } else {
            c.remove();
        }
        this.scrollplugin.reset();
    }

    removeMessageByUsername(username){
        this.lines.children(`div[data-username="${username.toLowerCase()}"]`).remove();
        this.scrollplugin.reset();
    }

    ignoreNick(nick, ignore=true){
        nick = nick.toLowerCase();
        if(!ignore)
            this.ignoring.add(nick);
        else if(this.ignoring.has(nick))
            this.ignoring.delete(nick);
        this.settings.set('ignorenicks', [...this.ignoring]);
        this.applySettings();
    }

    shouldIgnoreMessage(message){
        return message !== '' && this.ignoreregex && this.ignoreregex.test(message);
    }

    shouldIgnoreUser(nick){
        return this.ignoring.has(nick.toLowerCase());
    }

    addWhisper(username, message){
        const conv = this.whispers.get(username) || {nick:username, unread:0, messages:[], loaded:true};
        this.whispers.set(username, conv);
        if(!message.read)
            conv.unread++;
        if(conv.loaded)
            conv.messages.push(message);
        this.menus.get('whisper-messages').redraw();
        this.menus.get('whisper-users').redraw();
    }

    updateSettingsCss(){
        Array.from(this.settings.keys()).filter(key => typeof this.settings.get(key) === 'boolean').forEach(key => this.ui.toggleClass(`pref-${key}`, this.settings.get(key)));
    }

    updateIgnoreRegex(){
        const k = Array.from(this.ignoring.values()).map(Chat.makeSafeForRegex);
        this.ignoreregex = k.length > 0 ? new RegExp(`\\b(?:${k.join('|')})\\b`, 'i') : null;
    }

    updateHighlightRegex(){
        let nicks = [...this.settings.get('highlightnicks')];
        let words = [...this.settings.get('customhighlight')];
        nicks.push(this.user.username);
        let arr = [...nicks, ...words].filter(a => a !== '');
        this.highlightregex = arr.length > 0 ? new RegExp(`\\b(?:${arr.join('|')})\\b`, 'i') : null;
    }

    static isArraysEqual(a, b){
        return (!a || !b) ? (a.length !== b.length || a.sort().toString() !== b.sort().toString()) : false;
    }

    static showNotification(title, message, tag){
        if(Notification.permission === 'granted'){
            const n = new Notification(title, {
                body : message,
                tag  : `dgg${tag}`,
                icon : '/notifyicon.png',
                dir  : 'auto'
            });
            setTimeout(() => n.close(), 5000);
            n.onclick = function(){
                // todo open chat at specific line
            };
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