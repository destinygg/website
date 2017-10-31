require('dgg-chat-gui/assets/streamchat')

const Chat = require('dgg-chat-gui/assets/chat/js/chat')['default']
const emotes = require('dgg-chat-gui/assets/emotes.json')
const uri = `ws${window.location.protocol === 'https:' ? 's' : ''}://${window.location.host}/ws`

new Chat()
    .withEmotes(emotes)
    .withGui()
    .connect(uri)