/* global $, window */

require('core-js/es6')
require('jquery')
require('moment')
require('normalize.css')
require('font-awesome/scss/font-awesome.scss')
require('./chat/js/notification')
require('./chat/css/style.scss')
require('./chat/css/onstream.scss')

const Chat = require('./chat/js/chat.js')['default']
const emotes = require('./emotes.json')
const uri = `ws${window.location.protocol === 'https:' ? 's' : ''}://${window.location.host}/ws`

new Chat()
    .withEmotes(emotes)
    .withGui()
    .connect(uri)