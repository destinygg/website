/* global $, window, document */

import debounce from 'debounce';
import EventEmitter from './emitter.js';
import ChatSource from './source.js';
import ChatUser from './user.js';
import {MessageBuilder, MessageTypes} from './messages.js';
import {ChatMenu, ChatUserMenu, ChatPmMenu, ChatEmoteMenu, ChatSettingsMenu} from './menus.js';
import {EmoteFormatter, GreenTextFormatter, HtmlTextFormatter, MentionedUserFormatter, UrlFormatter} from './formatters.js';
import ChatAutoComplete from './autocomplete.js';
import ChatInputHistory from './history.js';
import ChatScrollPlugin from './scroll.js';
import ChatUserFocus from './focus.js';
import ChatHighlighter from './highlight.js';
import ChatStore from './store.js';
import UserFeatures from './features.js';

const nickregex    = /^[a-zA-Z0-9_]{3,20}$/;
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
    ['notfound', 'The user was not found']
]);
const hintstrings  = new Map([
    ['hint', 'Type in /hint for more hints'],
    ['slashhelp', 'Type in /help for more advanced features, like modifying the scrollback size'],
    ['tabcompletion', 'Use the tab key to auto-complete usernames and emotes (for user only completion prepend a @ or press shift)'],
    ['hoveremotes', 'Hovering your mouse over an emote will show you the emote code'],
    ['highlight', 'Chat messages containing your username will be highlighted'],
    ['notify', 'Use /msg <username> to send a private message to someone'],
    ['ignoreuser', 'Use /ignore <username> to hide messages from pesky chatters'],
    ['mutespermanent', 'Mutes are never persistent, don\'t worry it will pass!']
]);
const settings     = new Map([
    ['showtime', false],
    ['hideflairicons', false],
    ['timestampformat', 'HH:mm'],
    ['maxlines', 250],
    ['allowNotifications', false],
    ['highlight', true],
    ['customhighlight', []],
    ['highlightnicks', []]
]);

class Chat {

