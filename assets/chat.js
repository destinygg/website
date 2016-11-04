/* global $ */

require('bootstrap/dist/css/bootstrap.css');
require('bootstrap/dist/js/bootstrap.js');
require('font-awesome/scss/font-awesome.scss');
require('./fonts/roboto.scss');
require('./chat/css/style.scss');
require('./chat/css/onstream.scss');

window.destiny = require('./web/js/destiny.js')['default'];
window.Chat = require('./chat/js/chat.js')['default'];

window.setInterval(function(){
    $.ajax({url: '/ping', method: 'get'});
}, 10*60*1000);