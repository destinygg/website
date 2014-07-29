destiny = {
    cdn:     '',
    token:   '',
    baseUrl: '/',
    timeout: 15000,
    fn:      {}
};

destiny.init = function(args){
    $.extend(destiny, args);
};

// Document ready
$(function(){
    
    // Generic popup defaults
    var popupDefaults = {
        height     :500,
        width      :420,
        scrollbars :0,
        toolbar    :0,
        location   :0,
        status     :'no',
        menubar    :0,
        resizable  :0,
        dependent  :0
    };
    
    window.getOptionsString = (function(options){
        if(!options)
            options = popupDefaults;
        else
            options = $.extend({}, popupDefaults, options);
        var str = '';
        for(var i in options)
            str += i + '='+ options[i] +',';
        return str;
    });
    
    // Lastfm
    new DestinyFeedConsumer({
        url: destiny.baseUrl + 'lastfm.json',
        polling: 40,
        ifModified: true,
        start: false,
        ui: '#stream-lastfm',
        success: function(data, textStatus){
            if(textStatus == 'notmodified') return;
            var entries = $('#stream-lastfm .entries:first').empty();
            if(data != null && typeof data['recenttracks'] == 'object' && data.recenttracks.track.length > 0){
                for(var i in data.recenttracks.track){
                    if(i == 3){ break; };
                    var track = data.recenttracks.track[i];
                    var entry = $(
                            '<div class="media">'+ 
                                '<a class="pull-left cover-image" href="'+ track.url +'">'+ 
                                    '<img class="media-object" src="'+destiny.cdn+'/web/img/64x64.gif" data-src="'+ track.image[1]['#text'] +'">'+ 
                                '</a>'+ 
                                '<div class="media-body">'+ 
                                    '<div class="media-heading trackname"><a title="'+ htmlEncode(track.name) +'" href="'+ track.url +'">'+ htmlEncode(track.name) +'</a></div>'+ 
                                    '<div class="artist">'+ htmlEncode(track.artist['#text']) +'</div>'+ 
                                    '<div class="details">'+
                                        ((i<3 && track.date_str != '') ? '<time class="pull-right" datetime="'+ track.date_str +'">'+ moment(track.date_str).fromNow() +'</time>' : '')+ 
                                        ((i==0 && track.date_str == '') ? '<time class="pull-right" datetime="'+ track.date_str +'">now playing</time>' : '')+ 
                                        '<small class="album subtle">'+ track.album['#text'] +'</small>'+ 
                                    '</div>'+ 
                                '</div>'+ 
                            '</div>'
                    );
                    entries.append(entry);
                };
                entries.loadImages();
            };
        }
    });
    
    // Twitter
    new DestinyFeedConsumer({
        url: destiny.baseUrl + 'twitter.json',
        polling: 300,
        ifModified: true,
        start: false,
        ui: '#stream-twitter',
        success: function(tweets, textStatus){
            if(textStatus == 'notmodified') return;
            var entries = $('#stream-twitter .entries:first').empty();
            for(var i in tweets){
                if(i == 3){ break; };
                entries.append(
                    '<div class="media">'+ 
                        '<div class="media-body">'+ 
                            '<div class="media-heading"><a target="_blank" href="'+ 'https://twitter.com/'+ tweets[i].user.screen_name +'/status/' + tweets[i].id_str +'"><span class="glyphicon glyphicon-share"></span></a> '+ 
                                tweets[i].html +
                            '</div>'+ 
                            '<time datetime="'+ tweets[i].created_at +'" pubdate>'+ moment(tweets[i].created_at).fromNow() +'</time>'+ 
                        '</div>'+ 
                    '</div>'
                );
            };
            entries.loadImages();
        }
    });
    
    // Youtube
    new DestinyFeedConsumer({
        url: destiny.baseUrl + 'youtube.json',
        ifModified: true,
        start: false,
        ui: '#youtube',
        success: function(data, textStatus){
            if(textStatus == 'notmodified') return;
            var ui = $('#youtube .thumbnails:first').empty();
            ui.removeClass('thumbnails-loading');
            if(typeof data == 'object'){
                for(var i in data.items){
                    var title = htmlEncode(data.items[i].snippet.title);
                    ui.append(
                        '<li>'+
                            '<div class="thumbnail" data-toggle="tooltip" title="'+ title + '">'+
                                '<a href="'+ 'http://www.youtube.com/watch?v='+ data.items[i].snippet.resourceId.videoId +'">'+ 
                                    '<img alt="'+ title +'" src="'+destiny.cdn+'/web/img/320x240.gif" data-src="https://i.ytimg.com/vi/'+ data.items[i].snippet.resourceId.videoId +'/default.jpg" />'+
                                '</a>'+
                            '</div>'+
                        '</li>'
                    );
                    ui.find('.thumbnail').tooltip({placement:'bottom'});
                };
                ui.loadImages();
            };
        }
    });
    
    // Past Broadcasts
    new DestinyFeedConsumer({
        url: destiny.baseUrl + 'broadcasts.json',
        polling: 600,
        ifModified: true,
        start: false,
        ui: '#broadcasts',
        success: function(data, textStatus){
            if(textStatus == 'notmodified') return;
            var broadcasts = $('#broadcasts'),
                ui = broadcasts.find('.thumbnails:first').empty();
            ui.removeClass('thumbnails-loading');
            if(data != null && typeof data.videos != undefined){
                for(var i in data.videos){
                    var time = moment(data.videos[i].recorded_at).fromNow();
                    ui.append(
                        '<li>'+
                            '<div class="thumbnail" data-placement="bottom" rel="tooltip" title="'+ time +'">'+
                                '<a href="'+ data.videos[i].url +'">'+ 
                                    '<img alt="'+ time +'" src="'+destiny.cdn+'/web/img/320x240.gif" data-src="'+ data.videos[i].preview +'">'+
                                '</a>'+
                            '</div>'+
                        '</li>'
                    );
                };
                ui.loadImages();
                ui.find('[data-toggle="tooltip"]').tooltip();
            };
        }
    });
    
    var onlinebanner  = $('#online-banner-view'),
        offlinebanner = $('#offline-banner-view'),
        statusbanners = $('#status-banners');
    
    // Stream details
    new DestinyFeedConsumer({
        url: destiny.baseUrl + 'stream.json',
        polling: 30,
        ifModified: true,
        start: false,
        ui: 'body#home',
        success: function(data, textStatus){
            
            if(!data || textStatus == 'notmodified') 
                return;
            
            if(data != null && data.stream != null){
                onlinebanner.find('.preview a').attr('title', data.status);
                onlinebanner.find('.preview a').css('background-image', 'url('+data.stream.preview.medium+')');
                onlinebanner.find('.live-info-game').text(data.game);
                onlinebanner.find('.live-info-updated').text(moment(data.stream.channel.updated_at).fromNow());
                onlinebanner.find('.live-info-viewers').text(data.stream.viewers);
                onlinebanner.show().appendTo(statusbanners);
                offlinebanner.detach();
            }else{
                offlinebanner.find('.offline-status').text(data.status);
                offlinebanner.find('.offline-info-lastbroadcast').text(moment(data.lastbroadcast).fromNow());
                offlinebanner.find('.offline-info-game').text(data.game);
                
                if(data.previousbroadcast)
                    offlinebanner.find('.preview a').css('background-image', 'url('+data.previousbroadcast.preview+')');
                else
                    offlinebanner.find('.preview a').css('background-image', 'url('+data.video_banner+')');
                
                offlinebanner.show().appendTo(statusbanners);
                onlinebanner.detach();
            }
            
        }
    });
    
    // Private ads / rotation
    var pads = $('.private-ads .private-ad'), adRotateIndex = pads.length-1;
    setInterval(function(){
        $(pads[adRotateIndex]).fadeOut(500, function(){
            $(this).hide().removeClass('active');
        });
        if(adRotateIndex < pads.length-1) adRotateIndex++; else adRotateIndex = 0;
        $(pads[adRotateIndex]).hide().addClass('active').fadeIn(500);
    }, 8 * 1000);

    
    // Check if the ad has been blocked after X seconds
    var gad = $('#google-ad');
    setTimeout(function(){
        if(gad.css('display') == 'none' || parseInt(gad.height()) <= 0){
            gad.before('<div id="adblocker-message"><a>Add blocker</a><p>Please consider turning adblocker off for this website.</p></div>');
        }
    }, 8000);

    // Old style twitch panel
    $('#twitchpanel').each(function(){
        $('#popoutchat').on('click', function(){
            window.open('/embed/chat', '_blank', window.getOptionsString());
            $('body').addClass('nochat');
            $('#chat-embed').remove();
            $('#popoutchat,#popoutvideo').hide();
            return false;
        });
        $('#popoutvideo').on('click', function(){
            window.open('http://www.twitch.tv/destiny/popout', '_blank', window.getOptionsString({height:420, width:720}));
            $('body').addClass('novideo');
            $('#player-embed').remove();
            $('#popoutchat,#popoutvideo').hide();
            return false;
        });
    });
    
    // Bigscreen
    $('body#bigscreen').each(function(){
        
        $('iframe#chat-frame').on('load', function(){
            var chatframe = this.contentWindow;
            
            if(!chatframe)
                return;

            $('#chat-panel-tools').each(function(e){
                $(this).on('click', '#popout', function(){
                    window.open('/embed/chat', '_blank', window.getOptionsString());
                    $('body').addClass('nochat');
                    $('#chat-panel').remove();
                    return false;
                });
                $(this).on('click', '#refresh', function(){
                    chatframe.location.reload();
                    return false;
                });
                $(this).on('click', '#close', function(){
                    $('body').addClass('nochat');
                    $('#chat-panel').remove();
                    return false;
                });
            });
        });

        // Bigscreen resize
        $('#chat-panel-resize-bar').each(function(){
            var resizebar   = $(this),
                chatpanel   = $('#chat-panel'),
                streampanel = $('#stream-panel'),
                chatframe   = $('iframe#chat-frame'),
                overlay     = $('<div class="overlay" />');
                minwidth    = 320;

            // Resize the stream / chat frames
            var resizeFrames = function(nwidth)
            {

                if(nwidth < minwidth){
                    nwidth = minwidth;
                }

                streampanel.css('width', '-moz-calc(100% - '+ nwidth +'px)');
                streampanel.css('width', '-webkit-calc(100% - '+ nwidth +'px)');
                streampanel.css('width', '-o-calc(100% - '+ nwidth +'px)');
                streampanel.css('width', 'calc(100% - '+ nwidth +'px)');
                chatpanel.css('width', nwidth);

                resizebar.css('left', 0);

                if(localStorage){
                    localStorage['bigscreen.chat.width'] = nwidth;
                }

            };

            resizebar.on('mousedown.chatresize', function(e){
                e.preventDefault();

                // resizebar css
                resizebar.addClass('active');

                // disable background
                overlay.appendTo('body');

                // x,y of the drag bar
                var offsetX = e.clientX,
                    sx      = resizebar.position().left;

                $(document).on('mouseup.chatresize', function(e){
                    e.preventDefault();

                    // resizebar css
                    resizebar.removeClass('active');

                    // enable background
                    overlay.remove();

                    // position resize bar
                    resizebar.css('left', sx + (e.clientX - offsetX));

                    // no longer need to listen for events until next mouse down
                    $(document).unbind('mousemove.chatresize');
                    $(document).unbind('mouseup.chatresize');

                    // Resize the frame (width is for the chat panel, the stream is always 100%)
                    resizeFrames( (chatpanel.offset().left + chatpanel.outerWidth()) - resizebar.offset().left );
                });

                $(document).on('mousemove.chatresize', function(e){
                    e.preventDefault();

                    // position resize bar
                    resizebar.css('left', sx + (e.clientX - offsetX));
                });

            });

            // Onload, remember the last width the user chose
            var width = parseInt(localStorage['bigscreen.chat.width']);
            if(width > minwidth){
                resizeFrames(width);
            }

        });
        
        new DestinyFeedConsumer({
            url: destiny.baseUrl + 'stream.json',
            polling: 30,
            start: true,
            ui: '#stream-panel .panelheader .game',
            ifModified: true,
            success: function(data, textStatus){
                
                if(textStatus == 'notmodified') return;
                var ui = $('#stream-panel .panelheader .game');
                if(data != null && data.stream != null){
                    ui.html( '<i class="icon-time icon-white subtle"></i> <span>Started '+ moment(data.stream.channel.updated_at).fromNow() +'</span>' + ((data.stream.channel.delay > 0) ? ' - ' + parseInt((data.stream.channel.delay/60)) + 'm delay':'')).show();
                    return;
                }
                try{
                    ui.html( '<i class="icon-time icon-white subtle"></i> <span>Last broadcast ended '+ moment(data.lastbroadcast).fromNow() +'</span>').show();
                }catch(e){
                    ui.html( '<i class="icon-time icon-white subtle"></i> <span>Broadcast has ended</span>').show();
                }
            }
        });
        
    });
    
    // Add collapses
    $(".collapse").collapse();
    
    // Section collapse
    $(".collapsible").each(function(){
        var $c = $(this),
            $t = $c.find('> h3'),
            $v = $c.find('> content');
        $t.on('click', function(){
            if($c.hasClass('active')){
                $t.find('>.expander').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
                $v.hide();
            }else{
                $t.find('>.expander').removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
                $v.show();
            }
            $c.toggleClass('active');
        });
    });
    
    // Tabs selector - dont know why I need this
    if (location.hash !== '') 
        $('a[href="' + location.hash + '"]').tab('show');

    // Set the top nav selection
    $('body').each(function(){
        $('.navbar a[rel="'+$('body').attr('id')+'"]').closest('li').addClass('active');
        $('.navbar a[rel="'+$('body').attr('class')+'"]').closest('li').addClass('active');
    });
    
    // Generic popup links
    $('body').on('click', 'a.popup', function(e){
        var a = $(this)
        a.data('popup', window.open(a.attr('href'), '_blank', window.getOptionsString(a.data('options'))) );
        e.preventDefault();
        return false;
    });
    
    // Lazy load images
    $(this).loadImages();
    $(this).find('[data-toggle="tooltip"]').tooltip();
});