    constructor(){
        this.uri             = '';
        this.control         = new EventEmitter(this);
        this.source          = new ChatSource();
        this.user            = new ChatUser();
        this.users           = new Map();
        this.settings        = new Map();
        this.reconnect       = true;
        this.connected       = false;
        this.lastmessage     = null;
        this.unresolved      = [];
        this.formatters      = [];
        this.emoticons       = new Set();
        this.twitchemotes    = new Set();
        this.shownhints      = new Set();
        this.ignoring        = new Set();
        this.ignoreregex     = null;
        this.showstarthint   = true;

        this.settings        = new Map([...settings, ...(ChatStore.read('chat.settings') || [])]);
        this.ignoring        = new Set([...ChatStore.read('chat.ignoring') || []]);
        this.shownhints      = new Set([...ChatStore.read('chat.shownhints') || []]);

        this.ui              = $('#chat');
        this.css             = $('#chat-styles')[0]['sheet'];
        this.output          = $('#chat-output');
        this.lines           = $('#chat-lines');
        this.input           = $('#chat-input-control');
        this.scrollnotify    = $('#chat-scroll-notify');
        this.pmcount         = $('#chat-pm-count');

        this.backlogloading  = false; // todo rename
        this.pmcountnum      = 0;     // todo remove

        this.autocomplete    = new ChatAutoComplete(this);
        this.highlighter     = new ChatHighlighter(this);

        // Socket events
        this.source.on('PING',             data => this.source.send('PONG', data));
        this.source.on('OPEN',             data => this.connected = true);
        this.source.on('REFRESH',          data => window.location.reload(false));
        this.source.on('CONNECTING',       data => this.onCONNECTING(data));
        this.source.on('DISPATCH',         data => this.onDISPATCH(data));
        this.source.on('CLOSE',            data => this.onCLOSE(data));
        this.source.on('NAMES',            data => this.onNAMES(data));
        this.source.on('JOIN',             data => this.onJOIN(data));
        this.source.on('QUIT',             data => this.onQUIT(data));
        this.source.on('PRIVMSG',          data => this.onPRIVMSG(data));
        this.source.on('MSG',              data => this.onMSG(data));
        this.source.on('MUTE',             data => this.onMUTE(data));
        this.source.on('UNMUTE',           data => this.onUNMUTE(data));
        this.source.on('BAN',              data => this.onBAN(data));
        this.source.on('UNBAN',            data => this.onUNBAN(data));
        this.source.on('ERR',              data => this.onERR(data));
        this.source.on('SUBONLY',          data => this.onSUBONLY(data));
        this.source.on('BROADCAST',        data => this.onBROADCAST(data));
        this.source.on('PRIVMSGSENT',      data => this.onPRIVMSGSENT(data));

        // User actions
        this.control.on('SEND',            data => this.cmdSEND(data));
        this.control.on('HINT',            data => this.cmdHINT(data));
        this.control.on('EMOTES',          data => this.cmdEMOTES(data));
        this.control.on('HELP',            data => this.cmdHELP(data));
        this.control.on('ME',              data => this.cmdME(data));
        this.control.on('MESSAGE',         data => this.cmdWHISPER(data));
        this.control.on('MSG',             data => this.cmdWHISPER(data));
        this.control.on('WHISPER',         data => this.cmdWHISPER(data));
        this.control.on('W',               data => this.cmdWHISPER(data));
        this.control.on('TELL',            data => this.cmdWHISPER(data));
        this.control.on('T',               data => this.cmdWHISPER(data));
        this.control.on('NOTIFY',          data => this.cmdWHISPER(data));
        this.control.on('IGNORE',          data => this.cmdIGNORE(data));
        this.control.on('UNIGNORE',        data => this.cmdUNIGNORE(data));
        this.control.on('MUTE',            data => this.cmdMUTE(data));
        this.control.on('BAN',             data => this.cmdBAN(data));
        this.control.on('IPBAN',           data => this.cmdBAN(data));
        this.control.on('UNMUTE',          data => this.cmdUNBAN(data));
        this.control.on('UNBAN',           data => this.cmdUNBAN(data));
        this.control.on('UNBAN',           data => this.cmdUNBAN(data));
        this.control.on('SUBONLY',         data => this.cmdSUBONLY(data));
        this.control.on('MAXLINES',        data => this.cmdMAXLINES(data));
        this.control.on('UNHIGHLIGHT',     data => this.cmdTOGGLEHIGHLIGHT(data));
        this.control.on('HIGHLIGHT',       data => this.cmdTOGGLEHIGHLIGHT(data));
        this.control.on('TIMESTAMPFORMAT', data => this.cmdTIMESTAMPFORMAT(data));
        this.control.on('BROADCAST',       data => this.cmdBROADCAST(data));

        this.updateSettingsCss(); // todo :(
        this.updateIgnoreRegex(); // todo :(
    }

    withGui(){
        this.scrollplugin = new ChatScrollPlugin(this.output);
        this.inputhistory = new ChatInputHistory(this);
        this.userfocus    = new ChatUserFocus(this, this.css);
        this.menus        = new Map([
            ['settings', new ChatSettingsMenu($('#chat-settings'), this)],
            ['emotes', new ChatEmoteMenu($('#chat-emote-list'), this)],
            ['users', new ChatUserMenu($('#chat-user-list'), this)],
            ['pm', new ChatPmMenu($('#chat-pm-notification'), this)]
        ]);
        this.toolbuttons  = new Map([
            ['users', $('#chat-users-btn')],
            ['settings', $('#chat-settings-btn')],
            ['emoticon', $('#emoticon-btn')]
        ]);

        // The user input field
        this.ui.on('submit', e => {
            e.preventDefault();
            e.stopPropagation();
            this.control.emit('SEND', this.input.val().toString().trim());
            this.input.val('').focus();
        });

        // Tool buttons
        this.toolbuttons.get('settings').on('click', e => this.menus.get('settings').toggle(e.target));
        this.toolbuttons.get('emoticon').on('click', e => this.menus.get('emotes').toggle(e.target));
        this.toolbuttons.get('users').on('click', e => this.menus.get(this.pmcountnum > 0 ? 'pm' : 'users').toggle(e.target));

        // On update show/hide the scroll notify ui
        this.output.on('update', debounce(() => this.scrollnotify.toggle(!this.scrollplugin.isPinned()), 100));

        // When you click the scroll notify ui pin the scroll
        this.scrollnotify.on('click', () => this.scrollplugin.updateAndPin(true));

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
            ChatMenu.closeMenus(this); // todo move out
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

        this.scrollplugin.updateAndPin(true);
        return this;
    }

    connect(uri) {
        this.uri = uri;
        this.source.connect(uri);
        return this;
    }

