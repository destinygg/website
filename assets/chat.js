/* global $, document, window */

require('bootstrap/dist/css/bootstrap.css');
require('bootstrap/dist/js/bootstrap.js');
require('font-awesome/scss/font-awesome.scss');
require('./fonts/roboto.scss');
require('./chat/css/style.scss');
require('./chat/css/onstream.scss');

// Keep the session alive
window.setInterval(function(){
    $.ajax({url: '/ping', method: 'get'});
}, 10*60*1000);

window.destiny = {};
window.destiny.loglevel = 0;
window.destiny.chat = new (require('./chat/js/chat.js')['default'])();