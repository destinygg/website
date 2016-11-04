/* global $, destiny */
"use strict";

require('nanoscroller');

import debounce from 'debounce';
import ChatMenu from './menu.js';
import ChatMessage from './messages/message.js';
import ChatUserMessage from './messages/user.js';
import ChatAutoComplete from './autocomplete.js';
import ChatUIMessage from './messages/ui.js';
import UrlFormatter from './formatters/url.js';
import EmoteFormatter from './formatters/emote.js';
import MentionedUserFormatter from './formatters/mention.js';
import GreenTextFormatter from './formatters/greentext.js';

class ChatGui {

    constructor(engine, options){
        this.ui = $('#destinychat');
        this.engine = engine;

        this.maxlines           = 500; // legacy - set via php

        this.scrollPlugin       = null;
        this.autoCompletePlugin = null;
        this.lines              = null;
        this.output             = null;
        this.input              = null;
        this.userMessages       = [];
        this.focusedUsers       = [];

        this.inputhistory       = [];
        this.currenthistoryline = -1;
        this.storedinputline    = null;

        this.backlog            = [];
        this.backlogLoading     = false;

        this.highlightregex     = {};
        this.lastMessage        = null;

        this.emoticons          = [];
        this.twitchemotes       = [];
        this.formatters         = [];

        this.pmcountnum         = 0;
        this.pmcount            = null;

        this.stylesheet         = null;

        this.preferences = {
            'showtime' : {
                'value'  : false,
                'default': false
            },
            'hideflairicons' : {
                'value'  : false,
                'default': false
            },
            'timestampformat' : {
                'value'  : 'HH:mm',
                'default': 'HH:mm'
            },
            'maxlines' : {
                'value'  : 500,
                    'default': 500
            },
            'allowNotifications' : {
                'value'  : false,
                'default': false
            },
            'highlight': {
                'value'  : true,
                'default': true
            },
            'customhighlight': {
                'value'  : [],
                'default': []
            },
            'highlightnicks': {
                'value'  : {},
                'default': {}
            }
        };

        $.extend(this, options);
        this.init();
    }

    trigger(name, data){
        $(this).trigger(name, data);
    }

    on(name, fn){
        $(this).on(name, fn);
    }