// Change time on selected elements
(function(){

    var applyMomentToElement = function(e){
        
        var ui = $(e), 
            datetime = ui.data('datetime') || ui.attr('datetime') || ui.text(),
            format = ui.data('format') || 'MMMM Do, h:mm:ss a';
        
        if(ui.data('moment-fromnow')){
            ui.addClass('moment-update');
            ui.html(moment(datetime).fromNow());
        }else{
            ui.html(moment(datetime).format(format));
        }
        
        ui.data('datetime', datetime).addClass('moment-set');
    };

    window.setInterval(function(){
        $('time.moment-update').each(function(){
            applyMomentToElement(this);
        });
    }, 30000);

    $('time[data-moment="true"]:not(.moment-set)').each(function(){
        applyMomentToElement(this);
    });

})();


//Ping
(function(){
    window.setInterval(function(){
        $.ajax({
            url: '/ping',
            method: 'get'
        });
    }, 10*60*1000);
})();

//Remember dismissed permanent alert boxes
(function() {
    
    $.cookie.json = true;
    $('.alert .close.persist').each(function() {
        
        var parent = $(this).parent('.alert'),
                id     = parent.attr('id');
        
        if (!id)
            return;
        
        id = 'alert-dismissed-' + id;
        
        $(this).click(function() {
            $.cookie(id, true, {expires: 365})
        });
        
        if ($.cookie(id))
            parent.remove();
        
    })
    
})();

