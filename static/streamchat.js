webpackJsonp([2],{

/***/ 0:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	/* global $, document, window */

	__webpack_require__(70);
	__webpack_require__(65);
	__webpack_require__(71);
	__webpack_require__(69);
	__webpack_require__(126);
	__webpack_require__(496);

	window.setInterval(function () {
	    return $.ajax({ url: '/ping', method: 'get' });
	}, 10 * 60 * 1000); // keep connection alive

	window.destiny = { loglevel: 0 };
	window.destiny.chat = function () {
	    var chat = new (__webpack_require__(41)['default'])();
	    chat.init(null, null);
	    return chat;
	}();
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 16:
/***/ function(module, exports) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});
	exports.default = {
	    PROTECTED: 'protected',
	    SUBSCRIBER: 'subscriber',
	    SUBSCRIBERT0: 'flair9',
	    SUBSCRIBERT1: 'flair13',
	    SUBSCRIBERT2: 'flair1',
	    SUBSCRIBERT3: 'flair3',
	    SUBSCRIBERT4: 'flair8',
	    VIP: 'vip',
	    MODERATOR: 'moderator',
	    ADMIN: 'admin',
	    BROADCASTER: 'flair12',
	    BOT: 'bot',
	    BOT2: 'flair11',
	    NOTABLE: 'flair2',
	    TRUSTED: 'flair4',
	    CONTRIBUTOR: 'flair5',
	    COMPCHALLENGE: 'flair6',
	    EVE: 'flair7',
	    SC2: 'flair10'
	};

/***/ },

/***/ 20:
/***/ function(module, exports) {

	"use strict";

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var ChatFormatter = function () {
	    function ChatFormatter(chat) {
	        _classCallCheck(this, ChatFormatter);

	        this.chat = chat;
	    }

	    _createClass(ChatFormatter, [{
	        key: "format",
	        value: function format(str, user) {
	            return str;
	        }
	    }]);

	    return ChatFormatter;
	}();

	exports.default = ChatFormatter;

/***/ },

/***/ 21:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _scroll = __webpack_require__(64);

	var _scroll2 = _interopRequireDefault(_scroll);

	var _emitter = __webpack_require__(42);

	var _emitter2 = _interopRequireDefault(_emitter);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $ */

	var ChatMenu = function (_EventEmitter) {
	    _inherits(ChatMenu, _EventEmitter);

	    function ChatMenu(ui, chat) {
	        _classCallCheck(this, ChatMenu);

	        var _this = _possibleConstructorReturn(this, (ChatMenu.__proto__ || Object.getPrototypeOf(ChatMenu)).call(this));

	        _this.ui = $(ui);
	        _this.chat = chat;
	        _this.btn = null;
	        _this.visible = false;
	        _this.shown = false;
	        _this.ui.find('.scrollable').get().forEach(function (el) {
	            return _this.scrollPlugin = new _scroll2.default(el);
	        });
	        _this.ui.on('click', '.close', _this.hide.bind(_this));
	        return _this;
	    }

	    _createClass(ChatMenu, [{
	        key: 'show',
	        value: function show(btn) {
	            if (!this.visible) {
	                this.visible = true;
	                this.btn = $(btn);
	                this.shown = true;
	                this.btn.addClass('active');
	                this.ui.addClass('active');
	                this.redraw();
	                this.emit('show');
	            }
	        }
	    }, {
	        key: 'hide',
	        value: function hide() {
	            if (this.visible) {
	                this.visible = false;
	                this.btn.removeClass('active');
	                this.ui.removeClass('active');
	                this.emit('hide');
	            }
	        }
	    }, {
	        key: 'toggle',
	        value: function toggle(btn) {
	            var wasVisible = this.visible;
	            ChatMenu.closeMenus(this.chat);
	            if (!wasVisible) this.show(btn);
	        }
	    }, {
	        key: 'redraw',
	        value: function redraw() {
	            if (this.scrollPlugin) this.scrollPlugin.reset();
	        }
	    }], [{
	        key: 'closeMenus',
	        value: function closeMenus(chat) {
	            chat.menus.forEach(function (m) {
	                return m.hide();
	            });
	        }
	    }]);

	    return ChatMenu;
	}(_emitter2.default);

	exports.default = ChatMenu;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 29:
/***/ function(module, exports) {

	"use strict";

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	/* global window */

	var Logger = function () {
	    function Logger(context) {
	        _classCallCheck(this, Logger);

	        this.console = window.console;
	        this.context = context;
	    }

	    _createClass(Logger, [{
	        key: "debug",
	        value: function debug() {
	            var _console;

	            for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
	                args[_key] = arguments[_key];
	            }

	            if (window.destiny.loglevel >= 2) (_console = this.console).debug.apply(_console, [this.context].concat(args));
	        }
	    }, {
	        key: "log",
	        value: function log() {
	            var _console2;

	            for (var _len2 = arguments.length, args = Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	                args[_key2] = arguments[_key2];
	            }

	            if (window.destiny.loglevel >= 1) (_console2 = this.console).log.apply(_console2, [this.context].concat(args));
	        }
	    }, {
	        key: "info",
	        value: function info() {
	            var _console3;

	            for (var _len3 = arguments.length, args = Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
	                args[_key3] = arguments[_key3];
	            }

	            if (window.destiny.loglevel >= 0) (_console3 = this.console).info.apply(_console3, [this.context].concat(args));
	        }
	    }, {
	        key: "error",
	        value: function error() {
	            var _console4;

	            for (var _len4 = arguments.length, args = Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
	                args[_key4] = arguments[_key4];
	            }

	            if (window.destiny.loglevel >= 0) (_console4 = this.console).error.apply(_console4, [this.context].concat(args));
	        }
	    }], [{
	        key: "make",
	        value: function make(context) {
	            return new Logger("[" + context.constructor.name + "]");
	        }
	    }]);

	    return Logger;
	}();

	exports.default = Logger;

/***/ },

