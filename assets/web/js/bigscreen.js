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

    // Embedding, hosting and "navhostpill"
    const initUrl = document.location.href // important this is stored before any work is done that may change this value
    let streamframe = $body.find('#stream-panel iframe')
    const hashregex = /^#(twitch|twitch-vod|twitch-clip|youtube|youtube-live)\/([A-z0-9_\-]{3,64})$/

    const streamDetails = $body.find('.stream-details')
    const defaultEmbedInfo = {
        platform: streamDetails.data('platform'),
        name: streamDetails.data('name'),
        parents: streamDetails.data('twitch-parents')
    }

    const streamStatus = {live: false, host: null, preview: null},
        embedInfo = Object.assign({ embeddingOtherContent: false }, defaultEmbedInfo),
        navHostPill = $body.find('#nav-host-pill'),
        iconClose = '<i class="fas fa-fw fa-times-circle"></i>'
    navHostPill.left = navHostPill.find('#nav-host-pill-type')
    navHostPill.right = navHostPill.find('#nav-host-pill-name')
    navHostPill.icon = navHostPill.find('#nav-host-pill-icon')

    const embedUrlForEmbedInfo = embedInfo => {
        switch (embedInfo.platform) {
            case 'twitch':
                return 'https://player.twitch.tv/?' + $.param({ channel: embedInfo.name, parent: embedInfo.parents }, true)
            case 'twitch-vod':
                return 'https://player.twitch.tv/?' + $.param({ video: embedInfo.name, parent: embedInfo.parents }, true)
            case 'twitch-clip':
                return 'https://clips.twitch.tv/embed?' + $.param({ clip: embedInfo.name, parent: embedInfo.parents }, true)
            case 'youtube':
                return 'https://www.youtube.com/embed/' + encodeURIComponent(embedInfo.name)
            case 'youtube-live':
                return 'https://www.youtube.com/embed/live_stream?' + $.param({ channel: embedInfo.name })
            default: // unsupported platform
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
            default: // unsupported platform
                return null
        }
    }

    const updateStreamFrame = function(){
        const src = embedUrlForEmbedInfo(embedInfo)
        if (src && streamframe.attr('src') !== src) { // avoids a flow issue when in
            const frame = streamframe.clone()
            frame.attr('src', src)
            streamframe.replaceWith(frame)
            streamframe = frame
        }
    }

    const updateStreamPill = function(){
        navHostPill.removeClass('embedded hidden')
        if (!embedInfo.embeddingOtherContent) {
            if (streamStatus.host && !streamStatus.live) {
                navHostPill.left.text('HOSTING')
                navHostPill.right.text(streamStatus.host.name)
                navHostPill.icon.html(iconForPlatform('twitch'))
            } else {
                navHostPill.left.text(streamStatus.live ? 'LIVE' : 'OFFLINE')
                navHostPill.right.text(defaultEmbedInfo.name)
                navHostPill.icon.html(iconForPlatform(defaultEmbedInfo.platform))
            }
        } else {
            navHostPill.addClass('embedded');
            navHostPill.left.text('EMBED')
            navHostPill.right.text(embedInfo.name)
            navHostPill.icon.html(iconClose)
        }
    }

    const toggleEmbedHost = function() {
        if (!embedInfo.embeddingOtherContent && streamStatus.host) {
            embedInfo.embeddingOtherContent = true
            embedInfo.platform = 'twitch'
            embedInfo.name = streamStatus.host['name']
            window.history.pushState(embedInfo, null, `#twitch/${embedInfo.name}`)
        } else if (embedInfo.embeddingOtherContent) {
            embedInfo.embeddingOtherContent = false
            embedInfo.platform = defaultEmbedInfo.platform
            embedInfo.name = defaultEmbedInfo.name
            Object.assign(embedInfo, defaultEmbedInfo)
            window.history.pushState(embedInfo, null, `/bigscreen`)
        }
        updateStreamPill(streamStatus)
        updateStreamFrame(embedInfo)
        return false
    }

    const fetchStreamInfo = function(){
        return $.ajax({url: '/api/info/stream'})
            .then(data => {
                const {live, host, preview} = data
                return Object.assign(streamStatus, {live, host, preview})
            })
            .then(updateStreamPill)
    }

    const handleHistoryPopState = function(){
        const state = window.history.state
        if (state == null) {
            // state is null when someone changes the hash, or back, forward browser actions are performed
            updateEmbedInfoWithBrowserLocationHash()
        } else {
            // else get the state from the history and update embedInfo
            Object.assign(embedInfo, state)
        }
        updateStreamPill(streamStatus)
        updateStreamFrame(embedInfo)
    }

    const parseEmbedHash = function(str) {
        const hash = str || window.location.hash || ''
        if (hash.length > 0 && hashregex.test(hash)) {
            const res = hash.match(hashregex),
                platform = res[1],
                name = res[2]
            return {platform, name}
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

    // Makes it so the browser navigation,
    window.history.replaceState(embedInfo, null, initUrl)
    // When someone clicks the nav UI element
    navHostPill.on('click touch', toggleEmbedHost)
    // When the browser navigation is changed, also happens when you change the hash in the browser
    window.addEventListener('popstate', handleHistoryPopState)
    // The stream status info, pinged every x seconds and on initial start up
    fetchStreamInfo().always(() => window.setInterval(fetchStreamInfo, 90000))

})();
