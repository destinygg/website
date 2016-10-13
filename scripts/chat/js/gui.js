(function($){

    ChatGui = function(engine, options){
        this.ui = $('#destinychat');
        this.engine = engine;
        $.extend(this, options);
        return this.init();
    };

    $.extend(ChatGui.prototype, {

        loaded             : false,
        maxlines           : 500, // legacy - set via php

        scrollPlugin       : null,
        autoCompletePlugin : null,
        ui                 : null,
        lines              : null,
        output             : null,
        input              : null,
        userMessages       : [],
        focusedUsers       : [],

        inputhistory       : [],
        currenthistoryline : -1,
        storedinputline    : null,

        backlog            : [],
        backlogLoading     : false,

        highlightregex     : {},
        lastMessage        : null,

        emoticons          : [],
        twitchemotes       : [],
        formatters         : [],

        pmcountnum         : 0,
        pmcount            : null,

        stylesheet         : null,

        preferences : {
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
        },

        trigger: function(name, data){
            $(this).trigger(name, data);
        },

        on: function(name, fn){
            $(this).on(name, fn);
        },

        init: function(){

            // local var for this instance
            var chat = this;

            // local elements stored in vars to not have to get the elements via query each time
            this.pmcount    = this.ui.find('#chat-pm-count:first').eq(0);
            this.lines      = this.ui.find('#chat-lines:first').eq(0);
            this.output     = this.ui.find('#chat-output:first').eq(0);
            this.input      = this.ui.find('#chat-input .input:first').eq(0);
            this.stylesheet = $('#chat-styles')[0]['sheet'];

            // Message formatters
            this.formatters.push(new destiny.fn.UrlFormatter(this));
            this.formatters.push(new destiny.fn.EmoteFormatter(this));
            this.formatters.push(new destiny.fn.MentionedUserFormatter(this));
            this.formatters.push(new destiny.fn.GreenTextFormatter(this));

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
            this.scrollPlugin.isScrolledToBottom = function(){
                return (this.contentScrollTop >= this.maxScrollTop - 30);
            };
            this.scrollPlugin.updateAndScroll = function(scrollBottom){
                if(!this.isActive) return;
                this.reset();
                if(scrollBottom) this.scrollBottom(0);
            };

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
                var keycode = e.keyCode ? e.keyCode : e.which;
                if (keycode && keycode != 13) // not enter
                    return;

                var data = $(this).val().trim().split(',');
                for (var i = data.length - 1; i >= 0; i--) {
                    data[i] = data[i].trim();
                    if (!data[i])
                        data.splice(i, 1)
                }
                chat.setPreference('customhighlight', data );
                chat.loadHighlighters();
                return false;
            });
            chatSettingsUi.on('change', 'input[type="checkbox"]', function(){
                var name    = $(this).attr('name'),
                    checked = $(this).is(':checked');
                switch(name){

                    case 'showtime':
                        chat.setPreference(name, checked);
                        chat.scrollPlugin.updateAndScroll(chat.scrollPlugin.isScrolledToBottom());
                        break;

                    case 'hideflairicons':
                        chat.setPreference(name, checked);
                        chat.scrollPlugin.updateAndScroll(chat.scrollPlugin.isScrolledToBottom());
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
                    var name  = $(this).attr('name');
                    $(this).prop('checked', chat.getPreference(name));
                });
                if(Notification.permission !== "granted")
                    chatSettingsUi.find('input[name="allowNotifications"]').prop('checked', false);
                updateChatSettingsNotificationText();
            });


            var chatEmotesUi = this.ui.find('#chat-emote-list:first').eq(0),
                chatEmotesUiMenu = new ChatMenu(chatEmotesUi, chat);

            chatEmotesUiMenu.on('init', function(){
                var demotes = chatEmotesUi.find('#destiny-emotes'),
                    temotes = chatEmotesUi.find('#twitch-emotes');

                for(var i=0;i<chat.emoticons.length;i++)
                    demotes.append('<div class="emote"><span title="'+chat.emoticons[i]+'" class="chat-emote chat-emote-'+chat.emoticons[i]+'">'+chat.emoticons[i]+'</span></div>');
                for(var x=0;x<chat.twitchemotes.length;x++)
                    temotes.append('<div class="emote"><span title="'+chat.twitchemotes[x]+'" class="chat-emote chat-emote-'+chat.twitchemotes[x]+'">'+chat.twitchemotes[x]+'</span></div>');

                chatEmotesUi.on('click', '.chat-emote', function(e){
                    var value = chat.input.val().trim();
                    chat.input.val( value + ((value == "") ? "":" ")  +  $(this).text() + " ");
                    chat.input.focus();
                    return false;
                });
            });


            var pmPopupUi = this.ui.find('#chat-pm-notification:first').eq(0),
                pmPopupUiMenu = new ChatMenu(pmPopupUi, chat);

            pmPopupUi.on('click', '.user-list-link', function(e){
                ChatMenu.closeMenus(chat);
                userListUiMenu.show(pmPopupUiMenu.btn);
                return false;
            });
            pmPopupUi.on('click', '#markread-privmsg', function(e){
                chat.setUnreadMessageCount(0);
                ChatMenu.closeMenus(chat);
                $.ajax({
                    type: 'POST',
                    url: '/profile/messages/openall'
                });
                return false;
            });
            pmPopupUi.on('click', '#inbox-privmsg', function(e){
                chat.setUnreadMessageCount(0);
                ChatMenu.closeMenus(chat);
            });


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
                    var users = $(this).find('li').get();
                    users.sort(userListUiMenu.sortByName);
                    for (var i = 0; i<users.length; i++)
                        users[i].parentNode.appendChild(users[i]);
                });
            };
            userListUi.on('click', '.user', function(){
                chat.toggleUserFocus(this.textContent);
            });
            userListUi.each(function(){
                var header = userListUi.find('h5 span'),
                    groups = userListUiMenu.groups,
                    group1 = $('<ul id="chat-group1">').appendTo(groups),
                    group2 = $('<ul id="chat-group2">').appendTo(groups),
                    group3 = $('<ul id="chat-group3">').appendTo(groups),
                    group4 = $('<ul id="chat-group4">').appendTo(groups),
                    group5 = $('<ul id="chat-group5">').appendTo(groups);

                var updateCount = function(){
                    var n = Object.keys(chat.engine.users).length;
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
                    var u = chat.engine.users[username],
                        elem = '<li><a data-username="'+u.username+'" class="user '+ u.features.join(' ') +'">'+u.username+'</a></li>';
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
                    for(var username in chat.engine.users) {
                        if (chat.engine.users.hasOwnProperty(username))
                            addUser(username);
                    }
                    userListUiMenu.sortUsers();
                    userListUiMenu.redraw();
                });
            });

            this.ui.find('#chat-users-btn').on('click', function(e){
                if(chat.pmcountnum > 0)
                    pmPopupUiMenu.toggle(this);
                else
                    userListUiMenu.toggle(this);
                return false;
            });
            this.ui.find('#chat-settings-btn').on('click', function(e){
                chatSettingsMenu.toggle(this);
                return false;
            });
            this.ui.find('#emoticon-btn').on('click', function(e){
                chatEmotesUiMenu.toggle(this);
                return false;
            });

            this.lines.on('mousedown', '.user-msg a.user', function(e){
                var username = $(this).closest('.user-msg').data('username');
                chat.toggleUserFocus(username);
                return false;
            });

            this.lines.on('mousedown', '.user-msg .chat-user', function(e) {
                var username1 = $(this).closest('.user-msg').data('username');
                var username2 = this.textContent.toLowerCase();
                chat.addUserFocus(username1);
                chat.toggleUserFocus(username2);
                return false;
            });

            // Bind to user input submit
            this.ui.on('submit', 'form#chat-input', function(e){
                chat.send();
                return false;
            });

            // Close all menus and perform a scroll
            this.input.on('keydown mousedown', $.proxy(function(){
                ChatMenu.closeMenus(this);
            }, this));

            // Close all menus if someone clicks on any messages
            this.output.on('mousedown', $.proxy(function(){
                ChatMenu.closeMenus(this);
                chat.clearUserFocus();
            }, this));

            var scrollNotify = chat.ui.find('#chat-scroll-notify');
            scrollNotify.on('click', function() {
                chat.scrollPlugin.updateAndScroll(true);
            });

            chat.output.debounce("scrolled", function() {
                scrollNotify.toggle(!chat.scrollPlugin.isScrolledToBottom());
            }, 100);

            chat.output.debounce("scrollend", function() {
                scrollNotify.hide();
            }, 100);

            var fnUpdateScrollValues = chat.scrollPlugin.updateScrollValues;
            chat.scrollPlugin.updateScrollValues = function() {
                fnUpdateScrollValues.call(this, arguments);
                chat.output.trigger('scrolled');
            };
            // End Scrollbar

            // The login click
            this.ui.find('#chat-login-msg a[href="/login"]').on('click', function(e){
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
            var mouseDownCoords;
            var focusTimer;
            this.lines.on('click mousedown keydown', function(e) {
                var coords = e.clientX + '-' + e.clientY;
                switch(e.type) {
                    case 'click':
                        if (mouseDownCoords !== coords)
                            return;

                        focusTimer = setTimeout(function() { chat.input.focus(); }, 500);
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
            this.lines.on('click', '.mark-as-read', function(e){
                var messageEl      = $(this).closest('.private-message'),
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
                    chat.loaded = true;
                }
            });

            return this;
        },

        addUserFocusRule: function(username){
            this.stylesheet.insertRule('.user-msg[data-username="' + username + '"]{opacity:1 !important;}', this.focusedUsers.length); // max 4294967295
            this.focusedUsers.push(username);
            this.ui.toggleClass('focus-user', this.focusedUsers.length > 0);
        },

        removeUserFocusRule: function(username, index){
            this.stylesheet.deleteRule(index);
            this.focusedUsers.splice(index, 1);
            this.ui.toggleClass('focus-user', this.focusedUsers.length > 0);
        },

        clearUserFocus: function(){
            for(var i=this.focusedUsers.length-1; i>=0; --i)
                this.removeUserFocusRule(this.focusedUsers[i], i);
            this.ui.toggleClass('focus-user', false);
        },

        addUserFocus: function(username){
            if(username && this.focusedUsers.indexOf(username) === -1)
                this.addUserFocusRule(username);
        },

        removeUserFocus: function(username){
            if(!username) return;
            username = username.toLowerCase();
            var index = this.focusedUsers.indexOf(username);
            if(index !== -1) this.removeUserFocusRule(username, index);
        },

        toggleUserFocus: function(username) {
            if(!username) return;
            username = username.toLowerCase();
            var index = this.focusedUsers.indexOf(username);
            if(index === -1)
                this.addUserFocus(username);
            else
                this.removeUserFocus(username, index);
        },

        loadBacklog: function() {
            this.backlogLoading = true;
            if(this.backlog.length == 0) {
                this.backlogLoading = false;
                return;
            }

            for (var i = 0, j = this.backlog.length; i < j; i++)
                this.engine.dispatchBacklog(this.backlog[i]);

            this.put(new ChatUIMessage('<hr/>'));
            this.scrollPlugin.updateAndScroll(true);
            this.backlogLoading = false;
        },

        // Add a message to the UI
        put: function(message, state){
            if(message instanceof ChatUserMessage){
                if(this.lastMessage && this.lastMessage.user && message.user && this.lastMessage.user.username == message.user.username)
                    message.ui = $(message.addonHtml()); //same person consecutively
                else
                    message.ui = $(message.html()); //different person

                if(message.user && this.engine.user && this.engine.user.username == message.user.username)
                    message.ui.addClass('own-msg');
            }else
                message.ui = $(message.html());

            message.ui.data('message', message);
            message.ui.appendTo(this.lines);

            if(state != undefined)
                message.status(state);

            this.lastMessage = message;
            return message;
        },

        // Add a message
        push: function(message, state){
            // Get the scroll position before adding the new line / removing old lines
            var wasScrolledBottom = this.scrollPlugin.isScrolledToBottom(),
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

            // Make sure a reset has been called at least once when the scroll should be enabled, but isnt yet
            if(this.scrollPlugin.content.scrollHeight > this.scrollPlugin.el.clientHeight && !this.scrollPlugin.isActive)
                this.scrollPlugin.reset();

            // Reset and or scroll bottom
            this.scrollPlugin.updateAndScroll(wasScrolledBottom);
            // Handle highlight / and if highlighted, notification
            this.handleHighlight(message);
            return message;
        },

        send: function(){
            var str = this.input.val().trim();
            if(str != ''){
                this.input.val('').focus();

                if(this.engine.user == null || !this.engine.user.username)
                    return this.push(new ChatErrorMessage(this.engine.errorstrings.needlogin));

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
            return this;
        },

        resize: function(){
            this.scrollPlugin.updateAndScroll(this.scrollPlugin.isScrolledToBottom());
            return this;
        },

        setupInputHistory: function(){
            var modifierpressed = false,
                chat = this;
            $(this.input).on('keyup', function(e) {

                if (e.shiftKey || e.metaKey || e.ctrlKey)
                    modifierpressed = true;

                if ((e.keyCode != 38 && e.keyCode != 40) || modifierpressed) {
                    modifierpressed = false;
                    return;
                }

                var num  = e.keyCode == 38? -1: 1; // if up arrow we subtract otherwise add

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
        },

        loadInputHistory: function(){
            try {
                this.inputhistory = JSON.parse(localStorage['inputhistory'] || '[]');
                this.setupInputHistory();
            } catch (e) {}
        },

        insertInputHistory: function(message){
            this.inputhistory.push(message);
            if (this.inputhistory.length > 20)
                this.inputhistory.shift();
            localStorage['inputhistory'] = JSON.stringify(this.inputhistory);
        },

        resolveMessage: function(data){
            for(var i=0; i<this.userMessages.length; ++i){
                if(this.userMessages[i].message == data.data){
                    var message = this.userMessages.splice(i, 1)[0];
                    message.status();
                    return message;
                }
            }
            return null;
        },

        notificationPermission: function(){
            var deferred =  $.Deferred();
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
        },

        showNotification: function(message) {
            if(Notification.permission === "granted"){
                var n = new Notification( message.user.username+' said ...', {
                    body : message.message,
                    tag  : message.timestamp.unix(),
                    icon : destiny.cdn+'/chat/img/notifyicon.png',
                    dir  : "auto"
                });
                setTimeout(n.close.bind(n), 5000);
                n.onclick = function(){
                    // todo open chat at specific line
                };
            }
        },

        loadHighlighters: function() {
            if (this.engine.user && this.engine.user.username)
                this.highlightregex.user = new RegExp("\\b@?(?:"+this.engine.user.username+")\\b", "i");

            var highlights = this.getPreference('customhighlight');
            if (highlights.length > 0){
                for (var i = highlights.length - 1; i >= 0; i--)
                    highlights[i] = highlights[i].replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
                this.highlightregex.custom = new RegExp("\\b(?:"+highlights.join("|")+")\\b", "i");
            }
        },

        renewHighlight: function(nick, dohighlight){
            if (dohighlight){
                this.lines.children('div[data-username="'+nick.toLowerCase()+'"]').addClass("highlight");
            } else {
                this.lines.children('div[data-username="'+nick.toLowerCase()+'"]').removeClass("highlight");
            }
        },

        handleHighlight: function(message){
            if (!message.user || !message.user.username || message.user.username == this.engine.user.username || !this.getPreference('highlight'))
                return false;

            if(message.user.hasFeature(destiny.UserFeatures.BOT))
                return false;

            var nicks = this.getPreference('highlightnicks');
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
        },

        setPreference: function(key, value){
            this.preferences[key].value = value;
            this.applyPreferencesCss();
            this.savePreferences();
        },

        getPreference: function(key){
            return this.preferences[key].value || null;
        },

        loadPreferences: function() {
            try {
                var preferences = JSON.parse(localStorage['chatoptions'] || '{}');
                for(var key in preferences) {
                    if (preferences.hasOwnProperty(key) && this.preferences.hasOwnProperty(key))
                        this.preferences[key].value = preferences[key];
                }
                this.applyPreferencesCss();
            } catch (ignored) {}
        },

        savePreferences: function() {
            try {
                var preferences = {};
                for(var key in this.preferences) {
                    if (this.preferences.hasOwnProperty(key))
                        preferences[key] = this.preferences[key].value;
                }
                localStorage['chatoptions'] = JSON.stringify(preferences);
            } catch (ignored) {}
        },

        applyPreferencesCss: function() {
            for(var key in this.preferences) {
                if (this.preferences.hasOwnProperty(key) && typeof this.preferences[key].value === 'boolean')
                    this.ui.toggleClass('pref-' + key, this.preferences[key].value);
            }
        },

        removeUserMessages: function(username) {
            this.lines.children('div[data-username="'+username.toLowerCase()+'"]').remove();
            this.scrollPlugin.reset();
        },

        setUnreadMessageCount: function(n){
            this.pmcountnum = Math.max(0, n);
            this.pmcount.toggleClass('hidden', !this.pmcountnum).text(this.pmcountnum);
        },

        hasFocus: function(){
            return this.input.is(':focus');
        }

    });

    ChatUser = function cls(args){
        args = args || {};
        this.nick     = args.nick || '';
        this.username = args.nick || '';
        this.features = args.features || [];
        return this;
    };
    ChatUser.prototype.hasAnyFeatures = function(/* ... */){
        for(var i=0; i<arguments.length; i++){
            if(this.features.indexOf(arguments[i]) !== -1)
                return true;
        }
        return false;
    };
    ChatUser.prototype.hasFeature = function(feature){
        return this.hasAnyFeatures(feature);
    };

    // UI MESSAGE - ability to send HTML markup to the chat
    ChatUIMessage = function(html){
        return this.init(html);
    };
    ChatUIMessage.prototype.init = function(html){
        this.message = html;
        return this;
    };
    ChatUIMessage.prototype.html = function(){
        return this.wrap(this.wrapMessage());
    };
    ChatUIMessage.prototype.wrap = function(html){
        return '<div class="ui-msg">'+html+'</div>';
    };
    ChatUIMessage.prototype.wrapMessage = function(){
        return this.message;
    };
    // END UI MESSAGE

    // BASE MESSAGE
    ChatMessage = function(message, timestamp){
        return this.init(message, timestamp);
    };
    ChatMessage.prototype.init = function(message, timestamp){
        this.message = message;
        this.timestamp = moment.utc(timestamp).local();
        this.state = null;
        this.type = 'chat';
        this.timestampformat = destiny.chat.gui.getPreference('timestampformat');
        return this;
    };
    ChatMessage.prototype.status = function(state){
        if(this.ui){
            if(state)
                this.ui.addClass(state);
            else
                this.ui.removeClass(this.state);
        }
        this.state = state;
        return this;
    };
    ChatMessage.prototype.wrapTime = function(){
        return '<time title="'+this.timestamp.format('MMMM Do YYYY, h:mm:ss a')+'" datetime="'+this.timestamp.format('MMMM Do YYYY, h:mm:ss a')+'">'+
            this.timestamp.format(this.timestampformat)+
            '</time>';
    };
    ChatMessage.prototype.wrapMessage = function(){
        return $('<span class="msg"/>').text(this.message).get(0).outerHTML;
    };
    ChatMessage.prototype.html = function(){
        return this.wrap(this.wrapTime() + ' ' + this.wrapMessage());
    };
    ChatMessage.prototype.wrap = function(content){
        return '<div class="'+this.type+'-msg">'+content+'</div>';
    };
    // END BASE MESSAGE

    // ERROR MESSAGE
    ChatErrorMessage = function(error, timestamp){
        this.init(error, timestamp);
        this.type = 'error';
        return this;
    };
    $.extend(ChatErrorMessage.prototype, ChatMessage.prototype);
    ChatErrorMessage.prototype.html = function(){
        return this.wrap(this.wrapTime() + ' <i class="icon-error"></i> ' + this.wrapMessage());
    };
    // END ERROR MESSAGE

    // INFO / HELP MESSAGE
    ChatInfoMessage = function(message, timestamp){
        this.init(message, timestamp);
        this.type = 'info';
        return this;
    };
    $.extend(ChatInfoMessage.prototype, ChatMessage.prototype);
    ChatInfoMessage.prototype.html = function(){
        return this.wrap(this.wrapTime() + ' <i class="icon-info"></i> ' + this.wrapMessage());
    };
    ChatInfoMessage.prototype.wrapMessage = function(){
        var elem     = $('<span class="msg"/>').text(this.message),
            encoded  = elem.html();

        for(var i=0; i<destiny.chat.gui.formatters.length; ++i)
            encoded = destiny.chat.gui.formatters[i].format(encoded, null, this.message);

        elem.html(encoded);
        return elem.get(0).outerHTML;
    };
    // END INFO MESSAGE

    // COMMAND MESSAGE
    ChatCommandMessage = function(message, timestamp){
        this.init(message, timestamp);
        this.type = 'command';
        return this;
    };
    $.extend(ChatCommandMessage.prototype, ChatMessage.prototype);
    ChatCommandMessage.prototype.html = function(){
        return this.wrap(this.wrapTime() + ' <i class="icon-command"></i> ' + this.wrapMessage());
    };
    // END COMMAND MESSAGE

    // STATUS MESSAGE
    ChatStatusMessage = function(message, timestamp){
        this.init(message, timestamp);
        this.type = 'status';
        return this;
    };
    $.extend(ChatStatusMessage.prototype, ChatMessage.prototype);
    ChatStatusMessage.prototype.html = function(){
        return this.wrap(this.wrapTime() + ' <i class="icon-status"></i> ' + this.wrapMessage());
    };
    // END STATUS MESSAGE

    // BROADCAST MESSAGE
    ChatBroadcastMessage = function(message, timestamp){
        this.init(message, timestamp);
        this.type = 'broadcast';
        this.user = {features: [null]}; // so that global emotes are in effect
        return this;
    };
    $.extend(ChatBroadcastMessage.prototype, ChatMessage.prototype);
    ChatBroadcastMessage.prototype.html = function(){
        return this.wrap(this.wrapTime() + ' ' + this.wrapMessage());
    };
    ChatBroadcastMessage.prototype.wrapMessage = function(){
        var elem     = $('<span class="msg"/>').text(this.message),
            encoded  = elem.html();

        for(var i=0; i<destiny.chat.gui.formatters.length; ++i)
            encoded = destiny.chat.gui.formatters[i].format(encoded, null, this.message);

        elem.html(encoded);
        return elem.get(0).outerHTML;
    };
    // END BROADCAST MESSAGE

    // USER MESSAGE
    ChatUserMessage = function(message, user, timestamp){
        this.init(message, timestamp);
        this.type = 'user';
        this.user = user;
        this.prepareMessage();
        return this;
    };
    $.extend(ChatUserMessage.prototype, ChatMessage.prototype);
    ChatUserMessage.prototype.prepareMessage = function() {
        this.isSlashMe = false;
        if (this.message.substring(0, 4) === '/me ') {
            this.isSlashMe = true;
            this.message = this.message.substring(4);
        } else if (this.message.substring(0, 2) === '//')
            this.message = this.message.substring(1);
    };
    ChatUserMessage.prototype.wrap = function(html, css) {
        if (this.user && this.user.username)
            return '<div class="'+this.type+'-msg'+((css) ? ' '+css:'')+'" data-username="'+this.user.username.toLowerCase()+'">'+html+'</div>';
        else
            return '<div class="'+this.type+'-msg'+((css) ? ' '+css:'')+'">'+html+'</div>';
    };

    ChatUserMessage.prototype.getFeatureHTML = function(user){
        var icons = '';

        if(user.hasFeature(destiny.UserFeatures.SUBSCRIBERT4))
            icons += '<i class="icon-subscribert4" title="Subscriber (T4)"/>';
        else if(user.hasFeature(destiny.UserFeatures.SUBSCRIBERT3))
            icons += '<i class="icon-subscribert3" title="Subscriber (T3)"/>';
        else if(user.hasFeature(destiny.UserFeatures.SUBSCRIBERT2))
            icons += '<i class="icon-subscribert2" title="Subscriber (T2)"/>';
        else if(user.hasFeature(destiny.UserFeatures.SUBSCRIBERT1))
            icons += '<i class="icon-subscriber" title="Subscriber (T1)"/>';
        else if(!user.hasFeature(destiny.UserFeatures.SUBSCRIBERT0) && user.hasFeature(destiny.UserFeatures.SUBSCRIBER))
            icons += '<i class="icon-subscriber" title="Subscriber (T1)"/>';

        for (var i = 0; i < user.features.length; i++) {
            switch(user.features[i]){
                case destiny.UserFeatures.SUBSCRIBERT0 :
                    icons += '<i class="icon-minitwitch" title="Twitch subscriber"/>';
                    break;
                case destiny.UserFeatures.BOT :
                    icons += '<i class="icon-bot" title="Bot"/>';
                    break;
                case destiny.UserFeatures.BOT2 :
                    icons += '<i class="icon-bot2" title="Bot"/>';
                    break;
                case destiny.UserFeatures.NOTABLE :
                    icons += '<i class="icon-notable" title="Notable"/>';
                    break;
                case destiny.UserFeatures.TRUSTED :
                    icons += '<i class="icon-trusted" title="Trusted"/>';
                    break;
                case destiny.UserFeatures.CONTRIBUTOR :
                    icons += '<i class="icon-contributor" title="Contributor"/>';
                    break;
                case destiny.UserFeatures.COMPCHALLENGE :
                    icons += '<i class="icon-compchallenge" title="Composition Winner"/>';
                    break;
                case destiny.UserFeatures.EVE :
                    icons += '<i class="icon-eve" title="Eve"/>';
                    break;
                case destiny.UserFeatures.SC2 :
                    icons += '<i class="icon-sc2" title="Starcraft 2"/>';
                    break;
                case destiny.UserFeatures.BROADCASTER :
                    icons += '<i class="icon-broadcaster" title="Broadcaster"/>';
                    break;
            }
        }

        return icons;
    };

    ChatUserMessage.prototype.wrapUser = function(user){
        return ((this.isSlashMe) ? '':this.getFeatureHTML(user)) +' <a class="user '+ user.features.join(' ') +'">' +user.username+'</a>';
    };
    ChatUserMessage.prototype.wrapMessage = function(){
        var elem     = $('<span class="msg"/>').text(this.message),
            encoded  = elem.html();

        if(this.isSlashMe)
            elem.addClass('emote');

        for(var i=0; i<destiny.chat.gui.formatters.length; ++i)
            encoded = destiny.chat.gui.formatters[i].format(encoded, this.user, this.message);

        elem.html(encoded);
        return elem.get(0).outerHTML;
    };
    ChatUserMessage.prototype.html = function(){
        return this.wrap(this.wrapTime() + ' ' + ((!this.isSlashMe) ? '' : '*') + this.wrapUser(this.user) + ((!this.isSlashMe) ? ': ' : ' ') + this.wrapMessage());
    };
    ChatUserMessage.prototype.addonHtml = function(){
        if(this.isSlashMe)
            return this.wrap(this.wrapTime() + ' *' + this.wrapUser(this.user) + ' ' + this.wrapMessage());
        return this.wrap(this.wrapTime() + ' <span class="continue">&gt;</span> ' + this.wrapMessage(), 'continue');
    };
    // END USER MESSAGE

    // PRIVATE MESSAGE
    ChatUserPrivateMessage = function(data, user, messageid, timestamp){
        this.init(data, timestamp);
        this.type = 'user';
        this.user = user;
        this.messageid = messageid;
        this.prepareMessage();
        this.isSlashMe = false; // make sure a private message is never reformatted to /me
        return this;
    };
    $.extend(ChatUserPrivateMessage.prototype, ChatUserMessage.prototype);
    ChatUserPrivateMessage.prototype.wrap = function(html, css) {
        return '' +
            '<div class="'+this.type+'-msg'+((css) ? ' '+css:'')+' private-message" data-messageid="'+this.messageid+'" data-username="'+this.user.username.toLowerCase()+'">'+
            html+
            '<span class="message-actions">'+
            '<a href="#" class="mark-as-read">Mark as read <i class="fa fa-check-square-o"></i></a>'+
            '</span>'+
            '<i class="speech-arrow"></i>'+
            '</div>';
    };
    ChatUserPrivateMessage.prototype.wrapUser = function(user){
        return ' <i class="icon-mail-send" title="Received Message"></i> <a class="user">' +user.username+'</a>';
    };
    // END PRIVATE MESSAGE

    // EMOTE COUNT
    ChatEmoteMessage = function(emote, timestamp){
        this.init(emote, timestamp);
        this.emotecount = 1;
        this.emotecountui = null;
        return this;
    };
    $.extend(ChatEmoteMessage.prototype, ChatMessage.prototype);
    ChatEmoteMessage.prototype.getEmoteCountLabel = function(){
        return "<i class='count'>" + this.emotecount + "</i><i class='x'>X</i> C-C-C-COMBO";
    };
    ChatEmoteMessage.prototype.html = function(){
        return this.wrap(this.wrapTime() + ' ' + this.wrapMessage() + '<span class="emotecount">'+ this.getEmoteCountLabel() +'<span>');
    };
    ChatEmoteMessage.prototype.wrapMessage = function(){
        var elem     = $('<span class="msg"/>').text(this.message),
            encoded  = elem.html();

        for(var i=0; i<destiny.chat.gui.formatters.length; ++i)
            encoded = destiny.chat.gui.formatters[i].format(encoded, null, this.message);

        elem.html(encoded);
        return elem.get(0).outerHTML;
    };
    ChatEmoteMessage.prototype.incEmoteCount = function(){
        ++this.emotecount;

        var stepClass = '';
        if(this.emotecount >= 50)
            stepClass = ' x50';
        else if(this.emotecount >= 30)
            stepClass = ' x30';
        else if(this.emotecount >= 20)
            stepClass = ' x20';
        else if(this.emotecount >= 10)
            stepClass = ' x10';
        else if(this.emotecount >= 5)
            stepClass = ' x5';

        if(this.emotecountui == null)
            this.emotecountui = this.ui.find('.emotecount');

        this.emotecountui.detach().attr('class', 'emotecount' + stepClass).html(this.getEmoteCountLabel()).appendTo(this.ui);
    };
    // END EMOTE COUNT

})(jQuery);
