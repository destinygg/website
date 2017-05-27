/* global $, window */

require('bootstrap/dist/css/bootstrap.css');
require('bootstrap/dist/js/bootstrap.js');
require('font-awesome/scss/font-awesome.scss');
require('./fonts/roboto.scss');
require('./chat/css/style.scss');
require('./chat/css/onstream.scss');

const Chat = require('./chat/js/chat.js')['default'];
const emotes = require('./emotes.json');
const uri = `ws${window.location.protocol === 'https:' ? 's' : ''}://${window.location.host}/ws`;

new Chat()
    .withEmotes(emotes)
    .withFormatters()
    .withGui()
    .connect(uri);