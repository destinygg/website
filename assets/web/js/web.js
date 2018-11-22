import $ from 'jquery'
import moment from 'moment'

// Generic popup defaults
const popupDefaults = {
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
const getOptionsString = function(options){
    options = (!options) ? popupDefaults : Object.assign({}, popupDefaults, options);
    return Object.keys(options).map(k => `${k}=${options[k]}`).join(',');
};

// textarea[maxlength] , form.validate
$(function(){

    $('.text-message textarea[maxlength]').each((i, e) => {
        const ta = $(e), max = ta.attr('maxlength')
        const indicator = $(`<div class="max-length-indicator">${max}</div>`)
        ta.on('keyup', () => indicator.text(max - ta.val().toString().length))
        ta.after(indicator)
    });

    $('form.validate').validate({
        highlight: (e) => $(e).closest('.form-group').addClass('error'),
        unhighlight: (e) => $(e).closest('.form-group').removeClass('error')
    });
})

// Document ready
$(function(){

    const body = $('body');

    $('body#bigscreen').each(function(){

        const chatpanel   = $('#chat-panel'),
              streampanel = $('#stream-panel'),
              chatframe   = $('iframe#chat-frame'),
              overlay     = $('<div class="overlay" />');

        chatframe.on('load', function(){
            const chatwindow = this.contentWindow;

            if(!chatwindow)
                return;

            $('#chat-panel-tools').each(function(){
                $(this).on('click', '#popout', function(){
                    window.open('/embed/chat', '_blank', getOptionsString());
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
                $(this).on('click', '#swap', function(){
                    chatpanel.toggleClass('left').toggleClass('right');
                    streampanel.toggleClass('left').toggleClass('right');
                    localStorage.setItem('bigscreen.orientation', streampanel.hasClass('left') ? 0:1);
                    return false;
                });
            });
        });

        // Bigscreen resize
        $('#chat-panel-resize-bar').each(function(){

            const resizebar    = $(this),
                  minwidth     = 320,
                  disableWidth = 768;

            // Resize the stream / chat frames
            const resizeFrames = function(width=null) {
                let nwidth = 0;
                if(width === null) {
                    if(chatpanel.hasClass('left')){
                        nwidth = resizebar.offset().left;
                    } else {
                        nwidth = (chatpanel.offset().left + chatpanel.outerWidth()) - resizebar.offset().left;
                    }
                } else {
                    nwidth = width;
                }
                resizebar.css('left', '');
                nwidth = Math.max(nwidth, minwidth);
                streampanel.css('width', '-moz-calc(100% - '+ nwidth +'px)');
                streampanel.css('width', '-webkit-calc(100% - '+ nwidth +'px)');
                streampanel.css('width', '-o-calc(100% - '+ nwidth +'px)');
                streampanel.css('width', 'calc(100% - '+ nwidth +'px)');
                chatpanel.css('width', nwidth);
                localStorage.setItem('bigscreen.chat.width', nwidth);
            };

            resizebar.on('mousedown.chatresize', function(e){
                e.preventDefault();
                resizebar.addClass('active');
                overlay.appendTo('body');
                let offsetX = e.clientX,
                    sx      = resizebar.position().left;

                $(document).on('mouseup.chatresize', function(e){
                    e.preventDefault();
                    resizebar.removeClass('active');
                    overlay.remove();
                    resizebar.css('left', sx + (e.clientX - offsetX));
                    $(document).unbind('mousemove.chatresize');
                    $(document).unbind('mouseup.chatresize');
                    resizeFrames();
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
            const width = parseInt(localStorage.getItem('bigscreen.chat.width'));
            if(width > minwidth && $(window).width() > disableWidth)
                resizeFrames(width);

            const dir = localStorage.getItem('bigscreen.orientation') || -1;
            switch(parseInt(dir)){
                case 0:
                    streampanel.removeClass('right').addClass('left');
                    chatpanel.removeClass('left').addClass('right');
                    break;
                case 1:
                    streampanel.removeClass('left').addClass('right');
                    chatpanel.removeClass('right').addClass('left');
                    break;
            }

        });

    });

    $('.btn-show-all').on('click', function(e){
        e.preventDefault();
        $('.collapse').collapse('show');
    });

    // Tabs selector - dont know why I need this
    if (location.hash !== '') 
        $('a[href="' + location.hash + '"]').tab('show');

    // Set the top nav selection
    $('.navbar a[rel="'+body.attr('id')+'"]').closest('li').addClass('active');
    $('.navbar a[rel="'+body.attr('class')+'"]').closest('li').addClass('active');

    // lazy loading images
    $(document).find('img[data-src]').each(function () {
        const img = $(this), url = img.data('src');
        if (url !== '' && url !== null) {
            const clone = img.clone();
            clone.one('load', function () {
                img.replaceWith(clone);
            });
            clone.removeClass('img_320x240 img_64x64').removeAttr('data-src').attr('src', url);
        }
    });

    // Generic popup links
    body.on('click', 'a.popup', function(e){
        const a = $(this);
        a.data('popup', window.open(a.attr('href'), '_blank', getOptionsString(a.data('options'))) );
        e.preventDefault();
        return true;
    });

    // Tooltips
    $(this).find('[data-toggle="tooltip"]').tooltip();
})

// Change time on selected elements
$(function(){

    const applyMomentToElement = function(e){

        const ui = $(e),
          format = ui.data('format') || 'MMMM Do, h:mm:ss a';
        let datetime = ui.data('datetime') || ui.attr('datetime') || ui.text();

        if(datetime === true)
            datetime = ui.attr('title');
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

})

// Gifting / user search
$(function(){

    let usrSearch    = $('#usersearchmodal'),
        usrInput     = usrSearch.find('input#userSearchInput'),
        usrSelectBtn = usrSearch.find('button#userSearchSelect'),
        usrSearchForm = usrSearch.find('form#userSearchForm'),
        giftMsgInput = usrSearch.find('textarea#giftmessage'),
        hasErrors    = false,
        giftUsername = '';

    const checkUser = function(username, success){
        $.ajax({
            url: '/api/info/giftcheck',
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

    const showLookupError = function(message){
        hasErrors = true;
        usrSelectBtn.button('reset').attr('disabled', true);
        usrSearch.find('label.error').text(message).removeClass('hidden');
    };

    const cancelUserSelect = function(){
        usrSearch.modal('hide');
        usrInput.val('');
        giftMsgInput.val('');
        $('#subscriptionGiftUsername').text('');
        $('#giftSubscriptionConfirm').addClass('hidden');
        $('#giftSubscriptionSelect').removeClass('hidden');
        $('input[name="gift"]').val('');
        $('input[name="gift-message"]').val('');
    };

    const selectUser = function(username){
        usrSelectBtn.button('loading');
        checkUser(username, function(response){
            if(response['valid'] && response['cangift']){
                giftUsername = username;
                if(giftMsgInput.val() === '')
                    giftMsgInput.focus();
                else
                    usrSearchForm.submit();
                usrSelectBtn.button('reset').attr('disabled', false);
                hasErrors = false;
            }else if(!response['valid']){
                showLookupError('This user was not found. Try again.');
            }else if(!response['cangift']){
                showLookupError('This user is not eligible for a gift.');
            }
        });
    };

    usrInput.on('keydown change', function(){
        usrSelectBtn.attr('disabled', $(this).val() === '');
        usrSearch.find('label.error').addClass('hidden');
    });

    usrSearchForm.on('submit', function(){
        usrSearch.find('label.error').addClass('hidden');
        if(giftUsername !== usrInput.val()) {
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

    usrSearch.on('shown.bs.modal', function () {
        usrInput.focus();
    });

    usrSearch.on('hidden.bs.modal', function () {
        if(hasErrors){
            hasErrors = false;
            giftUsername = '';
            usrInput.val('');
            giftMsgInput.val('');
            usrSearch.find('label.error').addClass('hidden');
        }
    });

    $('#cancelGiftSubscription').on('click', function(){
        usrSearch.find('label.error').addClass('hidden');
        cancelUserSelect();
        return false;
    });

})

// Stream status
$(function(){

    $('#stream-status').each(function(){
        const el = $(this),
            a = el.find('#stream-status-preview > a'),
            end = el.find('#stream-status-end'),
            start = el.find('#stream-status-start'),
            host = el.find('#stream-status-host');

        let status = {
            live: false,
            game: null,
            preview: "",
            animated_preview: "",
            status_text: "",
            started_at: null,
            ended_at: "",
            duration: 0,
            viewers: 0,
            host: {}
        };

        const updateStatus = function(status){
            let state = (status['host'] && status.host['id'] !== undefined) ? 'hosting' : (status.live ? 'online':'offline');
            el.removeClass('online offline hosting').addClass(state);
            end.text(moment(status.ended_at).fromNow());
            start.text(moment(status.started_at).fromNow());
            a.data('animated', status.animated_preview).css('background-image', status.preview);
            if(state === 'hosting'){
                host.text(status.host['display_name']);
                host.attr('href', status.host['url']);
            }
        };

        setInterval(function(){
            $.ajax({
                url: '/api/info/stream',
                type: 'GET',
                success: function(data) {
                    try {
                        if(data !== null && data !== undefined){
                            updateStatus($.extend(status, data));
                        }
                    } catch(ignored){}
                }
            });
        }, 60000);

        a.src = a.css('background-image');
        a.animated =  'url('+ a.data('animated') +')';
        a.on('mouseover', function(){
            a.css('background-image', a.animated);
        })
        .on('mouseout', function(){
            a.css('background-image', a.src);
        });
    });

})

$(function(){

    const selectFollowUri = form => {
        let follow = ''
        try {
            const a = document.createElement('a');
            a.href = window.self !== window.top ? window.top.location.href.toString(): window.location.href.toString();
            follow = a.pathname + a.hash + a.search
        } catch (ignored) {}
        form.find('input[name="follow"]').val(follow)
    }

    const submitLogin = (form, provider) => {
        form.find('input[name="authProvider"]').val(provider)
        form.trigger('submit')
        return false
    }

    $('#loginmodal').find('form').each(function(){
        const form = $(this)
        form.on('submit', () => selectFollowUri(form))
        form.on('click', '#loginproviders .btn', function(){
            return submitLogin(form, $(this).data('provider'))
        })
        form.on('keyup', '#loginproviders .btn', e => {
            if(e.keyCode === 13) return submitLogin(form, $(this).data('provider'))
        })
    })

    $('#loginform').each(function(){
        const form = $(this)
        form.on('click', '#loginproviders .btn', function(){
            return submitLogin(form, $(this).data('provider'))
        })
        form.on('keyup', '#loginproviders .btn', e => {
            if(e.keyCode === 13) return submitLogin(form, $(this).data('provider'))
        })
    })

})

$(function(){
    // Subscription page
    $('body#subscription').each(function(){
        $('button#cancelSubscriptionBtn').on('click', function(){
            $('input[name="cancelSubscription"]').val('1');
            $(this).closest('form').submit();
        });
        $('button#stopRecurringBtn').on('click', function(){
            $('input[name="cancelSubscription"]').val('0');
            $(this).closest('form').submit();
        });
    });

    // Authentication
    $('body#authentication').each(function(){
        $('.btn-post').on('click', function(){
            const a = $(this), form = $(this).closest('form');
            form.attr("action", a.attr("href"));
            form.trigger('submit');
            return false;
        });
        $('#btn-create-key').on('click', function(){
            const recaptcha = $('#recaptcha'), form = $(this).closest('form');
            if(recaptcha.hasClass('hidden')){
                recaptcha.removeClass('hidden')
            }else{
                form.submit()
            }
            return false;
        });
    });
});



window.showLoginModal = () => $('#loginmodal').modal("show")
