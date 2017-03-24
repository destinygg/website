/* global $, document, window */

require('bootstrap/dist/css/bootstrap.css');
require('bootstrap/dist/js/bootstrap.js');
require('font-awesome/scss/font-awesome.scss');
require('./fonts/roboto.scss');
require('./chat/css/style.scss');

window.setInterval(() => $.ajax({url: '/ping', method: 'get'}), 10*60*1000); // keep connection alive

window.destiny = {loglevel:0};
window.destiny.chat = (() => {

    function getUser(){
        return new Promise((resolve) => {
            $.getJSON({url: '/profile/info', timeout: 5000})
                .done(resolve)
                .fail(() => resolve(null))
        });
    }

    function getHistory(){
        return new Promise((resolve) => {
            $.getJSON({url: '/chat/history', timeout: 5000})
                .done(resolve)
                .fail(() => resolve(null))
        });
    }

    const chat = new (require('./chat/js/chat.js')['default'])();
    $.when(getUser(), getHistory()).then((user, history) => chat.init(user, history));
    return chat;

})();