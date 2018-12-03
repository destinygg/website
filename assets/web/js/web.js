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

const $document = $(document),
    $body = $document.find('body');

(function(){

    $body.find('.text-message textarea[maxlength]').each((i, e) => {
        const ta = $(e), max = ta.attr('maxlength')
        const indicator = $(`<div class="max-length-indicator">${max}</div>`)
        ta.on('keyup', () => indicator.text(max - ta.val().toString().length))
        ta.after(indicator)
    });

    $body.find('form.validate').validate({
        highlight: (e) => $(e).closest('.form-group').addClass('error'),
        unhighlight: (e) => $(e).closest('.form-group').removeClass('error')
    });
})();

(function(){

    const $body = $('body#bigscreen');

    $body.each(function(){

        const chatpanel = $body.find('#chat-panel'),
            layout = $body.find('#bigscreen-layout'),
            resizebar = $body.find('#chat-panel-resize-bar'),
            paneltools = $body.find('#chat-panel-tools'),
            chatframe = $body.find('#chat-wrap iframe'),
            overlay = $('<div class="overlay"></div>'),
            minwidth = 300,     // pixels
            maxsize = 76.666;   // percent

        const BSSettings = {
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
            }
        }

        const updateOrientation = function() {
            const dir = BSSettings.getOrientation()
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
        };

        const updateSize = function() {
            const percent = BSSettings.getSize(),
                minp = (minwidth / layout.outerWidth() * 100);
            if (percent > minp) {
                chatpanel.css('width', Math.max(minp, percent) + '%')
            } else {
                chatpanel.css('width', 'inherit')
            }
        };

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
                    BSSettings.setOrientation(BSSettings.getOrientation() === '1' ? '0':'1')
                    updateOrientation()
                    return false
                });
        });

        // Bigscreen resize
        resizebar.each(function(){

            resizebar.on('mousedown.chat touchstart.chat', e => {
                const startClientX = e.clientX || e.originalEvent['touches'][0].clientX || 0,
                    startPosX = resizebar.position().left,
                    clientWidth = layout.outerWidth();
                resizebar.addClass('active')
                let clientX = -1
                $body
                    .on('mouseup.chat touchend.chat', e => {
                        if (clientX === -1) { return false; }
                        //const clientX = e.clientX || e.originalEvent['touches'][0].clientX || 0
                        $body.unbind('mousemove.chat mouseup.chat touchend.chat touchmove.chat')
                        BSSettings.setSize((clientX/clientWidth) * 100)
                        resizebar.removeClass('active').removeAttr('style')
                        overlay.remove()
                        updateSize()
                        return false
                    })
                    .on('mousemove.chat touchmove.chat', e => {
                        clientX = e.clientX || e.originalEvent['touches'][0].clientX || 0;
                        resizebar.css('left', startPosX + (clientX - startClientX));
                    })
                    .append(overlay)
                return false;
            })

            updateOrientation()
            updateSize()
        });

    });

})();

(function(){

    const $body = $('body');

    $body.find('.btn-show-all').on('click', e => {
        $body.find('.collapse').collapse('show');
        e.preventDefault();
    });

    // Tabs selector - dont know why I need this
    if (location.hash !== '') {
        $body.find('a[href="' + location.hash + '"]').tab('show');
    }

    // Set the top nav selection
    $body.find('.navbar a[rel="'+$body.attr('id')+'"]').closest('li').addClass('active');
    $body.find('.navbar a[rel="'+$body.attr('class')+'"]').closest('li').addClass('active');

    // lazy loading images
    $body.find('img[data-src]').each(function () {
        const img = $(this), url = img.data('src');
        if (url !== '' && url !== null) {
            const clone = img.clone();
            clone.one('load', function () {
                img.replaceWith(clone);
            });
            clone.removeClass('img_320x240 img_64x64')
                .removeAttr('data-src')
                .attr('src', url);
        }
    });

    // Generic popup links
    $body.on('click', 'a.popup', function(e){
        const a = $(this);
        a.data('popup', window.open(a.attr('href'), '_blank', getOptionsString(a.data('options'))) );
        e.preventDefault();
        return true;
    });

    // Tooltips
    $body.find('[data-toggle="tooltip"]').tooltip();
})();

(function(){

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

})();

(function(){

    let usrSearch = $('#usersearchmodal'),
        usrInput = usrSearch.find('input#userSearchInput'),
        usrSelectBtn = usrSearch.find('button#userSearchSelect'),
        usrSearchForm = usrSearch.find('form#userSearchForm'),
        giftMsgInput = usrSearch.find('textarea#giftmessage'),
        hasErrors = false,
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

})();

(function(){

    $('#stream-status').each(function(){
        const el = $(this),
            end = el.find('#stream-status-end'),
            start = el.find('#stream-status-start'),
            host = el.find('#stream-status-host');

        let status = {
            live: false,
            game: null,
            preview: "",
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

    });

})();

(function(){

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

})();

(function(){
    $('.btn-post').on('click', function () {
        const a = $(this), form = $(this).closest('form'), confirmMessage = a.data('confirm');
        if (!confirmMessage || confirm(confirmMessage)) {
            form.attr('action', a.attr('href'));
            form.trigger('submit');
        }
        return false;
    });
})();

(function(){
    // Subscription page
    $('body#subscription').each(function () {
        $(this).find('button#cancelSubscriptionBtn').on('click', function () {
            $('input[name="cancelSubscription"]').val('1');
            $(this).closest('form').submit();
        });
        $(this).find('button#stopRecurringBtn').on('click', function () {
            $('input[name="cancelSubscription"]').val('0');
            $(this).closest('form').submit();
        });
    });

    // Developer
    $('body#developer').each(function () {
        let $body = $(this);
        $body.find('#btn-create-app').on('click', function () {
            const recaptcha = $('#recaptcha1'), form = $(this).closest('form');
            if (recaptcha.hasClass('hidden')) {
                recaptcha.removeClass('hidden')
            } else {
                form.submit()
            }
            return false;
        });
        $body.find('#btn-create-key').on('click', function () {
            const recaptcha = $('#recaptcha2'), form = $(this).closest('form');
            if (recaptcha.hasClass('hidden')) {
                recaptcha.removeClass('hidden')
            } else {
                form.submit()
            }
            return false;
        });
        $body.find('form#app-form').each(function(){
            const $form = $(this);
            $form.on('click', '#app-form-secret-create', function(e){
                if (confirm('Are you sure? This will invalidate the previous secret.')) {
                    const id = $(this).data('id');
                    const $secret = $form.find('input[name="secret"]');
                    $.ajax({
                        url: '/profile/app/secret',
                        data: {id: id},
                        type: 'POST',
                        success: function(data){
                            $secret.val(data['secret'])
                        }
                    });
                }
                e.preventDefault();
                return false;
            });
        });
    });

    $body.on('click', '[data-toggle="show"]', function(){
        const elem = $(this),
            target = $(elem.attr('href'));
        target.addClass('show');
        elem.hide();
        return false;
    });
})();

window.showLoginModal = () => $('#loginmodal').modal("show")
