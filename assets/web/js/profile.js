// Message composition UI
// TODO re-implement emotes in private messages

import $ from 'jquery'
import {debounce} from 'throttle-debounce'
import {HtmlTextFormatter,UrlFormatter,EmoteFormatter} from 'dgg-chat-gui/assets/chat/js/formatters'

const formatters = [new HtmlTextFormatter(),new UrlFormatter(),new EmoteFormatter()], ctx = {};

(function(){

    const $inboxToolsForm = $('form#inbox-tools-form');

    // COMPOSE
    const $composeModal = $('#compose.message-composition')
    if($composeModal.length > 0) {
        (function () {
            const $modalmsg = $composeModal.find('.modal-message'),
                $form = $composeModal.find('#compose-form'),
                $message = $form.find('textarea#compose-message'),
                $recipients = $form.find('input#compose-recipients'),
                $recipientscont = $form.find('.modal-recipients .recipient-container'),
                $submitbtn = $form.find('button#modal-send-btn'),
                $clsbtn = $form.find('button#modal-close-btn'),
                $usergroups = $form.find('.modal-user-groups')
            let saveStateOnClose = false

            const disableMessageForm = function (){
                $form.find('button,input,textarea').attr('disabled', 'disabled');
            }

            const enableMessageForm = function (){
                $form.find('button,input,textarea').removeAttr('disabled');
            }

            const resetForm = function (){
                $message.val('')
                $recipients.val('')
                $recipientscont.empty()
                $modalmsg.hide()
                enableMessageForm()
            }

            const sendMessage = function (){
                const message = $message.val(),
                    recipients = getRecipientLabels()

                if(recipients.length === 0){
                    $modalmsg.show().html('<span class="text-danger">Recipients required</span>')
                    return
                }
                if(message.trim() === ''){
                    $modalmsg.show().html('<span class="text-danger">Message required</span>')
                    return
                }
                if(message.trim().length > 500){
                    $modalmsg.show().html('<span class="text-danger">Your message cannot be longer than 500 characters</span>')
                    return
                }

                disableMessageForm();
                $modalmsg.show().html('<i class="fas fa-cog fa-spin"></i> Sending message ...');

                saveStateOnClose = true;
                $.ajax({type: 'post', url: '/profile/messages/send', data: {'recipients' : recipients, 'message' : message}})
                    .always(() => saveStateOnClose = false)
                    .fail(e => $modalmsg.show().html(`<span class="text-danger">${e.status}: ${e.statusText}</span>`))
                    .done(data => {
                        if(data.success === true){
                            $(document).alertSuccess("Your message has been sent.", {delay: 3000});
                            $composeModal.modal('hide')
                        }else{
                            $modalmsg.show().html(`<span class="text-danger">${data.message}</span>`);
                            enableMessageForm();
                        }
                    });
            }

            const splitRecipientString = function (str){
                return str.split(' ').filter(e => e.search(/^[A-Z0-9_]{3,20}$/i) === 0);
            }

            const addRecipientLabel = function(recipient, style){
                const id = recipient.toLowerCase()
                style = ['recipient', style]
                if(!$recipientscont.find('.recipient[data-recipient="'+ id +'"]').get(0)){
                    $recipientscont.append(``+
                        `<span class="${style.join(' ')}" data-recipient="${id}">`+
                            `<span class="recipient-name">${recipient}</span>`+
                            `<i class="glyphicon glyphicon-remove remove-recipient" title="Remove"></i>`+
                        `</span>`
                    )
                }
            }

            const getRecipientLabels = function () {
                return $recipientscont.find('.recipient').get().map(e => $(e).data('recipient'))
            }

            $composeModal.on('keydown', () => $modalmsg.hide())
            $composeModal.on('shown.bs.modal', e => $(e.currentTarget).find('input#compose-recipients').focus())
            $composeModal.on('hidden.bs.modal', () => {
                if (!saveStateOnClose) {
                    resetForm();
                }
            })
            $composeModal.on('click', '.remove-recipient', e => $(e.currentTarget).closest('.recipient').remove())
            $composeModal.on('change', 'input#compose-recipients', function () {
                splitRecipientString($(this).val()).forEach(addRecipientLabel)
                $recipients.val('');
            })
            $composeModal.on('keypress', 'input#compose-recipients', function (e) {
                const keycheck = /[;:,']/i,
                    key = e.which,
                    KEYCODE_SPACE = 32,
                    KEYCODE_ENTER = 13;
                if (key === KEYCODE_SPACE || key === KEYCODE_ENTER || keycheck.test(String.fromCharCode(key))) {
                    splitRecipientString($(this).val()).forEach(addRecipientLabel)
                    $recipients.val('')
                    e.preventDefault()
                    e.stopPropagation()
                }
                $recipients.focus()
            })
            $clsbtn.on('click', () => $composeModal.modal('hide'))
            $submitbtn.on('click', sendMessage)

            $message.on('keydown', function (e) {
                if (e.ctrlKey && e.keyCode === 13) {
                    sendMessage();
                    e.preventDefault();
                    e.stopPropagation();
                }
            })

            $usergroups.on('click', '.groups a', e => addRecipientLabel($(e.currentTarget).text(), 'group'))

            $('#inbox-message-reply').on('click', (e) => {
                $composeModal.unbind('shown.bs.modal')
                    .on('shown.bs.modal', e => $(e.currentTarget).find('textarea').focus())
                $composeModal.find('#composeLabel')
                    .text('Reply ...')
                $composeModal.find('.modal-recipients,.modal-settings,.modal-user-groups')
                    .hide()
                $recipients.val('')
                $recipientscont.empty()
                addRecipientLabel($(e.currentTarget).data('replyto'))
            })
        })()
    }

    const toggleShowMoreBtn = function($btnShowMore, data, pageSize) {
        if (!data || data.length < pageSize) {
            $btnShowMore.text('The End')
                .prop('disabled', true)
                .attr('class', 'btn btn btn-dark')
                .show()
        } else {
            $btnShowMore.text('Show more')
                .prop('disabled', false)
                .attr('class', 'btn btn-primary')
                .show()
        }
    }

    // INBOX
    const $inboxTable = $('#inbox-list')
    if($inboxTable.length > 0) {
        (function(){
            const $inboxTools = $('#inbox-tools'),
                $btnToggle = $inboxTools.find('#inbox-toggle-select'),
                $btnReadSelected = $inboxTools.find('#inbox-read-selected'),
                $btnDelete = $inboxTools.find('#inbox-delete-selected'),
                $btnShowMore = $('#inbox-list-more'),
                $inboxLoading = $('#inbox-loading'),
                $inboxEmpty = $('#inbox-empty'),
                $modalDelete = $('#inbox-modal-delete');
            let start = 0, pageSize = 25;

            const toggleToolsBasedOnSelection = function() {
                const someSelected = getActiveSelectors().length === 0
                $btnReadSelected.prop( 'disabled', someSelected)
                $btnDelete.prop( 'disabled', someSelected)
            };

            const getActiveSelectors = function() {
                return $inboxTable.find('tbody td.selector.active').toArray()
            };

            const activateSelector = function(){
                $(this).addClass('active')
                    .find('i')
                    .attr('class', 'far fa-dot-circle')
                toggleToolsBasedOnSelection()
            };

            const deactivateSelector = function () {
                $(this).removeClass('active')
                    .find('i')
                    .attr('class', 'far fa-circle')
                toggleToolsBasedOnSelection()
            };

            const toggleRowSelector = function (e) {
                const self = $(this)
                e.preventDefault()
                e.stopPropagation()
                if (self.hasClass('active')) {
                    deactivateSelector.apply(self)
                } else {
                    activateSelector.apply(self)
                }
            };

            const toggleRowClick = function(e){
                const self = $(this),
                    userid = self.data('id')
                if(userid !== undefined){
                    e.preventDefault()
                    e.stopPropagation()
                    window.location.href = '/profile/messages/'+ encodeURIComponent(userid)
                }
            };

            const pressedTableRow = function(){
                $(this).addClass('pressed')
            }

            const releasedTableRow = function(){
                $(this).removeClass('pressed')
            }

            $inboxTable.each(function(i, el){
                $(el)
                    .on('click', 'tbody tr', toggleRowClick)
                    .on('click', 'tbody td.selector', toggleRowSelector)
                    .on('mousedown', 'tbody tr', pressedTableRow)
                    .on('mouseup', 'tbody tr', releasedTableRow)
            });

            $btnToggle.on('click touch', function(){
                $inboxTable.find('.selector').each(function(){
                    const self = $(this)
                    if (self.hasClass('active')) {
                        deactivateSelector.apply(self)
                    } else {
                        activateSelector.apply(self)
                    }
                })
                return false
            });

            $btnReadSelected.on('click touch', function(){
                const selected = getActiveSelectors().map(a => {
                    return $(a).closest('tr').data('id')
                })
                $inboxToolsForm.attr('action', '/profile/messages/read')
                    .append(selected.map(e => `<input type="hidden" name="selected[]" value="${e}" />`))
                    .submit();
                return false
            });

            $btnDelete.on('click touch', function(){
                const selected = getActiveSelectors().map(a => {
                    return $(a).closest('tr').data('id')
                })
                const buttons = $modalDelete.find('button')
                $modalDelete.find('.modal-body').html('' +
                    `<div>Are you sure you want to delete ${selected.length} conversation(s)?</div>` +
                    '<div>This cannot be undone.</div>'
                )
                $modalDelete.on('click touch', '#deleteConversation', function(){
                    buttons.prop('disabled', true)
                    $inboxToolsForm.attr('action', '/profile/messages/delete')
                        .append(selected.map(e => `<input type="hidden" name="selected[]" value="${e}" />`))
                        .submit();
                });
                $modalDelete.modal('show')
            });

            const handleMessage = function(msg) {
                let message = msg.message.trim()
                formatters.forEach(f => message = f.format(ctx, message));
                return ``+
                    `<tr data-id="${msg.userid}" data-username="${msg.user}" class="${msg.unread <= 0 ? 'read':'unread'}">`+
                        `<td class="selector"><i class="far fa-circle"></i></td>`+
                        `<td class="from">`+
                            `<a href="/profile/messages/${msg.userid}">${msg.user}</a> `+
                            `<span class="count">(${msg.unread > 0  ? msg.unread : parseInt(msg.read)+parseInt(msg.unread)})</span>`+
                        `</td>`+
                        `<td class="message-txt"><span>${message}</span></td>`+
                        `<td class="timestamp"><time data-moment>${msg.timestamp}</time></td>`+
                    `</tr>`
            }
            const displayInbox = function(data){
                start += pageSize
                $inboxTable.find('table#inbox-message-grid tbody').append((data || []).map(handleMessage).join(''))
                toggleShowMoreBtn($btnShowMore, data, pageSize)
                $inboxEmpty.toggle(start === pageSize && data.length === 0)
                $inboxLoading.fadeOut()
                window.applyMomentToElements()
            }
            const loadInbox = function(){
                $inboxLoading.fadeIn()
                return $.ajax({url: '/api/messages/inbox', data: {s: start}})
                    .always(displayInbox)
            }
            $btnShowMore.on('click', loadInbox)
            loadInbox()
        })()
    }

    // MESSAGES
    const $messageTable = $('#message-list')
    if($messageTable.length > 0) {

        const userid = $messageTable.data('userid'),
            username = $messageTable.data('username'),
            $inboxLoading = $('#inbox-loading'),
            $btnShowMore = $('#inbox-list-more'),
            $btnDelete = $('#inbox-delete-selected'),
            $inboxEmpty = $('#inbox-empty'),
            $modalDelete = $('#inbox-modal-delete');
        let start = 0, pageSize = 25;

        const handleMessage = function(msg){
            let message = msg.message.trim()
            formatters.forEach(f => message = f.format(ctx, message));
            const isme = (parseInt(userid) !== parseInt(msg.userid)), styles = []
            styles.push('message-active')
            styles.push('message-' + (isme ? 'me':'notme'))
            styles.push(msg.isread === 1 ? 'message-read' : 'message-unread')
            return ``+
                `<tr data-id="${msg.id}" data-username="${msg.from}" class="${styles.join(' ')}">`+
                    `<td class="message">`+
                        `<div class="message-header">`+
                            `<div class="from">`+
                                `<i title="${msg.isread ? 'Opened':'Unopened'}" class="fa fa-fw fa-dgg-${msg.isread ? 'read':'unread'}"></i>`+
                                `<div class="username">${msg.from}</div>`+
                                `<time class="timestamp" data-moment data-moment-fromnow="true">${msg.timestamp}</time>`+
                            `</div>`+
                            `<time class="timestamp" data-moment>${msg.timestamp}</time>`+
                        `</div>`+
                        `<div class="message-txt">${message}</div>`+
                    `</td>`+
                `</tr>`
        }
        const displayMessages = function(data){
            start += pageSize
            $messageTable.find('table#inbox-message-grid tbody').append((data.length ? data : []).map(handleMessage).join(''))
            toggleShowMoreBtn($btnShowMore, data, pageSize)
            $inboxEmpty.toggle(start === pageSize && data.length === 0)
            $inboxLoading.fadeOut()
            window.applyMomentToElements()
        }
        const loadMessages = function(){
            $inboxLoading.fadeIn()
            return $.ajax({
                url:`/api/messages/usr/${encodeURIComponent(username.toLowerCase())}/inbox`,
                data: {s: start}
            })
            .always(displayMessages)
        }

        $btnShowMore.on('click', loadMessages)
        loadMessages()

        $btnDelete.on('click touch', function(){
            const selected = [$messageTable.data('userid')]
            $modalDelete.find('.modal-body').html('' +
                `<div>Are you sure you want to delete this entire conversation?</div>` +
                '<div>This cannot be undone.</div>'
            )
            $modalDelete.on('click touch', '#deleteConversation', function(){
                $inboxToolsForm.attr('action', '/profile/messages/delete')
                    .append(selected.map(e => `<input type="hidden" name="selected[]" value="${e}" />`))
                    .submit();
            });
            $modalDelete.modal('show')
        });

        $('#inbox-scroll-bottom').on('click', () => {
            window.scrollTo(0, document.body.scrollHeight)
            return false
        });
        $('#inbox-scroll-top').on('click', () => {
            window.scrollTo(0, 0)
            return false
        });
    }

})();

(function(){
    const nameChangeForm = $('form#nameForm');
    nameChangeForm.each(function(){
        const nameChange = $('input#nameChange'),
            nameChangeAlert = $('#nameChangeAlert'),
            nameChangeBackDrop = $('#nameChangeBackDrop'),
            alertContentDefault = nameChangeAlert.find('p').html(),
            alertContentSuccess = '<i class="fas fa-fw fa-check-circle"></i>&nbsp;Click <strong>here</strong> to confirm your username!';
        let isValidName = false;
        const onUsernameChange = function(){
            const username = nameChange.val();
            if (username === '') {
                nameChangeAlert.find('p').html(alertContentDefault)
                return;
            }
            $.ajax({
                url: '/profile/usernamecheck',
                data: {username},
                type: 'GET',
                success: function(data) {
                    if (data) {
                        if (data['success'] === true) {
                            nameChangeAlert.removeClass('alert-danger').addClass('alert-success');
                            nameChangeAlert.find('p').html(alertContentSuccess)
                            isValidName = true
                        } else {
                            nameChangeAlert.removeClass('alert-success').addClass('alert-danger');
                            const msg = data['error'] ? data['error'] : 'Username already exists, try another!';
                            nameChangeAlert.find('p').html('<i class="fas fa-fw fa-times-circle"></i>&nbsp;' + msg)
                        }
                    } else {
                        nameChangeAlert.removeClass('alert-success').addClass('alert-danger');
                    }
                }
            });
        };
        nameChangeAlert.on('click', function(){
            if (isValidName) nameChangeForm.submit();
            return false;
        });
        nameChangeBackDrop.on('click', function(){
            nameChange.blur().focus();
            return false;
        });
        nameChange.on('change keyup', debounce(250, false, onUsernameChange));
    });

})();

(function(){

    const $connTable = $('#connections-content'),
        $btnToggle = $('#connectSelectToggleBtn'),
        $btnRemove = $('#connectRemoveBtn'),
        $connectToolsForm = $('#connectToolsForm');

    if ($connTable.length > 0) {

        const toggleToolsBasedOnSelection = function() {
            const someSelected = getActiveSelectors().length === 0
            $btnRemove.prop( 'disabled', someSelected)
        };

        const getActiveSelectors = function() {
            return $connTable.find('tbody td.selector.active').toArray()
        };

        const activateSelector = function(){
            $(this).addClass('active')
                .find('i')
                .attr('class', 'far fa-dot-circle')
            toggleToolsBasedOnSelection()
        };

        const deactivateSelector = function () {
            $(this).removeClass('active')
                .find('i')
                .attr('class', 'far fa-circle')
            toggleToolsBasedOnSelection()
        };

        const toggleRowSelector = function (e) {
            const self = $(this)
            e.preventDefault()
            e.stopPropagation()
            if (self.hasClass('active')) {
                deactivateSelector.apply(self)
            } else {
                activateSelector.apply(self)
            }
        };

        $btnToggle.on('click touch', function(e){
            $connTable.find('.selector').each(function(){
                const self = $(this)
                if (self.hasClass('active')) {
                    deactivateSelector.apply(self)
                } else {
                    activateSelector.apply(self)
                }
            })
            return false
        });

        $btnRemove.on('click', function(){
            if (confirm("Are you sure? This cannot be undone!")) {
                const selected = getActiveSelectors().map(a => {
                    return $(a).closest('tr').data('id')
                })
                $connectToolsForm.attr('action', '/profile/remove')
                    .append(selected.map(e => `<input type="hidden" name="selected[]" value="${e}" />`))
                    .submit();
                return false
            }
        });

        $connTable.on('click', 'tbody td.selector', toggleRowSelector)

    }
})();