    init(){
        // local var for this instance
        var chat = this;

        // local elements stored in vars to not have to get the elements via query each time
        this.menus      = [];
        this.pmcount    = this.ui.find('#chat-pm-count:first').eq(0);
        this.lines      = this.ui.find('#chat-lines:first').eq(0);
        this.output     = this.ui.find('#chat-output:first').eq(0);
        this.input      = this.ui.find('#chat-input .input:first').eq(0);
        this.stylesheet = $('#chat-styles')[0]['sheet'];

        // Message formatters
        this.formatters.push(new UrlFormatter(this));
        this.formatters.push(new EmoteFormatter(this));
        this.formatters.push(new MentionedUserFormatter(this));
        this.formatters.push(new GreenTextFormatter(this));

        // Legacy
        this.preferences.maxlines.value = this.maxlines;
        this.preferences.maxlines.default = this.maxlines;

        // Scrollbar plugin
        this.scrollPlugin = this.output.nanoScroller({
            disableResize: true,
            preventPageScrolling: true,
            sliderMinHeight: 40,
            tabIndex: 1
        })[0].nanoscroller;

        // Local stored
        this.loadPreferences();
        this.loadHighlighters();
        this.loadInputHistory();

        // Auto complete
        this.autoCompletePlugin = new ChatAutoComplete(this.input, this.emoticons.concat(this.twitchemotes));

        // Chat settings
        var chatSettingsUi = this.ui.find('#chat-settings:first').eq(0),
            chatSettingsMenu = new ChatMenu(chatSettingsUi, chat);
        var chatSettingsNotificationText = this.ui.find('#chat-settings-notification-permissions');
        var updateChatSettingsNotificationText = function(){
            chatSettingsNotificationText.text("(Permission "+ (Notification.permission === "default" ? "required":Notification.permission) +")");
        };

        chatSettingsUi.on('keypress blur', 'input[name=customhighlight]', function(e) {
            let keycode = e.keyCode ? e.keyCode : e.which;
            if (keycode && keycode != 13) // not enter
                return;

            let data = $(this).val().toString().trim().split(',');
            for (let i = data.length - 1; i >= 0; i--) {
                data[i] = data[i].trim();
                if (!data[i])
                    data.splice(i, 1)
            }
            chat.setPreference('customhighlight', data );
            chat.loadHighlighters();
            return false;
        });
        chatSettingsUi.on('change', 'input[type="checkbox"]', function(){
            let name    = $(this).attr('name'),
                checked = $(this).is(':checked');
            switch(name){

                case 'showtime':
                    chat.setPreference(name, checked);
                    chat.updateAndScroll(chat.isScrolledToBottom());
                    break;

                case 'hideflairicons':
                    chat.setPreference(name, checked);
                    chat.updateAndScroll(chat.isScrolledToBottom());
                    break;

                case 'highlight':
                    chat.setPreference(name, checked);
                    break;

                case 'allowNotifications':
                    if(checked){
                        chat.notificationPermission().then(
                            function(){
                                updateChatSettingsNotificationText();
                                chat.setPreference(name, true);
                            },
                            function(){
                                updateChatSettingsNotificationText();
                                chat.setPreference(name, false);
                            }
                        );
                    } else {
                        chat.setPreference(name, false);
                    }
                    break;
            }
        });
        chatSettingsMenu.on('show', function(){
            chatSettingsUi.find('input[name=customhighlight]').val( chat.getPreference('customhighlight').join(', ') );
            chatSettingsUi.find('input[type="checkbox"]').each(function() {
                let name = $(this).attr('name');
                $(this).prop('checked', chat.getPreference(name));
            });
            if(Notification.permission !== "granted")
                chatSettingsUi.find('input[name="allowNotifications"]').prop('checked', false);
            updateChatSettingsNotificationText();
        });


        var chatEmotesUi = this.ui.find('#chat-emote-list:first').eq(0),
            chatEmotesUiMenu = new ChatMenu(chatEmotesUi, chat);

        chatEmotesUiMenu.on('init', function(){
            let demotes = chatEmotesUi.find('#destiny-emotes'),
                temotes = chatEmotesUi.find('#twitch-emotes');

            for(var i=0;i<chat.emoticons.length;i++)
                demotes.append(`<div class="emote"><span title="${chat.emoticons[i]}" class="chat-emote chat-emote-${chat.emoticons[i]}">${chat.emoticons[i]}</span></div>`);
            for(var x=0;x<chat.twitchemotes.length;x++)
                temotes.append(`<div class="emote"><span title="${chat.twitchemotes[x]}" class="chat-emote chat-emote-${chat.twitchemotes[x]}">${chat.twitchemotes[x]}</span></div>`);

            chatEmotesUi.on('click', '.chat-emote', function(){
                let value = chat.input.val().toString().trim();
                chat.input.val( value + ((value == "") ? "":" ")  +  $(this).text() + " ");
                chat.input.focus();
                return false;
            });
        });


        var pmPopupUi = this.ui.find('#chat-pm-notification:first').eq(0),
            pmPopupUiMenu = new ChatMenu(pmPopupUi, chat);

        pmPopupUi.on('click', '.user-list-link', function(){
            ChatMenu.closeMenus(chat);
            userListUiMenu.show(pmPopupUiMenu.btn);
            return false;
        });
        pmPopupUi.on('click', '#markread-privmsg', function(){
            chat.setUnreadMessageCount(0);
            ChatMenu.closeMenus(chat);
            $.ajax({
                type: 'POST',
                url: '/profile/messages/openall'
            });
            return false;
        });
        pmPopupUi.on('click', '#inbox-privmsg', function(){
            chat.setUnreadMessageCount(0);
            ChatMenu.closeMenus(chat);
        });
        chat.setUnreadMessageCount(chat.pmcountnum);


        var userListUi = this.ui.find('#chat-user-list:first').eq(0),
            userListUiMenu = new ChatMenu(userListUi, chat);

        userListUiMenu.groups = userListUi.find('#chat-groups');
        userListUiMenu.clearAll = function(){
            userListUiMenu.groups.find('li').remove();
        };
        userListUiMenu.sortByName = function(a,b){
            try {
                return a.firstChild.getAttribute('data-username').localeCompare(b.firstChild.getAttribute('data-username'));
            } catch(e){}
        };
        userListUiMenu.sortUsers = function(){
            userListUiMenu.groups.children().each(function(){
                let users = $(this).find('li').get();
                users.sort(userListUiMenu.sortByName);
                for (let i = 0; i<users.length; i++)
                    users[i].parentNode.appendChild(users[i]);
            });
        };
        userListUi.on('click', '.user', function(){
            chat.toggleUserFocus(this.textContent);
        });
        userListUi.each(function(){
            let header = userListUi.find('h5 span'),
                groups = userListUiMenu.groups,
                group1 = $('<ul id="chat-group1">').appendTo(groups),
                group2 = $('<ul id="chat-group2">').appendTo(groups),
                group3 = $('<ul id="chat-group3">').appendTo(groups),
                group4 = $('<ul id="chat-group4">').appendTo(groups),
                group5 = $('<ul id="chat-group5">').appendTo(groups);

            var updateCount = function(){
                const n = Object.keys(chat.engine.users).length;
                header.text(n);
                return n;
            };
            var hasUser = function(username){
                return userListUi.find('a[data-username="'+username+'"]').length > 0;
            };
            var removeUser = function(username){
                return userListUi.find('a[data-username="'+username+'"]').closest('li').remove();
            };
            var addUser = function(username){
                const u        = chat.engine.users[username],
                      features = u.features.join(' '),
                      elem     = `<li><a data-username="${u.username}" class="user ${features}">${u.username}</a></li>`;
                if(u.hasFeature(destiny.UserFeatures.BOT) || u.hasFeature(destiny.UserFeatures.BOT2))
                    group5.append(elem);
                else if (u.hasFeature(destiny.UserFeatures.ADMIN) || u.hasFeature(destiny.UserFeatures.VIP))
                    group1.append(elem);
                else if(u.hasFeature(destiny.UserFeatures.BROADCASTER))
                    group2.append(elem);
                else if(u.hasFeature(destiny.UserFeatures.SUBSCRIBER))
                    group3.append(elem);
                else
                    group4.append(elem);
            };
            chat.on('join', function(e, data){
                if(userListUiMenu.visible && !hasUser(data.nick)){
                    addUser(data.nick);
                    updateCount();
                    userListUiMenu.sortUsers();
                    userListUiMenu.redraw();
                }
            });
            chat.on('quit', function(e, data){
                if(userListUiMenu.visible && hasUser(data.nick)){
                    removeUser(data.nick);
                    updateCount();
                    userListUiMenu.redraw();
                }
            });
            userListUiMenu.on('show', function(){
                userListUiMenu.clearAll();
                updateCount();
                for(let username in chat.engine.users) {
                    if (chat.engine.users.hasOwnProperty(username))
                        addUser(username);
                }
                userListUiMenu.sortUsers();
                userListUiMenu.redraw();
            });
        });

        this.ui.find('#chat-users-btn').on('click', function(){
            if(chat.pmcountnum > 0)
                pmPopupUiMenu.toggle(this);
            else
                userListUiMenu.toggle(this);
            return false;
        });
        this.ui.find('#chat-settings-btn').on('click', function(){
            chatSettingsMenu.toggle(this);
            return false;
        });
        this.ui.find('#emoticon-btn').on('click', function(){
            chatEmotesUiMenu.toggle(this);
            return false;
        });

        this.lines.on('mousedown', '.user-msg a.user', function(){
            const username = $(this).closest('.user-msg').data('username');
            chat.toggleUserFocus(username);
            return false;
        });

        this.lines.on('mousedown', '.user-msg .chat-user', function() {
            const username1 = $(this).closest('.user-msg').data('username'),
                  username2 = this.textContent.toLowerCase();
            chat.addUserFocus(username1);
            chat.toggleUserFocus(username2);
            return false;
        });

        // Bind to user input submit
        this.ui.on('submit', 'form#chat-input', function(){
            chat.send();
            return false;
        });

        // Close all menus and perform a scroll
        this.input.on('keydown mousedown', function(){
            ChatMenu.closeMenus(chat);
        });

        // Close all menus if someone clicks on any messages
        this.output.on('mousedown', function(){
            ChatMenu.closeMenus(chat);
            chat.clearUserFocus();
        });

        const scrollNotify = chat.ui.find('#chat-scroll-notify');
        scrollNotify.on('click', function() {
            chat.updateAndScroll(true);
        });
        chat.output.on('update', debounce(function() {
            scrollNotify.toggle(!chat.isScrolledToBottom());
        }, 100));

        // The login click
        this.ui.find('#chat-login-msg a[href="/login"]').on('click', function(){
            try {
                if(window.self !== window.top){
                    window.parent.location.href = $(this).attr('href') + '?follow=' + encodeURIComponent(window.parent.location.pathname);
                }else{
                    window.location.href = $(this).attr('href') + '?follow=' + encodeURIComponent(window.location.pathname);
                }
            } catch (e) {}
            return false;
        });

        // when clicking on "nothing" move the focus to the input
        let mouseDownCoords, focusTimer;
        this.lines.on('click mousedown keydown', function(e) {
            let coords = e.clientX + '-' + e.clientY;
            switch(e.type) {
                case 'click':
                    if (mouseDownCoords !== coords)
                        return;
                    focusTimer = setTimeout(chat.input.focus.bind(chat.input), 500);
                    break;
                case 'mousedown':
                    mouseDownCoords = coords;
                    break;
                case 'keydown':
                    if (focusTimer) {
                        clearTimeout(focusTimer);
                        focusTimer = 0;
                    }
                    break;
            }
        });

        // Private message onclick
        this.lines.on('click', '.mark-as-read', function(){
            let messageEl      = $(this).closest('.private-message'),
                message        = messageEl.data('message'),
                messageIcnSend = message.ui.find('.icon-mail-send'),
                messageActions = message.ui.find('.message-actions');
            $.ajax({
                type: 'POST',
                url: '/profile/messages/'+ encodeURIComponent(message.messageid) +'/open',
                complete: function(data){
                    messageIcnSend.attr('class', 'icon-mail-open-document');
                    messageActions.remove();
                    chat.setUnreadMessageCount(data.unread);
                }
            });
            return false;
        });

        // should be moved somewhere better
        $(window).on({
            'resize.chat': function(){
                chat.resize();
            },
            'focus.chat': function(){
                chat.input.focus();
            },
            'load.chat': function(){
                chat.input.focus();
            }
        });
    }

