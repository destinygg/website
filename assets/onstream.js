/* global $, document, window */

require('bootstrap/dist/css/bootstrap.css');
require('bootstrap/dist/js/bootstrap.js');
require('font-awesome/scss/font-awesome.scss');
require('./fonts/roboto.scss');
require('./chat/css/style.scss');
require('./chat/css/onstream.scss');

window.destiny = {};
window.destiny.chat = new (require('./chat/js/chat.js')['default'])();