(function(){

    /**
     * Iterate over browser variants of the given fullscreen API function string,
     * and if found, call it on the given element.
     */ 
    var fullscreenFn = function(fn, elem) {
        var agents = ["webkit", "moz", "ms", "o", ""];
        for (var i = agents.length - 1; i >= 0; i--) {
            var agent  = agents[i],
                    fullFn = null;
            
            if (!agent) // if no agent the function starts with a lower case letter
                fullFn = fn.substr(0,1).toLowerCase() + fn.substr(1);
            else // just preprend the agent to the function
                fullFn = agent + fn;
            
            if (typeof elem[fullFn] != "function")
                continue;
            
            elem[fullFn]();
            break;
        };
    };
    
    // Toggles player fullscreen using the global fullscreen flag
    var toggleFullscreen = function(event, element) {
        if(document.fullscreen || document.mozFullScreen || document.webkitIsFullScreen){
            fullscreenFn("CancelFullScreen", document);
            $(element).removeClass('fullscreened');
        } else {
            fullscreenFn("RequestFullScreen", element);
            $(element).addClass('fullscreened');
        }
    }
    
    // Global fullscreen change event (using ESC to cancel fullscreen
    $(document).on("fullscreenchange mozfullscreenchange webkitfullscreenchange", function () {
        if(!(document.fullscreen || document.mozFullScreen || document.webkitIsFullScreen)){
            $('.fullscreened').removeClass('fullscreened');
        }
    });
    
    // Stream overloays
    $('.stream-overlay.to-main').on('dblclick', function(e){
        toggleFullscreen(e, $(this).parent().get(0));
        return false;
    });
    $('.stream-overlay.fsbtn').on('click', function(e){
        toggleFullscreen(e, $(this).parent().get(0));
        return false;
    });
    
})();