    isScrolledToBottom(){
        if(!this.scrollPlugin || !this.scrollPlugin.isActive) return false;
        return (this.scrollPlugin.contentScrollTop >= this.scrollPlugin.maxScrollTop - 30); // 30px is used as a padding and relates to no elements
    }

    updateAndScroll(scrollBottom){
        if(!this.scrollPlugin || !this.scrollPlugin.isActive) return;
        this.scrollPlugin.reset();
        if(scrollBottom)
            this.scrollPlugin.scrollBottom(0);
    }

    addUserFocusRule(username){
        this.stylesheet.insertRule('.user-msg[data-username="' + username + '"]{opacity:1 !important;}', this.focusedUsers.length); // max 4294967295
        this.focusedUsers.push(username);
        this.ui.toggleClass('focus-user', this.focusedUsers.length > 0);
    }

    removeUserFocusRule(index){
        this.stylesheet.deleteRule(index);
        this.focusedUsers.splice(index, 1);
        this.ui.toggleClass('focus-user', this.focusedUsers.length > 0);
    }

    clearUserFocus(){
        for(let i=this.focusedUsers.length-1; i>=0; --i)
            this.removeUserFocusRule(this.focusedUsers[i], i);
        this.ui.toggleClass('focus-user', false);
    }

    addUserFocus(username){
        if(username && this.focusedUsers.indexOf(username) === -1)
            this.addUserFocusRule(username);
    }

