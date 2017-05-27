/* global $, window */

require('bootstrap/dist/css/bootstrap.css');
require('bootstrap/dist/js/bootstrap.js');
require('font-awesome/scss/font-awesome.scss');
require('./fonts/roboto.scss');
require('./chat/css/style.scss');

const Chat = require('./chat/js/chat.js')['default'];
const emotes = require('./emotes.json');
const uri = `ws${window.location.protocol === 'https:' ? 's' : ''}://${window.location.host}/ws`;

$.when(
    new Promise(res => $.getJSON({
        url: '/chat/me',
        timeout: 5000
    }).done(res).fail(() => res(null))),
    new Promise(res => $.getJSON({
        url: '/chat/history',
        timeout: 5000
    }).done(res).fail(() => res(null)))
).then((user, history) =>
    new Chat()
      .withUser(user)
      .withEmotes(emotes)
      .withFormatters()
      .withGui()
      .withHistory(history)
      .withMessages()
      .connect(uri)
);