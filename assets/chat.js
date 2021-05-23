import {fetch} from 'whatwg-fetch'
import 'dgg-chat-gui/assets/chat/css/style.scss'
import Chat from 'dgg-chat-gui/assets/chat/js/chat'

const script = document.getElementById('chat-include')
const chat = new Chat({
    url: script.getAttribute('data-ws-url'),
    api: {base: `${location.protocol}//${location.host}`},
    cdn: {base: script.getAttribute('data-cdn')},
    cacheKey: script.getAttribute('data-cache-key'),
    banAppealUrl: script.getAttribute('data-ban-appeal-url')
});

chat.withGui(require('dgg-chat-gui/assets/views/embed.html').default)
    .then(() => chat.loadUserAndSettings())
    .then(() => chat.loadEmotesAndFlairs())
    .then(() => chat.loadHistory())
    .then(() => chat.loadWhispers())
    .then(() => chat.connect())

// Keep the website session alive.
setInterval(() => fetch(`${chat.config.api.base}/ping`).catch(console.warn), 10*60*1000)
