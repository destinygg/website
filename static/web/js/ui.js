// Document ready
$(function(){
    var unreadElem = $('.pm-count');
    if (unreadElem && localStorage) {
        var unreadMessageCount = parseInt(unreadElem.text() || "0", 10);
        localStorage['unreadMessageCount'] = unreadMessageCount || 0;
    }

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
                                '<a href="'+ '//www.youtube.com/watch?v='+ data.items[i].snippet.resourceId.videoId +'">'+ 
                                    '<img alt="'+ title +'" src="'+destiny.cdn+'/web/img/320x240.gif" data-src="//i.ytimg.com/vi/'+ data.items[i].snippet.resourceId.videoId +'/default.jpg" />'+
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
                onlinebanner.find('.banner-thumbnail a').attr('title', data.status);
                onlinebanner.find('.banner-thumbnail a').css('background-image', 'url('+data.stream.preview.medium+')');
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
                    offlinebanner.find('.banner-thumbnail a').css('background-image', 'url('+data.previousbroadcast.preview+')');
                else
                    offlinebanner.find('.banner-thumbnail a').css('background-image', 'url('+data.video_banner+')');
                
                offlinebanner.show().appendTo(statusbanners);
                onlinebanner.detach();
            }
            
        }
    });
    
    // Bigscreen
    $('body#bigscreen').each(function(){

        var chatpanel   = $('#chat-panel'),
            streampanel = $('#stream-panel'),
            chatframe   = $('iframe#chat-frame'),
            overlay     = $('<div class="overlay" />');
        
        $('iframe#chat-frame').on('load', function(){
            var chatwindow = this.contentWindow;
            
            if(!chatwindow)
                return;

            $('#chat-panel-tools').each(function(e){
                $(this).on('click', '#popout', function(){
                    window.open('/embed/chat', '_blank', window.getOptionsString());
                    $('body').addClass('nochat');
                    chatpanel.remove();
                    streampanel.removeAttr('style');
                    return false;
                });
                $(this).on('click', '#refresh', function(){
                    chatwindow.location.reload();
                    return false;
                });
                $(this).on('click', '#close', function(){
                    $('body').addClass('nochat');
                    chatpanel.remove();
                    streampanel.removeAttr('style');
                    return false;
                });
            });
        });

        // Bigscreen resize
        $('#chat-panel-resize-bar').each(function(){

            var resizebar    = $(this),
                minwidth     = 320,
                disableWidth = 768;

            resizebar.css('left', 0);

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

                    // Resize the frames
                    resizeFrames( (chatpanel.offset().left + chatpanel.outerWidth()) - resizebar.offset().left );
                });

                $(document).on('mousemove.chatresize', function(e){
                    e.preventDefault();
                    resizebar.css('left', sx + (e.clientX - offsetX));
                });

            });

            // If the window is reduced to a size below the disableWidth, disable the custom resizing
            $(window).on('resize.chatresize', function(){
                if($(window).width() <= disableWidth){
                    streampanel.removeAttr('style');
                    chatpanel.removeAttr('style');
                }
            });

            // Onload, remember the last width the user chose
            var width = parseInt(localStorage['bigscreen.chat.width']);
            if(width > minwidth && $(window).width() > disableWidth){
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
    $(".collapse").not('#collapsemenu').collapse();
    
    // Section collapse
    $(".collapsible").each(function(){
        var $c = $(this),
            $t = $c.find('> h3'),
            $v = $c.find('> content');
        $t.on('click', function(){
            if($c.hasClass('active')){
                $t.find('>.expander').removeClass('fa-chevron-down').addClass('fa-chevron-right');
                $v.hide();
            }else{
                $t.find('>.expander').removeClass('fa-chevron-right').addClass('fa-chevron-down');
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
        }else if(ui.data('moment-calendar')){
            ui.addClass('moment-update');
            ui.html(moment(datetime).calendar());
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

//Query the unread message count
(function(){
    if (!localStorage)
        return;

    window.setInterval(function(){
        $.ajax({
            url: '/api/messages/unreadcount',
            method: 'get',
            cache: false,
            dataType: 'json',
            success: function(data) {
                if (!data || !data.success)
                    return;

                var count = parseInt(localStorage['unreadMessageCount'] || "0", 10);
                if (count == data.unreadcount)
                    return;

                localStorage['unreadMessageCount'] = data.unreadcount;
                $(document).trigger('privmsg-update');
            }
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

    var users        = {}, 
        usrSearch    = $('#userSearchModal'), 
        usrInput     = usrSearch.find('input#userSearchInput'),
        usrSelectBtn = usrSearch.find('button#userSearchSelect'),
        usrSearchFrm = usrSearch.find('form#userSearchForm'),
        giftMsgInput = usrSearch.find('textarea#giftmessage'),
        hasErrors    = false,
        giftUsername = '';

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
    };

    var cancelUserSelect = function(){
        usrSearch.modal('hide');
        usrInput.val('');
        giftMsgInput.val('');
        $('#subscriptionGiftUsername').text('');
        $('#giftSubscriptionConfirm').addClass('hidden');
        $('#giftSubscriptionSelect').removeClass('hidden');
        $('input[name="gift"]').val('');
        $('input[name="gift-message"]').val('');
    };

    var selectUser = function(username){
        usrSelectBtn.button('loading');
        checkUser(username, function(response){
            if(response.valid && response.cangift){
                giftUsername = username;

                if(giftMsgInput.val() == ''){
                    giftMsgInput.focus();
                }else{
                    usrSearchFrm.submit();
                }

                usrSelectBtn.button('reset').attr('disabled', false);
                hasErrors = false;
            }else if(!response.valid){
                showLookupError('This user was not found. Try again.');
            }else if(!response.cangift){
                showLookupError('This user is not eligible for a gift.');
            }
        });
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
        usrSearch.find('label.error').addClass('hidden');
        if(giftUsername != usrInput.val()) {
            selectUser(usrInput.val());
        } else {
            $('#subscriptionGiftUsername').text(usrInput.val());
            $('#giftSubscriptionConfirm').removeClass('hidden');
            $('#giftSubscriptionSelect').addClass('hidden');
            $('input[name="gift"]').val(usrInput.val());
            $('input[name="gift-message"]').val(giftMsgInput.val());
            usrSearch.modal('hide');
        }
        return false;
    });

    usrSearch.on('shown.bs.modal', function (e) {
        usrInput.focus();
    });

    usrSearch.on('hidden.bs.modal', function (e) {
        if(hasErrors){
            hasErrors = false;
            giftUsername = '';
            usrInput.val('');
            giftMsgInput.val('');
            usrSearch.find('label.error').addClass('hidden');
        };
    });

    $('#cancelGiftSubscription').on('click', function(){
        usrSearch.find('label.error').addClass('hidden');
        cancelUserSelect();
        return false;
    });

})();

$(function(){
    $('form#addressSaveForm').validate({
        rules: {
            fullName : { required: true },
            line1    : { required: true },
            line2    : { required: false },
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