// Gifting / user search
(function(){

    var users      = {}, 
        usrSearch    = $('#userSearchModal'), 
        usrInput     = usrSearch.find('input#userSearchInput'),
        usrSelectBtn = usrSearch.find('button#userSearchSelect'),
        usrSearchFrm = usrSearch.find('form#userSearchForm'),
        hasErrors    = false;

    var checkUser = function(username, success){
        $.ajax({
            url: '/gift/check',
            data: {s: username},
            type: 'GET',
            success: function(data){
                    success.call(this, data);
            },
            error: function(){
                    showLookupError('Error looking up user. Try again');
            }
        });
    };
    
    var showLookupError = function(message){
            hasErrors = true;
            usrSelectBtn.button('reset').attr('disabled', true);
            usrSearch.find('label.error').text(message).removeClass('hidden');
    }

    var selectUser = function(username){
        usrSelectBtn.button('loading');
        checkUser(username, function(response){
            if(response.valid && response.cangift){
                usrSearch.modal('hide');
                $('#giftSubscriptionConfirm').removeClass('hidden');
                $('#giftSubscriptionSelect').addClass('hidden');
                $('#subscriptionGiftUsername').text(username);
                $('input[name="gift"]').val(username);
                usrSelectBtn.button('reset').attr('disabled', false);
                hasErrors = false;
            }else if(!response.valid){
                    showLookupError('This user was not found. Try again.');
            }else if(!response.cangift){
                    showLookupError('This user is not eligible for a gift.');
            }
        });
    };

    var cancelUserSelect = function(){
        usrSearch.modal('hide');
        $('#giftSubscriptionConfirm').addClass('hidden');
        $('#giftSubscriptionSelect').removeClass('hidden');
        $('#subscriptionGiftUsername').text('');
        $('input[name="gift"]').val('');
        usrSearch.val('');
    };

    usrInput.on('keydown change', function(){
        if($(this).val() == ''){
            usrSelectBtn.attr('disabled', true);
        }else{
            usrSelectBtn.attr('disabled', false);
        };
        usrSearch.find('label.error').addClass('hidden');
    });

    usrSearchFrm.on('submit', function(){
        usrSearch.find('label.error').addClass('hidden')
        selectUser(usrInput.val());
        return false;
    });

    $('#cancelGiftSubscription').on('click', function(){
        usrSearch.find('label.error').addClass('hidden');
        cancelUserSelect();
        return false;
    });

    usrSearch.on('shown.bs.modal', function (e) {
        usrInput.focus();
    });

    usrSearch.on('hidden.bs.modal', function (e) {
        if(hasErrors){
            hasErrors = false;
            usrInput.val('');
            usrSearch.find('label.error').addClass('hidden');
        };
    });

})();

