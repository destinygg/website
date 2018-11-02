require('dgg-chat-gui/assets/chat')

const Chat = require('dgg-chat-gui/assets/chat/js/chat')['default']
const chatUri = `ws${window.location.protocol === 'https:' ? 's' : ''}://${window.location.host}/ws`
const script = document.getElementById('chat-include')
const cacheKey = script.getAttribute('data-cache-key')
const cdnUrl = script.getAttribute('data-cdn')

const chat = new Chat().withGui().withSettings()
$.when(
    new Promise(res => $.getJSON('/api/chat/me').done(res).fail(() => res(null))),
    new Promise(res => $.getJSON('/api/chat/history').done(res).fail(() => res(null))),
    new Promise(res => $.getJSON(`${cdnUrl}/flairs/flairs.json?_=${cacheKey}`).done(res).fail(() => res(null))),
    new Promise(res => $.getJSON(`${cdnUrl}/emotes/emotes.json?_=${cacheKey}`).done(res).fail(() => res(null))),
    new Promise(res => res(Chat.loadCss(`${cdnUrl}/flairs/flairs.css?_=${cacheKey}`))),
    new Promise(res => res(Chat.loadCss(`${cdnUrl}/emotes/emotes.css?_=${cacheKey}`))),
)
    .then((settings, history, flairs, emotes) =>
        chat.withUserAndSettings(settings)
            .withEmotes(emotes)
            .withFlairs(flairs)
            .withHistory(history)
    )
    .then(chat => chat.connect(chatUri))
    .then(chat => chat.withWhispers())

// Keep the website session alive.
setInterval(() => $.ajax({url: `/ping`}), 10*60*1000)