/***/ 41:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }(); /* global $, window, document */

	var _debounce = __webpack_require__(124);

	var _debounce2 = _interopRequireDefault(_debounce);

	var _emitter = __webpack_require__(42);

	var _emitter2 = _interopRequireDefault(_emitter);

	var _source = __webpack_require__(100);

	var _source2 = _interopRequireDefault(_source);

	var _menu = __webpack_require__(21);

	var _menu2 = _interopRequireDefault(_menu);

	var _user = __webpack_require__(101);

	var _user2 = _interopRequireDefault(_user);

	var _message = __webpack_require__(43);

	var _message2 = _interopRequireDefault(_message);

	var _user3 = __webpack_require__(44);

	var _user4 = _interopRequireDefault(_user3);

	var _pm = __webpack_require__(98);

	var _pm2 = _interopRequireDefault(_pm);

	var _emote = __webpack_require__(97);

	var _emote2 = _interopRequireDefault(_emote);

	var _autocomplete = __webpack_require__(84);

	var _autocomplete2 = _interopRequireDefault(_autocomplete);

	var _history = __webpack_require__(92);

	var _history2 = _interopRequireDefault(_history);

	var _scroll = __webpack_require__(64);

	var _scroll2 = _interopRequireDefault(_scroll);

	var _user5 = __webpack_require__(96);

	var _user6 = _interopRequireDefault(_user5);

	var _pm3 = __webpack_require__(94);

	var _pm4 = _interopRequireDefault(_pm3);

	var _emote3 = __webpack_require__(93);

	var _emote4 = _interopRequireDefault(_emote3);

	var _settings = __webpack_require__(95);

	var _settings2 = _interopRequireDefault(_settings);

	var _focus = __webpack_require__(85);

	var _focus2 = _interopRequireDefault(_focus);

	var _highlight = __webpack_require__(91);

	var _highlight2 = _interopRequireDefault(_highlight);

	var _store = __webpack_require__(45);

	var _store2 = _interopRequireDefault(_store);

	var _url = __webpack_require__(90);

	var _url2 = _interopRequireDefault(_url);

	var _emote5 = __webpack_require__(86);

	var _emote6 = _interopRequireDefault(_emote5);

	var _mention = __webpack_require__(89);

	var _mention2 = _interopRequireDefault(_mention);

	var _greentext = __webpack_require__(87);

	var _greentext2 = _interopRequireDefault(_greentext);

	var _html = __webpack_require__(88);

	var _html2 = _interopRequireDefault(_html);

	var _features = __webpack_require__(16);

	var _features2 = _interopRequireDefault(_features);

	var _log = __webpack_require__(29);

	var _log2 = _interopRequireDefault(_log);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var emotes = __webpack_require__(146);
	var location = window.location;

	var Chat = function () {
	    function Chat() {
	        _classCallCheck(this, Chat);

	        this.log = _log2.default.make(this);
	        this.uri = (location.protocol === 'https:' ? 'wss' : 'ws') + '://' + location.host + '/ws';
	        this.reconnect = true;
	        this.connected = false;
	        this.showstarthint = true;
	        this.maxlines = 250;
	        this.users = new Map();
	        this.unresolved = [];
	        this.backlogLoading = false;
	        this.pmcountnum = 0;
	        this.nickregex = /^[a-zA-Z0-9_]{3,20}$/;
	        this.lastMessage = null;
	        this.emoticons = new Set(emotes['destiny']);
	        this.twitchemotes = new Set(emotes['twitch']);
	        this.shownhints = new Set();
	        this.formatters = [];
	        this.ignoreregex = null;
	        this.ignoring = new Set();
	        this.settings = new Map([['showtime', false], ['hideflairicons', false], ['timestampformat', 'HH:mm'], ['maxlines', this.maxlines], ['allowNotifications', false], ['highlight', true], ['customhighlight', []], ['highlightnicks', []]]);
	        this.errorstrings = new Map([['unknown', 'Unknown error, this usually indicates an internal problem :('], ['nopermission', 'You do not have the required permissions to use that'], ['protocolerror', 'Invalid or badly formatted'], ['needlogin', 'You have to be logged in to use that'], ['invalidmsg', 'The message was invalid'], ['throttled', 'Throttled! You were trying to send messages too fast'], ['duplicate', 'The message is identical to the last one you sent'], ['muted', 'You are muted (subscribing auto-removes mutes). Check your profile for more information.'], ['submode', 'The channel is currently in subscriber only mode'], ['needbanreason', 'Providing a reason for the ban is mandatory'], ['banned', 'You have been banned (subscribing auto-removes non-permanent bans), disconnecting. Check your profile for more information.'], ['requiresocket', 'This chat requires WebSockets'], ['toomanyconnections', 'Only 5 concurrent connections allowed'], ['socketerror', 'Error contacting server'], ['privmsgbanned', 'Cannot send private messages while banned'], ['privmsgaccounttooyoung', 'Your account is too recent to send private messages'], ['notfound', 'The user was not found']]);
	        this.hints = new Map([['hint', 'Type in /hint for more hints'], ['slashhelp', 'Type in /help for more advanced features, like modifying the scrollback size'], ['tabcompletion', 'Use the tab key to auto-complete usernames and emotes (for user only completion prepend a @ or press shift)'], ['hoveremotes', 'Hovering your mouse over an emote will show you the emote code'], ['highlight', 'Chat messages containing your username will be highlighted'], ['notify', 'Use /msg <username> to send a private message to someone'], ['ignoreuser', 'Use /ignore <username> to hide messages from pesky chatters'], ['mutespermanent', 'Mutes are never persistent, don\'t worry it will pass!']]);

	        this.ui = $('#chat');
	        this.css = $('#chat-styles')[0]['sheet'];
	        this.output = $('#chat-output');
	        this.lines = $('#chat-lines');
	        this.input = $('#chat-input-control');
	        this.scrollnotify = $('#chat-scroll-notify');
	        this.pmcount = $('#chat-pm-count');

	        this.source = new _source2.default(this, this.uri);
	        this.control = new EvessssntEmitter(this);
	    }

	    _createClass(Chat, [{
	        key: 'init',
	        value: function init(user, history) {
	            var _this = this;

	            this.user = this.addUser(user || {});
	            this.settings = new Map([].concat(_toConsumableArray(this.settings), _toConsumableArray(_store2.default.read('chat.settings') || [])));
	            this.ignoring = new Set([].concat(_toConsumableArray(_store2.default.read('chat.ignoring') || [])));
	            this.shownhints = new Set([].concat(_toConsumableArray(_store2.default.read('chat.shownhints') || [])));

	            // Socket events
	            this.source.on('PING', function (data) {
	                return _this.source.send('PONG', data);
	            });
	            this.source.on('OPEN', function (data) {
	                return _this.connected = true;
	            });
	            this.source.on('REFRESH', function (data) {
	                return window.location.reload(false);
	            });
	            this.source.on('DISPATCH', function (data) {
	                return _this.onDISPATCH(data);
	            });
	            this.source.on('CLOSE', function (data) {
	                return _this.onCLOSE(data);
	            });
	            this.source.on('NAMES', function (data) {
	                return _this.onNAMES(data);
	            });
	            this.source.on('JOIN', function (data) {
	                return _this.onJOIN(data);
	            });
	            this.source.on('QUIT', function (data) {
	                return _this.onQUIT(data);
	            });
	            this.source.on('PRIVMSG', function (data) {
	                return _this.onPRIVMSG(data);
	            });
	            this.source.on('MSG', function (data) {
	                return _this.onMSG(data);
	            });
	            this.source.on('MUTE', function (data) {
	                return _this.onMUTE(data);
	            });
	            this.source.on('UNMUTE', function (data) {
	                return _this.onUNMUTE(data);
	            });
	            this.source.on('BAN', function (data) {
	                return _this.onBAN(data);
	            });
	            this.source.on('UNBAN', function (data) {
	                return _this.onUNBAN(data);
	            });
	            this.source.on('ERR', function (data) {
	                return _this.onERR(data);
	            });
	            this.source.on('SUBONLY', function (data) {
	                return _this.onSUBONLY(data);
	            });
	            this.source.on('BROADCAST', function (data) {
	                return _this.onBROADCAST(data);
	            });
	            this.source.on('PRIVMSGSENT', function (data) {
	                return _this.onPRIVMSGSENT(data);
	            });

	            // User actions
	            this.control.on('SEND', function (data) {
	                return _this.cmdSEND(data);
	            });
	            this.control.on('HINT', function (data) {
	                return _this.cmdHINT(data);
	            });
	            this.control.on('EMOTES', function (data) {
	                return _this.cmdEMOTES(data);
	            });
	            this.control.on('HELP', function (data) {
	                return _this.cmdHELP(data);
	            });
	            this.control.on('ME', function (data) {
	                return _this.cmdME(data);
	            });
	            this.control.on('MESSAGE', function (data) {
	                return _this.cmdWHISPER(data);
	            });
	            this.control.on('MSG', function (data) {
	                return _this.cmdWHISPER(data);
	            });
	            this.control.on('WHISPER', function (data) {
	                return _this.cmdWHISPER(data);
	            });
	            this.control.on('W', function (data) {
	                return _this.cmdWHISPER(data);
	            });
	            this.control.on('TELL', function (data) {
	                return _this.cmdWHISPER(data);
	            });
	            this.control.on('T', function (data) {
	                return _this.cmdWHISPER(data);
	            });
	            this.control.on('NOTIFY', function (data) {
	                return _this.cmdWHISPER(data);
	            });
	            this.control.on('IGNORE', function (data) {
	                return _this.cmdIGNORE(data);
	            });
	            this.control.on('UNIGNORE', function (data) {
	                return _this.cmdUNIGNORE(data);
	            });
	            this.control.on('MUTE', function (data) {
	                return _this.cmdMUTE(data);
	            });
	            this.control.on('BAN', function (data) {
	                return _this.cmdBAN(data);
	            });
	            this.control.on('IPBAN', function (data) {
	                return _this.cmdBAN(data);
	            });
	            this.control.on('UNMUTE', function (data) {
	                return _this.cmdUNBAN(data);
	            });
	            this.control.on('UNBAN', function (data) {
	                return _this.cmdUNBAN(data);
	            });
	            this.control.on('UNBAN', function (data) {
	                return _this.cmdUNBAN(data);
	            });
	            this.control.on('SUBONLY', function (data) {
	                return _this.cmdSUBONLY(data);
	            });
	            this.control.on('MAXLINES', function (data) {
	                return _this.cmdMAXLINES(data);
	            });
	            this.control.on('UNHIGHLIGHT', function (data) {
	                return _this.cmdTOGGLEHIGHLIGHT(data);
	            });
	            this.control.on('HIGHLIGHT', function (data) {
	                return _this.cmdTOGGLEHIGHLIGHT(data);
	            });
	            this.control.on('TIMESTAMPFORMAT', function (data) {
	                return _this.cmdTIMESTAMPFORMAT(data);
	            });
	            this.control.on('BROADCAST', function (data) {
	                return _this.cmdBROADCAST(data);
	            });

	            // Message formatters
	            this.formatters.push(new _html2.default(this));
	            this.formatters.push(new _url2.default(this));
	            this.formatters.push(new _emote6.default(this));
	            this.formatters.push(new _mention2.default(this));
	            this.formatters.push(new _greentext2.default(this));

	            this.scrollPlugin = new _scroll2.default(this.output, {
	                sliderMinHeight: 40,
	                tabIndex: 1
	            });

	            this.autocomplete = new _autocomplete2.default(this);
	            this.emoticons.forEach(function (emote) {
	                return _this.autocomplete.addEmote(emote);
	            });
	            this.twitchemotes.forEach(function (emote) {
	                return _this.autocomplete.addEmote(emote);
	            });

	            this.inputhistory = new _history2.default(this);
	            this.highlighter = new _highlight2.default(this);
	            this.userfocus = new _focus2.default(this, this.css);

	            this.menus = new Map([['settings', new _settings2.default($('#chat-settings'), this)], ['emotes', new _emote4.default($('#chat-emote-list'), this)], ['users', new _user6.default($('#chat-user-list'), this)], ['pm', new _pm4.default($('#chat-pm-notification'), this)]]);

	            this.toolbuttons = new Map([['users', $('#chat-users-btn')], ['settings', $('#chat-settings-btn')], ['emoticon', $('#emoticon-btn')]]);

	            // Set pm count on startup
	            this.setUnreadMessageCount(this.pmcountnum);
	            this.updateSettingsCss();
	            this.updateIgnoreRegex();

	            // Tool buttons
	            this.toolbuttons.get('settings').on('click', function (e) {
	                return _this.menus.get('settings').toggle(e.target);
	            });
	            this.toolbuttons.get('emoticon').on('click', function (e) {
	                return _this.menus.get('emotes').toggle(e.target);
	            });
	            this.toolbuttons.get('users').on('click', function (e) {
	                return _this.menus.get(_this.pmcountnum > 0 ? 'pm' : 'users').toggle(e.target);
	            });

	            // The user input field
	            this.ui.on('submit', function (e) {
	                e.preventDefault();
	                e.stopPropagation();
	                _this.control.emit('SEND', _this.input.val().toString().trim());
	                _this.input.val('').focus();
	            });

	            // On update show/hide the scroll notify ui
	            this.output.on('update', (0, _debounce2.default)(function () {
	                return _this.scrollnotify.toggle(!_this.scrollPlugin.isPinned());
	            }, 100));

	            // When you click the scroll notify ui ping the scroll to the bottom
	            this.scrollnotify.on('click', function () {
	                return _this.scrollPlugin.updateAndPin(true);
	            });

	            // Interaction with the mouse down in the chat lines
	            // Chat stops scrolling if the mouse is down
	            // resumes scrolling after X ms, remembers if the chat was pinned.
	            this.mousedown = false;
	            this.waspinned = true;
	            this.waitingtopin = false;
	            var delayedpin = (0, _debounce2.default)(function () {
	                if (_this.waspinned && !_this.mousedown) {
	                    _this.scrollPlugin.updateAndPin(true);
	                    _this.log.debug('Unfreeze scrolling.');
	                }
	                _this.waitingtopin = false;
	            }, 750);
	            this.output.on('mouseup', function () {
	                _this.mousedown = false;
	                _this.waitingtopin = true;
	                delayedpin();
	            });
	            this.output.on('mousedown', function () {
	                _this.mousedown = true;
	                if (!_this.waitingtopin) {
	                    _this.waspinned = _this.scrollPlugin.isPinned();
	                    _this.log.debug('Freeze scrolling.', _this.waspinned);
	                }
	                _menu2.default.closeMenus(_this); // todo move out
	            });

	            // On window resize, update scroll
	            var waspinnedbeforeresize = null;
	            var isresizing = false;
	            var delayedresizepin = (0, _debounce2.default)(function () {
	                _this.scrollPlugin.updateAndPin(waspinnedbeforeresize);
	                isresizing = false;
	            }, 300);
	            $(window).on('resize', function () {
	                if (!isresizing) {
	                    waspinnedbeforeresize = _this.scrollPlugin.isPinned();
	                    isresizing = true;
	                }
	                delayedresizepin();
	            });

	            // Must login click
	            $('#chat-login-msg').on('click', 'a', function (e) {
	                e.preventDefault();
	                try {
	                    if (window.self !== window.top) {
	                        window.parent.location.href = $(e.target).attr('href') + '?follow=' + encodeURIComponent(window.parent.location.pathname);
	                        return;
	                    }
	                } catch (ignored) {}
	                window.location.href = $(e.target).attr('href') + '?follow=' + encodeURIComponent(window.location.pathname);
	            });

	            // Load backlog
	            this.backlogLoading = true;
	            history.forEach(function (line) {
	                return _this.source.parseAndDispatch({ data: line });
	            });
	            if (history.length > 0) this.push(_message2.default.uiMessage('<hr/>'));
	            this.scrollPlugin.updateAndPin(true);
	            this.backlogLoading = false;

	            // Connect
	            this.push(_message2.default.statusMessage('Connecting...'));
	            this.source.connect();
	        }
	    }, {
	        key: 'sendCommand',
	        value: function sendCommand(command) {
	            var payload = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	            var parts = (payload || '').match(/([^ ]+)/g);
	            this.log.log(command, '->', parts);
	            this.control.emit(command, parts || []);
	        }
	    }, {
	        key: 'addUser',
	        value: function addUser(data) {
	            var user = this.users.get(data.nick);
	            if (!user) {
	                user = new _user2.default(data);
	                this.users.set(data.nick, user);
	            } else if (data.hasOwnProperty('features') && !Chat.isArraysEqual(data.features, user.features)) {
	                user.features = data.features;
	            }
	            return user;
	        }
	    }, {
	        key: 'onDISPATCH',
	        value: function onDISPATCH() {
	            var _this2 = this;

	            var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	            if ((typeof data === 'undefined' ? 'undefined' : _typeof(data)) === 'object') {
	                (function () {
	                    var add = function add(_ref) {
	                        var nick = _ref.nick,
	                            ignored = _ref.ignored;
	                        return _this2.autocomplete.toggleNick(nick, ignored);
	                    };
	                    if (data.hasOwnProperty('nick')) {
	                        add(_this2.addUser(data));
	                    }
	                    if (data.hasOwnProperty('users')) {
	                        data.users.forEach(function (u) {
	                            return add(_this2.addUser(u));
	                        });
	                    }
	                })();
	            }
	        }
	    }, {
	        key: 'onCLOSE',
	        value: function onCLOSE() {
	            var _this3 = this;

	            var wasconnected = this.connected;
	            this.connected = false;
	            if (this.reconnect) {
	                var rand = wasconnected ? Math.floor(Math.random() * (3000 - 501 + 1)) + 501 : Math.floor(Math.random() * (30000 - 5000 + 1)) + 5000;
	                setTimeout(function () {
	                    return _this3.source.connect();
	                }, rand);
	                this.push(_message2.default.statusMessage('Disconnected... reconnecting in ' + Math.round(rand / 1000) + ' seconds'));
	            }
	        }
	    }, {
	        key: 'onNAMES',
	        value: function onNAMES(data) {
	            this.push(_message2.default.statusMessage('Connected. Server connections: ' + data['connectioncount']));
	            if (this.showstarthint) {
	                this.showstarthint = false;
	                this.sendCommand('HINT');
	            }
	        }
	    }, {
	        key: 'onJOIN',
	        value: function onJOIN(data) {}
	    }, {
	        key: 'onQUIT',
	        value: function onQUIT(data) {
	            if (this.users.has(data.nick)) {
	                delete this.users.delete(data.nick);
	                this.autocomplete.removeNick(data.nick);
	            }
	        }
	    }, {
	        key: 'onMSG',
	        value: function onMSG(data) {
	            var text = (data.data.substring(0, 4) === '/me ' ? data.data.substring(4) : data.data).trim();
	            var isemote = this.emoticons.has(text) || this.twitchemotes.has(text);
	            if (isemote && this.lastMessage !== null && this.lastMessage.message === text) {
	                if (this.lastMessage instanceof _emote2.default) {
	                    this.lastMessage.incEmoteCount();
	                } else {
	                    this.lastMessage.ui.remove();
	                    this.push(new _emote2.default(text, data.timestamp, 2));
	                }
	            } else if (!this.resolveMessage(data)) {
	                this.push(new _user4.default(data.data, this.users.get(data.nick), data.timestamp));
	            }
	        }
	    }, {
	        key: 'onMUTE',
	        value: function onMUTE(data) {
	            var suppressednick = data.data;
	            if (this.user.username.toLowerCase() === data.data.toLowerCase()) suppressednick = 'You have been';else if (!this.user.hasAnyFeatures(_features2.default.SUBSCRIBERT3, _features2.default.SUBSCRIBERT4, _features2.default.SUBSCRIBERT2, _features2.default.ADMIN, _features2.default.MODERATOR)) this.removeMessageByUsername(data.data);

	            this.push(_message2.default.commandMessage(suppressednick + ' muted by ' + data.nick, data.timestamp));
	        }
	    }, {
	        key: 'onUNMUTE',
	        value: function onUNMUTE(data) {
	            var suppressednick = data.data;
	            if (this.user.username.toLowerCase() === data.data.toLowerCase()) suppressednick = 'You have been';

	            this.push(_message2.default.commandMessage(suppressednick + ' unmuted by ' + data.nick, data.timestamp));
	        }
	    }, {
	        key: 'onBAN',
	        value: function onBAN(data) {
	            // data.data is the nick which has been banned, no info about duration
	            var suppressednick = data.data;
	            if (this.user.username.toLowerCase() === suppressednick.toLowerCase()) {
	                suppressednick = 'You have been';
	                this.ui.addClass('banned');
	            } else if (!this.user.hasAnyFeatures(_features2.default.SUBSCRIBERT3, _features2.default.SUBSCRIBERT4, _features2.default.SUBSCRIBERT2, _features2.default.ADMIN, _features2.default.MODERATOR)) this.removeMessageByUsername(data.data);
	            this.push(_message2.default.commandMessage(suppressednick + ' banned by ' + data.nick, data.timestamp));
	        }
	    }, {
	        key: 'onUNBAN',
	        value: function onUNBAN(data) {
	            var suppressednick = data.data;
	            if (this.user.username.toLowerCase() === data.data.toLowerCase()) {
	                suppressednick = 'You have been';
	                this.ui.removeClass('banned');
	            }
	            this.push(_message2.default.commandMessage(suppressednick + ' unbanned by ' + data.nick, data.timestamp));
	        }
	    }, {
	        key: 'onERR',
	        value: function onERR(data) {
	            this.reconnect = data !== 'toomanyconnections' && data !== 'banned';
	            var errorString = this.errorstrings.has(data) ? this.errorstrings.get(data) : data;
	            this.push(_message2.default.errorMessage(errorString));
	        }
	    }, {
	        key: 'onSUBONLY',
	        value: function onSUBONLY(data) {
	            var submode = data.data === 'on' ? 'enabled' : 'disabled';
	            this.push(_message2.default.commandMessage('Subscriber only mode ' + submode + ' by ' + data.nick, data.timestamp));
	        }
	    }, {
	        key: 'onBROADCAST',
	        value: function onBROADCAST(data) {
	            var _this4 = this;

	            if (this.backlogLoading) return;
	            if (data.data.substring(0, 9) === 'redirect:') {
	                (function () {
	                    var url = data.data.substring(9);
	                    setTimeout(function () {
	                        // try redirecting the parent window too if possible
	                        if (window.parent) window.parent.location.href = url;else window.location.href = url;
	                    }, 5000);
	                    _this4.push(_message2.default.broadcastMessage('Redirecting in 5 seconds to ' + url, data.timestamp));
	                })();
	            } else {
	                this.push(_message2.default.broadcastMessage(data.data, data.timestamp));
	            }
	        }
	    }, {
	        key: 'onPRIVMSGSENT',
	        value: function onPRIVMSGSENT() {
	            this.push(_message2.default.infoMessage('Your message has been sent!'));
	        }
	    }, {
	        key: 'onPRIVMSG',
	        value: function onPRIVMSG(data) {
	            var user = this.users.get(data.nick);
	            if (user && !this.shouldIgnoreUser(user.username)) {
	                this.setUnreadMessageCount(this.pmcountnum + 1);
	                this.push(new _pm2.default(data.data, user, data.messageid, data.timestamp));
	            }
	        }
	    }, {
	        key: 'cmdSEND',
	        value: function cmdSEND(str) {
	            var normalizedstr = str.toLowerCase();
	            if (normalizedstr !== '' && normalizedstr !== '/me' && normalizedstr !== '/me ') {

	                if (this.user === null || !this.user.username) return this.push(_message2.default.errorMessage(this.errorstrings.get('needlogin')));

	                if (/^\/[^\/|me]/i.test(str)) {

	                    // Command message
	                    var command = str.split(' ', 1)[0];
	                    this.sendCommand(command.substring(1).toUpperCase(), // remove the leading /
	                    str.substring(command.length + 1) // the rest of the string
	                    );
	                } else {

	                    var text = (normalizedstr.substring(0, 4) === '/me ' ? str.substring(4) : str).trim();
	                    if (this.isEmote(text)) {

	                        // Emoticon combo
	                        // If this is an isemote spam, emit the message but don't add the line immediately
	                        this.source.send('MSG', { data: str });
	                    } else {

	                        // Normal text message
	                        // We add the message to the gui immediately
	                        // But we will also get the MSG event, so we need to make sure we dont add the message to the gui again.
	                        // We do this by storing the message in the unresolved array
	                        // The onMSG then looks in the unresolved array for the message using the nick + message
	                        // If found, the message is not added to the gui, removed from the unresolved array and the message.resolve method is run on the message
	                        var message = new _user4.default(str, this.user);
	                        this.push(message);
	                        this.unresolved.unshift(message);
	                        this.source.send('MSG', { data: str });
	                    }
	                }

	                this.inputhistory.add(str);
	                this.autocomplete.markLastComplete();
	            }
	        }
	    }, {
	        key: 'cmdEMOTES',
	        value: function cmdEMOTES() {
	            this.push(_message2.default.infoMessage('Available emoticons: ' + this.emoticons.join(', ') + ' (www.destiny.gg/emotes)'));
	        }
	    }, {
	        key: 'cmdHELP',
	        value: function cmdHELP() {
	            this.push(_message2.default.infoMessage("Available commands: /emotes /me /msg /ignore (without arguments to list the nicks ignored) /unignore /highlight (highlights target nicks messages for easier visibility) /unhighlight /maxlines /mute /unmute /subonly /ban /ipban /unban (also unbans ip bans) /timestampformat"));
	        }
	    }, {
	        key: 'cmdHINT',
	        value: function cmdHINT() {
	            var i = -1,
	                key = null;
	            var keys = [].concat(_toConsumableArray(this.hints.keys()));
	            while (true) {
	                ++i;
	                if (i >= this.hints.size) {
	                    key = keys[0];
	                    this.shownhints.clear();
	                    this.shownhints.add(key);
	                    break;
	                }
	                key = keys[i];
	                if (!this.shownhints.has(key)) {
	                    this.shownhints.add(key);
	                    break;
	                }
	            }
	            _store2.default.write('chat.shownhints', this.shownhints);
	            this.push(_message2.default.infoMessage(this.hints.get(key)));
	        }
	    }, {
	        key: 'cmdME',
	        value: function cmdME(parts) {
	            this.source.send('MSG', { data: parts.join(' ') });
	        }
	    }, {
	        key: 'cmdWHISPER',
	        value: function cmdWHISPER(parts) {
	            if (!parts[0] || !this.nickregex.test(parts[0].toLowerCase())) this.push(_message2.default.errorMessage('Invalid nick - /msg nick message'));else if (parts[0].toLowerCase() === this.user.username.toLowerCase()) this.push(_message2.default.errorMessage('Cannot send a message to yourself'));else this.source.send('PRIVMSG', {
	                nick: parts[0],
	                data: parts.slice(1, parts.length).join(' ')
	            });
	        }
	    }, {
	        key: 'cmdIGNORE',
	        value: function cmdIGNORE(parts) {
	            var username = parts[0] || null;
	            if (!username) {
	                if (this.ignoring.size <= 0) {
	                    this.push(_message2.default.infoMessage('Your ignore list is empty'));
	                } else {
	                    this.push(_message2.default.infoMessage('Ignoring the following people: ' + Array.from(this.ignoring.values()).join(', ')));
	                }
	            } else if (!this.nickregex.test(username)) {
	                this.push(_message2.default.infoMessage('Invalid nick - /ignore nick'));
	            } else {
	                this.ignoreNick(username, true);
	                this.removeMessageByUsername(username);
	                this.push(_message2.default.statusMessage('Ignoring ' + username));
	            }
	        }
	    }, {
	        key: 'cmdUNIGNORE',
	        value: function cmdUNIGNORE(parts) {
	            var username = parts[0] || null;
	            if (!username || !this.nickregex.test(username)) {
	                this.push(_message2.default.errorMessage('Invalid nick - /ignore nick'));
	            } else {
	                this.ignoreNick(username, false);
	                this.push(_message2.default.statusMessage(username + ' has been removed from your ignore list'));
	            }
	        }
	    }, {
	        key: 'cmdMUTE',
	        value: function cmdMUTE(parts) {
	            if (!parts[0]) {
	                this.push(_message2.default.infoMessage('Usage: /mute nick[ time]'));
	            } else if (!this.nickregex.test(parts[0])) {
	                this.push(_message2.default.infoMessage('Invalid nick - /mute nick[ time]'));
	            } else {
	                var duration = parts[1] ? Chat.parseTimeInterval(parts[1]) : null;
	                if (duration && duration > 0) {
	                    this.source.send('MUTE', { data: parts[0], duration: duration });
	                } else {
	                    this.source.send('MUTE', { data: parts[0] });
	                }
	            }
	        }
	    }, {
	        key: 'cmdBAN',
	        value: function cmdBAN(parts, command) {
	            if (parts.length < 3) {
	                this.push(_message2.default.infoMessage('Usage: /' + command + ' nick time reason (time can be \'permanent\')'));
	            } else if (!this.nickregex.test(parts[0])) {
	                this.push(_message2.default.infoMessage('Invalid nick'));
	            } else if (!parts[2]) {
	                this.push(_message2.default.errorMessage('Providing a reason is mandatory'));
	            } else {
	                var payload = {
	                    nick: parts[0],
	                    reason: parts.slice(2, parts.length).join(' ')
	                };
	                if (command === 'IPBAN' || /^perm/i.test(parts[1])) payload.ispermanent = command === 'IPBAN' || /^perm/i.test(parts[1]);else payload.duration = Chat.parseTimeInterval(parts[1]);
	                this.source.send('BAN', payload);
	            }
	        }
	    }, {
	        key: 'cmdUNBAN',
	        value: function cmdUNBAN(parts, command) {
	            if (!parts[0]) {
	                this.push(_message2.default.infoMessage('Usage: /' + command + ' nick'));
	            } else if (!this.nickregex.test(parts[0])) {
	                this.push(_message2.default.infoMessage('Invalid nick - /' + command + ' nick'));
	            } else {
	                this.source.send(command, { data: parts[0] });
	            }
	        }
	    }, {
	        key: 'cmdSUBONLY',
	        value: function cmdSUBONLY(parts, command) {
	            if (parts[0] !== 'on' && parts[0] !== 'off') {
	                this.push(_message2.default.errorMessage('Invalid argument - /' + command + ' on/off'));
	            } else {
	                this.source.send(command.toUpperCase(), { data: parts[0] });
	            }
	        }
	    }, {
	        key: 'cmdMAXLINES',
	        value: function cmdMAXLINES(parts, command) {
	            if (!parts[0]) {
	                this.push(_message2.default.infoMessage('Current number of lines shown: ' + this.settings.get('maxlines')));
	            } else {
	                var newmaxlines = Math.abs(parseInt(parts[0], 10));
	                if (!newmaxlines) {
	                    this.push(_message2.default.infoMessage('Invalid argument - /' + command + ' is expecting a number'));
	                } else {
	                    this.settings.set('maxlines', newmaxlines);
	                    _store2.default.write('chat.settings', this.settings);
	                    this.updateSettingsCss();
	                    this.push(_message2.default.infoMessage('Current number of lines shown: ' + this.settings.get('maxlines')));
	                }
	            }
	        }
	    }, {
	        key: 'cmdTOGGLEHIGHLIGHT',
	        value: function cmdTOGGLEHIGHLIGHT(parts, command) {
	            var highlights = this.settings.get('highlightnicks'),
	                nicks = Object.keys(highlights);
	            if (!parts[0]) {
	                this.push(_message2.default.infoMessage('Currently highlighted users: ' + nicks.join(', ')));
	            } else if (!this.nickregex.test(parts[1])) {
	                this.push(_message2.default.errorMessage('Invalid nick - /' + command + ' nick'));
	            } else {
	                var nick = parts[0].toLowerCase();
	                switch (command) {
	                    case 'UNHIGHLIGHT':
	                        if (highlights[nick]) delete highlights[nick];
	                        this.push(_message2.default.infoMessage('No longer highlighting ' + nick));
	                        break;
	                    default:
	                    case 'HIGHLIGHT':
	                        if (!highlights[nick]) highlights[nick] = true;
	                        this.push(_message2.default.infoMessage('Now highlighting ' + nick));
	                        break;
	                }
	                this.settings.set('highlightnicks', highlights);
	                _store2.default.write('chat.settings', this.settings);
	                this.highlighter.loadHighlighters();
	                this.highlighter.redraw();
	                this.updateSettingsCss();
	            }
	        }
	    }, {
	        key: 'cmdTIMESTAMPFORMAT',
	        value: function cmdTIMESTAMPFORMAT(parts) {
	            if (!parts[0]) {
	                this.push(_message2.default.infoMessage('Current format: ' + this.settings.get('timestampformat') + ' (the default is \'HH:mm\', for more info: http://momentjs.com/docs/#/displaying/format/)'));
	            } else {
	                var format = parts.slice(1, parts.length);
	                if (!/^[a-z :.,-\\*]+$/i.test(format)) {
	                    this.push(_message2.default.errorMessage('Invalid format, see: http://momentjs.com/docs/#/displaying/format/'));
	                } else {
	                    this.settings.set('timestampformat', format);
	                    _store2.default.write('chat.settings', this.settings);
	                    this.updateSettingsCss();
	                    this.push(_message2.default.infoMessage('New format: ' + this.settings.get('timestampformat')));
	                }
	            }
	        }
	    }, {
	        key: 'cmdBROADCAST',
	        value: function cmdBROADCAST(parts) {
	            this.source.send('BROADCAST', { data: parts.join(' ') });
	        }
	    }, {
	        key: 'push',
	        value: function push(message) {
	            // Dont add the gui if user is ignored
	            if (message instanceof _user4.default && this.shouldIgnoreMessage(message.message)) return;

	            // Get the scroll position before adding the new line / removing old lines
	            var maxlines = this.settings.get('maxlines'),
	                lines = this.lines.children(),
	                pin = this.scrollPlugin.isPinned() && !this.mousedown && !this.waitingtopin;

	            // Rid excess lines if the user is scrolled to the bottom
	            if (pin && lines.length >= maxlines) lines.slice(0, lines.length - maxlines).remove();

	            // Highlight and append to the chat gui
	            message.highlighted = this.highlighter.mustHighlight(message);
	            this.lines.append(message.attach(this));

	            if (!this.backlogLoading) {
	                // Pin the chat scroll
	                this.log.debug('Update scroll Pinned: ' + pin + ' contentScrollTop: ' + this.scrollPlugin.scroller.contentScrollTop + ' maxScrollTop: ' + this.scrollPlugin.scroller.maxScrollTop);
	                this.scrollPlugin.updateAndPin(pin);

	                // Show desktop notification
	                if (message.highlighted && this.settings.get('allowNotifications') && !this.input.is(':focus')) Chat.showNotification(message);
	            }

	            // Cache the last message for interrogation
	            this.lastMessage = message;
	            return message;
	        }
	    }, {
	        key: 'resolveMessage',
	        value: function resolveMessage(data) {
	            var _iteratorNormalCompletion = true;
	            var _didIteratorError = false;
	            var _iteratorError = undefined;

	            try {
	                for (var _iterator = this.unresolved[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	                    var message = _step.value;

	                    if (this.user.username === data.nick && message.message === data.data) return this.unresolved.splice(0, 1)[0].resolve(this);
	                }
	            } catch (err) {
	                _didIteratorError = true;
	                _iteratorError = err;
	            } finally {
	                try {
	                    if (!_iteratorNormalCompletion && _iterator.return) {
	                        _iterator.return();
	                    }
	                } finally {
	                    if (_didIteratorError) {
	                        throw _iteratorError;
	                    }
	                }
	            }

	            return null;
	        }
	    }, {
	        key: 'removeMessageByUsername',
	        value: function removeMessageByUsername(username) {
	            this.lines.children('div[data-username="' + username.toLowerCase() + '"]').remove();
	            this.scrollPlugin.reset();
	        }
	    }, {
	        key: 'ignoreNick',
	        value: function ignoreNick(nick) {
	            var ignore = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	            nick = nick.toLowerCase();
	            if (!ignore) this.ignoring.add(nick);else if (this.ignoring.has(nick)) this.ignoring.delete(nick);
	            _store2.default.write('chat.ignoring', this.ignoring);
	            this.updateIgnoreRegex();
	        }
	    }, {
	        key: 'shouldIgnoreMessage',
	        value: function shouldIgnoreMessage(message) {
	            return message !== '' && this.ignoreregex && this.ignoreregex.test(message);
	        }
	    }, {
	        key: 'shouldIgnoreUser',
	        value: function shouldIgnoreUser(nick) {
	            return this.ignoring.has(nick.toLowerCase());
	        }
	    }, {
	        key: 'updateSettingsCss',
	        value: function updateSettingsCss() {
	            var _this5 = this;

	            Array.from(this.settings.keys()).filter(function (key) {
	                return typeof _this5.settings.get(key) === 'boolean';
	            }).forEach(function (key) {
	                return _this5.ui.toggleClass('pref-' + key, _this5.settings.get(key));
	            });
	        }
	    }, {
	        key: 'updateIgnoreRegex',
	        value: function updateIgnoreRegex() {
	            var k = Array.from(this.ignoring.values()).map(Chat.makeSafeForRegex);
	            this.ignoreregex = k.length > 0 ? new RegExp(k.join('|'), 'i') : null;
	        }
	    }, {
	        key: 'setUnreadMessageCount',
	        value: function setUnreadMessageCount(n) {
	            this.pmcountnum = Math.max(0, n);
	            this.pmcount.toggleClass('hidden', !this.pmcountnum).text(this.pmcountnum);
	        }

	        // simple array check for features

	    }, {
	        key: 'isEmote',
	        value: function isEmote(text) {
	            return this.emoticons.has(text) || this.twitchemotes.has(text);
	        }
	    }], [{
	        key: 'isArraysEqual',
	        value: function isArraysEqual(a, b) {
	            return !a || !b ? a.length !== b.length || a.sort().toString() !== b.sort().toString() : false;
	        }
	    }, {
	        key: 'showNotification',
	        value: function showNotification(message) {
	            if (Notification.permission === 'granted') {
	                (function () {
	                    var n = new Notification(message.user.username + ' said ...', {
	                        body: message.message,
	                        tag: 'dgg' + message.timestamp.valueOf(),
	                        icon: '/notifyicon.png',
	                        dir: 'auto'
	                    });
	                    setTimeout(function () {
	                        return n.close();
	                    }, 5000);
	                    n.onclick = function () {
	                        // todo open chat at specific line
	                    };
	                })();
	            }
	        }
	    }, {
	        key: 'makeSafeForRegex',
	        value: function makeSafeForRegex(str) {
	            return str.trim().replace(/[\-\[\]\/{}()*+?.\\\^$|]/g, "\\$&");
	        }
	    }, {
	        key: 'parseTimeInterval',
	        value: function parseTimeInterval(str) {
	            var nanoseconds = 0,
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
	            str.replace(/(\d+(?:\.\d*)?)([a-z]+)?/ig, function ($0, number, unit) {
	                number *= unit ? units[unit.toLowerCase()] || units.s : units.s;
	                nanoseconds += +number;
	            });
	            return nanoseconds;
	        }
	    }]);

	    return Chat;
	}();

	exports.default = Chat;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 42:
/***/ function(module, exports) {

	"use strict";

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var EventEmitter = function () {
	    function EventEmitter() {
	        _classCallCheck(this, EventEmitter);

	        this.listeners = new Map();
	    }

	    _createClass(EventEmitter, [{
	        key: "on",
	        value: function on(name, fn) {
	            this.listeners.has(name) || this.listeners.set(name, []);
	            this.listeners.get(name).push(fn);
	            return this;
	        }
	    }, {
	        key: "emit",
	        value: function emit(name) {
	            for (var _len = arguments.length, args = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	                args[_key - 1] = arguments[_key];
	            }

	            var listeners = this.listeners.get(name);
	            if (listeners && listeners.length) {
	                listeners.forEach(function (listener) {
	                    return listener.apply(undefined, args);
	                });
	                return true;
	            }
	            return false;
	        }
	    }]);

	    return EventEmitter;
	}();

	exports.default = EventEmitter;

/***/ },

/***/ 43:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _moment = __webpack_require__(1);

	var _moment2 = _interopRequireDefault(_moment);

	var _ui = __webpack_require__(99);

	var _ui2 = _interopRequireDefault(_ui);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $, destiny */

	var ChatMessage = function (_ChatUIMessage) {
	    _inherits(ChatMessage, _ChatUIMessage);

	    function ChatMessage(message) {
	        var timestamp = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	        var type = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'chat';

	        _classCallCheck(this, ChatMessage);

	        var _this = _possibleConstructorReturn(this, (ChatMessage.__proto__ || Object.getPrototypeOf(ChatMessage)).call(this, message));

	        _this.type = type;
	        _this.timestamp = timestamp ? _moment2.default.utc(timestamp).local() : (0, _moment2.default)();
	        return _this;
	    }

	    _createClass(ChatMessage, [{
	        key: 'wrapTime',
	        value: function wrapTime() {
	            var datetime = this.timestamp.format('MMMM Do YYYY, h:mm:ss a');
	            var label = this.timestamp.format(this.chat.settings.get('timestampformat'));
	            return '<time class="time" title="' + datetime + '">' + label + '</time>';
	        }
	    }, {
	        key: 'wrapMessage',
	        value: function wrapMessage() {
	            var _this2 = this;

	            var message = this.message;
	            this.chat.formatters.forEach(function (formatter) {
	                return message = formatter.format(message, _this2.user);
	            });
	            return '<span class="text">' + message + '</span>';
	        }
	    }, {
	        key: 'html',
	        value: function html() {
	            return this.wrap(this.wrapTime() + ' ' + this.wrapMessage());
	        }
	    }], [{
	        key: 'uiMessage',
	        value: function uiMessage(message) {
	            return new _ui2.default(message);
	        }
	    }, {
	        key: 'statusMessage',
	        value: function statusMessage(message) {
	            var timestamp = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	            return new ChatMessage(message, timestamp, 'status');
	        }
	    }, {
	        key: 'errorMessage',
	        value: function errorMessage(message) {
	            var timestamp = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	            return new ChatMessage(message, timestamp, 'error');
	        }
	    }, {
	        key: 'infoMessage',
	        value: function infoMessage(message) {
	            var timestamp = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	            return new ChatMessage(message, timestamp, 'info');
	        }
	    }, {
	        key: 'broadcastMessage',
	        value: function broadcastMessage(message) {
	            var timestamp = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	            return new ChatMessage(message, timestamp, 'broadcast');
	        }
	    }, {
	        key: 'commandMessage',
	        value: function commandMessage(message) {
	            var timestamp = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	            return new ChatMessage(message, timestamp, 'command');
	        }
	    }]);

	    return ChatMessage;
	}(_ui2.default);

	exports.default = ChatMessage;

/***/ },

