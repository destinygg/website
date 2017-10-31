// Message composition UI
const chatformatters = require('dgg-chat-gui/assets/chat/js/formatters')
const chatemotes = require('dgg-chat-gui/assets/emotes.json')
const ctx = {emoticons: chatemotes.destiny, twitchemotes:chatemotes.twitch}
const formatters = [new chatformatters.HtmlTextFormatter(),new chatformatters.UrlFormatter(),new chatformatters.EmoteFormatter()]

// COMPOSE
$(function(){

    $('#compose.message-composition').each(function(){

        const $modal = $('#compose'),
            $modalmsg = $modal.find('.modal-message'),
            $form = $modal.find('#compose-form'),
            $pagealrt = $('#alerts-container'),
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
            $modalmsg.show().html('<i class="fa fa-cog fa-spin"></i> Sending message ...');

            saveStateOnClose = true;
            $.ajax({type: 'post', url: '/profile/messages/send', data: {'recipients' : recipients, 'message' : message}})
                .always(() => saveStateOnClose = false)
                .fail(e => $modalmsg.show().html(`<span class="text-danger">${e.status}: ${e.statusText}</span>`))
                .done(data => {
                    if(data.success === true){
                        $pagealrt.show().html('<div class="alert alert-info"><strong>Sent!</strong> Your message has been sent.</div>')
                        window.setTimeout(() => $pagealrt.hide(), 3000)
                        $modal.modal('hide')
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
                $recipientscont.append(
                 `<span class="${style.join(' ')}" data-recipient="${id}">`+
                    `<span class="recipient-name">${recipient}</span>`+
                    `<i class="glyphicon glyphicon-remove remove-recipient" title="Remove"></i>`+
                 `</span>`
                )
            }
        }

        const getRecipientLabels = function(){
            return $recipientscont.find('.recipient').get().map(e => $(e).data('recipient'))
        }

        $modal.on('keydown', () => $modalmsg.hide())
        $modal.on('shown.bs.modal', e => $(e.currentTarget).find('input#compose-recipients').focus())
        $modal.on('hidden.bs.modal', () => {
            if(!saveStateOnClose){
                resetForm();
            }
        })
        $modal.on('click', '.remove-recipient', e => $(e.currentTarget).closest('.recipient').remove())
        $modal.on('change', 'input#compose-recipients', function(){
            splitRecipientString( $(this).val() ).forEach(addRecipientLabel)
            $recipients.val('');
        })
        $modal.on('keypress', 'input#compose-recipients', function(e){
            const keycheck = /[;:,']/i,
                 key = e.which,
                 KEYCODE_SPACE = 32,
                 KEYCODE_ENTER = 13;
            if(key === KEYCODE_SPACE || key === KEYCODE_ENTER || keycheck.test(String.fromCharCode(key))){
                splitRecipientString( $(this).val() ).forEach(addRecipientLabel)
                $recipients.val('')
                e.preventDefault()
                e.stopPropagation()
            }
            $recipients.focus()
        })
        $clsbtn.on('click', () => $modal.modal('hide'))
        $submitbtn.on('click', sendMessage)

        $message.on('keydown', function (e) {
            if (e.ctrlKey && e.keyCode === 13) {
                sendMessage();
                e.preventDefault();
                e.stopPropagation();
            }
        })

        $usergroups.on('click', '.groups a', e => addRecipientLabel( $(e.currentTarget).text(), 'group' ))

        $('#message-reply').on('click', (e) => {
            $modal.unbind('shown.bs.modal')
                  .on('shown.bs.modal', e => $(e.currentTarget).find('textarea').focus())
            $modal.find('#composeLabel')
                  .text('Reply ...')
            $modal.find('.modal-recipients,.modal-settings,.modal-user-groups')
                  .hide()
            $recipients.val('')
            $recipientscont.empty()
            addRecipientLabel( $(e.currentTarget).data('replyto') )
        })
    })

})

// INBOX
$(function(){

    const inboxtable = $('table#inbox')
    if(inboxtable.length > 0) {
        const activateSelector = function(){
            $(this).find('i').attr('class', 'fa fa-dot-circle-o')
            $(this).addClass('active')
        };

        const deactivateSelector = function(){
            $(this).find('i').attr('class', 'fa fa-circle-o')
            $(this).removeClass('active')
        };

        const toggleRowSelector = function(e){
            const self = $(this)
            e.preventDefault()
            e.stopPropagation()
            if(self.hasClass('active')){
                deactivateSelector.apply(self)
            }else{
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

        inboxtable.each(function(i, el){
            $(el)
                .on('click', 'tbody tr', toggleRowClick)
                .on('click', 'tbody td.selector', toggleRowSelector)
                .on('mousedown', 'tbody tr', pressedTableRow)
                .on('mouseup', 'tbody tr', releasedTableRow)
        });

        $('#mark-all').on('click', function(){
            $.ajax({url: '/api/messages/open'})
            .always(() => window.location.reload())
            return false
        });

        let start = 0
        const messagemore = $('#inbox-show-more'),
               inboxempty = $('#inbox-empty'),
             inboxloading = $('#inbox-loading')
        function displayInbox(data){
            start += 25
            inboxloading.fadeOut()
            const container = inboxtable.find('tbody')
            const out = (data.length? data: []).map(msg => {
                let message = msg.message.trim()
                formatters.forEach(f => message = f.format(ctx, message));
                return ``+
                `<tr data-id="${msg.userid}" data-username="${msg.user}" class="${msg.unread <= 0 ? 'read':'unread'}">`+
                    `<td class="from">`+
                        `<a href="/profile/messages/${msg.userid}">${msg.user}</a> `+
                        `<span class="count">(${msg.unread > 0  ? msg.unread : parseInt(msg.read)+parseInt(msg.unread)})</span>`+
                    `</td>`+
                    `<td class="message"><span>${message}</span></td>`+
                    `<td class="timestamp">${msg.timestamp}</td>`+
                `</tr>`
            });
            container.append(out.join('') + (out.length? `<hr />`: ``))
            messagemore.toggle(out.length > 20)
            inboxtable.toggle(out.length > 0)
            inboxempty.toggle(out.length === 0)
        }
        function loadInbox(){
            inboxloading.fadeIn()
            return $.ajax({url: '/api/messages/inbox', data: {s: start}})
                    .done(displayInbox)
                    .fail(console.error)
        }
        messagemore.on('click', loadInbox)
        loadInbox()
    }

});

// MESSAGE
$(function(){

    const messagelist = $('#message-list'),
               userid = messagelist.data('userid'),
             username = messagelist.data('username'),
             messages = messagelist.find('.message'),
            container = messagelist.find('#message-container'),
              loading = messagelist.find('#message-list-loading'),
             showmore = messagelist.find('#message-list-more')

    if(messagelist.length > 0) {
        let start = 0
        function displayMessages(data){
            start += 10
            loading.fadeOut()
            showmore.show()
            showmore.attr('disabled', !data || data.length === 0 ? 'disabled' : null)
            const out = (data.length? data: []).reverse().map(msg => {
                let message = msg['message'].trim()
                formatters.forEach(f => message = f.format(ctx, message));
                const isme = (parseInt(userid) !== parseInt(msg['userid'])), styles = []
                styles.push('message-active')
                styles.push('message-' + (isme ? 'me':'notme'))
                styles.push(msg['isread'] === 1 ? 'message-read' : 'message-unread')
                return ``+
                `<div id="${msg['id']}" class="message ${styles.join(' ')} content">`+
                    `<div class="message-from">`+
                        `<div class="message-date pull-right"><i class="fa message-list-item-status pull-right ${isme ? 'read':'unread'}"></i>  ${msg['timestamp']}</div>`+
                        `<span title="${msg['from']}">${msg['from']}</span>`+
                    `</div>`+
                    `<div class="message-content">`+
                        `<div class="message-txt">${message}</div>`+
                    `</div>`+
                `</div>`
            })
            container.prepend(out.join('') + (out.length? `<hr />`: ``))
        }
        function loadMessages(){
            loading.fadeIn()
            return $.ajax({url:`/api/messages/usr/${encodeURIComponent(username.toLowerCase())}/inbox`, data: {s: start}})
                    .always(displayMessages)
        }
        showmore.on('click', () => loadMessages().done(() => window.scrollTo(0,0)))
        loadMessages().done(() => window.scrollTo(0,document.body.scrollHeight))
    }
})