import $ from 'jquery'
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

$('body,html').css('background', 'transparent')
chat.withGui(require('dgg-chat-gui/assets/views/stream.html').default)
    .then(() => {
        chat.settings.set('fontscale', Chat.reqParam('f') || 'auto')
        chat.settings.set('censorbadwords', true)
        chat.applySettings(false)
    })
    .then(() => chat.loadEmotesAndFlairs())
    .then(() => chat.loadHistory())
    .then(() => chat.connect())