    removeUserFocus(username){
        if(!username) return;
        username = username.toLowerCase();
        const index = this.focusedUsers.indexOf(username);
        if(index !== -1) this.removeUserFocusRule(index);
    }

    toggleUserFocus(username){
        if(!username) return;
        username = username.toLowerCase();
        const index = this.focusedUsers.indexOf(username);
        if(index === -1)
            this.addUserFocus(username);
        else
            this.removeUserFocus(username, index);
    }

    loadBacklog(){
        this.backlogLoading = true;
        if(this.backlog.length == 0) {
            this.backlogLoading = false;
            return;
        }

        for (var i = 0, j = this.backlog.length; i < j; i++)
            this.engine.dispatchBacklog(this.backlog[i]);

        this.put(new ChatUIMessage('<hr/>'));
        this.updateAndScroll(true);
        this.backlogLoading = false;
    }

    put(message, state){
        message.ui = $(message.html());
        message.ui.data('message', message);
        message.ui.appendTo(this.lines);

        if(state != undefined)
            message.status(state);

        this.lastMessage = message;
        return message;
    }

    push(message, state){
        // Get the scroll position before adding the new line / removing old lines
        const wasScrolledBottom = this.isScrolledToBottom(),
              lines = this.lines.children(),
              maxlines = this.getPreference('maxlines');
        // Rid excess lines if the user is scrolled to the bottom
        var lineCount = lines.length;
        if(wasScrolledBottom && lineCount >= maxlines){
            var unwantedlines = lines.slice(0, lineCount - maxlines);
            for (var i = unwantedlines.length - 1; i >= 0; i--) {
                $(unwantedlines[i]).remove();
            }
        }

        this.userMessages.push(message);
        this.put(message, state);

        // Make sure a reset has been called at least once when the scroll should be enabled, but isn't yet
        if(this.scrollPlugin.content.scrollHeight > this.scrollPlugin.el.clientHeight && !this.scrollPlugin.isActive)
            this.scrollPlugin.reset();

        // Reset and or scroll bottom
        this.updateAndScroll(wasScrolledBottom);
        // Handle highlight / and if highlighted, notification
        this.handleHighlight(message);
        return message;
    }