    sendCommand(command, payload=null){
        const parts = (payload || '').match(/([^ ]+)/g);
        this.control.emit(command, parts || []);
    }

    addUser(data){
        let user = this.users.get(data.nick);
        if (!user) {
            user = new ChatUser(data);
            this.users.set(data.nick, user);
        } else if (data.hasOwnProperty('features') && !Chat.isArraysEqual(data.features, user.features)) {
            user.features = data.features;
        }
        return user;
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
        this.emoticons.forEach(e => this.autocomplete.addEmote(e));
        this.twitchemotes.forEach(e => this.autocomplete.addEmote(e));
        return this;
    }

    withHistory(history) {
        if(history.length > 0) {
            this.backlogloading = true;
            history.forEach(line => this.source.parseAndDispatch({data: line}));
            this.backlogloading = false;
            this.push(MessageBuilder.uiMessage('<hr/>'));
            this.scrollplugin.updateAndPin(true);
        }
        return this;
    }

    withUser(user){
        this.user = this.addUser(user || {});
        return this;
    }

    onDISPATCH({data}){
        if (typeof data === 'object'){
            const add = ({nick, ignored}) => this.autocomplete.toggleNick(nick, ignored);
            if(data.hasOwnProperty('nick')){
                add(this.addUser(data));
            }
            if(data.hasOwnProperty('users')){
                data.users.forEach(u => add(this.addUser(u)));
            }
        }
    }

    onCLOSE(){
        const wasconnected = this.connected;
        this.connected = false;
        if (this.reconnect){
            const rand = ((wasconnected) ? Math.floor(Math.random() * (3000 - 501 + 1)) + 501 : Math.floor(Math.random() * (30000 - 5000 + 1)) + 5000);
            setTimeout(() => this.connect(this.uri), rand);
            this.push(MessageBuilder.statusMessage(`Disconnected... reconnecting in ${Math.round(rand/1000)} seconds`));
        }
    }

    onCONNECTING(){
        this.push(MessageBuilder.statusMessage('Connecting...'));
    }

