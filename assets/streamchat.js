require('dgg-chat-gui/assets/streamchat')

const Chat = require('dgg-chat-gui/assets/chat/js/chat')['default']
const chatUri = `ws${window.location.protocol === 'https:' ? 's' : ''}://${window.location.host}/ws`
const script = document.getElementById('chat-include')
const cacheKey = script.getAttribute('data-cache-key')
const cdnUrl = script.getAttribute('data-cdn')

const loadCss = function(url) {
    const link = document.createElement('link');
    link.href = url;
    link.type = 'text/css';
    link.rel = 'stylesheet';
    link.media = 'screen';
    document.getElementsByTagName('head')[0].appendChild(link);
    return link;
}

$.when(
    new Promise(res => $.getJSON(`${cdnUrl}/flairs/flairs.json?_=${cacheKey}`).done(res).fail(() => res(null))),
    new Promise(res => res(loadCss(`${cdnUrl}/flairs/flairs.css?_=${cacheKey}`))),
    new Promise(res => $.getJSON(`${cdnUrl}/emotes/emotes.json?_=${cacheKey}`).done(res).fail(() => res(null))),
    new Promise(res => res(loadCss(`${cdnUrl}/emotes/emotes.css?_=${cacheKey}`))),
).then((flairs, flairsCss, emotes, emotesCss) => {
    return new Chat()
        .withFlairs(flairs)
        .withEmotes(emotes)
        .withGui()
        .connect(chatUri)
})