    send(){
        let str = this.input.val().toString().trim();
        if(str != ''){
            this.input.val('').focus();

            if(this.engine.user == null || !this.engine.user.username)
                return this.push(ChatMessage.errorMessage(this.engine.errorstrings.needlogin));

            var message = (str.substring(0, 4) === '/me ') ? str.substring(4) : str;

            // If this is an emoticon spam, emit the message but don't add the line immediately
            if (this.emoticons.indexOf(message) !== -1 && this.engine.previousemote && this.engine.previousemote.message == message)
                return this.engine.emit('MSG', {data: str});

            if (str.substring(0, 1) === '/')
                return this.engine.handleCommand(str.substring(1));

            // Normal user message, emit
            this.push(new ChatUserMessage(str, this.engine.user), (!this.engine.connected) ? 'unsent' : 'pending');
            this.engine.emit('MSG', {data: str});

            this.insertInputHistory(str);
            this.currenthistoryline = -1;
            this.autoCompletePlugin.markLastComplete();
        }
        str = null;
    }

    resize(){
        this.updateAndScroll(this.isScrolledToBottom());
    }

    setupInputHistory(){
        let modifierpressed = false,
            chat = this;
        $(this.input).on('keyup', function(e) {

            if (e.shiftKey || e.metaKey || e.ctrlKey)
                modifierpressed = true;

            if ((e.keyCode != 38 && e.keyCode != 40) || modifierpressed) {
                modifierpressed = false;
                return;
            }

            const num  = e.keyCode == 38? -1: 1; // if up arrow we subtract otherwise add

            // if up arrow and we are not currently showing any lines from the history
            if (chat.currenthistoryline < 0 && e.keyCode == 38) {
                // set the current line to the end if the history, do not subtract 1
                // that's done later
                chat.currenthistoryline = chat.inputhistory.length;
                // store the typed in message so that we can go back to it
                chat.storedinputline = chat.input.val();

                if (chat.currenthistoryline <= 0) // nothing in the history, bail out
                    return;

            } else if (chat.currenthistoryline < 0 && e.keyCode == 40)
                return; // down arrow, but nothing to show

            var index = chat.currenthistoryline + num;
            // out of bounds
            if (index >= chat.inputhistory.length || index < 0) {

                // down arrow was pressed to get back to the original line, reset
                if (index >= chat.inputhistory.length) {
                    chat.input.val(chat.storedinputline);
                    chat.currenthistoryline = -1;
                }
                return;
            }

            chat.currenthistoryline = index;
            chat.input.val(chat.inputhistory[index]);

        });
    }

    loadInputHistory(){
        try {
            this.inputhistory = JSON.parse(localStorage['inputhistory'] || '[]');
            this.setupInputHistory();
        } catch (e) {}
    }