/***/ 44:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _message = __webpack_require__(43);

	var _message2 = _interopRequireDefault(_message);

	var _features = __webpack_require__(16);

	var _features2 = _interopRequireDefault(_features);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $, destiny */

	var ChatUserMessage = function (_ChatMessage) {
	    _inherits(ChatUserMessage, _ChatMessage);

	    function ChatUserMessage(message, user, timestamp) {
	        _classCallCheck(this, ChatUserMessage);

	        var _this = _possibleConstructorReturn(this, (ChatUserMessage.__proto__ || Object.getPrototypeOf(ChatUserMessage)).call(this, message, timestamp, 'user'));

	        _this.user = user;
	        _this.highlighted = false;
	        _this.prepareMessage();
	        return _this;
	    }

	    _createClass(ChatUserMessage, [{
	        key: 'prepareMessage',
	        value: function prepareMessage() {
	            this.isSlashMe = false;
	            if (this.message.substring(0, 4) === '/me ') {
	                this.isSlashMe = true;
	                this.message = this.message.substring(4);
	            } else if (this.message.substring(0, 2) === '//') {
	                this.message = this.message.substring(1);
	            }
	        }
	    }, {
	        key: 'wrapUser',
	        value: function wrapUser(user) {
	            var features = user.features.length > 0 ? '<span class="features">' + ChatUserMessage.getFeatureHTML(user) + '</span>' : '';
	            return features + ' <a class="user ' + user.features.join(' ') + '">' + user.username + '</a>';
	        }
	    }, {
	        key: 'html',
	        value: function html() {
	            var classes = [],
	                attr = {};
	            if (this.user && this.user.username) attr['data-username'] = this.user.username.toLowerCase();
	            if (this.chat.user && this.chat.user.username === this.user.username) classes.push('msg-own');
	            if (this.isSlashMe) classes.push('msg-emote');
	            if (this.highlighted) classes.push('msg-highlight');
	            if (this.chat.lastMessage && this.chat.lastMessage.user && this.user && this.chat.lastMessage.user.username === this.user.username) classes.push('msg-continue');
	            return this.wrap(this.wrapTime() + ' ' + this.wrapUser(this.user) + ' ' + this.wrapMessage(), classes, attr);
	        }
	    }], [{
	        key: 'getFeatureHTML',
	        value: function getFeatureHTML(user) {
	            var icons = '';

	            if (user.hasFeature(_features2.default.SUBSCRIBERT4)) icons += '<i class="icon-subscribert4" title="Subscriber (T4)"/>';else if (user.hasFeature(_features2.default.SUBSCRIBERT3)) icons += '<i class="icon-subscribert3" title="Subscriber (T3)"/>';else if (user.hasFeature(_features2.default.SUBSCRIBERT2)) icons += '<i class="icon-subscribert2" title="Subscriber (T2)"/>';else if (user.hasFeature(_features2.default.SUBSCRIBERT1)) icons += '<i class="icon-subscriber" title="Subscriber (T1)"/>';else if (!user.hasFeature(_features2.default.SUBSCRIBERT0) && user.hasFeature(_features2.default.SUBSCRIBER)) icons += '<i class="icon-subscriber" title="Subscriber (T1)"/>';

	            var _iteratorNormalCompletion = true;
	            var _didIteratorError = false;
	            var _iteratorError = undefined;

	            try {
	                for (var _iterator = user.features[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	                    var feature = _step.value;

	                    switch (feature) {
	                        case _features2.default.SUBSCRIBERT0:
	                            icons += '<i class="icon-minitwitch" title="Twitch subscriber"/>';
	                            break;
	                        case _features2.default.BOT:
	                            icons += '<i class="icon-bot" title="Bot"/>';
	                            break;
	                        case _features2.default.BOT2:
	                            icons += '<i class="icon-bot2" title="Bot"/>';
	                            break;
	                        case _features2.default.NOTABLE:
	                            icons += '<i class="icon-notable" title="Notable"/>';
	                            break;
	                        case _features2.default.TRUSTED:
	                            icons += '<i class="icon-trusted" title="Trusted"/>';
	                            break;
	                        case _features2.default.CONTRIBUTOR:
	                            icons += '<i class="icon-contributor" title="Contributor"/>';
	                            break;
	                        case _features2.default.COMPCHALLENGE:
	                            icons += '<i class="icon-compchallenge" title="Composition Winner"/>';
	                            break;
	                        case _features2.default.EVE:
	                            icons += '<i class="icon-eve" title="Eve"/>';
	                            break;
	                        case _features2.default.SC2:
	                            icons += '<i class="icon-sc2" title="Starcraft 2"/>';
	                            break;
	                        case _features2.default.BROADCASTER:
	                            icons += '<i class="icon-broadcaster" title="Broadcaster"/>';
	                            break;
	                    }
	                }
	            } catch (err) {
	                _didIteratorError = true;
	                _iteratorError = err;
	            } finally {
	                try {
	                    if (!_iteratorNormalCompletion && _iterator.return) {
	                        _iterator.return();
	                    }
	                } finally {
	                    if (_didIteratorError) {
	                        throw _iteratorError;
	                    }
	                }
	            }

	            return icons;
	        }
	    }]);

	    return ChatUserMessage;
	}(_message2.default);

	exports.default = ChatUserMessage;

/***/ },

/***/ 45:
/***/ function(module, exports) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	/* global window */

	var localStorage = window.localStorage || {};
	var JSON = window.JSON;

	var ChatStore = function () {
	    function ChatStore() {
	        _classCallCheck(this, ChatStore);
	    }

	    _createClass(ChatStore, null, [{
	        key: 'write',
	        value: function write(name, obj) {
	            var str = '';
	            try {
	                str = JSON.stringify(obj instanceof Map || obj instanceof Set ? [].concat(_toConsumableArray(obj)) : obj);
	            } catch (ignored) {}
	            localStorage.setItem(name, str);
	        }
	    }, {
	        key: 'read',
	        value: function read(name) {
	            var data = null;
	            try {
	                data = JSON.parse(localStorage.getItem(name));
	            } catch (ignored) {}
	            return data;
	        }
	    }]);

	    return ChatStore;
	}();

	exports.default = ChatStore;

/***/ },

/***/ 64:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	/* global $ */

	__webpack_require__(148);

	var ChatScrollPlugin = function () {
	    function ChatScrollPlugin(el, options) {
	        _classCallCheck(this, ChatScrollPlugin);

	        this.scroller = $(el).nanoScroller(Object.assign({
	            disableResize: true,
	            preventPageScrolling: true
	        }, options))[0].nanoscroller;
	    }

	    _createClass(ChatScrollPlugin, [{
	        key: 'isPinned',
	        value: function isPinned() {
	            // 30 is used to allow the scrollbar to be just offset, but still count as scrolled to bottom
	            return !this.scroller.isActive ? true : this.scroller.contentScrollTop >= this.scroller.maxScrollTop - 30;
	        }
	    }, {
	        key: 'updateAndPin',
	        value: function updateAndPin(pin) {
	            this.reset();
	            if (pin) this.scroller.scrollBottom(0);
	        }
	    }, {
	        key: 'reset',
	        value: function reset() {
	            this.scroller.reset();
	        }
	    }]);

	    return ChatScrollPlugin;
	}();

	exports.default = ChatScrollPlugin;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 84:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }(); /* global $, destiny */

	var _chat = __webpack_require__(41);

	var _chat2 = _interopRequireDefault(_chat);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var ChatAutoComplete = function () {
	    function ChatAutoComplete(chat) {
	        var _this = this;

	        _classCallCheck(this, ChatAutoComplete);

	        this.minWordLength = 1;
	        this.maxResults = 10;
	        this.buckets = {};
	        this.origVal = null;
	        this.searchResults = [];
	        this.searchIndex = -1;
	        this.searchWord = null;
	        this.input = chat.input;

	        //HTMLInputElement.prototype.setSelectionRange
	        if (!this.input[0].setSelectionRange) return this;

	        setInterval(this.expireUsers.bind(this), 60000); // 1 minute

	        this.input.on('mousedown', this.resetSearch.bind(this));
	        this.input.on('keydown', function (e) {
	            if (e.which === 9) {
	                // if TAB
	                e.preventDefault();
	                e.stopPropagation();
	                if (_this.searchResults.length <= 0) {
	                    _this.resetSearch();
	                    _this.searchSelectWord(e.shiftKey);
	                }
	                _this.showAutoComplete();
	            } else {
	                // Cancel the search and continue the keydown
	                _this.resetSearch();
	            }
	        });
	    }

	    _createClass(ChatAutoComplete, [{
	        key: 'getBucketId',
	        value: function getBucketId(id) {
	            if (id.length === 0) return '';
	            return id[0].toLowerCase();
	        }
	    }, {
	        key: 'addToBucket',
	        value: function addToBucket(data, weight, isemote, promoteTimestamp) {
	            var id = this.getBucketId(data);

	            if (!this.buckets[id]) this.buckets[id] = {};

	            if (!this.buckets[id][data]) this.buckets[id][data] = {
	                data: data,
	                weight: weight,
	                isemote: !!isemote,
	                promoted: promoteTimestamp
	            };

	            return this.buckets[id][data];
	        }
	    }, {
	        key: 'toggleNick',
	        value: function toggleNick(data, val) {
	            return val ? this.removeNick(data) : this.updateNick(data);
	        }
	    }, {
	        key: 'removeNick',
	        value: function removeNick(data) {
	            var id = this.getBucketId(data);
	            if (this.buckets[id] && this.buckets[id][data]) {
	                delete this.buckets[id][data];
	                return true;
	            }
	            return false;
	        }
	    }, {
	        key: 'addEmote',
	        value: function addEmote(emote) {
	            this.addToBucket(emote, 1, true, 0);
	        }
	    }, {
	        key: 'addNick',
	        value: function addNick(nick) {
	            this.addToBucket(nick, 1, false, 0);
	        }
	    }, {
	        key: 'updateNick',
	        value: function updateNick(nick) {
	            var weight = Date.now();
	            var data = this.addToBucket(nick, weight, false, 0);
	            data.weight = weight;
	        }
	    }, {
	        key: 'promoteNick',
	        value: function promoteNick(nick) {
	            var promoteTimestamp = Date.now();
	            var data = this.addToBucket(nick, 1, false, promoteTimestamp);

	            if (data.isemote) return this;

	            data.promoted = promoteTimestamp;
	        }
	    }, {
	        key: 'getSearchWord',
	        value: function getSearchWord(str, offset) {
	            var pre = str.substring(0, offset),
	                post = str.substring(offset),
	                startCaret = pre.lastIndexOf(' ') + 1,
	                endCaret = post.indexOf(' '),
	                isUserSearch = false;

	            if (startCaret > 0) pre = pre.substring(startCaret);

	            if (endCaret > -1) post = post.substring(0, endCaret);

	            // Ignore the first char as part of the search and flag as a user only search
	            if (pre.lastIndexOf('@') === 0) {
	                startCaret++;
	                pre = pre.substring(1);
	                isUserSearch = true;
	            }

	            return {
	                word: pre + post,
	                startCaret: startCaret,
	                isUserSearch: isUserSearch
	            };
	        }
	    }, {
	        key: 'sortResults',
	        value: function sortResults(a, b) {
	            if (!a || !b) return 0;

	            // order promoted things first
	            if (a.promoted !== b.promoted) return a.promoted > b.promoted ? -1 : 1;

	            // order emotes second
	            if (a.isemote !== b.isemote) return a.isemote && !b.isemote ? -1 : 1;

	            // order according to recency third
	            if (a.weight !== b.weight) return a.weight > b.weight ? -1 : 1;

	            // order lexically fourth
	            a = a.data.toLowerCase();
	            b = b.data.toLowerCase();

	            if (a === b) return 0;

	            return a > b ? 1 : -1;
	        }
	    }, {
	        key: 'searchBuckets',
	        value: function searchBuckets(str, limit, usernamesOnly) {
	            str = _chat2.default.makeSafeForRegex(str);
	            var res = [],
	                f = new RegExp('^' + str, 'i'),
	                data = this.buckets[this.getBucketId(str)] || {};

	            for (var nick in data) {
	                if (!data.hasOwnProperty(nick) || usernamesOnly && data[nick].isemote) continue;

	                if (f.test(nick)) res.push(data[nick]);
	            }

	            res.sort(this.sortResults);
	            return res.slice(0, limit);
	        }
	    }, {
	        key: 'expireUsers',
	        value: function expireUsers() {
	            // every 10 minutes reset the promoted users so that emotes can be
	            // ordered before the user again
	            var tenminutesago = Date.now() - 600000;
	            for (var i in this.buckets) {
	                if (!this.buckets.hasOwnProperty(i)) continue;

	                for (var j in this.buckets[i]) {
	                    if (!this.buckets[i].hasOwnProperty(j)) continue;

	                    var data = this.buckets[i][j];
	                    if (!data.isemote && data.promoted <= tenminutesago) data.promoted = 0;

	                    if (!data.isemote && data.weight <= tenminutesago) data.weight = 1;
	                }
	            }
	        }
	    }, {
	        key: 'markLastComplete',
	        value: function markLastComplete() {
	            if (!this.lastComplete) return;

	            var data = this.buckets[this.getBucketId(this.lastComplete)] || {};

	            // should never happen, but just in case
	            if (!data[this.lastComplete]) return this.lastComplete = null;

	            if (data[this.lastComplete].isemote) {
	                // reset the promotion of users near the emote
	                for (var j in data) {
	                    if (!data.hasOwnProperty(j)) continue;

	                    data[j].promoted = 0;
	                }
	                return this.lastComplete = null;
	            }

	            this.promoteNick(this.lastComplete);
	            this.lastComplete = null;
	        }
	    }, {
	        key: 'resetSearch',
	        value: function resetSearch() {
	            this.origVal = null;
	            this.searchResults = [];
	            this.searchIndex = -1;
	            this.searchWord = null;
	        }
	    }, {
	        key: 'searchSelectWord',
	        value: function searchSelectWord(forceUserSearch) {
	            var searchWord = this.getSearchWord(this.input.val(), this.input[0].selectionStart);
	            if (searchWord.word.length >= this.minWordLength) {
	                this.searchWord = searchWord;
	                var isUserSearch = forceUserSearch ? true : this.searchWord.isUserSearch;
	                this.searchResults = this.searchBuckets(this.searchWord.word, this.maxResults, isUserSearch);
	                this.origVal = this.input.val().toString();
	            }
	        }
	    }, {
	        key: 'showAutoComplete',
	        value: function showAutoComplete() {
	            this.searchIndex = this.searchIndex >= this.searchResults.length - 1 ? 0 : this.searchIndex + 1;
	            var result = this.searchResults[this.searchIndex];
	            if (!result || result.data === this.searchWord.word) return;

	            this.lastComplete = result.data;
	            var pre = this.origVal.substr(0, this.searchWord.startCaret),
	                post = this.origVal.substr(this.searchWord.startCaret + this.searchWord.word.length);

	            // always add a space after our completion if there isn't one since people
	            // would add one anyway
	            if (post[0] !== ' ' || post.length === 0) post = ' ' + post;

	            this.input.focus().val(pre + result.data + post);

	            // Move the caret to the end of the replacement string + 1 for the space
	            this.input[0].setSelectionRange(pre.length + result.data.length + 1, pre.length + result.data.length + 1);
	        }
	    }]);

	    return ChatAutoComplete;
	}();

	exports.default = ChatAutoComplete;

/***/ },

/***/ 85:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }(); /* global $ */

	var _log = __webpack_require__(29);

	var _log2 = _interopRequireDefault(_log);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var ChatUserFocus = function () {
	    function ChatUserFocus(chat, css) {
	        var _this = this;

	        _classCallCheck(this, ChatUserFocus);

	        this.log = _log2.default.make(this);
	        this.chat = chat;
	        this.css = css;
	        this.focused = [];
	        this.chat.lines.on('mousedown', function (e) {
	            return _this.toggleElement(e.target);
	        });
	    }

	    _createClass(ChatUserFocus, [{
	        key: 'toggleElement',
	        value: function toggleElement(target) {
	            var t = $(target);
	            if (t.hasClass('chat-user')) {
	                this.toggleFocus(t.closest('.msg-user').data('username'), true).toggleFocus(t.text().toLowerCase());
	            } else if (t.hasClass('user')) {
	                this.toggleFocus(t.closest('.msg-user').data('username'));
	            } else if (this.focused.length > 0) {
	                this.clearFocus();
	            }
	        }
	    }, {
	        key: 'addCssRule',
	        value: function addCssRule(username) {
	            this.log.debug('Add focus user', username);
	            this.css.insertRule('.msg-user[data-username="' + username + '"]{opacity:1 !important;}', this.focused.length); // max 4294967295
	            this.focused.push(username);
	            this.redraw();
	        }
	    }, {
	        key: 'removeCssRule',
	        value: function removeCssRule(index) {
	            this.log.debug('Remove focus user', index);
	            this.css.deleteRule(index);
	            this.focused.splice(index, 1);
	            this.redraw();
	        }
	    }, {
	        key: 'clearFocus',
	        value: function clearFocus() {
	            var _this2 = this;

	            this.log.debug('Clearing focus', this.focused.length);
	            this.focused.forEach(function (i) {
	                return _this2.css.deleteRule(0);
	            });
	            this.focused = [];
	            this.redraw();
	        }
	    }, {
	        key: 'redraw',
	        value: function redraw() {
	            this.chat.ui.toggleClass('focus-user', this.focused.length > 0);
	        }
	    }, {
	        key: 'toggleFocus',
	        value: function toggleFocus(username) {
	            var bool = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	            var index = this.focused.indexOf(username.toLowerCase()),
	                focused = index !== -1;
	            if (bool === null) bool = !focused;
	            if (bool && !focused) this.addCssRule(username);else if (!bool && focused) this.removeCssRule(index);
	            return this;
	        }
	    }]);

	    return ChatUserFocus;
	}();

	exports.default = ChatUserFocus;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 86:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _formatter = __webpack_require__(20);

	var _formatter2 = _interopRequireDefault(_formatter);

	var _features = __webpack_require__(16);

	var _features2 = _interopRequireDefault(_features);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $, destiny */

	var EmoteFormatter = function (_ChatFormatter) {
	    _inherits(EmoteFormatter, _ChatFormatter);

	    function EmoteFormatter(chat) {
	        _classCallCheck(this, EmoteFormatter);

	        var _this = _possibleConstructorReturn(this, (EmoteFormatter.__proto__ || Object.getPrototypeOf(EmoteFormatter)).call(this, chat));

	        var emoticons = [].concat(_toConsumableArray(chat.emoticons)).join('|');
	        var twitchemotes = [].concat(_toConsumableArray(chat.twitchemotes)).join('|');
	        _this.emoteregex = new RegExp('(^|\\s)(' + emoticons + ')(?=$|\\s)');
	        _this.gemoteregex = new RegExp('(^|\\s)(' + emoticons + ')(?=$|\\s)', 'gm');
	        _this.twitchemoteregex = new RegExp('(^|\\s)(' + emoticons + '|' + twitchemotes + ')(?=$|\\s)', 'gm');
	        return _this;
	    }

	    _createClass(EmoteFormatter, [{
	        key: 'format',
	        value: function format(str, user) {
	            var regex = user && user.features.length > 0 ? user.hasFeature(_features2.default.SUBSCRIBERT0) ? this.twitchemoteregex : this.gemoteregex : this.emoteregex;
	            return str.replace(regex, '$1<div title="$2" class="chat-emote chat-emote-$2">$2 </div>');
	        }
	    }]);

	    return EmoteFormatter;
	}(_formatter2.default);

	exports.default = EmoteFormatter;

