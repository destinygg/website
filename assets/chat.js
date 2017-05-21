/* global $, window */

//require('bootstrap/dist/css/bootstrap.css');
//require('bootstrap/dist/js/bootstrap.js');
require('font-awesome/scss/font-awesome.scss');
require('./fonts/roboto.scss');
require('./chat/css/style.scss');

const Chat = require('./chat/js/chat.js')['default'];
const emotes = require('./emotes.json');
//const uri = `ws${window.location.protocol === 'https:' ? 's' : ''}://${window.location.host}/ws`;
const uri = `wss://www.destiny.gg/ws`;

$.when(
    new Promise(resolve => $.getJSON({url: '/chat/me', timeout: 5000}).done(resolve).fail(() => resolve(null))),
    new Promise(resolve => $.getJSON({url: '/chat/history', timeout: 5000}).done(resolve).fail(() => resolve(null)))
).then((user, history) =>
    new Chat()
      .withUser(user)
      .withEmotes(emotes)
      .withFormatters()
      .withGui()
      .withHistory(history)
      .connect(uri)
);

$('#chat-login-msg').on('click', 'a', e => {
    e.preventDefault();
    try {
        if(window.self !== window.top){
            window.parent.location.href = $(e.target).attr('href') + '?follow=' + encodeURIComponent(window.parent.location.pathname);
            return;
        }
    } catch(ignored){}
    window.location.href = $(e.target).attr('href') + '?follow=' + encodeURIComponent(window.location.pathname);
});