$(function(){
    $('form#addressSaveForm').validate({
        rules: {
            fullName : { required: true },
            line1    : { required: true },
            line2    : { required: true },
            city     : { required: true },
            region   : { required: true },
            zip      : { required: true },
            country  : { required: true }
        },
        highlight: function(element) {
            $(element).closest('.form-group').addClass('error');
        },
        unhighlight: function(element) {
            $(element).closest('.form-group').removeClass('error');
        }
    });
});

$(function(){
    $('body#tournament').each(function(){

        var dates = [new Date('2014-08-03T14:00:00+00:00')],
            nextdateavailable = false,
            tui  = $('#timer'),
            tday = tui.find('#t-timer-day .t-number'),
            thr  = tui.find('#t-timer-hour .t-number'),
            tmin = tui.find('#t-timer-minute .t-number'),
            tsec = tui.find('#t-timer-second .t-number');

        var npad = function (n, width)
        {
            n = n + '';
            return n.length >= width ? n : new Array(width - n.length + 1).join(0) + n;
        };

        var updatetimer = function()
        {
            for(var i in dates)
            {
                var then = moment(dates[i]),
                    now  = moment();

                if(then > now)
                {
                    var ms     = then.diff(now, 'milliseconds', true),
                        months = Math.floor(moment.duration(ms).asMonths());
                 
                    // subtract months from the original moment (not sure why I had to offset by 1 day)
                    then = then.subtract('months', months);
                    ms = then.diff(now, 'milliseconds', true);
                    var days = Math.floor(moment.duration(ms).asDays());
                 
                    then = then.subtract('days', days);
                    ms = then.diff(now, 'milliseconds', true);
                    var hours = Math.floor(moment.duration(ms).asHours());
                 
                    then = then.subtract('hours', hours);
                    ms = then.diff(now, 'milliseconds', true);
                    var minutes = Math.floor(moment.duration(ms).asMinutes());
                 
                    then = then.subtract('minutes', minutes);
                    ms = then.diff(now, 'milliseconds', true);
                    var seconds = Math.floor(moment.duration(ms).asSeconds());

                    tday.text(npad(days, 2));
                    thr.text(npad(hours, 2));
                    tmin.text(npad(minutes, 2));
                    tsec.text(npad(seconds, 2));
                    nextdateavailable = true;
                    window.setTimeout(updatetimer, 1000);
                    break;
                }
            }

            if(!nextdateavailable){
                tui.hide();
            }
        };

        updatetimer();

        // Navigation highlight
        var items = $('#main-nav .nav > li');
        $('#main-nav').on('click', 'li', function(){
            items.removeClass('active');
            $(this).addClass('active');
        });

        // Auto sizing the scroller
        var tscroller = $('#scroller'),
            mainnav   = $('#main-nav');
        var centerSlides = function(){
            var wh = $(window).height() - mainnav.height();
            if(wh > parseInt(tscroller.css('min-height')) && wh < parseInt(tscroller.css('max-height'))){  
                tscroller.css('height', wh);
                tscroller.find('.t-scroller-slide').each(function(){
                    $(this).css('top', wh/2 - $(this).height()/2);
                });
            }
        };
        centerSlides();
        $(window).on('resize', centerSlides);

        // Top icon
        var topicon = $('#back-to-top');
        $(window).on('scroll', function(e){
            if($(this).scrollTop() >= 100){
                topicon.fadeIn();
            }else{
                topicon.fadeOut();
            }
        });

    });
});