    onNAMES(data){
        this.push(MessageBuilder.statusMessage(`Connected. Server connections: ${data['connectioncount']}`));
        if(this.showstarthint){
            this.showstarthint = false;
            this.sendCommand('HINT');
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
        const text    = (data.data.substring(0, 4) === '/me ' ? data.data.substring(4) : data.data).trim();
        const isemote = this.emoticons.has(text) || this.twitchemotes.has(text);
        if(isemote && this.lastmessage !== null && this.lastmessage.message === text){
            if(this.lastmessage.type === MessageTypes.emote) {
                this.lastmessage.incEmoteCount();
            } else {
                this.lastmessage.ui.remove();
                this.push(MessageBuilder.emoteMessage(text, data.timestamp, 2));
            }
        } else if(!this.resolveMessage(data)){
            this.push(MessageBuilder.userMessage(data.data, this.users.get(data.nick), data.timestamp));
        }
    }

    onMUTE(data){
        let suppressednick = data.data;
        if (this.user.username.toLowerCase() === data.data.toLowerCase())
            suppressednick = 'You have been';
        else if (!this.user.hasAnyFeatures(
                UserFeatures.SUBSCRIBERT3,
                UserFeatures.SUBSCRIBERT4,
                UserFeatures.SUBSCRIBERT2,
                UserFeatures.ADMIN,
                UserFeatures.MODERATOR
            ))
            this.removeMessageByUsername(data.data);

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
        if (this.user.username.toLowerCase() === suppressednick.toLowerCase()) {
            suppressednick = 'You have been';
            this.ui.addClass('banned');
        } else if(!this.user.hasAnyFeatures(
                UserFeatures.SUBSCRIBERT3,
                UserFeatures.SUBSCRIBERT4,
                UserFeatures.SUBSCRIBERT2,
                UserFeatures.ADMIN,
                UserFeatures.MODERATOR
            ))
            this.removeMessageByUsername(data.data);
        this.push(MessageBuilder.commandMessage(`${suppressednick} banned by ${data.nick}`, data.timestamp));
    }

    onUNBAN(data){
        let suppressednick = data.data;
        if (this.user.username.toLowerCase() === data.data.toLowerCase()){
            suppressednick = 'You have been';
            this.ui.removeClass('banned');
        }
        this.push(MessageBuilder.commandMessage(`${suppressednick} unbanned by ${data.nick}`, data.timestamp));
    }

    onERR(data){
        this.reconnect = (data !== 'toomanyconnections' && data !== 'banned');
        const errorString = errorstrings.has(data) ? errorstrings.get(data) : data;
        this.push(MessageBuilder.errorMessage(errorString));
    }

    onSUBONLY(data){
        const submode = data.data === 'on' ? 'enabled': 'disabled';
        this.push(MessageBuilder.commandMessage(`Subscriber only mode ${submode} by ${data.nick}`, data.timestamp));
    }

    onBROADCAST(data){
        this.push(MessageBuilder.broadcastMessage(data.data, data.timestamp));
    }

    onPRIVMSGSENT(){
        this.push(MessageBuilder.infoMessage('Your message has been sent!'));
    }

    onPRIVMSG(data) {
        const user = this.users.get(data.nick);
        if (user && !this.shouldIgnoreUser(user.username)){
            this.setUnreadMessageCount(this.pmcountnum+1);
            this.push(MessageBuilder.privateMessage(data.data, user, data.messageid, data.timestamp));
        }
    }


    cmdSEND(str) {
        const normalizedstr = str.toLowerCase();
        if(normalizedstr !== '' && normalizedstr !== '/me' && normalizedstr !== '/me '){

            if (this.user === null || !this.user.username)
                return this.push(MessageBuilder.errorMessage(errorstrings.get('needlogin')));

            if (/^\/[^\/|me]/i.test(str)){

                // Command message
                const command = str.split(' ', 1)[0];
                this.sendCommand(
                    command.substring(1).toUpperCase(), // remove the leading /
                    str.substring(command.length+1)     // the rest of the string
                );

            } else {

                const text = (normalizedstr.substring(0, 4) === '/me ' ? str.substring(4) : str).trim();
                if (this.isEmote(text)){
                    // Emoticon combo
                    // If this is an isemote spam, emit the message but don't add the line immediately
                    this.source.send('MSG', {data: str});
                } else {
                    // Normal text message
                    // We add the message to the gui immediately
                    // But we will also get the MSG event, so we need to make sure we dont add the message to the gui again.
                    // We do this by storing the message in the unresolved array
                    // The onMSG then looks in the unresolved array for the message using the nick + message
                    // If found, the message is not added to the gui, removed from the unresolved array and the message.resolve method is run on the message
                    const message = MessageBuilder.userMessage(str, this.user);
                    this.push(message);
                    this.unresolved.unshift(message);
                    this.source.send('MSG', {data: str});
                }
            }

            this.inputhistory.add(str);
            this.autocomplete.markLastComplete();
        }
    }

    cmdEMOTES(){
        this.push(MessageBuilder.infoMessage(`Available emoticons: ${this.emoticons.join(', ')} (www.destiny.gg/emotes)`));
    }

    cmdHELP(){
        this.push(MessageBuilder.infoMessage("Available commands: /emotes /me /msg /ignore (without arguments to list the nicks ignored) /unignore /highlight (highlights target nicks messages for easier visibility) /unhighlight /maxlines /mute /unmute /subonly /ban /ipban /unban (also unbans ip bans) /timestampformat"));
    }

    cmdHINT(){
        let i = -1, key = null;
        const keys = [...hintstrings.keys()];
        while(true){
            ++i;
            if(i >= hintstrings.size){
                key = keys[0];
                this.shownhints.clear();
                this.shownhints.add(key);
                break;
            }
            key = keys[i];
            if(!this.shownhints.has(key)){
                this.shownhints.add(key);
                break;
            }
        }
        ChatStore.write('chat.shownhints', this.shownhints);
        this.push(MessageBuilder.infoMessage(hintstrings.get(key)));
    }

    cmdME(parts){
        this.source.send('MSG', {data: parts.join(' ')});
    }

    cmdWHISPER(parts){
        if (!parts[0] || !nickregex.test(parts[0].toLowerCase()))
            this.push(MessageBuilder.errorMessage('Invalid nick - /msg nick message'));
        else if(parts[0].toLowerCase() === this.user.username.toLowerCase())
            this.push(MessageBuilder.errorMessage('Cannot send a message to yourself'));
        else
            this.source.send('PRIVMSG', {
                nick: parts[0],
                data: parts.slice(1, parts.length).join(' ')
            });
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
            this.push(MessageBuilder.infoMessage(`Current number of lines shown: ${this.settings.get('maxlines')}`));
        } else {
            const newmaxlines = Math.abs(parseInt(parts[0], 10));
            if (!newmaxlines) {
                this.push(MessageBuilder.infoMessage(`Invalid argument - /${command} is expecting a number`));
            } else {
                this.settings.set('maxlines', newmaxlines);
                ChatStore.write('chat.settings', this.settings);
                this.updateSettingsCss();
                this.push(MessageBuilder.infoMessage(`Current number of lines shown: ${this.settings.get('maxlines')}`));
            }
        }
    }