/***/ },

/***/ 87:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _formatter = __webpack_require__(20);

	var _formatter2 = _interopRequireDefault(_formatter);

	var _features = __webpack_require__(16);

	var _features2 = _interopRequireDefault(_features);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $, destiny */

	var GreenTextFormatter = function (_ChatFormatter) {
	    _inherits(GreenTextFormatter, _ChatFormatter);

	    function GreenTextFormatter() {
	        _classCallCheck(this, GreenTextFormatter);

	        return _possibleConstructorReturn(this, (GreenTextFormatter.__proto__ || Object.getPrototypeOf(GreenTextFormatter)).apply(this, arguments));
	    }

	    _createClass(GreenTextFormatter, [{
	        key: 'format',
	        value: function format(str, user) {
	            if (user && str.indexOf('&gt;') === 0) {
	                if (user.hasAnyFeatures(_features2.default.SUBSCRIBERT3, _features2.default.SUBSCRIBERT4, _features2.default.SUBSCRIBERT2, _features2.default.ADMIN, _features2.default.MODERATOR)) str = '<span class="greentext">' + str + '</span>';
	            }
	            return str;
	        }
	    }]);

	    return GreenTextFormatter;
	}(_formatter2.default);

	exports.default = GreenTextFormatter;

/***/ },

/***/ 88:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _formatter = __webpack_require__(20);

	var _formatter2 = _interopRequireDefault(_formatter);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

	var el = document.createElement('div');

	var HtmlTextFormatter = function (_ChatFormatter) {
	    _inherits(HtmlTextFormatter, _ChatFormatter);

	    function HtmlTextFormatter() {
	        _classCallCheck(this, HtmlTextFormatter);

	        return _possibleConstructorReturn(this, (HtmlTextFormatter.__proto__ || Object.getPrototypeOf(HtmlTextFormatter)).call(this));
	    }

	    _createClass(HtmlTextFormatter, [{
	        key: 'format',
	        value: function format(str) {
	            el.textContent = str;
	            return el.innerHTML;
	        }
	    }]);

	    return HtmlTextFormatter;
	}(_formatter2.default);

	exports.default = HtmlTextFormatter;

/***/ },

/***/ 89:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _formatter = __webpack_require__(20);

	var _formatter2 = _interopRequireDefault(_formatter);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $, destiny */

	var MentionedUserFormatter = function (_ChatFormatter) {
	    _inherits(MentionedUserFormatter, _ChatFormatter);

	    function MentionedUserFormatter(chat) {
	        _classCallCheck(this, MentionedUserFormatter);

	        var _this = _possibleConstructorReturn(this, (MentionedUserFormatter.__proto__ || Object.getPrototypeOf(MentionedUserFormatter)).call(this, chat));

	        _this.userregex = /((?:^|\s)@?)([a-zA-Z0-9_]{3,20})(?=$|\s|[\.\?!,])/g;
	        return _this;
	    }

	    _createClass(MentionedUserFormatter, [{
	        key: 'format',
	        value: function format(str, user) {
	            var _this2 = this;

	            return str.replace(this.userregex, function (match, prefix, nick) {
	                return _this2.chat.users.has(nick) ? prefix + '<span class="chat-user">' + nick + '</span>' : match;
	            });
	        }
	    }]);

	    return MentionedUserFormatter;
	}(_formatter2.default);

	exports.default = MentionedUserFormatter;

/***/ },

/***/ 90:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _formatter = __webpack_require__(20);

	var _formatter2 = _interopRequireDefault(_formatter);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /*
	                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 transformed from https://github.com/mvdan/xurls
	                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 with help to translate unicode shortcuts to javascript by
	                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 https://github.com/danielberndt/babel-plugin-utf-8-regex/blob/master/src/transformer.js
	                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               */

	var UrlFormatter = function (_ChatFormatter) {
	    _inherits(UrlFormatter, _ChatFormatter);

	    function UrlFormatter(chat) {
	        _classCallCheck(this, UrlFormatter);

	        /** @var Array tlds */
	        var _this = _possibleConstructorReturn(this, (UrlFormatter.__proto__ || Object.getPrototypeOf(UrlFormatter)).call(this, chat));

	        var tlds = __webpack_require__(147);
	        var gtld = "(?:" + tlds.join('|') + ")";
	        var unicodeShortcuts = {
	            "p{L}": '\\u0041-\\u005A\\u0061-\\u007A\\u00AA\\u00B5\\u00BA\\u00C0-\\u00D6\\u00D8-\\u00F6\\u00F8-\\u02C1\\u02C6-\\u02D1\\u02E0-\\u02E4\\u02EC\\u02EE\\u0370-\\u0374\\u0376\\u0377\\u037A-\\u037D\\u0386\\u0388-\\u038A\\u038C\\u038E-\\u03A1\\u03A3-\\u03F5\\u03F7-\\u0481\\u048A-\\u0527\\u0531-\\u0556\\u0559\\u0561-\\u0587\\u05D0-\\u05EA\\u05F0-\\u05F2\\u0620-\\u064A\\u066E\\u066F\\u0671-\\u06D3\\u06D5\\u06E5\\u06E6\\u06EE\\u06EF\\u06FA-\\u06FC\\u06FF\\u0710\\u0712-\\u072F\\u074D-\\u07A5\\u07B1\\u07CA-\\u07EA\\u07F4\\u07F5\\u07FA\\u0800-\\u0815\\u081A\\u0824\\u0828\\u0840-\\u0858\\u08A0\\u08A2-\\u08AC\\u0904-\\u0939\\u093D\\u0950\\u0958-\\u0961\\u0971-\\u0977\\u0979-\\u097F\\u0985-\\u098C\\u098F\\u0990\\u0993-\\u09A8\\u09AA-\\u09B0\\u09B2\\u09B6-\\u09B9\\u09BD\\u09CE\\u09DC\\u09DD\\u09DF-\\u09E1\\u09F0\\u09F1\\u0A05-\\u0A0A\\u0A0F\\u0A10\\u0A13-\\u0A28\\u0A2A-\\u0A30\\u0A32\\u0A33\\u0A35\\u0A36\\u0A38\\u0A39\\u0A59-\\u0A5C\\u0A5E\\u0A72-\\u0A74\\u0A85-\\u0A8D\\u0A8F-\\u0A91\\u0A93-\\u0AA8\\u0AAA-\\u0AB0\\u0AB2\\u0AB3\\u0AB5-\\u0AB9\\u0ABD\\u0AD0\\u0AE0\\u0AE1\\u0B05-\\u0B0C\\u0B0F\\u0B10\\u0B13-\\u0B28\\u0B2A-\\u0B30\\u0B32\\u0B33\\u0B35-\\u0B39\\u0B3D\\u0B5C\\u0B5D\\u0B5F-\\u0B61\\u0B71\\u0B83\\u0B85-\\u0B8A\\u0B8E-\\u0B90\\u0B92-\\u0B95\\u0B99\\u0B9A\\u0B9C\\u0B9E\\u0B9F\\u0BA3\\u0BA4\\u0BA8-\\u0BAA\\u0BAE-\\u0BB9\\u0BD0\\u0C05-\\u0C0C\\u0C0E-\\u0C10\\u0C12-\\u0C28\\u0C2A-\\u0C33\\u0C35-\\u0C39\\u0C3D\\u0C58\\u0C59\\u0C60\\u0C61\\u0C85-\\u0C8C\\u0C8E-\\u0C90\\u0C92-\\u0CA8\\u0CAA-\\u0CB3\\u0CB5-\\u0CB9\\u0CBD\\u0CDE\\u0CE0\\u0CE1\\u0CF1\\u0CF2\\u0D05-\\u0D0C\\u0D0E-\\u0D10\\u0D12-\\u0D3A\\u0D3D\\u0D4E\\u0D60\\u0D61\\u0D7A-\\u0D7F\\u0D85-\\u0D96\\u0D9A-\\u0DB1\\u0DB3-\\u0DBB\\u0DBD\\u0DC0-\\u0DC6\\u0E01-\\u0E30\\u0E32\\u0E33\\u0E40-\\u0E46\\u0E81\\u0E82\\u0E84\\u0E87\\u0E88\\u0E8A\\u0E8D\\u0E94-\\u0E97\\u0E99-\\u0E9F\\u0EA1-\\u0EA3\\u0EA5\\u0EA7\\u0EAA\\u0EAB\\u0EAD-\\u0EB0\\u0EB2\\u0EB3\\u0EBD\\u0EC0-\\u0EC4\\u0EC6\\u0EDC-\\u0EDF\\u0F00\\u0F40-\\u0F47\\u0F49-\\u0F6C\\u0F88-\\u0F8C\\u1000-\\u102A\\u103F\\u1050-\\u1055\\u105A-\\u105D\\u1061\\u1065\\u1066\\u106E-\\u1070\\u1075-\\u1081\\u108E\\u10A0-\\u10C5\\u10C7\\u10CD\\u10D0-\\u10FA\\u10FC-\\u1248\\u124A-\\u124D\\u1250-\\u1256\\u1258\\u125A-\\u125D\\u1260-\\u1288\\u128A-\\u128D\\u1290-\\u12B0\\u12B2-\\u12B5\\u12B8-\\u12BE\\u12C0\\u12C2-\\u12C5\\u12C8-\\u12D6\\u12D8-\\u1310\\u1312-\\u1315\\u1318-\\u135A\\u1380-\\u138F\\u13A0-\\u13F4\\u1401-\\u166C\\u166F-\\u167F\\u1681-\\u169A\\u16A0-\\u16EA\\u1700-\\u170C\\u170E-\\u1711\\u1720-\\u1731\\u1740-\\u1751\\u1760-\\u176C\\u176E-\\u1770\\u1780-\\u17B3\\u17D7\\u17DC\\u1820-\\u1877\\u1880-\\u18A8\\u18AA\\u18B0-\\u18F5\\u1900-\\u191C\\u1950-\\u196D\\u1970-\\u1974\\u1980-\\u19AB\\u19C1-\\u19C7\\u1A00-\\u1A16\\u1A20-\\u1A54\\u1AA7\\u1B05-\\u1B33\\u1B45-\\u1B4B\\u1B83-\\u1BA0\\u1BAE\\u1BAF\\u1BBA-\\u1BE5\\u1C00-\\u1C23\\u1C4D-\\u1C4F\\u1C5A-\\u1C7D\\u1CE9-\\u1CEC\\u1CEE-\\u1CF1\\u1CF5\\u1CF6\\u1D00-\\u1DBF\\u1E00-\\u1F15\\u1F18-\\u1F1D\\u1F20-\\u1F45\\u1F48-\\u1F4D\\u1F50-\\u1F57\\u1F59\\u1F5B\\u1F5D\\u1F5F-\\u1F7D\\u1F80-\\u1FB4\\u1FB6-\\u1FBC\\u1FBE\\u1FC2-\\u1FC4\\u1FC6-\\u1FCC\\u1FD0-\\u1FD3\\u1FD6-\\u1FDB\\u1FE0-\\u1FEC\\u1FF2-\\u1FF4\\u1FF6-\\u1FFC\\u2071\\u207F\\u2090-\\u209C\\u2102\\u2107\\u210A-\\u2113\\u2115\\u2119-\\u211D\\u2124\\u2126\\u2128\\u212A-\\u212D\\u212F-\\u2139\\u213C-\\u213F\\u2145-\\u2149\\u214E\\u2183\\u2184\\u2C00-\\u2C2E\\u2C30-\\u2C5E\\u2C60-\\u2CE4\\u2CEB-\\u2CEE\\u2CF2\\u2CF3\\u2D00-\\u2D25\\u2D27\\u2D2D\\u2D30-\\u2D67\\u2D6F\\u2D80-\\u2D96\\u2DA0-\\u2DA6\\u2DA8-\\u2DAE\\u2DB0-\\u2DB6\\u2DB8-\\u2DBE\\u2DC0-\\u2DC6\\u2DC8-\\u2DCE\\u2DD0-\\u2DD6\\u2DD8-\\u2DDE\\u2E2F\\u3005\\u3006\\u3031-\\u3035\\u303B\\u303C\\u3041-\\u3096\\u309D-\\u309F\\u30A1-\\u30FA\\u30FC-\\u30FF\\u3105-\\u312D\\u3131-\\u318E\\u31A0-\\u31BA\\u31F0-\\u31FF\\u3400-\\u4DB5\\u4E00-\\u9FCC\\uA000-\\uA48C\\uA4D0-\\uA4FD\\uA500-\\uA60C\\uA610-\\uA61F\\uA62A\\uA62B\\uA640-\\uA66E\\uA67F-\\uA697\\uA6A0-\\uA6E5\\uA717-\\uA71F\\uA722-\\uA788\\uA78B-\\uA78E\\uA790-\\uA793\\uA7A0-\\uA7AA\\uA7F8-\\uA801\\uA803-\\uA805\\uA807-\\uA80A\\uA80C-\\uA822\\uA840-\\uA873\\uA882-\\uA8B3\\uA8F2-\\uA8F7\\uA8FB\\uA90A-\\uA925\\uA930-\\uA946\\uA960-\\uA97C\\uA984-\\uA9B2\\uA9CF\\uAA00-\\uAA28\\uAA40-\\uAA42\\uAA44-\\uAA4B\\uAA60-\\uAA76\\uAA7A\\uAA80-\\uAAAF\\uAAB1\\uAAB5\\uAAB6\\uAAB9-\\uAABD\\uAAC0\\uAAC2\\uAADB-\\uAADD\\uAAE0-\\uAAEA\\uAAF2-\\uAAF4\\uAB01-\\uAB06\\uAB09-\\uAB0E\\uAB11-\\uAB16\\uAB20-\\uAB26\\uAB28-\\uAB2E\\uABC0-\\uABE2\\uAC00-\\uD7A3\\uD7B0-\\uD7C6\\uD7CB-\\uD7FB\\uF900-\\uFA6D\\uFA70-\\uFAD9\\uFB00-\\uFB06\\uFB13-\\uFB17\\uFB1D\\uFB1F-\\uFB28\\uFB2A-\\uFB36\\uFB38-\\uFB3C\\uFB3E\\uFB40\\uFB41\\uFB43\\uFB44\\uFB46-\\uFBB1\\uFBD3-\\uFD3D\\uFD50-\\uFD8F\\uFD92-\\uFDC7\\uFDF0-\\uFDFB\\uFE70-\\uFE74\\uFE76-\\uFEFC\\uFF21-\\uFF3A\\uFF41-\\uFF5A\\uFF66-\\uFFBE\\uFFC2-\\uFFC7\\uFFCA-\\uFFCF\\uFFD2-\\uFFD7\\uFFDA-\\uFFDC',
	            "p{N}": '\\u0030-\\u0039\\u00B2\\u00B3\\u00B9\\u00BC-\\u00BE\\u0660-\\u0669\\u06F0-\\u06F9\\u07C0-\\u07C9\\u0966-\\u096F\\u09E6-\\u09EF\\u09F4-\\u09F9\\u0A66-\\u0A6F\\u0AE6-\\u0AEF\\u0B66-\\u0B6F\\u0B72-\\u0B77\\u0BE6-\\u0BF2\\u0C66-\\u0C6F\\u0C78-\\u0C7E\\u0CE6-\\u0CEF\\u0D66-\\u0D75\\u0E50-\\u0E59\\u0ED0-\\u0ED9\\u0F20-\\u0F33\\u1040-\\u1049\\u1090-\\u1099\\u1369-\\u137C\\u16EE-\\u16F0\\u17E0-\\u17E9\\u17F0-\\u17F9\\u1810-\\u1819\\u1946-\\u194F\\u19D0-\\u19DA\\u1A80-\\u1A89\\u1A90-\\u1A99\\u1B50-\\u1B59\\u1BB0-\\u1BB9\\u1C40-\\u1C49\\u1C50-\\u1C59\\u2070\\u2074-\\u2079\\u2080-\\u2089\\u2150-\\u2182\\u2185-\\u2189\\u2460-\\u249B\\u24EA-\\u24FF\\u2776-\\u2793\\u2CFD\\u3007\\u3021-\\u3029\\u3038-\\u303A\\u3192-\\u3195\\u3220-\\u3229\\u3248-\\u324F\\u3251-\\u325F\\u3280-\\u3289\\u32B1-\\u32BF\\uA620-\\uA629\\uA6E6-\\uA6EF\\uA830-\\uA835\\uA8D0-\\uA8D9\\uA900-\\uA909\\uA9D0-\\uA9D9\\uAA50-\\uAA59\\uABF0-\\uABF9\\uFF10-\\uFF19',
	            "p{Sc}": '\\u0024\\u00A2-\\u00A5\\u058F\\u060B\\u09F2\\u09F3\\u09FB\\u0AF1\\u0BF9\\u0E3F\\u17DB\\u20A0-\\u20B9\\uA838\\uFDFC\\uFE69\\uFF04\\uFFE0\\uFFE1\\uFFE5\\uFFE6',
	            "p{Sk}": '\\u005E\\u0060\\u00A8\\u00AF\\u00B4\\u00B8\\u02C2-\\u02C5\\u02D2-\\u02DF\\u02E5-\\u02EB\\u02ED\\u02EF-\\u02FF\\u0375\\u0384\\u0385\\u1FBD\\u1FBF-\\u1FC1\\u1FCD-\\u1FCF\\u1FDD-\\u1FDF\\u1FED-\\u1FEF\\u1FFD\\u1FFE\\u309B\\u309C\\uA700-\\uA716\\uA720\\uA721\\uA789\\uA78A\\uFBB2-\\uFBC1\\uFF3E\\uFF40\\uFFE3',
	            "p{So}": '\\u00A6\\u00A9\\u00AE\\u00B0\\u0482\\u060E\\u060F\\u06DE\\u06E9\\u06FD\\u06FE\\u07F6\\u09FA\\u0B70\\u0BF3-\\u0BF8\\u0BFA\\u0C7F\\u0D79\\u0F01-\\u0F03\\u0F13\\u0F15-\\u0F17\\u0F1A-\\u0F1F\\u0F34\\u0F36\\u0F38\\u0FBE-\\u0FC5\\u0FC7-\\u0FCC\\u0FCE\\u0FCF\\u0FD5-\\u0FD8\\u109E\\u109F\\u1390-\\u1399\\u1940\\u19DE-\\u19FF\\u1B61-\\u1B6A\\u1B74-\\u1B7C\\u2100\\u2101\\u2103-\\u2106\\u2108\\u2109\\u2114\\u2116\\u2117\\u211E-\\u2123\\u2125\\u2127\\u2129\\u212E\\u213A\\u213B\\u214A\\u214C\\u214D\\u214F\\u2195-\\u2199\\u219C-\\u219F\\u21A1\\u21A2\\u21A4\\u21A5\\u21A7-\\u21AD\\u21AF-\\u21CD\\u21D0\\u21D1\\u21D3\\u21D5-\\u21F3\\u2300-\\u2307\\u230C-\\u231F\\u2322-\\u2328\\u232B-\\u237B\\u237D-\\u239A\\u23B4-\\u23DB\\u23E2-\\u23F3\\u2400-\\u2426\\u2440-\\u244A\\u249C-\\u24E9\\u2500-\\u25B6\\u25B8-\\u25C0\\u25C2-\\u25F7\\u2600-\\u266E\\u2670-\\u26FF\\u2701-\\u2767\\u2794-\\u27BF\\u2800-\\u28FF\\u2B00-\\u2B2F\\u2B45\\u2B46\\u2B50-\\u2B59\\u2CE5-\\u2CEA\\u2E80-\\u2E99\\u2E9B-\\u2EF3\\u2F00-\\u2FD5\\u2FF0-\\u2FFB\\u3004\\u3012\\u3013\\u3020\\u3036\\u3037\\u303E\\u303F\\u3190\\u3191\\u3196-\\u319F\\u31C0-\\u31E3\\u3200-\\u321E\\u322A-\\u3247\\u3250\\u3260-\\u327F\\u328A-\\u32B0\\u32C0-\\u32FE\\u3300-\\u33FF\\u4DC0-\\u4DFF\\uA490-\\uA4C6\\uA828-\\uA82B\\uA836\\uA837\\uA839\\uAA77-\\uAA79\\uFDFD\\uFFE4\\uFFE8\\uFFED\\uFFEE\\uFFFC\\uFFFD'
	        };
	        var letter = unicodeShortcuts["p{L}"],
	            number = unicodeShortcuts["p{N}"],
	            iriChar = letter + number,
	            pathChar = iriChar + "/\\-+=_&~*%@|#.,:;'?!" + unicodeShortcuts["p{Sc}"] + unicodeShortcuts["p{Sk}"] + unicodeShortcuts["p{So}"],
	            endChar = iriChar + "/\\-+=_&~*%;" + unicodeShortcuts["p{Sc}"],
	            octet = "(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])",
	            ipAddr = "(?:\\b" + octet + "\\." + octet + "\\." + octet + "\\." + octet + "\\b)",
	            iri = "[" + iriChar + "](?:[" + iriChar + "\\-]*[" + iriChar + "])?",
	            domain = "(?:" + iri + "\\.)+",
	            hostName = "(?:" + domain + gtld + "|" + ipAddr + ")",
	            wellBrack = "\\[[" + pathChar + "]*(?:\\[[" + pathChar + "]*\\][" + pathChar + "]*)*\\]",
	            wellParen = "\\([" + pathChar + "]*(?:\\([" + pathChar + "]*\\)[" + pathChar + "]*)*\\)",
	            wellAll = wellParen + "|" + wellBrack,
	            pathCont = "(?:[" + pathChar + "]*(?:" + wellAll + "|[" + endChar + "])+)+",
	            path = "(?:" + pathCont + "|/|\\b|$)",
	            port = "(?::[0-9]+)?",
	            webURL = "(?:" + hostName + port + "/" + path + ")|(?:" + hostName + port + "(?:\\b|$))",
	            scheme = "(https?|ftp)://",
	            strict = "\\b" + scheme + pathCont,
	            relaxed = strict + "|" + webURL;
	        _this.linkregex = new RegExp(relaxed, "gi");
	        _this._elem = $('<div/>');
	        return _this;
	    }

	    // stolen from angular.js
	    // https://github.com/angular/angular.js/blob/v1.3.14/src/ngSanitize/sanitize.js#L435


	    _createClass(UrlFormatter, [{
	        key: 'encodeUrl',
	        value: function encodeUrl(value) {
	            return value.replace(/&/g, '&amp;').replace(/[\uD800-\uDBFF][\uDC00-\uDFFF]/g, function (value) {
	                var hi = value.charCodeAt(0);
	                var low = value.charCodeAt(1);
	                return '&#' + ((hi - 0xD800) * 0x400 + (low - 0xDC00) + 0x10000) + ';';
	            }).replace(/([^\#-~| |!])/g, function (value) {
	                return '&#' + value.charCodeAt(0) + ';';
	            }).replace(/</g, '&lt;').replace(/>/g, '&gt;');
	        }
	    }, {
	        key: 'format',
	        value: function format(str) {
	            if (!str) return;
	            var self = this;
	            var extraclass = '';

	            if (/\b(?:NSFL)\b/i.test(str)) extraclass = 'nsfl-link';else if (/\b(?:NSFW|SPOILER)\b/i.test(str)) extraclass = 'nsfw-link';

	            return str.replace(self.linkregex, function (url, scheme) {
	                scheme = scheme ? '' : 'http://';
	                var decodedUrl = self._elem.html(url).text(),
	                    m = decodedUrl.match(self.linkregex);
	                if (!m) return url;
	                url = self.encodeUrl(m[0]);
	                var extra = self.encodeUrl(decodedUrl.substring(m[0].length));
	                var href = scheme + url;
	                return '<a target="_blank" class="externallink ' + extraclass + '" href="' + href + '" rel="nofollow">' + url + '</a>' + extra;
	            });
	        }
	    }]);

	    return UrlFormatter;
	}(_formatter2.default);

	exports.default = UrlFormatter;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 91:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }(); /* global */

	var _chat = __webpack_require__(41);

	var _chat2 = _interopRequireDefault(_chat);

	var _user = __webpack_require__(44);

	var _user2 = _interopRequireDefault(_user);

	var _features = __webpack_require__(16);

	var _features2 = _interopRequireDefault(_features);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var ChatHighlighter = function () {
	    function ChatHighlighter(chat) {
	        _classCallCheck(this, ChatHighlighter);

	        this.chat = chat;
	        this.customregex = null;
	        this.userregex = null;
	        this.highlightnicks = null;
	        this.loadHighlighters();
	    }

	    _createClass(ChatHighlighter, [{
	        key: 'loadHighlighters',
	        value: function loadHighlighters() {
	            var highlights = this.chat.settings.get('customhighlight').map(_chat2.default.makeSafeForRegex).join('|');
	            if (highlights !== '') this.customregex = new RegExp('\\b(?:' + highlights + ')\\b', 'i');
	            if (this.chat.user && this.chat.user.username) this.userregex = new RegExp('\\b@?(?:' + this.chat.user.username + ')\\b', 'i');
	            this.highlightnicks = Object.keys(this.chat.settings.get('highlightnicks'));
	        }
	    }, {
	        key: 'mustHighlight',
	        value: function mustHighlight(message) {
	            if (!this.chat.user || !(message instanceof _user2.default) || !this.chat.settings.get('highlight') || message.user.hasFeature(_features2.default.BOT) || message.user.username === this.chat.user.username) return false;
	            return Boolean(this.highlightnicks.find(function (nick) {
	                return message.user.username.toLowerCase() === nick.toLowerCase();
	            }) || this.userregex && this.userregex.test(message.message) || this.customregex && this.customregex.test(message.message));
	        }
	    }, {
	        key: 'redraw',
	        value: function redraw() {}
	    }]);

	    return ChatHighlighter;
	}();

	exports.default = ChatHighlighter;

