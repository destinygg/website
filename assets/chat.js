/* global $, window */

require('core-js/es6')
require('jquery')
require('moment')
require('normalize.css')
require('font-awesome/scss/font-awesome.scss')
require('./chat/js/notification')
require('./chat/css/style.scss')

const Chat = require('./chat/js/chat.js')['default']
const emotes = require('./emotes.json')
const uri = `ws${window.location.protocol === 'https:' ? 's' : ''}://${window.location.host}/ws`

$.when(
    new Promise(res => $.getJSON('/api/chat/me').done(res).fail(() => res(null))),
    new Promise(res => $.getJSON('/api/chat/history').done(res).fail(() => res(null)))
).then((userAndSettings, history) =>
    new Chat()
      .withUserAndSettings(userAndSettings)
      .withEmotes(emotes)
      .withGui()
      .withHistory(history)
      .withWhispers()
      .connect(uri)
)