    insertInputHistory(message){
        this.inputhistory.push(message);
        if (this.inputhistory.length > 20)
            this.inputhistory.shift();
        localStorage['inputhistory'] = JSON.stringify(this.inputhistory);
    }

    resolveMessage(data){
        for(var i=0; i<this.userMessages.length; ++i){
            if(this.userMessages[i].message == data.data){
                var message = this.userMessages.splice(i, 1)[0];
                message.status();
                return message;
            }
        }
        return null;
    }

    notificationPermission(){
        let deferred =  $.Deferred();
        switch(Notification.permission) {
            case "default":
                Notification.requestPermission(function(permission){
                    switch(permission) {
                        case "granted":
                            deferred.resolve(permission);
                            break;
                        default:
                            deferred.reject(permission);
                    }
                });
                break;
            case "granted":
                deferred.resolve(Notification.permission);
                break;
            case "denied":
            default:
                deferred.reject(Notification.permission);
                break;
        }
        return deferred.promise();
    }

    showNotification(message){
        if(Notification.permission === 'granted'){
            const n = new Notification(`${message.user.username} said ...`, {
                body : message.message,
                tag  : message.timestamp.unix(),
                icon : '/notifyicon.png',
                dir  : 'auto'
            });
            setTimeout(n.close.bind(n), 5000);
            n.onclick = function(){
                // todo open chat at specific line
            };
        }
    }

    loadHighlighters(){
        if (this.engine.user && this.engine.user.username)
            this.highlightregex.user = new RegExp("\\b@?(?:"+this.engine.user.username+")\\b", "i");

        let highlights = this.getPreference('customhighlight');
        if (highlights.length > 0){
            for (let i = highlights.length - 1; i >= 0; i--)
                highlights[i] = highlights[i].replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
            this.highlightregex.custom = new RegExp("\\b(?:"+highlights.join("|")+")\\b", "i");
        }
    }

    renewHighlight(nick, dohighlight){
        this.lines.children('div[data-username="'+nick.toLowerCase()+'"]').toggleClass("highlight", dohighlight);
    }

    handleHighlight(message){
        if (!message.user || !message.user.username || message.user.username == this.engine.user.username || !this.getPreference('highlight'))
            return false;

        if(message.user.hasFeature(destiny.UserFeatures.BOT))
            return false;

        const nicks = this.getPreference('highlightnicks');
        if (
            nicks[message.user.username.toLowerCase()] || nicks[message.user.username] ||
            (this.highlightregex.user && this.highlightregex.user.test(message.message)) ||
            (this.highlightregex.custom && this.highlightregex.custom.test(message.message))
        ) {
            message.ui.addClass('highlight');

            if(this.getPreference('allowNotifications') && !this.hasFocus() && !this.backlogLoading)
                this.showNotification(message);
        }
        return false;
    }

    setPreference(key, value){
        this.preferences[key].value = value;
        this.applyPreferencesCss();
        this.savePreferences();
    }

    getPreference(key){
        return this.preferences[key].value || null;
    }

    loadPreferences(){
        try {
            let preferences = JSON.parse(localStorage['chatoptions'] || '{}');
            for(let key in preferences) {
                if (preferences.hasOwnProperty(key) && this.preferences.hasOwnProperty(key))
                    this.preferences[key].value = preferences[key];
            }
            this.applyPreferencesCss();
        } catch (ignored) {}
    }

    savePreferences(){
        try {
            let preferences = {};
            for(let key in this.preferences) {
                if (this.preferences.hasOwnProperty(key))
                    preferences[key] = this.preferences[key].value;
            }
            localStorage['chatoptions'] = JSON.stringify(preferences);
        } catch (ignored) {}
    }

    applyPreferencesCss(){
        for(let key in this.preferences) {
            if (this.preferences.hasOwnProperty(key) && typeof this.preferences[key].value === 'boolean')
                this.ui.toggleClass('pref-' + key, this.preferences[key].value);
        }
    }

    removeUserMessages(username){
        this.lines.children('div[data-username="'+username.toLowerCase()+'"]').remove();
        this.scrollPlugin.reset();
    }

    setUnreadMessageCount(n){
        this.pmcountnum = Math.max(0, n);
        this.pmcount.toggleClass('hidden', !this.pmcountnum).text(this.pmcountnum);
    }

    hasFocus(){
        return this.input.is(':focus');
    }

}

export default ChatGui;