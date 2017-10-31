require('dgg-chat-gui/assets/chat')

const Chat = require('dgg-chat-gui/assets/chat/js/chat')['default']
const emotes = require('dgg-chat-gui/assets/emotes.json')
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