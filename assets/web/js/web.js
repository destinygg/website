import $ from 'jquery'
import moment from 'moment'

// Generic popup defaults
const popupDefaults = {height: 500, width: 420, scrollbars: 0, toolbar: 0, location: 0, status: 'no', menubar: 0, resizable: 0, dependent: 0 };
const getOptionsString = function(options){
    options = (!options) ? popupDefaults : Object.assign({}, popupDefaults, options);
    return Object.keys(options).map(k => `${k}=${options[k]}`).join(',');
};

const $document = $(document),
    $body = $document.find('body');

(function(){

    $body.find('.text-message textarea[maxlength]').each((i, e) => {
        const ta = $(e), max = ta.attr('maxlength'),
            indicator = $(`<div class="max-length-indicator">${max}</div>`)
        ta.on('keyup', () => indicator.text(max - ta.val().toString().length))
        ta.after(indicator)
    });

    $body.find('form.validate').validate({
        highlight: (e) => $(e).closest('.form-group').addClass('error'),
        unhighlight: (e) => $(e).closest('.form-group').removeClass('error')
    });

})();

(function(){

    const getRootContainer = function(e) {
        return $('#alerts-container')
    };

    const addAutoHideTimer = function($e, delay = 7000) {
        setTimeout(function(){
            if ($e[0] && $e.parent()[0]) {
                $e.detach().remove()
            }
        }, delay)
    };

    const ensureContainerLimit = function($c) {
        const alerts = $c.find('.alert-container');
        if (alerts.length > 2) {
            alerts.last().fadeOut(500, function(){ $(this).detach() });
        }
    };

    const addToRootContainer = function(root, alert) {
        alert.prependTo(root);
        addAutoHideTimer(alert);
        ensureContainerLimit(root);
        setTimeout(() => { alert.addClass('show') }, 1);
    };

    $.fn.alertSuccess = function(message){
        const root = getRootContainer(),
            alert = $(`<div class="alert-container"><div class="alert alert-info alert-dismissable">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
                          <strong><i class="fas fa-check-square"></i> Success</strong>
                          <div>${message}</div>
                      </div></div>`);
        addToRootContainer(root, alert)
    };

    $.fn.alertDanger = function(message){
        const root = getRootContainer(),
            alert = $(`<div class="alert-container"><div class="alert alert-danger alert-dismissable">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
                          <strong><i class="fas fa-exclamation-triangle"></i> Error</strong>
                          <div>${message}</div>
                      </div></div>`);
        addToRootContainer(root, alert)
    };

    $(function(){
        getRootContainer(this).find('.alert-container').each(function(){
            const root = getRootContainer(),
                alert = $(this).detach();
            addToRootContainer(root, alert);
        })
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
            clone.one('error', function () {
                img.removeClass('is-loading')
                    .addClass('is-invalid');
            });
            clone.removeClass('img_320x240 img_64x64 is-loading')
                .removeAttr('data-src')
                .attr('src', url);
            img.addClass('is-loading');
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

    const applyMomentToElement = function (e) {
        const ui = $(e),
            format = ui.data('format') || 'MMMM Do, h:mm:ss a';
        let datetime = ui.data('datetime') || ui.attr('datetime') || ui.text();
        if (datetime === true)
            datetime = ui.attr('title');
        if (!ui.attr('title')) {
            ui.attr('title', datetime)
        }
        if (ui.data('moment-fromnow')) {
            ui.addClass('moment-update');
            ui.html(moment(datetime).fromNow());
        } else if (ui.data('moment-calendar')) {
            ui.addClass('moment-update');
            ui.html(moment(datetime).calendar());
        } else {
            ui.html(moment(datetime).format(format));
        }
        ui.data('datetime', datetime).addClass('moment-set');
    };

    const applyMomentUpdate = function () {
        $('time.moment-update').each(function () {
            applyMomentToElement(this);
        });
    };

    const applyMomentToElements = function () {
        $('time[data-moment]:not(.moment-set)').each(function () {
            applyMomentToElement(this);
        });
    };

    window.applyMomentUpdate = applyMomentUpdate;
    window.applyMomentToElements = applyMomentToElements;
    window.applyMomentToElement = applyMomentToElement;
    window.setInterval(applyMomentUpdate, 30000);

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
        }, 15000);

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

// For `/subscribe`.
(function(){
    const $searchUserForm = $('form#search-user')
    const $searchUserUsernameInput = $searchUserForm.find('#username-input')
    const $searchUserValidFeedback = $searchUserForm.find('.valid-feedback')
    const $searchUserInvalidFeedback = $searchUserForm.find('.invalid-feedback')
    const $searchUserConfirmButton = $searchUserForm.find(' > button:last-child')

    const $periodsSelectables = $('.periods .selectable')
    const $selfSelectable = $('#self .selectable')
    const $directGiftSelectable = $('#direct-gift .selectable')
    const $massGiftSelectable = $('#mass-gift .selectable')

    const $directGiftExpansionArrow = $('#direct-gift .expansion-arrow')
    const $gifteeField = $('#direct-gift .value')

    const $massGiftExpansionArrow = $('#mass-gift .expansion-arrow')
    const $quantityField = $('#mass-gift .value')

    const $quantitySelector = $('#quantity-selector')
    const $quantityButtons = $quantitySelector.find('.two-tone-button')
    const $staticQuantityButtons = $quantitySelector.find('#static-quantity-buttons .two-tone-button')
    const $customQuantityButton = $quantitySelector.find('#custom-quantity-button .two-tone-button')
    const $quantitySelectorInput = $quantitySelector.find('#quantity')

    const $continueForm = $('#continue-form')
    const $subscriptionInput = $continueForm.find('input:first-child')
    const $purchaseTypeInput = $continueForm.find('input:nth-child(2)')
    const $gifteeInput = $continueForm.find('input:nth-child(3)')
    const $quantityInput = $continueForm.find('input:nth-child(4)')
    const $continueButton = $continueForm.find('button')
    const $continueFormInvalidFeedback = $continueForm.find('.invalid-feedback')

    // Convert an element into a jQuery object if it isn't one already.
    const makeDollar = function(element) {
        return element instanceof $ ? element : $(element)
    }

    const selectSelectableElement = function(element) {
        // Imitate the functionality of radio buttons. If a selectable is
        // clicked, toggle it on and toggle off all other selectables in its
        // group.
        const $element = makeDollar(element)

        if (!$element.hasClass('selected')) {
            const group = $element.data('select-group')
            $(`.selected[data-select-group="${group}"]`).removeClass('selected')
            $element.removeClass('considering')
            $element.addClass('selected')
        }
    }

    const toggleExpandingElementForArrow = function(clickedArrow) {
        // Imitate Bootstrap's collapsible component.
        const $clickedArrow = makeDollar(clickedArrow)
        const $target = $($clickedArrow.data('expansion-target'))

        if ($target.is(':visible')) {
            // Change arrow direction depending on if the element is expanded or
            // collapsed.
            $clickedArrow.removeClass('fa-arrow-up')
            $clickedArrow.addClass('fa-arrow-down')
        } else {
            $clickedArrow.removeClass('fa-arrow-down')
            $clickedArrow.addClass('fa-arrow-up')
        }

        $target.slideToggle(200)
    }

    const setSearchUserSuccess = function(message) {
        $searchUserValidFeedback.text(message)
        $searchUserUsernameInput.addClass('is-valid')
        $searchUserConfirmButton.prop('disabled', false)
    }

    const setSearchUserError = function(message) {
        $searchUserInvalidFeedback.text(message)
        $searchUserUsernameInput.addClass('is-invalid')
        $searchUserConfirmButton.prop('disabled', true)
    }

    const clearSearchUserMessage = function() {
        $searchUserUsernameInput.removeClass('is-valid is-invalid')
        $searchUserConfirmButton.prop('disabled', true)
    }

    const confirmGiftee = function(giftee) {
        $gifteeField.text(giftee)
        $gifteeField.data('giftee-username', giftee)
        $gifteeField.addClass('badge badge-light')
        toggleExpandingElementForArrow($directGiftExpansionArrow)
        clearContinueFormErrorMessage()
    }

    const setContinueFormErrorMesage = function(message) {
        $continueFormInvalidFeedback.text(message)
        $continueButton.addClass('is-invalid')
    }

    const clearContinueFormErrorMessage = function() {
        $continueButton.removeClass('is-invalid')
    }

    const updateQuantityButtonCosts = function() {
        const $selectedSub = $('.selected[data-select-group="sub-tier"]')
        const selectedSubPrice = parseInt($selectedSub.data('select-price'))

        $quantityButtons.each(function() {
            updateQuantityButton(this, null, selectedSubPrice)
        })
    }

    const updateQuantityButton = function(button, quantity = null, subPrice = null) {
        const $button = makeDollar(button)
        const $numberOfSubsField = $button.find('div:first-child > p')
        const $costField = $button.find('div:last-child > p')

        if (!quantity) {
            quantity = $button.data('quantity')
        }

        if (!subPrice) {
            const $selectedSub = $('.selected[data-select-group="sub-tier"]')
            subPrice = parseInt($selectedSub.data('select-price'))
        }

        $numberOfSubsField.text(`${quantity} Sub`)
        if (quantity > 1) {
            $numberOfSubsField.text($numberOfSubsField.text() + 's')
        }

        $button.data('quantity', quantity)
        $costField.text(`$${quantity * subPrice}`)
    }

    $('.selectable').click(function() {
        selectSelectableElement(this)
    })

    $('.expansion-arrow').click(function() {
        toggleExpandingElementForArrow(this)
    })

    $periodsSelectables.click(function() {
        updateQuantityButtonCosts()
    })

    $searchUserForm.submit(function(event) {
        event.preventDefault()

        const username = $searchUserUsernameInput.val().trim()
        $searchUserUsernameInput.val(username)
        if (username === '') {
            return
        }

        $.ajax({
            url: '/api/info/giftcheck',
            data: {s: username},
            type: 'GET',
            success: function(data) {
                if (data['valid'] && data['cangift']) {
                    setSearchUserSuccess('This user can accept gift subs!')
                } else if (!data['valid'] && !data['cangift']) {
                    setSearchUserError('This user doesn\'t exist.')
                } else if (data['valid'] && !data['cangift']) {
                    setSearchUserError('This user can\'t accept gift subs.')
                } else if (!data['valid'] && data['cangift']) {
                    setSearchUserError('YEE NEVA EVA LOSE.')
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    // This is required because of a server-side check that
                    // prevents the user from gifting a sub to themselves.
                    setSearchUserError('Sorry, you have to be logged in to search for users.')
                } else {
                    setSearchUserError('Something went wrong. Please try again later.')
                }
            }
        })
    })

    $searchUserUsernameInput.keydown(function() {
        clearSearchUserMessage()
    })

    $searchUserConfirmButton.click(function() {
        confirmGiftee($searchUserUsernameInput.val())
    })

    $selfSelectable.click(function() {
        clearContinueFormErrorMessage()
    })

    $directGiftExpansionArrow.click(function() {
        selectSelectableElement($directGiftSelectable)
    })

    $directGiftSelectable.click(function() {
        toggleExpandingElementForArrow($directGiftExpansionArrow)
    })

    $massGiftSelectable.click(function() {
        toggleExpandingElementForArrow($massGiftExpansionArrow)
    })

    $massGiftExpansionArrow.click(function() {
        selectSelectableElement($massGiftSelectable)
    })

    $quantitySelectorInput.on('keyup change', function() {
        // No negatives allowed.
        if (event.type === 'keyup' && event.which === 189) {
            return false
        }

        let quantity = parseInt($quantitySelectorInput.val())
        if (isNaN(quantity)) {
            return
        }

        if (quantity < 1) {
            quantity = 1
            $quantitySelectorInput.val(quantity)
        } else if (quantity > 100) {
            quantity = 100
            $quantitySelectorInput.val(quantity)
        }

        updateQuantityButton($customQuantityButton, quantity, null)
    })

    $quantitySelectorInput.select(function(event) {
        const selection = $quantitySelectorInput.val().substring(this.selectionStart, this.selectionEnd)
        console.log(selection)
    })

    $quantityButtons.click(function() {
        const $clickedButton = $(this)
        const $numberOfSubsField = $clickedButton.find('div:first-child > p')

        $quantityField.text($numberOfSubsField.text().toLowerCase())
        $quantityField.data('quantity', $clickedButton.data('quantity'))
        $quantityField.addClass('badge badge-light')

        toggleExpandingElementForArrow($massGiftExpansionArrow)
    })

    $continueForm.submit(function() {
        const $selectedSub = $('.selected[data-select-group="sub-tier"]')
        $subscriptionInput.val($selectedSub.data('select-id'))

        const $selectedRecipient = $('.selected[data-select-group="recipient"]')
        $purchaseTypeInput.val($selectedRecipient.data('select-id'));

        switch ($purchaseTypeInput.val()) {
            case 'self':
                $gifteeInput.val('')
                $quantityInput.val(1)
                break
            case 'direct-gift':
                const username = $gifteeField.data('giftee-username')
                if (username === '') {
                    setContinueFormErrorMesage('You haven\'t picked a recipient for your gift sub.')
                    return false
                }

                $gifteeInput.val(username)
                $quantityInput.val(1)
                break
            case 'mass-gift':
                const quantity = parseInt($quantityField.data('quantity'))
                if (isNaN(quantity)) {
                    setContinueFormErrorMesage('You haven\'t selected how many subs to gift.')
                    return false
                }

                $gifteeInput.val('')
                $quantityInput.val(quantity)
                break
        }

        // Submit form normally after updating inputs.
        return true
    })
})()

window.showLoginModal = () => $('#loginmodal').modal("show")
