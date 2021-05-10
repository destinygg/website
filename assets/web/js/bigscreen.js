import $ from 'jquery'

(function(){

    const popupDefaults = {height: 500, width: 420, scrollbars: 0, toolbar: 0, location: 0, status: 'no', menubar: 0, resizable: 0, dependent: 0 };
    const getOptionsString = options => {
        options = (!options) ? popupDefaults : Object.assign({}, popupDefaults, options);
        return Object.keys(options).map(k => `${k}=${options[k]}`).join(',');
    };

    const $body = $('body#bigscreen'),
        chatpanel = $body.find('#chat-panel'),
        layout = $body.find('#bigscreen-layout'),
        resizebar = $body.find('#chat-panel-resize-bar'),
        paneltools = $body.find('#chat-panel-tools'),
        chatframe = $body.find('#chat-wrap iframe'),
        overlay = $('<div class="overlay"></div>'),
        minwidth = 300,     // pixels
        maxsize = 76.6666;  // percent

    const Bigscreen = {
        getOrientation: function(){
            return localStorage.getItem('bigscreen.chat.orientation') || '0'
        },
        setOrientation: function(dir){
            localStorage.setItem('bigscreen.chat.orientation', dir)
        },
        getSize: function(){
            return parseFloat(localStorage.getItem('bigscreen.chat.size') || 20.00)
        },
        setSize: function(percentage){
            const percent = (this.getOrientation() === '0') ? 100 - percentage : percentage
            localStorage.setItem('bigscreen.chat.size', Math.min(maxsize, Math.max(0, percent)).toFixed(4))
        },
        applyOrientation: function() {
            const dir = Bigscreen.getOrientation()
            layout.attr('data-orientation', dir)
            switch(parseInt(dir)){
                case 0:
                    layout.removeClass('chat-left')
                        .addClass('chat-right')
                    break
                case 1:
                    layout.removeClass('chat-right')
                        .addClass('chat-left')
                    break
            }
        },
        applySize: function() {
            const percent = Bigscreen.getSize(),
                minp = (minwidth / layout.outerWidth() * 100);
            if (percent > minp) {
                chatpanel.css('width', Math.max(minp, percent) + '%')
            } else {
                chatpanel.css('width', 'inherit')
            }
        }
    }

    // Chat top tools
    chatframe.on('load', function(){
        const chatwindow = this.contentWindow
        if(!chatwindow) return;
        paneltools
            .on('click touch', '#popout', function(){
                window.open('/embed/chat', '_blank', getOptionsString())
                $body.addClass('nochat')
                chatpanel.remove()
                return false
            })
            .on('click touch', '#refresh', function(){
                chatwindow.location.reload()
                return false
            })
            .on('click touch', '#close', function(){
                $body.addClass('nochat')
                chatpanel.remove()
                return false
            })
            .on('click touch', '#swap', function(){
                Bigscreen.setOrientation(Bigscreen.getOrientation() === '1' ? '0':'1')
                Bigscreen.applyOrientation()
                return false
            });
    });

    // Bigscreen resize bar / drag resize
    resizebar.on('mousedown.chat touchstart.chat', e => {
        const startClientX = e.clientX || e.originalEvent['touches'][0].clientX || 0,
            startPosX = resizebar.position().left,
            clientWidth = layout.outerWidth();
        resizebar.addClass('active')
        let clientX = -1
        $body
            .on('mouseup.chat touchend.chat', () => {
                if (clientX === -1) { return false; }
                //const clientX = e.clientX || e.originalEvent['touches'][0].clientX || 0
                $body.unbind('mousemove.chat mouseup.chat touchend.chat touchmove.chat')
                Bigscreen.setSize((clientX/clientWidth) * 100)
                resizebar.removeClass('active').removeAttr('style')
                overlay.remove()
                Bigscreen.applySize()
                return false
            })
            .on('mousemove.chat touchmove.chat', e => {
                clientX = e.clientX || e.originalEvent['touches'][0].clientX || 0;
                resizebar.css('left', startPosX + (clientX - startClientX));
            })
            .append(overlay)
        return false;
    })

    Bigscreen.applyOrientation()
    Bigscreen.applySize()

    // Embedding, hosting, and the host pill.
    const defaultStreamIndex = {
        ls: window.localStorage,
        key: 'defaultStreamIndex',
        get: function() {
            const value = parseInt(this.ls.getItem(this.key))
            if (isNaN(value)) {
                this.ls.setItem(this.key, 0)
                return 0
            } else {
                return value
            }
        },
        set: function(value) {
            this.ls.setItem(this.key, value)
        }
    }

    const initUrl = document.location.href // Important this is stored before any work is done that may change this value.
    const hashregex = /^#(twitch|twitch-vod|twitch-clip|youtube|youtube-live)\/([A-z0-9_\-]{3,64})$/

    const streamsMetadata = $body.find('.streams-metadata')
    const displayName = streamsMetadata.data('display-name')
    const twitchParents = streamsMetadata.data('twitch-parents')
    const embedTwitchStream = streamsMetadata.data('embed-twitch-stream')
    const embedYouTubeStream = streamsMetadata.data('embed-youtube-stream')
    const twitchChannelName = streamsMetadata.data('twitch-channel-name')
    const youTubeStreamVideoId = streamsMetadata.data('youtube-stream-video-id')

    const streams = []
    let hostedChannel = null
    if (embedTwitchStream) {
        streams.push({ live: false, platform: 'twitch', name: twitchChannelName })
    }
    if (embedYouTubeStream) {
        streams.push({ live: false, platform: 'youtube', name: youTubeStreamVideoId })
    }

    const index = defaultStreamIndex.get()
    let activeStreamIndex = index < streams.length ? index : 0

    const embedInfo = { ...streams[activeStreamIndex], embeddingOtherContent: false }

    let streamFrame = $body.find('#stream-panel iframe')
    const closeIcon = '<i class="fas fa-fw fa-times-circle"></i>'
    const hostPill = $body.find('#host-pill')
    hostPill.text = hostPill.find('#host-pill-text')
    hostPill.type = hostPill.find('#host-pill-type')
    hostPill.name = hostPill.find('#host-pill-name')
    hostPill.icon = hostPill.find('#host-pill-icon')

    if (streams.length > 1) {
        hostPill.icon.addClass('clickable')
    }

    const embedUrlForEmbedInfo = embedInfo => {
        switch (embedInfo.platform) {
            case 'twitch':
                return 'https://player.twitch.tv/?' + $.param({ channel: embedInfo.name, parent: twitchParents }, true)
            case 'twitch-vod':
                return 'https://player.twitch.tv/?' + $.param({ video: embedInfo.name, parent: twitchParents }, true)
            case 'twitch-clip':
                return 'https://clips.twitch.tv/embed?' + $.param({ clip: embedInfo.name, parent: twitchParents }, true)
            case 'youtube':
                return 'https://www.youtube.com/embed/' + encodeURIComponent(embedInfo.name) + '?' + $.param({ autoplay: 1, playsinline: 1 }, true)
            case 'youtube-live':
                return 'https://www.youtube.com/embed/live_stream?' + $.param({ channel: embedInfo.name })
            default: // Unsupported platform.
                return null
        }
    }

    const iconForPlatform = platform => {
        switch (platform) {
            case 'twitch':
            case 'twitch-vod':
            case 'twitch-clip':
                return '<i class="fab fa-fw fa-twitch"></i>'
            case 'youtube':
            case 'youtube-live':
                return '<i class="fab fa-fw fa-youtube"></i>'
            default: // Unsupported platform.
                return null
        }
    }

    const updateStreamFrame = function() {
        // No YouTube stream URL, so the stream must be offline.
        if (embedInfo.platform === 'youtube' && !embedInfo.name) {
            const frame = streamFrame.clone()
            frame.removeAttr('src')
            streamFrame.replaceWith(frame)
            streamFrame = frame
            return
        }

        const src = embedUrlForEmbedInfo(embedInfo)

        if (src && streamFrame.attr('src') !== src) {
            // Replace existing iframe with a new one to avoid unwanted history entries.
            const frame = streamFrame.clone()
            frame.attr('src', src)
            streamFrame.replaceWith(frame)
            streamFrame = frame
        }
    }

    const updateStreamPill = function(animateIcon = false) {
        hostPill.removeClass('hidden hosting embedded')

        if (embedInfo.embeddingOtherContent) {
            hostPill.addClass('embedded');

            hostPill.type.text('EMBED')
            hostPill.name.text(embedInfo.name)
            hostPill.icon.html(closeIcon)
        } else {
            if (hostedChannel) {
                hostPill.addClass('hosting');

                hostPill.type.text('HOSTING')
                hostPill.name.text(hostedChannel.name)
            } else {
                const currentStream = streams[activeStreamIndex]
                hostPill.type.text(currentStream.live ? 'LIVE' : 'OFFLINE')
                hostPill.name.text(displayName)
            }

            const newIcon = iconForPlatform(embedInfo.platform)
            if (animateIcon) {
                const $oldIcon = hostPill.icon.find('i')
                $oldIcon.removeClass('animate-icon add')
                $oldIcon.addClass('animate-icon remove')

                const $newIcon = $(newIcon)
                $newIcon.addClass('animate-icon add')
                hostPill.icon.append($newIcon)

                // Remove old icon after the animation ends.
                $oldIcon.on('animationend webkitanimationend', function() {
                    $(this).remove()
                })
            } else {
                hostPill.icon.html(newIcon)
            }
        }
    }

    const toggleEmbedHost = function() {
        if (!embedInfo.embeddingOtherContent && hostedChannel) {
            embedInfo.embeddingOtherContent = true
            embedInfo.platform = 'twitch' // Only twitch streams can be hosted.
            embedInfo.name = hostedChannel.name

            window.history.pushState(embedInfo, null, `#twitch/${embedInfo.name}`)
        } else if (embedInfo.embeddingOtherContent) {
            embedInfo.embeddingOtherContent = false
            embedInfo.platform = streams[activeStreamIndex].platform
            embedInfo.name = streams[activeStreamIndex].name

            window.history.pushState(embedInfo, null, `/bigscreen`)
        }

        updateStreamFrame()
        updateStreamPill()

        return false
    }

    const handlePlatformIconClick = function() {
        if (embedInfo.embeddingOtherContent) {
            toggleEmbedHost()
        } else {
            if (streams.length <= 1) {
                return
            }

            activeStreamIndex++
            if (activeStreamIndex >= streams.length) {
                activeStreamIndex = 0
            }
            Object.assign(embedInfo, streams[activeStreamIndex])
            defaultStreamIndex.set(activeStreamIndex)

            updateStreamPill(true)
            updateStreamFrame()
        }
    }

    const fetchStreamInfo = function() {
        return $.ajax({ url: '/api/info/stream' })
            .then(response => {
                const { data: { streams: { twitch, youtube }, hostedChannel: newHostedChannel } } = response
                hostedChannel = newHostedChannel
                streams.forEach((stream, i) => {
                    switch (stream.platform) {
                        case 'twitch':
                            streams[i].live = twitch?.live
                            break
                        case 'youtube':
                            const { live, videoId } = youtube

                            streams[i].live = live
                            // Update the embed if the video ID of the stream changed.
                            if (live && streams[i].name !== videoId) {
                                streams[i].name = videoId
                                updateStreamFrame()
                            } else if (!live) {
                                streams[i].name = null
                                updateStreamFrame()
                            }
                            break
                    }
                })
            })
            .then(() => updateStreamPill())
    }

    const handleHistoryPopState = function() {
        const state = window.history.state
        if (state) { // Only exists when toggling the hosted stream embed.
            Object.assign(embedInfo, state)
        } else {
            updateEmbedInfoWithBrowserLocationHash()
        }

        updateStreamPill()
        updateStreamFrame()
    }

    const parseEmbedHash = function(str) {
        const hash = str || window.location.hash || ''
        if (hash.length > 0 && hashregex.test(hash)) {
            const res = hash.match(hashregex),
                platform = res[1],
                name = res[2]
            return { platform, name }
        }

        return null
    }

    const updateEmbedInfoWithBrowserLocationHash = function() {
        const parts = parseEmbedHash(window.location.hash)
        if (parts) {
            embedInfo.embeddingOtherContent = true
            embedInfo.platform = parts.platform
            embedInfo.name = parts.name
        }
    }

    updateEmbedInfoWithBrowserLocationHash()
    updateStreamFrame()

    hostPill.text.on('click touch', toggleEmbedHost)
    hostPill.icon.on('click touch', handlePlatformIconClick)

    // Makes it so the browser navigation...
    window.history.replaceState(embedInfo, null, initUrl)

    // When the user goes back or forward or changes the hash in the URL bar.
    window.addEventListener('popstate', handleHistoryPopState)

    // Fetch stream status on load and every 90 seconds thereafter.
    fetchStreamInfo().always(() => window.setInterval(fetchStreamInfo, 90000))

})();