    cmdTOGGLEHIGHLIGHT(parts, command){
        const highlights = this.settings.get('highlightnicks'),
              nicks      = Object.keys(highlights);
        if (!parts[0]) {
            this.push(MessageBuilder.infoMessage('Currently highlighted users: ' + nicks.join(', ')));
        } else if (!nickregex.test(parts[1])) {
            this.push(MessageBuilder.errorMessage(`Invalid nick - /${command} nick`));
        } else {
            const nick = parts[0].toLowerCase();
            switch(command) {
                case 'UNHIGHLIGHT':
                    if(highlights[nick]) delete(highlights[nick]);
                    this.push(MessageBuilder.infoMessage(`No longer highlighting ${nick}`));
                    break;
                default:
                case 'HIGHLIGHT':
                    if(!highlights[nick]) highlights[nick] = true;
                    this.push(MessageBuilder.infoMessage(`Now highlighting ${nick}`));
                    break;
            }
            this.settings.set('highlightnicks', highlights);
            ChatStore.write('chat.settings', this.settings);
            this.highlighter.loadHighlighters();
            this.highlighter.redraw();
            this.updateSettingsCss();
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
                ChatStore.write('chat.settings', this.settings);
                this.updateSettingsCss();
                this.push(MessageBuilder.infoMessage(`New format: ${this.settings.get('timestampformat')}`));
            }
        }
    }

    cmdBROADCAST(parts){
        this.source.send('BROADCAST', {data: parts.join(' ')});
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

        // Highlight and append to the chat gui
        message.highlighted = this.highlighter.mustHighlight(message);
        this.lines.append(message.attach(this));

        if(!this.backlogloading){
            // Pin the chat scroll
            //console.debug(`Update scroll Pinned: ${pin} contentScrollTop: ${this.scrollplugin.scroller.contentScrollTop} maxScrollTop: ${this.scrollplugin.scroller.maxScrollTop}`);
            this.scrollplugin.updateAndPin(pin);

            // Show desktop notification
            if(message.highlighted && this.settings.get('allowNotifications') && !this.input.is(':focus'))
                Chat.showNotification(message);
        }

        // Cache the last message for interrogation
        this.lastmessage = message;
        return message;
    }

    resolveMessage(data){
        for(const message of this.unresolved){
            if(this.user.username === data.nick && message.message === data.data)
                return this.unresolved.splice(0, 1)[0].resolve(this);
        }
        return null;
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
        ChatStore.write('chat.ignoring', this.ignoring);
        this.updateIgnoreRegex();
    }

    shouldIgnoreMessage(message){
        return message !== '' && this.ignoreregex && this.ignoreregex.test(message);
    }

    shouldIgnoreUser(nick){
        return this.ignoring.has(nick.toLowerCase());
    }


    updateSettingsCss(){
        Array.from(this.settings.keys()).filter(key => typeof this.settings.get(key) === 'boolean').forEach(key => this.ui.toggleClass(`pref-${key}`, this.settings.get(key)));
    }

    updateIgnoreRegex(){
        const k = Array.from(this.ignoring.values()).map(Chat.makeSafeForRegex);
        this.ignoreregex = k.length > 0 ? new RegExp(k.join('|'), 'i') : null;
    }


    setUnreadMessageCount(n){
        this.pmcountnum = Math.max(0, n);
        this.pmcount.toggleClass('hidden', !this.pmcountnum).text(this.pmcountnum);
    }


    static isArraysEqual(a, b){
        return (!a || !b) ? (a.length !== b.length || a.sort().toString() !== b.sort().toString()) : false;
    }

    static showNotification(message){
        if(Notification.permission === 'granted'){
            const n = new Notification(`${message.user.username} said ...`, {
                body : message.message,
                tag  : `dgg${message.timestamp.valueOf()}`,
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

    isEmote(text) {
        return this.emoticons.has(text) || this.twitchemotes.has(text);
    }

}

export default Chat;