/***/ },

/***/ 92:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }(); /* global Math */

	var _store = __webpack_require__(45);

	var _store2 = _interopRequireDefault(_store);

	var _log = __webpack_require__(29);

	var _log2 = _interopRequireDefault(_log);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var ChatInputHistory = function () {
	    function ChatInputHistory(chat) {
	        var _this = this;

	        _classCallCheck(this, ChatInputHistory);

	        this.log = _log2.default.make(this);
	        this.input = $(chat.input);
	        this.history = _store2.default.read('chat.history') || [];
	        this.index = -1;
	        this.lastinput = '';
	        this.maxentries = 20;
	        this.input.on('keyup', function (e) {
	            if (!(e.shiftKey || e.metaKey || e.ctrlKey) && (e.which === 38 || e.which === 40)) _this.show(e.which === 38 ? -1 : 1); // if up arrow we subtract otherwise add
	            else _this.index = -1;
	        });
	    }

	    _createClass(ChatInputHistory, [{
	        key: 'show',
	        value: function show(direction) {
	            var dir = direction === -1 ? 'UP' : 'DOWN';
	            this.log.debug('Show ' + dir + '(' + direction + ') index ' + this.index + ' total ' + this.history.length);
	            // if we are not currently showing any lines from the history
	            if (this.index < 0) {
	                // if up arrow
	                if (direction === -1) {
	                    // set the current line to the end if the history, do not subtract 1
	                    // that's done later
	                    this.index = this.history.length;
	                    // store the typed in message so that we can go back to it
	                    this.lastinput = this.input.val();

	                    if (this.index <= 0) // nothing in the history, bail out
	                        return;
	                    // down arrow, but nothing to show
	                } else return;
	            }

	            var index = this.index + direction;
	            // out of bounds
	            if (index >= this.history.length || index < 0) {
	                // down arrow was pressed to get back to the original line, reset
	                if (index >= this.history.length) {
	                    this.input.val(this.lastinput);
	                    this.index = -1;
	                }
	                return;
	            }

	            this.index = index;
	            this.input.val(this.history[index]);
	        }
	    }, {
	        key: 'add',
	        value: function add(message) {
	            this.log.debug('Add', message);
	            this.index = -1;
	            // dont add entry if the last entry is the same
	            if (this.history.length > 0 && this.history[this.history.length - 1] === message) return;
	            this.history.push(message);
	            // limit entries
	            if (this.history.length > this.maxentries) this.history = this.history.slice(0, this.history.length - this.maxentries);
	            // set the current index to the start
	            _store2.default.write('chat.history', this.history);
	        }
	    }]);

	    return ChatInputHistory;
	}();

	exports.default = ChatInputHistory;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 93:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _menu = __webpack_require__(21);

	var _menu2 = _interopRequireDefault(_menu);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $ */

	var ChatEmoteMenu = function (_ChatMenu) {
	    _inherits(ChatEmoteMenu, _ChatMenu);

	    function ChatEmoteMenu(ui, chat) {
	        _classCallCheck(this, ChatEmoteMenu);

	        var _this = _possibleConstructorReturn(this, (ChatEmoteMenu.__proto__ || Object.getPrototypeOf(ChatEmoteMenu)).call(this, ui, chat));

	        _this.input = $(_this.chat.input);
	        _this.temotes = $('#twitch-emotes');
	        _this.demotes = $('#destiny-emotes');
	        _this.demotes.append([].concat(_toConsumableArray(_this.chat.emoticons)).map(function (emote) {
	            return ChatEmoteMenu.buildEmote(emote);
	        }).join(''));
	        _this.temotes.append([].concat(_toConsumableArray(_this.chat.twitchemotes)).map(function (emote) {
	            return ChatEmoteMenu.buildEmote(emote);
	        }).join(''));
	        _this.ui.on('click', '.chat-emote', function (e) {
	            return _this.selectEmote(e.target.innerText);
	        });
	        return _this;
	    }

	    _createClass(ChatEmoteMenu, [{
	        key: 'selectEmote',
	        value: function selectEmote(emote) {
	            var value = this.input.val().toString().trim();
	            this.input.val(value + (value === '' ? '' : ' ') + emote + ' ').focus();
	        }
	    }], [{
	        key: 'buildEmote',
	        value: function buildEmote(emote) {
	            return '<div class="emote"><span title="' + emote + '" class="chat-emote chat-emote-' + emote + '">' + emote + '</span></div>';
	        }
	    }]);

	    return ChatEmoteMenu;
	}(_menu2.default);

	exports.default = ChatEmoteMenu;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 94:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _menu = __webpack_require__(21);

	var _menu2 = _interopRequireDefault(_menu);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $ */

	var ChatPmMenu = function (_ChatMenu) {
	    _inherits(ChatPmMenu, _ChatMenu);

	    function ChatPmMenu(ui, chat) {
	        _classCallCheck(this, ChatPmMenu);

	        var _this = _possibleConstructorReturn(this, (ChatPmMenu.__proto__ || Object.getPrototypeOf(ChatPmMenu)).call(this, ui, chat));

	        _this.ui.on('click', '#user-list-link', function () {
	            _menu2.default.closeMenus(_this.chat);
	            _this.chat.menus.get('users').show(_this.btn);
	        });
	        _this.ui.on('click', '#markread-privmsg', function () {
	            _this.chat.setUnreadMessageCount(0);
	            _menu2.default.closeMenus(_this.chat);
	            $.ajax({
	                type: 'POST',
	                url: '/profile/messages/openall'
	            });
	        });
	        _this.ui.on('click', '#inbox-privmsg', function () {
	            _this.chat.setUnreadMessageCount(0);
	            _menu2.default.closeMenus(_this.chat);
	        });
	        return _this;
	    }

	    return ChatPmMenu;
	}(_menu2.default);

	exports.default = ChatPmMenu;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 95:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

	var _menu = __webpack_require__(21);

	var _menu2 = _interopRequireDefault(_menu);

	var _store = __webpack_require__(45);

	var _store2 = _interopRequireDefault(_store);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $, document */

	var ChatSettingsMenu = function (_ChatMenu) {
	    _inherits(ChatSettingsMenu, _ChatMenu);

	    function ChatSettingsMenu(ui, chat) {
	        _classCallCheck(this, ChatSettingsMenu);

	        var _this = _possibleConstructorReturn(this, (ChatSettingsMenu.__proto__ || Object.getPrototypeOf(ChatSettingsMenu)).call(this, ui, chat));

	        _this.notificationEl = _this.ui.find('#chat-settings-notification-permissions');
	        _this.customHighlightEl = _this.ui.find('input[name=customhighlight]');
	        _this.allowNotificationsEl = _this.ui.find('input[name="allowNotifications"]');
	        _this.customHighlightEl.on('keypress blur', function (e) {
	            if (e.which && e.which !== 13) return; // not enter
	            var data = $(e.target).val().toString().split(',').map(function (s) {
	                return s.trim();
	            });
	            _this.chat.settings.set('customhighlight', [].concat(_toConsumableArray(new Set(data))));
	            _this.chat.highlighter.loadHighlighters();
	            _this.chat.highlighter.redraw();
	        });
	        _this.ui.on('change', 'input[type="checkbox"]', function (e) {
	            var name = $(e.target).attr('name'),
	                checked = $(e.target).is(':checked');
	            switch (name) {
	                case 'showtime':
	                    _this.updateSetting(name, checked);
	                    break;
	                case 'hideflairicons':
	                    _this.updateSetting(name, checked);
	                    break;
	                case 'highlight':
	                    _this.updateSetting(name, checked);
	                    break;
	                case 'allowNotifications':
	                    if (checked) {
	                        _this.notificationPermission().then(function (p) {
	                            return _this.updateSetting(name, true);
	                        }, function (p) {
	                            return _this.updateSetting(name, false);
	                        });
	                    } else {
	                        _this.updateSetting(name, false);
	                    }
	                    break;
	            }
	        });
	        return _this;
	    }

	    _createClass(ChatSettingsMenu, [{
	        key: 'show',
	        value: function show(btn) {
	            var _this2 = this;

	            _get(ChatSettingsMenu.prototype.__proto__ || Object.getPrototypeOf(ChatSettingsMenu.prototype), 'show', this).call(this, btn);
	            Object.keys(this.chat.settings).forEach(function (key) {
	                _this2.ui.find('input[name=' + key + '][type="checkbox"]').prop('checked', _this2.chat.settings.get(key));
	            });
	            if (Notification.permission !== 'granted') this.allowNotificationsEl.prop('checked', false);
	            this.customHighlightEl.val(this.chat.settings.get('customhighlight').join(','));
	            this.updateNotification();
	        }
	    }, {
	        key: 'updateSetting',
	        value: function updateSetting(name, value) {
	            this.updateNotification();
	            this.chat.settings.set(name, value);
	            _store2.default.write('chat.settings', this.chat.settings);
	            this.chat.updateSettingsCss();
	            this.chat.scrollPlugin.updateAndPin(this.chat.scrollPlugin.isPinned());
	        }
	    }, {
	        key: 'updateNotification',
	        value: function updateNotification() {
	            var perm = Notification.permission === 'default' ? 'required' : Notification.permission;
	            this.notificationEl.text('(Permission ' + perm + ')');
	        }
	    }, {
	        key: 'notificationPermission',
	        value: function notificationPermission() {
	            return new Promise(function (resolve, reject) {
	                switch (Notification.permission) {
	                    case 'default':
	                        Notification.requestPermission(function (permission) {
	                            switch (permission) {
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
	    }]);

	    return ChatSettingsMenu;
	}(_menu2.default);

	exports.default = ChatSettingsMenu;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 96:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

	var _menu = __webpack_require__(21);

	var _menu2 = _interopRequireDefault(_menu);

	var _features = __webpack_require__(16);

	var _features2 = _interopRequireDefault(_features);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $ */

	var ChatUserMenu = function (_ChatMenu) {
	    _inherits(ChatUserMenu, _ChatMenu);

	    function ChatUserMenu(ui, chat) {
	        _classCallCheck(this, ChatUserMenu);

	        var _this = _possibleConstructorReturn(this, (ChatUserMenu.__proto__ || Object.getPrototypeOf(ChatUserMenu)).call(this, ui, chat));

	        _this.header = _this.ui.find('h5 span');
	        _this.groupsEl = $('#chat-groups');
	        _this.group1 = $('<ul id="chat-group1">');
	        _this.group2 = $('<ul id="chat-group2">');
	        _this.group3 = $('<ul id="chat-group3">');
	        _this.group4 = $('<ul id="chat-group4">');
	        _this.group5 = $('<ul id="chat-group5">');
	        _this.groups = [_this.group1, _this.group2, _this.group3, _this.group4, _this.group5];
	        _this.groupsEl.on('click', '.user', function (e) {
	            return _this.chat.userfocus.toggleFocus(e.target.textContent);
	        });
	        _this.chat.source.on('JOIN', function (data) {
	            return _this.addAndRedraw(data.nick);
	        });
	        _this.chat.source.on('QUIT', function (data) {
	            return _this.removeAndRedraw(data.nick);
	        });
	        _this.chat.source.on('NAMES', function (data) {
	            return _this.redraw();
	        });
	        return _this;
	    }

	    _createClass(ChatUserMenu, [{
	        key: 'redraw',
	        value: function redraw() {
	            var _this2 = this;

	            if (this.visible) {
	                this.groups.forEach(function (e) {
	                    return e.detach().children('li').remove();
	                });
	                this.chat.users.forEach(function (_ref) {
	                    var username = _ref.username;
	                    return _this2.addUser(username);
	                });
	                this.sort();
	                this.groupsEl.append(this.groups);
	                this.header.text(this.chat.users.size);
	            }
	            _get(ChatUserMenu.prototype.__proto__ || Object.getPrototypeOf(ChatUserMenu.prototype), 'redraw', this).call(this);
	        }
	    }, {
	        key: 'addAndRedraw',
	        value: function addAndRedraw(username) {
	            if (this.visible && !this.hasUser(username)) {
	                this.addUser(username);
	                this.sort();
	                this.redraw();
	            }
	        }
	    }, {
	        key: 'removeAndRedraw',
	        value: function removeAndRedraw(username) {
	            if (this.visible && this.hasUser(username)) {
	                this.removeUser(username);
	                this.redraw();
	            }
	        }
	    }, {
	        key: 'removeUser',
	        value: function removeUser(username) {
	            return this.groupsEl.find('.user[data-username="' + username + '"]').parent().remove();
	        }
	    }, {
	        key: 'addUser',
	        value: function addUser(username) {
	            var user = this.chat.users.get(username),
	                elem = '<li><a data-username="' + user.username + '" class="user ' + user.features.join(' ') + '">' + user.username + '</a></li>';
	            if (user.hasFeature(_features2.default.BOT) || user.hasFeature(_features2.default.BOT2)) this.group5.append(elem);else if (user.hasFeature(_features2.default.ADMIN) || user.hasFeature(_features2.default.VIP)) this.group1.append(elem);else if (user.hasFeature(_features2.default.BROADCASTER)) this.group2.append(elem);else if (user.hasFeature(_features2.default.SUBSCRIBER)) this.group3.append(elem);else this.group4.append(elem);
	        }
	    }, {
	        key: 'hasUser',
	        value: function hasUser(username) {
	            return this.groupsEl.find('.user[data-username="' + username + '"]').length > 0;
	        }
	    }, {
	        key: 'sort',
	        value: function sort() {
	            this.groups.forEach(function (e) {
	                e.children('.user').get().sort(function (a, b) {
	                    return a.getAttribute('data-username').localeCompare(b.getAttribute('data-username'));
	                }).forEach(function (a) {
	                    return a.parentNode.appendChild(a);
	                });
	            });
	        }
	    }]);

	    return ChatUserMenu;
	}(_menu2.default);

	exports.default = ChatUserMenu;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 97:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _message = __webpack_require__(43);

	var _message2 = _interopRequireDefault(_message);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $, destiny */

	var ChatEmoteMessage = function (_ChatMessage) {
	    _inherits(ChatEmoteMessage, _ChatMessage);

	    function ChatEmoteMessage(emote, timestamp) {
	        var count = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;

	        _classCallCheck(this, ChatEmoteMessage);

	        var _this = _possibleConstructorReturn(this, (ChatEmoteMessage.__proto__ || Object.getPrototypeOf(ChatEmoteMessage)).call(this, emote, timestamp, 'emote'));

	        _this.emotecount = count;
	        _this.emotecountui = null;
	        return _this;
	    }

	    _createClass(ChatEmoteMessage, [{
	        key: 'getEmoteCountLabel',
	        value: function getEmoteCountLabel() {
	            return '<i class=\'count\'>' + this.emotecount + '</i><i class=\'x\'>X</i> C-C-C-COMBO';
	        }
	    }, {
	        key: 'html',
	        value: function html() {
	            return this.wrap(this.wrapTime() + ' ' + this.wrapMessage() + ' <span class="emotecount">' + this.getEmoteCountLabel() + '<span>');
	        }
	    }, {
	        key: 'incEmoteCount',
	        value: function incEmoteCount() {
	            ++this.emotecount;

	            var stepClass = '';
	            if (this.emotecount >= 50) stepClass = ' x50';else if (this.emotecount >= 30) stepClass = ' x30';else if (this.emotecount >= 20) stepClass = ' x20';else if (this.emotecount >= 10) stepClass = ' x10';else if (this.emotecount >= 5) stepClass = ' x5';

	            if (!this.emotecountui) this.emotecountui = this.ui.find('.emotecount');

	            this.emotecountui.detach().attr('class', 'emotecount' + stepClass).html(this.getEmoteCountLabel()).appendTo(this.ui);
	        }
	    }]);

	    return ChatEmoteMessage;
	}(_message2.default);

	exports.default = ChatEmoteMessage;

/***/ },

/***/ 98:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

	var _user = __webpack_require__(44);

	var _user2 = _interopRequireDefault(_user);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global $, destiny */

	var ChatUserPrivateMessage = function (_ChatUserMessage) {
	    _inherits(ChatUserPrivateMessage, _ChatUserMessage);

	    function ChatUserPrivateMessage(data, user, messageid, timestamp) {
	        _classCallCheck(this, ChatUserPrivateMessage);

	        var _this = _possibleConstructorReturn(this, (ChatUserPrivateMessage.__proto__ || Object.getPrototypeOf(ChatUserPrivateMessage)).call(this, data, user, timestamp, 'user'));

	        _this.user = user;
	        _this.messageid = messageid;
	        _this.isSlashMe = false; // make sure a private message is never reformatted to /me
	        return _this;
	    }

	    _createClass(ChatUserPrivateMessage, [{
	        key: 'attach',
	        value: function attach(chat) {
	            var _this2 = this;

	            _get(ChatUserPrivateMessage.prototype.__proto__ || Object.getPrototypeOf(ChatUserPrivateMessage.prototype), 'attach', this).call(this, chat);
	            return this.ui.on('click', '.mark-as-read', function (e) {
	                $.ajax({
	                    type: 'POST',
	                    url: '/profile/messages/' + encodeURIComponent(_this2.messageid) + '/open'
	                }).then(function (data) {
	                    return _this2.chat.setUnreadMessageCount(data['unread'] || 0);
	                });
	                _this2.ui.find('.icon-mail-send').attr('class', 'icon-mail-open-document');
	                _this2.ui.find('.message-actions').remove();
	                e.preventDefault();
	                e.stopPropagation();
	            });
	        }
	    }, {
	        key: 'wrapUser',
	        value: function wrapUser(user) {
	            return ' <i class="icon-mail-send" title="Received Message"></i> <a class="user">' + user.username + '</a>';
	        }
	    }, {
	        key: 'html',
	        value: function html() {
	            var classes = [],
	                args = {};
	            classes.push('private-message');
	            args['data-messageid'] = this.messageid;
	            args['data-username'] = this.user.username.toLowerCase();
	            return this.wrap(this.wrapTime() + (' <a class="user">' + this.user.username + '</a> ') + this.wrapMessage() + '<span class="message-actions">' + '<a href="#" class="mark-as-read">Mark as read <i class="fa fa-check-square-o"></i></a>' + '</span>' + '<i class="speech-arrow"></i>', classes, args);
	        }
	    }]);

	    return ChatUserPrivateMessage;
	}(_user2.default);

	exports.default = ChatUserPrivateMessage;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 99:
/***/ function(module, exports, __webpack_require__) {

	/* WEBPACK VAR INJECTION */(function($) {'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var ChatUIMessage = function () {
	    function ChatUIMessage(str) {
	        _classCallCheck(this, ChatUIMessage);

	        this.ui = null;
	        this.chat = null;
	        this.message = str;
	        this.type = 'ui';
	    }

	    _createClass(ChatUIMessage, [{
	        key: 'resolve',
	        value: function resolve() {
	            return this;
	        }
	    }, {
	        key: 'attach',
	        value: function attach(chat, resolvable) {
	            this.chat = chat;
	            this.ui = $(this.html());
	            return this.ui;
	        }
	    }, {
	        key: 'wrap',
	        value: function wrap(content) {
	            var classes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	            var attr = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

	            classes.unshift('msg-' + this.type);
	            attr['class'] = classes.join(' ');
	            return $('<div>', attr).html(content)[0].outerHTML;
	        }
	    }, {
	        key: 'html',
	        value: function html() {
	            return this.wrap(this.message);
	        }
	    }]);

	    return ChatUIMessage;
	}();

	exports.default = ChatUIMessage;
	/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(4)))

/***/ },

/***/ 100:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	    value: true
	});

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	var _emitter = __webpack_require__(42);

	var _emitter2 = _interopRequireDefault(_emitter);

	var _log = __webpack_require__(29);

	var _log2 = _interopRequireDefault(_log);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

	function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; } /* global window */

	var webSocket = window['WebSocket'] || window['MozWebSocket'];

	var ChatSource = function (_EventEmitter) {
	    _inherits(ChatSource, _EventEmitter);

	    function ChatSource(chat, server) {
	        _classCallCheck(this, ChatSource);

	        var _this = _possibleConstructorReturn(this, (ChatSource.__proto__ || Object.getPrototypeOf(ChatSource)).call(this));

	        _this.log = _log2.default.make(_this);
	        _this.sock = null;
	        _this.server = server;
	        _this.chat = chat;
	        return _this;
	    }

	    _createClass(ChatSource, [{
	        key: 'connect',
	        value: function connect() {
	            var _this2 = this;

	            try {
	                this.sock = new webSocket(this.server);
	                this.sock.onopen = function (e) {
	                    return _this2.emit('OPEN', e);
	                };
	                this.sock.onclose = function (e) {
	                    return _this2.emit('CLOSE', e);
	                };
	                this.sock.onerror = function (e) {
	                    return _this2.emit('ERR', 'socketerror');
	                };
	                this.sock.onmessage = function (e) {
	                    return _this2.parseAndDispatch(e);
	                };
	            } catch (e) {
	                this.log.error(e);
	                return this.emit('ERR', 'unknown');
	            }
	        }

	        // @param event Object {data: 'EVENT "DATA"'}

	    }, {
	        key: 'parseAndDispatch',
	        value: function parseAndDispatch(event) {
	            var eventname = event.data.split(' ', 1)[0].toUpperCase(),
	                payload = event.data.substring(eventname.length + 1),
	                data = null;
	            try {
	                data = JSON.parse(payload);
	            } catch (ignored) {
	                data = payload;
	            }
	            this.log.log(eventname, data);
	            this.emit('DISPATCH', data); // Event is used to hook into all dispatched events
	            this.emit(eventname, data);
	        }
	    }, {
	        key: 'send',
	        value: function send(eventname, data) {
	            var payload = typeof data === 'string' ? data : JSON.stringify(data);
	            this.sock.send(eventname + ' ' + payload);
	        }
	    }]);

	    return ChatSource;
	}(_emitter2.default);

	exports.default = ChatSource;

/***/ },

/***/ 101:
/***/ function(module, exports) {

	'use strict';

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var ChatUser = function () {
	    function ChatUser(args) {
	        _classCallCheck(this, ChatUser);

	        this.nick = args.nick || '';
	        this.username = args.nick || '';
	        this.features = args.features || [];
	    }

	    _createClass(ChatUser, [{
	        key: 'hasAnyFeatures',
	        value: function hasAnyFeatures() {
	            for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
	                args[_key] = arguments[_key];
	            }

	            var _iteratorNormalCompletion = true;
	            var _didIteratorError = false;
	            var _iteratorError = undefined;

	            try {
	                for (var _iterator = args[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	                    var element = _step.value;

	                    if (this.features.indexOf(element) !== -1) return true;
	                }
	            } catch (err) {
	                _didIteratorError = true;
	                _iteratorError = err;
	            } finally {
	                try {
	                    if (!_iteratorNormalCompletion && _iterator.return) {
	                        _iterator.return();
	                    }
	                } finally {
	                    if (_didIteratorError) {
	                        throw _iteratorError;
	                    }
	                }
	            }

	            return false;
	        }
	    }, {
	        key: 'hasFeature',
	        value: function hasFeature(feature) {
	            return this.hasAnyFeatures(feature);
	        }
	    }]);

	    return ChatUser;
	}();

	module.exports = ChatUser;

/***/ },

/***/ 124:
/***/ function(module, exports, __webpack_require__) {

	
	/**
	 * Module dependencies.
	 */

	var now = __webpack_require__(125);

	/**
	 * Returns a function, that, as long as it continues to be invoked, will not
	 * be triggered. The function will be called after it stops being called for
	 * N milliseconds. If `immediate` is passed, trigger the function on the
	 * leading edge, instead of the trailing.
	 *
	 * @source underscore.js
	 * @see http://unscriptable.com/2009/03/20/debouncing-javascript-methods/
	 * @param {Function} function to wrap
	 * @param {Number} timeout in ms (`100`)
	 * @param {Boolean} whether to execute at the beginning (`false`)
	 * @api public
	 */

	module.exports = function debounce(func, wait, immediate){
	  var timeout, args, context, timestamp, result;
	  if (null == wait) wait = 100;

	  function later() {
	    var last = now() - timestamp;

	    if (last < wait && last > 0) {
	      timeout = setTimeout(later, wait - last);
	    } else {
	      timeout = null;
	      if (!immediate) {
	        result = func.apply(context, args);
	        if (!timeout) context = args = null;
	      }
	    }
	  };

	  return function debounced() {
	    context = this;
	    args = arguments;
	    timestamp = now();
	    var callNow = immediate && !timeout;
	    if (!timeout) timeout = setTimeout(later, wait);
	    if (callNow) {
	      result = func.apply(context, args);
	      context = args = null;
	    }

	    return result;
	  };
	};


/***/ },

/***/ 125:
/***/ function(module, exports) {

	module.exports = Date.now || now

	function now() {
	    return new Date().getTime()
	}


/***/ },

/***/ 126:
69,

/***/ 146:
/***/ function(module, exports) {

	module.exports = {
		"destiny": [
			"Dravewin",
			"INFESTINY",
			"FIDGETLOL",
			"Hhhehhehe",
			"GameOfThrows",
			"WORTH",
			"FeedNathan",
			"Abathur",
			"LUL",
			"Heimerdonger",
			"SoSad",
			"DURRSTINY",
			"SURPRISE",
			"NoTears",
			"OverRustle",
			"DuckerZ",
			"Kappa",
			"Klappa",
			"DappaKappa",
			"BibleThump",
			"AngelThump",
			"FrankerZ",
			"BasedGod",
			"OhKrappa",
			"SoDoge",
			"WhoahDude",
			"MotherFuckinGame",
			"DaFeels",
			"UWOTM8",
			"CallCatz",
			"CallChad",
			"DatGeoff",
			"Disgustiny",
			"FerretLOL",
			"Sippy",
			"DestiSenpaii",
			"Nappa",
			"DAFUK",
			"AYYYLMAO",
			"DANKMEMES",
			"MLADY",
			"SOTRIGGERED",
			"MASTERB8",
			"NOTMYTEMPO",
			"LIES",
			"LeRuse",
			"YEE",
			"SWEATSTINY",
			"PEPE",
			"CheekerZ",
			"SpookerZ",
			"SLEEPSTINY",
			"PICNIC",
			"Memegasm",
			"WEEWOO",
			"KappaRoss",
			"ASLAN",
			"DJAslan",
			"TRUMPED",
			"BASEDWATM8",
			"BERN",
			"HmmStiny",
			"PepoThink"
		],
		"twitch": [
			"nathanD",
			"nathanDank",
			"nathanF",
			"nathanNotears",
			"nathanPepe",
			"nathanRustle",
			"nathanTowel",
			"nathanWat",
			"nathanThinking",
			"nathanRuse",
			"nathanYee",
			"nathanWeeb"
		]
	};

/***/ },

/***/ 147:
/***/ function(module, exports) {

	module.exports = [
		"NORTHWESTERNMUTUAL",
		"TRAVELERSINSURANCE",
		"AMERICANEXPRESS",
		"KERRYPROPERTIES",
		"SANDVIKCOROMANT",
		"AFAMILYCOMPANY",
		"AMERICANFAMILY",
		"BANANAREPUBLIC",
		"CANCERRESEARCH",
		"COOKINGCHANNEL",
		"KERRYLOGISTICS",
		"WEATHERCHANNEL",
		"INTERNATIONAL",
		"LIFEINSURANCE",
		"ORIENTEXPRESS",
		"SPREADBETTING",
		"TRAVELCHANNEL",
		"WOLTERSKLUWER",
		"CONSTRUCTION",
		"LPLFINANCIAL",
		"PAMPEREDCHEF",
		"SCHOLARSHIPS",
		"VERSICHERUNG",
		"ACCOUNTANTS",
		"BARCLAYCARD",
		"BLACKFRIDAY",
		"BLOCKBUSTER",
		"BRIDGESTONE",
		"CALVINKLEIN",
		"CONTRACTORS",
		"CREDITUNION",
		"ENGINEERING",
		"ENTERPRISES",
		"FOODNETWORK",
		"INVESTMENTS",
		"KERRYHOTELS",
		"LAMBORGHINI",
		"MOTORCYCLES",
		"OLAYANGROUP",
		"PHOTOGRAPHY",
		"PLAYSTATION",
		"PRODUCTIONS",
		"PROGRESSIVE",
		"REDUMBRELLA",
		"RIGHTATHOME",
		"WILLIAMHILL",
		"ACCOUNTANT",
		"APARTMENTS",
		"ASSOCIATES",
		"BASKETBALL",
		"BNPPARIBAS",
		"BOEHRINGER",
		"CAPITALONE",
		"CONSULTING",
		"CREDITCARD",
		"CUISINELLA",
		"EUROVISION",
		"EXTRASPACE",
		"FOUNDATION",
		"HEALTHCARE",
		"IMMOBILIEN",
		"INDUSTRIES",
		"MANAGEMENT",
		"MITSUBISHI",
		"NATIONWIDE",
		"NEWHOLLAND",
		"NEXTDIRECT",
		"ONYOURSIDE",
		"PROPERTIES",
		"PROTECTION",
		"PRUDENTIAL",
		"REALESTATE",
		"REPUBLICAN",
		"RESTAURANT",
		"SCHAEFFLER",
		"SWIFTCOVER",
		"TATAMOTORS",
		"TECHNOLOGY",
		"TELEFONICA",
		"UNIVERSITY",
		"VISTAPRINT",
		"VLAANDEREN",
		"VOLKSWAGEN",
		"ACCENTURE",
		"ALFAROMEO",
		"ALLFINANZ",
		"AMSTERDAM",
		"ANALYTICS",
		"AQUARELLE",
		"BARCELONA",
		"BLOOMBERG",
		"CHRISTMAS",
		"COMMUNITY",
		"DIRECTORY",
		"EDUCATION",
		"EQUIPMENT",
		"FAIRWINDS",
		"FINANCIAL",
		"FIRESTONE",
		"FRESENIUS",
		"FRONTDOOR",
		"FUJIXEROX",
		"FURNITURE",
		"GOLDPOINT",
		"GOODHANDS",
		"HISAMITSU",
		"HOMEDEPOT",
		"HOMEGOODS",
		"HOMESENSE",
		"HONEYWELL",
		"INSTITUTE",
		"INSURANCE",
		"KUOKGROUP",
		"LADBROKES",
		"LANCASTER",
		"LANDROVER",
		"LIFESTYLE",
		"MARKETING",
		"MARSHALLS",
		"MCDONALDS",
		"MELBOURNE",
		"MICROSOFT",
		"MONTBLANC",
		"PANASONIC",
		"PASSAGENS",
		"PRAMERICA",
		"RICHARDLI",
		"SCJOHNSON",
		"SHANGRILA",
		"SOLUTIONS",
		"STATEBANK",
		"STATEFARM",
		"STOCKHOLM",
		"TRAVELERS",
		"VACATIONS",
		"YODOBASHI",
		"ABUDHABI",
		"AIRFORCE",
		"ALLSTATE",
		"ATTORNEY",
		"BARCLAYS",
		"BAREFOOT",
		"BARGAINS",
		"BASEBALL",
		"BOUTIQUE",
		"BRADESCO",
		"BROADWAY",
		"BRUSSELS",
		"BUDAPEST",
		"BUILDERS",
		"BUSINESS",
		"CAPETOWN",
		"CATERING",
		"CATHOLIC",
		"CHRYSLER",
		"CIPRIANI",
		"CITYEATS",
		"CLEANING",
		"CLINIQUE",
		"CLOTHING",
		"COMMBANK",
		"COMPUTER",
		"DELIVERY",
		"DELOITTE",
		"DEMOCRAT",
		"DIAMONDS",
		"DISCOUNT",
		"DISCOVER",
		"DOWNLOAD",
		"ENGINEER",
		"ERICSSON",
		"ESURANCE",
		"EVERBANK",
		"EXCHANGE",
		"FEEDBACK",
		"FIDELITY",
		"FIRMDALE",
		"FOOTBALL",
		"FRONTIER",
		"GOODYEAR",
		"GRAINGER",
		"GRAPHICS",
		"GUARDIAN",
		"HDFCBANK",
		"HELSINKI",
		"HOLDINGS",
		"HOSPITAL",
		"INFINITI",
		"IPIRANGA",
		"ISTANBUL",
		"JPMORGAN",
		"LIGHTING",
		"LUNDBECK",
		"MARRIOTT",
		"MASERATI",
		"MCKINSEY",
		"MEMORIAL",
		"MORTGAGE",
		"MOVISTAR",
		"OBSERVER",
		"PARTNERS",
		"PHARMACY",
		"PICTURES",
		"PLUMBING",
		"PROPERTY",
		"REDSTONE",
		"RELIANCE",
		"SAARLAND",
		"SAMSCLUB",
		"SECURITY",
		"SERVICES",
		"SHOPPING",
		"SHOWTIME",
		"SOFTBANK",
		"SOFTWARE",
		"STCGROUP",
		"SUPPLIES",
		"SYMANTEC",
		"TELECITY",
		"TRAINING",
		"UCONNECT",
		"VANGUARD",
		"VENTURES",
		"VERISIGN",
		"WOODSIDE",
		"YOKOHAMA",
		"ABOGADO",
		"ACADEMY",
		"AGAKHAN",
		"ALIBABA",
		"ANDROID",
		"ATHLETA",
		"AUCTION",
		"AUDIBLE",
		"AUSPOST",
		"AVIANCA",
		"BANAMEX",
		"BAUHAUS",
		"BENTLEY",
		"BESTBUY",
		"BOOKING",
		"BROTHER",
		"BUGATTI",
		"CAPITAL",
		"CARAVAN",
		"CAREERS",
		"CARTIER",
		"CHANNEL",
		"CHINTAI",
		"CITADEL",
		"CLUBMED",
		"COLLEGE",
		"COLOGNE",
		"COMCAST",
		"COMPANY",
		"COMPARE",
		"CONTACT",
		"COOKING",
		"CORSICA",
		"COUNTRY",
		"COUPONS",
		"COURSES",
		"CRICKET",
		"CRUISES",
		"DENTIST",
		"DIGITAL",
		"DOMAINS",
		"EXPOSED",
		"EXPRESS",
		"FARMERS",
		"FASHION",
		"FERRARI",
		"FERRERO",
		"FINANCE",
		"FISHING",
		"FITNESS",
		"FLIGHTS",
		"FLORIST",
		"FLOWERS",
		"FORSALE",
		"FROGANS",
		"FUJITSU",
		"GALLERY",
		"GENTING",
		"GODADDY",
		"GUITARS",
		"HAMBURG",
		"HANGOUT",
		"HITACHI",
		"HOLIDAY",
		"HOSTING",
		"HOTELES",
		"HOTMAIL",
		"HYUNDAI",
		"ISELECT",
		"ISMAILI",
		"JEWELRY",
		"JUNIPER",
		"KITCHEN",
		"KOMATSU",
		"LACAIXA",
		"LANCOME",
		"LANXESS",
		"LASALLE",
		"LATROBE",
		"LECLERC",
		"LIAISON",
		"LIMITED",
		"LINCOLN",
		"MARKETS",
		"METLIFE",
		"MONSTER",
		"NETBANK",
		"NETFLIX",
		"NETWORK",
		"NEUSTAR",
		"OKINAWA",
		"OLDNAVY",
		"ORGANIC",
		"ORIGINS",
		"PANERAI",
		"PHILIPS",
		"PIONEER",
		"POLITIE",
		"REALTOR",
		"RECIPES",
		"RENTALS",
		"REVIEWS",
		"REXROTH",
		"SAMSUNG",
		"SANDVIK",
		"SCHMIDT",
		"SCHWARZ",
		"SCIENCE",
		"SHIKSHA",
		"SHRIRAM",
		"SINGLES",
		"SPIEGEL",
		"STAPLES",
		"STARHUB",
		"STATOIL",
		"STORAGE",
		"SUPPORT",
		"SURGERY",
		"SYSTEMS",
		"TEMASEK",
		"THEATER",
		"THEATRE",
		"TICKETS",
		"TIFFANY",
		"TOSHIBA",
		"TRADING",
		"WALMART",
		"WANGGOU",
		"WATCHES",
		"WEATHER",
		"WEBSITE",
		"WEDDING",
		"WHOSWHO",
		"WINDOWS",
		"WINNERS",
		"XFINITY",
		"YAMAXUN",
		"YOUTUBE",
		"ZUERICH",
		"ABARTH",
		"ABBOTT",
		"ABBVIE",
		"ACTIVE",
		"AFRICA",
		"AGENCY",
		"AIRBUS",
		"AIRTEL",
		"ALIPAY",
		"ALSACE",
		"ALSTOM",
		"ANQUAN",
		"ARAMCO",
		"AUTHOR",
		"BAYERN",
		"BEAUTY",
		"BERLIN",
		"BHARTI",
		"BLANCO",
		"BOSTIK",
		"BOSTON",
		"BROKER",
		"CAMERA",
		"CAREER",
		"CASEIH",
		"CASINO",
		"CENTER",
		"CHANEL",
		"CHROME",
		"CHURCH",
		"CIRCLE",
		"CLAIMS",
		"CLINIC",
		"COFFEE",
		"COMSEC",
		"CONDOS",
		"COUPON",
		"CREDIT",
		"CRUISE",
		"DATING",
		"DATSUN",
		"DEALER",
		"DEGREE",
		"DENTAL",
		"DESIGN",
		"DIRECT",
		"DOCTOR",
		"DUNLOP",
		"DUPONT",
		"DURBAN",
		"EMERCK",
		"ENERGY",
		"ESTATE",
		"EVENTS",
		"EXPERT",
		"FAMILY",
		"FLICKR",
		"FUTBOL",
		"GALLUP",
		"GARDEN",
		"GEORGE",
		"GIVING",
		"GLOBAL",
		"GOOGLE",
		"GRATIS",
		"HEALTH",
		"HERMES",
		"HIPHOP",
		"HOCKEY",
		"HUGHES",
		"IMAMAT",
		"INSURE",
		"INTUIT",
		"JAGUAR",
		"JOBURG",
		"JUEGOS",
		"KAUFEN",
		"KINDER",
		"KINDLE",
		"KOSHER",
		"LANCIA",
		"LATINO",
		"LAWYER",
		"LEFRAK",
		"LIVING",
		"LOCKER",
		"LONDON",
		"LUXURY",
		"MADRID",
		"MAISON",
		"MAKEUP",
		"MARKET",
		"MATTEL",
		"MOBILE",
		"MOBILY",
		"MONASH",
		"MORMON",
		"MOSCOW",
		"MUSEUM",
		"MUTUAL",
		"NAGOYA",
		"NATURA",
		"NISSAN",
		"NISSAY",
		"NORTON",
		"NOWRUZ",
		"OFFICE",
		"OLAYAN",
		"ONLINE",
		"ORACLE",
		"ORANGE",
		"OTSUKA",
		"PFIZER",
		"PHOTOS",
		"PHYSIO",
		"PIAGET",
		"PICTET",
		"QUEBEC",
		"RACING",
		"REALTY",
		"REISEN",
		"REPAIR",
		"REPORT",
		"REVIEW",
		"ROCHER",
		"ROGERS",
		"RYUKYU",
		"SAFETY",
		"SAKURA",
		"SANOFI",
		"SCHOOL",
		"SCHULE",
		"SECURE",
		"SELECT",
		"SHOUJI",
		"SOCCER",
		"SOCIAL",
		"STREAM",
		"STUDIO",
		"SUPPLY",
		"SUZUKI",
		"SWATCH",
		"SYDNEY",
		"TAIPEI",
		"TAOBAO",
		"TARGET",
		"TATTOO",
		"TENNIS",
		"TIENDA",
		"TJMAXX",
		"TKMAXX",
		"TOYOTA",
		"TRAVEL",
		"UNICOM",
		"VIAJES",
		"VIKING",
		"VILLAS",
		"VIRGIN",
		"VISION",
		"VOTING",
		"VOYAGE",
		"VUELOS",
		"WALTER",
		"WARMAN",
		"WEBCAM",
		"XIHUAN",
		"XPERIA",
		"YACHTS",
		"YANDEX",
		"ZAPPOS",
		"ACTOR",
		"ADULT",
		"AETNA",
		"AMFAM",
		"AMICA",
		"APPLE",
		"ARCHI",
		"AUDIO",
		"AUTOS",
		"AZURE",
		"BAIDU",
		"BEATS",
		"BIBLE",
		"BINGO",
		"BLACK",
		"BOATS",
		"BOOTS",
		"BOSCH",
		"BUILD",
		"CANON",
		"CARDS",
		"CHASE",
		"CHEAP",
		"CHLOE",
		"CISCO",
		"CITIC",
		"CLICK",
		"CLOUD",
		"COACH",
		"CODES",
		"CROWN",
		"CYMRU",
		"DABUR",
		"DANCE",
		"DEALS",
		"DELTA",
		"DODGE",
		"DRIVE",
		"DUBAI",
		"EARTH",
		"EDEKA",
		"EMAIL",
		"EPOST",
		"EPSON",
		"FAITH",
		"FEDEX",
		"FINAL",
		"FOREX",
		"FORUM",
		"GALLO",
		"GAMES",
		"GIFTS",
		"GIVES",
		"GLADE",
		"GLASS",
		"GLOBO",
		"GMAIL",
		"GREEN",
		"GRIPE",
		"GROUP",
		"GUCCI",
		"GUIDE",
		"HOMES",
		"HONDA",
		"HORSE",
		"HOUSE",
		"HYATT",
		"IKANO",
		"INTEL",
		"IRISH",
		"IVECO",
		"JETZT",
		"KOELN",
		"KYOTO",
		"LAMER",
		"LEASE",
		"LEGAL",
		"LEXUS",
		"LILLY",
		"LINDE",
		"LIPSY",
		"LIXIL",
		"LOANS",
		"LOCUS",
		"LOTTE",
		"LOTTO",
		"LUPIN",
		"MACYS",
		"MANGO",
		"MEDIA",
		"MIAMI",
		"MONEY",
		"MOPAR",
		"MOVIE",
		"NADEX",
		"NEXUS",
		"NIKON",
		"NINJA",
		"NOKIA",
		"NOWTV",
		"OMEGA",
		"OSAKA",
		"PARIS",
		"PARTS",
		"PARTY",
		"PHONE",
		"PHOTO",
		"PIZZA",
		"PLACE",
		"POKER",
		"PRAXI",
		"PRESS",
		"PRIME",
		"PROMO",
		"QUEST",
		"RADIO",
		"REHAB",
		"REISE",
		"RICOH",
		"ROCKS",
		"RODEO",
		"SALON",
		"SENER",
		"SEVEN",
		"SHARP",
		"SHELL",
		"SHOES",
		"SKYPE",
		"SLING",
		"SMART",
		"SMILE",
		"SOLAR",
		"SPACE",
		"STADA",
		"STORE",
		"STUDY",
		"STYLE",
		"SUCKS",
		"SWISS",
		"TATAR",
		"TIRES",
		"TIROL",
		"TMALL",
		"TODAY",
		"TOKYO",
		"TOOLS",
		"TORAY",
		"TOTAL",
		"TOURS",
		"TRADE",
		"TRUST",
		"TUNES",
		"TUSHU",
		"UBANK",
		"VEGAS",
		"VIDEO",
		"VISTA",
		"VODKA",
		"VOLVO",
		"WALES",
		"WATCH",
		"WEBER",
		"WEIBO",
		"WORKS",
		"WORLD",
		"XEROX",
		"YAHOO",
		"ZIPPO",
		"local",
		"onion",
		"AARP",
		"ABLE",
		"ADAC",
		"AERO",
		"AIGO",
		"AKDN",
		"ALLY",
		"AMEX",
		"ARMY",
		"ARPA",
		"ARTE",
		"ASDA",
		"ASIA",
		"AUDI",
		"AUTO",
		"BABY",
		"BAND",
		"BANK",
		"BBVA",
		"BEER",
		"BEST",
		"BIKE",
		"BING",
		"BLOG",
		"BLUE",
		"BOFA",
		"BOND",
		"BOOK",
		"BUZZ",
		"CAFE",
		"CALL",
		"CAMP",
		"CARE",
		"CARS",
		"CASA",
		"CASE",
		"CASH",
		"CBRE",
		"CERN",
		"CHAT",
		"CITI",
		"CITY",
		"CLUB",
		"COOL",
		"COOP",
		"CYOU",
		"DATA",
		"DATE",
		"DCLK",
		"DEAL",
		"DELL",
		"DESI",
		"DIET",
		"DISH",
		"DOCS",
		"DOHA",
		"DUCK",
		"DUNS",
		"DVAG",
		"ERNI",
		"FAGE",
		"FAIL",
		"FANS",
		"FARM",
		"FAST",
		"FIAT",
		"FIDO",
		"FILM",
		"FIRE",
		"FISH",
		"FLIR",
		"FOOD",
		"FORD",
		"FREE",
		"FUND",
		"GAME",
		"GBIZ",
		"GENT",
		"GGEE",
		"GIFT",
		"GMBH",
		"GOLD",
		"GOLF",
		"GOOG",
		"GUGE",
		"GURU",
		"HAIR",
		"HAUS",
		"HDFC",
		"HELP",
		"HERE",
		"HGTV",
		"HOST",
		"HSBC",
		"ICBC",
		"IEEE",
		"IMDB",
		"IMMO",
		"INFO",
		"ITAU",
		"JAVA",
		"JEEP",
		"JOBS",
		"JPRS",
		"KDDI",
		"KIWI",
		"KPMG",
		"KRED",
		"LAND",
		"LEGO",
		"LGBT",
		"LIDL",
		"LIFE",
		"LIKE",
		"LIMO",
		"LINK",
		"LIVE",
		"LOAN",
		"LOFT",
		"LOVE",
		"LTDA",
		"LUXE",
		"MAIF",
		"MEET",
		"MEME",
		"MENU",
		"MINI",
		"MINT",
		"MOBI",
		"MODA",
		"MOTO",
		"MTPC",
		"NAME",
		"NAVY",
		"NEWS",
		"NEXT",
		"NICO",
		"NIKE",
		"OLLO",
		"OPEN",
		"PAGE",
		"PARS",
		"PCCW",
		"PICS",
		"PING",
		"PINK",
		"PLAY",
		"PLUS",
		"POHL",
		"PORN",
		"POST",
		"PROD",
		"PROF",
		"QPON",
		"RAID",
		"READ",
		"REIT",
		"RENT",
		"REST",
		"RICH",
		"RMIT",
		"ROOM",
		"RSVP",
		"RUHR",
		"SAFE",
		"SALE",
		"SAPO",
		"SARL",
		"SAVE",
		"SAXO",
		"SCOR",
		"SCOT",
		"SEAT",
		"SEEK",
		"SEXY",
		"SHAW",
		"SHIA",
		"SHOP",
		"SHOW",
		"SILK",
		"SINA",
		"SITE",
		"SKIN",
		"SNCF",
		"SOHU",
		"SONG",
		"SONY",
		"SPOT",
		"STAR",
		"SURF",
		"TALK",
		"TAXI",
		"TEAM",
		"TECH",
		"TEVA",
		"TIAA",
		"TIPS",
		"TOWN",
		"TOYS",
		"TUBE",
		"VANA",
		"VISA",
		"VIVA",
		"VIVO",
		"VOTE",
		"VOTO",
		"WANG",
		"WEIR",
		"WIEN",
		"WIKI",
		"WINE",
		"WORK",
		"XBOX",
		"YOGA",
		"ZARA",
		"ZERO",
		"ZONE",
		"exit",
		"zkey",
		"AAA",
		"ABB",
		"ABC",
		"ACO",
		"ADS",
		"AEG",
		"AFL",
		"AIG",
		"ANZ",
		"AOL",
		"APP",
		"ART",
		"AWS",
		"AXA",
		"BAR",
		"BBC",
		"BBT",
		"BCG",
		"BCN",
		"BET",
		"BID",
		"BIO",
		"BIZ",
		"BMS",
		"BMW",
		"BNL",
		"BOM",
		"BOO",
		"BOT",
		"BOX",
		"BUY",
		"BZH",
		"CAB",
		"CAL",
		"CAM",
		"CAR",
		"CAT",
		"CBA",
		"CBN",
		"CBS",
		"CEB",
		"CEO",
		"CFA",
		"CFD",
		"COM",
		"CRS",
		"CSC",
		"DAD",
		"DAY",
		"DDS",
		"DEV",
		"DHL",
		"DIY",
		"DNP",
		"DOG",
		"DOT",
		"DTV",
		"DVR",
		"EAT",
		"ECO",
		"EDU",
		"ESQ",
		"EUS",
		"FAN",
		"FIT",
		"FLY",
		"FOO",
		"FOX",
		"FRL",
		"FTR",
		"FUN",
		"FYI",
		"GAL",
		"GAP",
		"GDN",
		"GEA",
		"GLE",
		"GMO",
		"GMX",
		"GOO",
		"GOP",
		"GOT",
		"GOV",
		"HBO",
		"HIV",
		"HKT",
		"HOT",
		"HOW",
		"HTC",
		"IBM",
		"ICE",
		"ICU",
		"IFM",
		"ING",
		"INK",
		"INT",
		"IST",
		"ITV",
		"IWC",
		"JCB",
		"JCP",
		"JIO",
		"JLC",
		"JLL",
		"JMP",
		"JNJ",
		"JOT",
		"JOY",
		"KFH",
		"KIA",
		"KIM",
		"KPN",
		"KRD",
		"LAT",
		"LAW",
		"LDS",
		"LOL",
		"LPL",
		"LTD",
		"MAN",
		"MBA",
		"MCD",
		"MED",
		"MEN",
		"MEO",
		"MIL",
		"MIT",
		"MLB",
		"MLS",
		"MMA",
		"MOE",
		"MOI",
		"MOM",
		"MOV",
		"MSD",
		"MTN",
		"MTR",
		"NAB",
		"NBA",
		"NEC",
		"NET",
		"NEW",
		"NFL",
		"NGO",
		"NHK",
		"NOW",
		"NRA",
		"NRW",
		"NTT",
		"NYC",
		"OBI",
		"OFF",
		"ONE",
		"ONG",
		"ONL",
		"OOO",
		"ORG",
		"OTT",
		"OVH",
		"PAY",
		"PET",
		"PID",
		"PIN",
		"PNC",
		"PRO",
		"PRU",
		"PUB",
		"PWC",
		"QVC",
		"RED",
		"REN",
		"RIL",
		"RIO",
		"RIP",
		"RUN",
		"RWE",
		"SAP",
		"SAS",
		"SBI",
		"SBS",
		"SCA",
		"SCB",
		"SES",
		"SEW",
		"SEX",
		"SFR",
		"SKI",
		"SKY",
		"SOY",
		"SRL",
		"SRT",
		"STC",
		"TAB",
		"TAX",
		"TCI",
		"TDK",
		"TEL",
		"THD",
		"TJX",
		"TOP",
		"TRV",
		"TUI",
		"TVS",
		"UBS",
		"UNO",
		"UOL",
		"UPS",
		"VET",
		"VIG",
		"VIN",
		"VIP",
		"WED",
		"WIN",
		"WME",
		"WOW",
		"WTC",
		"WTF",
		"XIN",
		"XXX",
		"XYZ",
		"YOU",
		"YUN",
		"ZIP",
		"bit",
		"gnu",
		"i2p",
		"AC",
		"AD",
		"AE",
		"AF",
		"AG",
		"AI",
		"AL",
		"AM",
		"AO",
		"AQ",
		"AR",
		"AS",
		"AT",
		"AU",
		"AW",
		"AX",
		"AZ",
		"BA",
		"BB",
		"BD",
		"BE",
		"BF",
		"BG",
		"BH",
		"BI",
		"BJ",
		"BM",
		"BN",
		"BO",
		"BR",
		"BS",
		"BT",
		"BV",
		"BW",
		"BY",
		"BZ",
		"CA",
		"CC",
		"CD",
		"CF",
		"CG",
		"CH",
		"CI",
		"CK",
		"CL",
		"CM",
		"CN",
		"CO",
		"CR",
		"CU",
		"CV",
		"CW",
		"CX",
		"CY",
		"CZ",
		"DE",
		"DJ",
		"DK",
		"DM",
		"DO",
		"DZ",
		"EC",
		"EE",
		"EG",
		"ER",
		"ES",
		"ET",
		"EU",
		"FI",
		"FJ",
		"FK",
		"FM",
		"FO",
		"FR",
		"GA",
		"GB",
		"GD",
		"GE",
		"GF",
		"GG",
		"GH",
		"GI",
		"GL",
		"GM",
		"GN",
		"GP",
		"GQ",
		"GR",
		"GS",
		"GT",
		"GU",
		"GW",
		"GY",
		"HK",
		"HM",
		"HN",
		"HR",
		"HT",
		"HU",
		"ID",
		"IE",
		"IL",
		"IM",
		"IN",
		"IO",
		"IQ",
		"IR",
		"IS",
		"IT",
		"JE",
		"JM",
		"JO",
		"JP",
		"KE",
		"KG",
		"KH",
		"KI",
		"KM",
		"KN",
		"KP",
		"KR",
		"KW",
		"KY",
		"KZ",
		"LA",
		"LB",
		"LC",
		"LI",
		"LK",
		"LR",
		"LS",
		"LT",
		"LU",
		"LV",
		"LY",
		"MA",
		"MC",
		"MD",
		"ME",
		"MG",
		"MH",
		"MK",
		"ML",
		"MM",
		"MN",
		"MO",
		"MP",
		"MQ",
		"MR",
		"MS",
		"MT",
		"MU",
		"MV",
		"MW",
		"MX",
		"MY",
		"MZ",
		"NA",
		"NC",
		"NE",
		"NF",
		"NG",
		"NI",
		"NL",
		"NO",
		"NP",
		"NR",
		"NU",
		"NZ",
		"OM",
		"PA",
		"PE",
		"PF",
		"PG",
		"PH",
		"PK",
		"PL",
		"PM",
		"PN",
		"PR",
		"PS",
		"PT",
		"PW",
		"PY",
		"QA",
		"RE",
		"RO",
		"RS",
		"RU",
		"RW",
		"SA",
		"SB",
		"SC",
		"SD",
		"SE",
		"SG",
		"SH",
		"SI",
		"SJ",
		"SK",
		"SL",
		"SM",
		"SN",
		"SO",
		"SR",
		"ST",
		"SU",
		"SV",
		"SX",
		"SY",
		"SZ",
		"TC",
		"TD",
		"TF",
		"TG",
		"TH",
		"TJ",
		"TK",
		"TL",
		"TM",
		"TN",
		"TO",
		"TR",
		"TT",
		"TV",
		"TW",
		"TZ",
		"UA",
		"UG",
		"UK",
		"US",
		"UY",
		"UZ",
		"VA",
		"VC",
		"VE",
		"VG",
		"VI",
		"VN",
		"VU",
		"WF",
		"WS",
		"YE",
		"YT",
		"ZA",
		"ZM",
		"ZW"
	];

/***/ },

/***/ 148:
/***/ function(module, exports, __webpack_require__) {

	var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*! nanoScrollerJS - v0.8.7 - 2015
	* http://jamesflorentino.github.com/nanoScrollerJS/
	* Copyright (c) 2015 James Florentino; Licensed MIT */
	(function(factory) {
	  if (true) {
	    return !(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(4)], __WEBPACK_AMD_DEFINE_RESULT__ = function($) {
	      return factory($, window, document);
	    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__), __WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
	  } else if (typeof exports === 'object') {
	    return module.exports = factory(require('jquery'), window, document);
	  } else {
	    return factory(jQuery, window, document);
	  }
	})(function($, window, document) {
	  "use strict";
	  var BROWSER_IS_IE7, BROWSER_SCROLLBAR_WIDTH, DOMSCROLL, DOWN, DRAG, ENTER, KEYDOWN, KEYUP, MOUSEDOWN, MOUSEENTER, MOUSEMOVE, MOUSEUP, MOUSEWHEEL, NanoScroll, PANEDOWN, RESIZE, SCROLL, SCROLLBAR, TOUCHMOVE, UP, WHEEL, cAF, defaults, getBrowserScrollbarWidth, hasTransform, isFFWithBuggyScrollbar, rAF, transform, _elementStyle, _prefixStyle, _vendor;
	  defaults = {

	    /**
	      a classname for the pane element.
	      @property paneClass
	      @type String
	      @default 'nano-pane'
	     */
	    paneClass: 'nano-pane',

	    /**
	      a classname for the slider element.
	      @property sliderClass
	      @type String
	      @default 'nano-slider'
	     */
	    sliderClass: 'nano-slider',

	    /**
	      a classname for the content element.
	      @property contentClass
	      @type String
	      @default 'nano-content'
	     */
	    contentClass: 'nano-content',

	    /**
	      a setting to enable native scrolling in iOS devices.
	      @property iOSNativeScrolling
	      @type Boolean
	      @default false
	     */
	    iOSNativeScrolling: false,

	    /**
	      a setting to prevent the rest of the page being
	      scrolled when user scrolls the `.content` element.
	      @property preventPageScrolling
	      @type Boolean
	      @default false
	     */
	    preventPageScrolling: false,

	    /**
	      a setting to disable binding to the resize event.
	      @property disableResize
	      @type Boolean
	      @default false
	     */
	    disableResize: false,

	    /**
	      a setting to make the scrollbar always visible.
	      @property alwaysVisible
	      @type Boolean
	      @default false
	     */
	    alwaysVisible: false,

	    /**
	      a default timeout for the `flash()` method.
	      @property flashDelay
	      @type Number
	      @default 1500
	     */
	    flashDelay: 1500,

	    /**
	      a minimum height for the `.slider` element.
	      @property sliderMinHeight
	      @type Number
	      @default 20
	     */
	    sliderMinHeight: 20,

	    /**
	      a maximum height for the `.slider` element.
	      @property sliderMaxHeight
	      @type Number
	      @default null
	     */
	    sliderMaxHeight: null,

	    /**
	      an alternate document context.
	      @property documentContext
	      @type Document
	      @default null
	     */
	    documentContext: null,

	    /**
	      an alternate window context.
	      @property windowContext
	      @type Window
	      @default null
	     */
	    windowContext: null
	  };

	  /**
	    @property SCROLLBAR
	    @type String
	    @static
	    @final
	    @private
	   */
	  SCROLLBAR = 'scrollbar';

	  /**
	    @property SCROLL
	    @type String
	    @static
	    @final
	    @private
	   */
	  SCROLL = 'scroll';

	  /**
	    @property MOUSEDOWN
	    @type String
	    @final
	    @private
	   */
	  MOUSEDOWN = 'mousedown';

	  /**
	    @property MOUSEENTER
	    @type String
	    @final
	    @private
	   */
	  MOUSEENTER = 'mouseenter';

	  /**
	    @property MOUSEMOVE
	    @type String
	    @static
	    @final
	    @private
	   */
	  MOUSEMOVE = 'mousemove';

	  /**
	    @property MOUSEWHEEL
	    @type String
	    @final
	    @private
	   */
	  MOUSEWHEEL = 'mousewheel';

	  /**
	    @property MOUSEUP
	    @type String
	    @static
	    @final
	    @private
	   */
	  MOUSEUP = 'mouseup';

	  /**
	    @property RESIZE
	    @type String
	    @final
	    @private
	   */
	  RESIZE = 'resize';

	  /**
	    @property DRAG
	    @type String
	    @static
	    @final
	    @private
	   */
	  DRAG = 'drag';

	  /**
	    @property ENTER
	    @type String
	    @static
	    @final
	    @private
	   */
	  ENTER = 'enter';

	  /**
	    @property UP
	    @type String
	    @static
	    @final
	    @private
	   */
	  UP = 'up';

	  /**
	    @property PANEDOWN
	    @type String
	    @static
	    @final
	    @private
	   */
	  PANEDOWN = 'panedown';

	  /**
	    @property DOMSCROLL
	    @type String
	    @static
	    @final
	    @private
	   */
	  DOMSCROLL = 'DOMMouseScroll';

	  /**
	    @property DOWN
	    @type String
	    @static
	    @final
	    @private
	   */
	  DOWN = 'down';

	  /**
	    @property WHEEL
	    @type String
	    @static
	    @final
	    @private
	   */
	  WHEEL = 'wheel';

	  /**
	    @property KEYDOWN
	    @type String
	    @static
	    @final
	    @private
	   */
	  KEYDOWN = 'keydown';

	  /**
	    @property KEYUP
	    @type String
	    @static
	    @final
	    @private
	   */
	  KEYUP = 'keyup';

	  /**
	    @property TOUCHMOVE
	    @type String
	    @static
	    @final
	    @private
	   */
	  TOUCHMOVE = 'touchmove';

	  /**
	    @property BROWSER_IS_IE7
	    @type Boolean
	    @static
	    @final
	    @private
	   */
	  BROWSER_IS_IE7 = window.navigator.appName === 'Microsoft Internet Explorer' && /msie 7./i.test(window.navigator.appVersion) && window.ActiveXObject;

	  /**
	    @property BROWSER_SCROLLBAR_WIDTH
	    @type Number
	    @static
	    @default null
	    @private
	   */
	  BROWSER_SCROLLBAR_WIDTH = null;
	  rAF = window.requestAnimationFrame;
	  cAF = window.cancelAnimationFrame;
	  _elementStyle = document.createElement('div').style;
	  _vendor = (function() {
	    var i, transform, vendor, vendors, _i, _len;
	    vendors = ['t', 'webkitT', 'MozT', 'msT', 'OT'];
	    for (i = _i = 0, _len = vendors.length; _i < _len; i = ++_i) {
	      vendor = vendors[i];
	      transform = vendors[i] + 'ransform';
	      if (transform in _elementStyle) {
	        return vendors[i].substr(0, vendors[i].length - 1);
	      }
	    }
	    return false;
	  })();
	  _prefixStyle = function(style) {
	    if (_vendor === false) {
	      return false;
	    }
	    if (_vendor === '') {
	      return style;
	    }
	    return _vendor + style.charAt(0).toUpperCase() + style.substr(1);
	  };
	  transform = _prefixStyle('transform');
	  hasTransform = transform !== false;

	  /**
	    Returns browser's native scrollbar width
	    @method getBrowserScrollbarWidth
	    @return {Number} the scrollbar width in pixels
	    @static
	    @private
	   */
	  getBrowserScrollbarWidth = function() {
	    var outer, outerStyle, scrollbarWidth;
	    outer = document.createElement('div');
	    outerStyle = outer.style;
	    outerStyle.position = 'absolute';
	    outerStyle.width = '100px';
	    outerStyle.height = '100px';
	    outerStyle.overflow = SCROLL;
	    outerStyle.top = '-9999px';
	    document.body.appendChild(outer);
	    scrollbarWidth = outer.offsetWidth - outer.clientWidth;
	    document.body.removeChild(outer);
	    return scrollbarWidth;
	  };
	  isFFWithBuggyScrollbar = function() {
	    var isOSXFF, ua, version;
	    ua = window.navigator.userAgent;
	    isOSXFF = /(?=.+Mac OS X)(?=.+Firefox)/.test(ua);
	    if (!isOSXFF) {
	      return false;
	    }
	    version = /Firefox\/\d{2}\./.exec(ua);
	    if (version) {
	      version = version[0].replace(/\D+/g, '');
	    }
	    return isOSXFF && +version > 23;
	  };

	  /**
	    @class NanoScroll
	    @param element {HTMLElement|Node} the main element
	    @param options {Object} nanoScroller's options
	    @constructor
	   */
	  NanoScroll = (function() {
	    function NanoScroll(el, options) {
	      this.el = el;
	      this.options = options;
	      BROWSER_SCROLLBAR_WIDTH || (BROWSER_SCROLLBAR_WIDTH = getBrowserScrollbarWidth());
	      this.$el = $(this.el);
	      this.doc = $(this.options.documentContext || document);
	      this.win = $(this.options.windowContext || window);
	      this.body = this.doc.find('body');
	      this.$content = this.$el.children("." + this.options.contentClass);
	      this.$content.attr('tabindex', this.options.tabIndex || 0);
	      this.content = this.$content[0];
	      this.previousPosition = 0;
	      if (this.options.iOSNativeScrolling && (this.el.style.WebkitOverflowScrolling != null)) {
	        this.nativeScrolling();
	      } else {
	        this.generate();
	      }
	      this.createEvents();
	      this.addEvents();
	      this.reset();
	    }


	    /**
	      Prevents the rest of the page being scrolled
	      when user scrolls the `.nano-content` element.
	      @method preventScrolling
	      @param event {Event}
	      @param direction {String} Scroll direction (up or down)
	      @private
	     */

	    NanoScroll.prototype.preventScrolling = function(e, direction) {
	      if (!this.isActive) {
	        return;
	      }
	      if (e.type === DOMSCROLL) {
	        if (direction === DOWN && e.originalEvent.detail > 0 || direction === UP && e.originalEvent.detail < 0) {
	          e.preventDefault();
	        }
	      } else if (e.type === MOUSEWHEEL) {
	        if (!e.originalEvent || !e.originalEvent.wheelDelta) {
	          return;
	        }
	        if (direction === DOWN && e.originalEvent.wheelDelta < 0 || direction === UP && e.originalEvent.wheelDelta > 0) {
	          e.preventDefault();
	        }
	      }
	    };


	    /**
	      Enable iOS native scrolling
	      @method nativeScrolling
	      @private
	     */

	    NanoScroll.prototype.nativeScrolling = function() {
	      this.$content.css({
	        WebkitOverflowScrolling: 'touch'
	      });
	      this.iOSNativeScrolling = true;
	      this.isActive = true;
	    };


	    /**
	      Updates those nanoScroller properties that
	      are related to current scrollbar position.
	      @method updateScrollValues
	      @private
	     */

	    NanoScroll.prototype.updateScrollValues = function() {
	      var content, direction;
	      content = this.content;
	      this.maxScrollTop = content.scrollHeight - content.clientHeight;
	      this.prevScrollTop = this.contentScrollTop || 0;
	      this.contentScrollTop = content.scrollTop;
	      direction = this.contentScrollTop > this.previousPosition ? "down" : this.contentScrollTop < this.previousPosition ? "up" : "same";
	      this.previousPosition = this.contentScrollTop;
	      if (direction !== "same") {
	        this.$el.trigger('update', {
	          position: this.contentScrollTop,
	          maximum: this.maxScrollTop,
	          direction: direction
	        });
	      }
	      if (!this.iOSNativeScrolling) {
	        this.maxSliderTop = this.paneHeight - this.sliderHeight;
	        this.sliderTop = this.maxScrollTop === 0 ? 0 : this.contentScrollTop * this.maxSliderTop / this.maxScrollTop;
	      }
	    };


	    /**
	      Updates CSS styles for current scroll position.
	      Uses CSS 2d transfroms and `window.requestAnimationFrame` if available.
	      @method setOnScrollStyles
	      @private
	     */

	    NanoScroll.prototype.setOnScrollStyles = function() {
	      var cssValue;
	      if (hasTransform) {
	        cssValue = {};
	        cssValue[transform] = "translate(0, " + this.sliderTop + "px)";
	      } else {
	        cssValue = {
	          top: this.sliderTop
	        };
	      }
	      if (rAF) {
	        if (cAF && this.scrollRAF) {
	          cAF(this.scrollRAF);
	        }
	        this.scrollRAF = rAF((function(_this) {
	          return function() {
	            _this.scrollRAF = null;
	            return _this.slider.css(cssValue);
	          };
	        })(this));
	      } else {
	        this.slider.css(cssValue);
	      }
	    };


	    /**
	      Creates event related methods
	      @method createEvents
	      @private
	     */

	    NanoScroll.prototype.createEvents = function() {
	      this.events = {
	        down: (function(_this) {
	          return function(e) {
	            _this.isBeingDragged = true;
	            _this.offsetY = e.pageY - _this.slider.offset().top;
	            if (!_this.slider.is(e.target)) {
	              _this.offsetY = 0;
	            }
	            _this.pane.addClass('active');
	            _this.doc.bind(MOUSEMOVE, _this.events[DRAG]).bind(MOUSEUP, _this.events[UP]);
	            _this.body.bind(MOUSEENTER, _this.events[ENTER]);
	            return false;
	          };
	        })(this),
	        drag: (function(_this) {
	          return function(e) {
	            _this.sliderY = e.pageY - _this.$el.offset().top - _this.paneTop - (_this.offsetY || _this.sliderHeight * 0.5);
	            _this.scroll();
	            if (_this.contentScrollTop >= _this.maxScrollTop && _this.prevScrollTop !== _this.maxScrollTop) {
	              _this.$el.trigger('scrollend');
	            } else if (_this.contentScrollTop === 0 && _this.prevScrollTop !== 0) {
	              _this.$el.trigger('scrolltop');
	            }
	            return false;
	          };
	        })(this),
	        up: (function(_this) {
	          return function(e) {
	            _this.isBeingDragged = false;
	            _this.pane.removeClass('active');
	            _this.doc.unbind(MOUSEMOVE, _this.events[DRAG]).unbind(MOUSEUP, _this.events[UP]);
	            _this.body.unbind(MOUSEENTER, _this.events[ENTER]);
	            return false;
	          };
	        })(this),
	        resize: (function(_this) {
	          return function(e) {
	            _this.reset();
	          };
	        })(this),
	        panedown: (function(_this) {
	          return function(e) {
	            _this.sliderY = (e.offsetY || e.originalEvent.layerY) - (_this.sliderHeight * 0.5);
	            _this.scroll();
	            _this.events.down(e);
	            return false;
	          };
	        })(this),
	        scroll: (function(_this) {
	          return function(e) {
	            _this.updateScrollValues();
	            if (_this.isBeingDragged) {
	              return;
	            }
	            if (!_this.iOSNativeScrolling) {
	              _this.sliderY = _this.sliderTop;
	              _this.setOnScrollStyles();
	            }
	            if (e == null) {
	              return;
	            }
	            if (_this.contentScrollTop >= _this.maxScrollTop) {
	              if (_this.options.preventPageScrolling) {
	                _this.preventScrolling(e, DOWN);
	              }
	              if (_this.prevScrollTop !== _this.maxScrollTop) {
	                _this.$el.trigger('scrollend');
	              }
	            } else if (_this.contentScrollTop === 0) {
	              if (_this.options.preventPageScrolling) {
	                _this.preventScrolling(e, UP);
	              }
	              if (_this.prevScrollTop !== 0) {
	                _this.$el.trigger('scrolltop');
	              }
	            }
	          };
	        })(this),
	        wheel: (function(_this) {
	          return function(e) {
	            var delta;
	            if (e == null) {
	              return;
	            }
	            delta = e.delta || e.wheelDelta || (e.originalEvent && e.originalEvent.wheelDelta) || -e.detail || (e.originalEvent && -e.originalEvent.detail);
	            if (delta) {
	              _this.sliderY += -delta / 3;
	            }
	            _this.scroll();
	            return false;
	          };
	        })(this),
	        enter: (function(_this) {
	          return function(e) {
	            var _ref;
	            if (!_this.isBeingDragged) {
	              return;
	            }
	            if ((e.buttons || e.which) !== 1) {
	              return (_ref = _this.events)[UP].apply(_ref, arguments);
	            }
	          };
	        })(this)
	      };
	    };


	    /**
	      Adds event listeners with jQuery.
	      @method addEvents
	      @private
	     */

	    NanoScroll.prototype.addEvents = function() {
	      var events;
	      this.removeEvents();
	      events = this.events;
	      if (!this.options.disableResize) {
	        this.win.bind(RESIZE, events[RESIZE]);
	      }
	      if (!this.iOSNativeScrolling) {
	        this.slider.bind(MOUSEDOWN, events[DOWN]);
	        this.pane.bind(MOUSEDOWN, events[PANEDOWN]).bind("" + MOUSEWHEEL + " " + DOMSCROLL, events[WHEEL]);
	      }
	      this.$content.bind("" + SCROLL + " " + MOUSEWHEEL + " " + DOMSCROLL + " " + TOUCHMOVE, events[SCROLL]);
	    };


	    /**
	      Removes event listeners with jQuery.
	      @method removeEvents
	      @private
	     */

	    NanoScroll.prototype.removeEvents = function() {
	      var events;
	      events = this.events;
	      this.win.unbind(RESIZE, events[RESIZE]);
	      if (!this.iOSNativeScrolling) {
	        this.slider.unbind();
	        this.pane.unbind();
	      }
	      this.$content.unbind("" + SCROLL + " " + MOUSEWHEEL + " " + DOMSCROLL + " " + TOUCHMOVE, events[SCROLL]);
	    };


	    /**
	      Generates nanoScroller's scrollbar and elements for it.
	      @method generate
	      @chainable
	      @private
	     */

	    NanoScroll.prototype.generate = function() {
	      var contentClass, cssRule, currentPadding, options, pane, paneClass, sliderClass;
	      options = this.options;
	      paneClass = options.paneClass, sliderClass = options.sliderClass, contentClass = options.contentClass;
	      if (!(pane = this.$el.children("." + paneClass)).length && !pane.children("." + sliderClass).length) {
	        this.$el.append("<div class=\"" + paneClass + "\"><div class=\"" + sliderClass + "\" /></div>");
	      }
	      this.pane = this.$el.children("." + paneClass);
	      this.slider = this.pane.find("." + sliderClass);
	      if (BROWSER_SCROLLBAR_WIDTH === 0 && isFFWithBuggyScrollbar()) {
	        currentPadding = window.getComputedStyle(this.content, null).getPropertyValue('padding-right').replace(/[^0-9.]+/g, '');
	        cssRule = {
	          right: -14,
	          paddingRight: +currentPadding + 14
	        };
	      } else if (BROWSER_SCROLLBAR_WIDTH) {
	        cssRule = {
	          right: -BROWSER_SCROLLBAR_WIDTH
	        };
	        this.$el.addClass('has-scrollbar');
	      }
	      if (cssRule != null) {
	        this.$content.css(cssRule);
	      }
	      return this;
	    };


	    /**
	      @method restore
	      @private
	     */

	    NanoScroll.prototype.restore = function() {
	      this.stopped = false;
	      if (!this.iOSNativeScrolling) {
	        this.pane.show();
	      }
	      this.addEvents();
	    };


	    /**
	      Resets nanoScroller's scrollbar.
	      @method reset
	      @chainable
	      @example
	          $(".nano").nanoScroller();
	     */

	    NanoScroll.prototype.reset = function() {
	      var content, contentHeight, contentPosition, contentStyle, contentStyleOverflowY, paneBottom, paneHeight, paneOuterHeight, paneTop, parentMaxHeight, right, sliderHeight;
	      if (this.iOSNativeScrolling) {
	        this.contentHeight = this.content.scrollHeight;
	        return;
	      }
	      if (!this.$el.find("." + this.options.paneClass).length) {
	        this.generate().stop();
	      }
	      if (this.stopped) {
	        this.restore();
	      }
	      content = this.content;
	      contentStyle = content.style;
	      contentStyleOverflowY = contentStyle.overflowY;
	      if (BROWSER_IS_IE7) {
	        this.$content.css({
	          height: this.$content.height()
	        });
	      }
	      contentHeight = content.scrollHeight + BROWSER_SCROLLBAR_WIDTH;
	      parentMaxHeight = parseInt(this.$el.css("max-height"), 10);
	      if (parentMaxHeight > 0) {
	        this.$el.height("");
	        this.$el.height(content.scrollHeight > parentMaxHeight ? parentMaxHeight : content.scrollHeight);
	      }
	      paneHeight = this.pane.outerHeight(false);
	      paneTop = parseInt(this.pane.css('top'), 10);
	      paneBottom = parseInt(this.pane.css('bottom'), 10);
	      paneOuterHeight = paneHeight + paneTop + paneBottom;
	      sliderHeight = Math.round(paneOuterHeight / contentHeight * paneHeight);
	      if (sliderHeight < this.options.sliderMinHeight) {
	        sliderHeight = this.options.sliderMinHeight;
	      } else if ((this.options.sliderMaxHeight != null) && sliderHeight > this.options.sliderMaxHeight) {
	        sliderHeight = this.options.sliderMaxHeight;
	      }
	      if (contentStyleOverflowY === SCROLL && contentStyle.overflowX !== SCROLL) {
	        sliderHeight += BROWSER_SCROLLBAR_WIDTH;
	      }
	      this.maxSliderTop = paneOuterHeight - sliderHeight;
	      this.contentHeight = contentHeight;
	      this.paneHeight = paneHeight;
	      this.paneOuterHeight = paneOuterHeight;
	      this.sliderHeight = sliderHeight;
	      this.paneTop = paneTop;
	      this.slider.height(sliderHeight);
	      this.events.scroll();
	      this.pane.show();
	      this.isActive = true;
	      if ((content.scrollHeight === content.clientHeight) || (this.pane.outerHeight(true) >= content.scrollHeight && contentStyleOverflowY !== SCROLL)) {
	        this.pane.hide();
	        this.isActive = false;
	      } else if (this.el.clientHeight === content.scrollHeight && contentStyleOverflowY === SCROLL) {
	        this.slider.hide();
	      } else {
	        this.slider.show();
	      }
	      this.pane.css({
	        opacity: (this.options.alwaysVisible ? 1 : ''),
	        visibility: (this.options.alwaysVisible ? 'visible' : '')
	      });
	      contentPosition = this.$content.css('position');
	      if (contentPosition === 'static' || contentPosition === 'relative') {
	        right = parseInt(this.$content.css('right'), 10);
	        if (right) {
	          this.$content.css({
	            right: '',
	            marginRight: right
	          });
	        }
	      }
	      return this;
	    };


	    /**
	      @method scroll
	      @private
	      @example
	          $(".nano").nanoScroller({ scroll: 'top' });
	     */

	    NanoScroll.prototype.scroll = function() {
	      if (!this.isActive) {
	        return;
	      }
	      this.sliderY = Math.max(0, this.sliderY);
	      this.sliderY = Math.min(this.maxSliderTop, this.sliderY);
	      this.$content.scrollTop(this.maxScrollTop * this.sliderY / this.maxSliderTop);
	      if (!this.iOSNativeScrolling) {
	        this.updateScrollValues();
	        this.setOnScrollStyles();
	      }
	      return this;
	    };


	    /**
	      Scroll at the bottom with an offset value
	      @method scrollBottom
	      @param offsetY {Number}
	      @chainable
	      @example
	          $(".nano").nanoScroller({ scrollBottom: value });
	     */

	    NanoScroll.prototype.scrollBottom = function(offsetY) {
	      if (!this.isActive) {
	        return;
	      }
	      this.$content.scrollTop(this.contentHeight - this.$content.height() - offsetY).trigger(MOUSEWHEEL);
	      this.stop().restore();
	      return this;
	    };


	    /**
	      Scroll at the top with an offset value
	      @method scrollTop
	      @param offsetY {Number}
	      @chainable
	      @example
	          $(".nano").nanoScroller({ scrollTop: value });
	     */

	    NanoScroll.prototype.scrollTop = function(offsetY) {
	      if (!this.isActive) {
	        return;
	      }
	      this.$content.scrollTop(+offsetY).trigger(MOUSEWHEEL);
	      this.stop().restore();
	      return this;
	    };


	    /**
	      Scroll to an element
	      @method scrollTo
	      @param node {Node} A node to scroll to.
	      @chainable
	      @example
	          $(".nano").nanoScroller({ scrollTo: $('#a_node') });
	     */

	    NanoScroll.prototype.scrollTo = function(node) {
	      if (!this.isActive) {
	        return;
	      }
	      this.scrollTop(this.$el.find(node).get(0).offsetTop);
	      return this;
	    };


	    /**
	      To stop the operation.
	      This option will tell the plugin to disable all event bindings and hide the gadget scrollbar from the UI.
	      @method stop
	      @chainable
	      @example
	          $(".nano").nanoScroller({ stop: true });
	     */

	    NanoScroll.prototype.stop = function() {
	      if (cAF && this.scrollRAF) {
	        cAF(this.scrollRAF);
	        this.scrollRAF = null;
	      }
	      this.stopped = true;
	      this.removeEvents();
	      if (!this.iOSNativeScrolling) {
	        this.pane.hide();
	      }
	      return this;
	    };


	    /**
	      Destroys nanoScroller and restores browser's native scrollbar.
	      @method destroy
	      @chainable
	      @example
	          $(".nano").nanoScroller({ destroy: true });
	     */

	    NanoScroll.prototype.destroy = function() {
	      if (!this.stopped) {
	        this.stop();
	      }
	      if (!this.iOSNativeScrolling && this.pane.length) {
	        this.pane.remove();
	      }
	      if (BROWSER_IS_IE7) {
	        this.$content.height('');
	      }
	      this.$content.removeAttr('tabindex');
	      if (this.$el.hasClass('has-scrollbar')) {
	        this.$el.removeClass('has-scrollbar');
	        this.$content.css({
	          right: ''
	        });
	      }
	      return this;
	    };


	    /**
	      To flash the scrollbar gadget for an amount of time defined in plugin settings (defaults to 1,5s).
	      Useful if you want to show the user (e.g. on pageload) that there is more content waiting for him.
	      @method flash
	      @chainable
	      @example
	          $(".nano").nanoScroller({ flash: true });
	     */

	    NanoScroll.prototype.flash = function() {
	      if (this.iOSNativeScrolling) {
	        return;
	      }
	      if (!this.isActive) {
	        return;
	      }
	      this.reset();
	      this.pane.addClass('flashed');
	      setTimeout((function(_this) {
	        return function() {
	          _this.pane.removeClass('flashed');
	        };
	      })(this), this.options.flashDelay);
	      return this;
	    };

	    return NanoScroll;

	  })();
	  $.fn.nanoScroller = function(settings) {
	    return this.each(function() {
	      var options, scrollbar;
	      if (!(scrollbar = this.nanoscroller)) {
	        options = $.extend({}, defaults, settings);
	        this.nanoscroller = scrollbar = new NanoScroll(this, options);
	      }
	      if (settings && typeof settings === "object") {
	        $.extend(scrollbar.options, settings);
	        if (settings.scrollBottom != null) {
	          return scrollbar.scrollBottom(settings.scrollBottom);
	        }
	        if (settings.scrollTop != null) {
	          return scrollbar.scrollTop(settings.scrollTop);
	        }
	        if (settings.scrollTo) {
	          return scrollbar.scrollTo(settings.scrollTo);
	        }
	        if (settings.scroll === 'bottom') {
	          return scrollbar.scrollBottom(0);
	        }
	        if (settings.scroll === 'top') {
	          return scrollbar.scrollTop(0);
	        }
	        if (settings.scroll && settings.scroll instanceof $) {
	          return scrollbar.scrollTo(settings.scroll);
	        }
	        if (settings.stop) {
	          return scrollbar.stop();
	        }
	        if (settings.destroy) {
	          return scrollbar.destroy();
	        }
	        if (settings.flash) {
	          return scrollbar.flash();
	        }
	      }
	      return scrollbar.reset();
	    });
	  };
	  $.fn.nanoScroller.Constructor = NanoScroll;
	});

	//# sourceMappingURL=jquery.nanoscroller.js.map


/***/ },

/***/ 496:
69

});