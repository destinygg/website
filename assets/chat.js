/* global $, window */

require('bootstrap/dist/css/bootstrap.css');
require('bootstrap/dist/js/bootstrap.js');
require('./chat/css/style.scss');

const Chat = require('./chat/js/chat.js')['default'];
const emotes = require('./emotes.json');
const uri = `ws${window.location.protocol === 'https:' ? 's' : ''}://${window.location.host}/ws`;

$.when(
    new Promise(res => $.getJSON({
        url: '/api/chat/me',
        timeout: 5000
    }).done(res).fail(() => res(null))),
    new Promise(res => $.getJSON({
        url: '/api/chat/history',
        timeout: 5000
    }).done(res).fail(() => res(null)))
).then((me, history) =>
    new Chat()
      .withUserAndSettings(me)
      .withEmotes(emotes)
      .withGui()
      .withHistory(history)
      .withWhispers()
      .connect(uri)
);