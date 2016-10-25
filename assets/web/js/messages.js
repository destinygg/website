// Message composition UI
$(function(){

    $('#compose.message-composition').each(function(){

        var $modal = $('#compose'),
            $modalmsg = $modal.find('.modal-message'),
            $form = $modal.find('#compose-form'),
            $pagealrt = $('#alerts-container'),
            $message = $form.find('textarea#compose-message'),
            $recipients = $form.find('input#compose-recipients'),
            $recipientscont = $form.find('.modal-recipients .recipient-container'),
            $submitbtn = $form.find('button#modal-send-btn'),
            $clsbtn = $form.find('button#modal-close-btn'),
            $usergroups = $form.find('.modal-user-groups'),
            saveStateOnClose = false;

        var disableMessageForm = function (){
            $form.find('button,input,textarea').attr('disabled', 'disabled');
        };

        var enableMessageForm = function (){
            $form.find('button,input,textarea').removeAttr('disabled');
        };

        var resetForm = function (){
            $message.val('');
            $recipients.val('');
            $recipientscont.empty();
            $modalmsg.hide();
            enableMessageForm();
        };

        var sendMessage = function (){

            var message = $message.val(),
                recipients = getRecipientLabels();

            if(recipients.length == 0){
                $modalmsg.show().html('<span class="text-danger">Recipients required</span>');
                return;
            }

            if(message.trim() == ''){
                $modalmsg.show().html('<span class="text-danger">Message required</span>');
                return;
            }

            if(message.trim().length > 500){
                $modalmsg.show().html('<span class="text-danger">Your message cannot be longer than 500 characters</span>');
                return;
            }

            saveStateOnClose = true;
            disableMessageForm();
            $modalmsg.show().html('<i class="fa fa-cog fa-spin"></i> Sending message ...');

            $.ajax({
                type: 'POST',
                url: '/profile/messages/send',
                data: {
                    'recipients' : recipients,
                    'message' : message
                },
                success: function(data){
                    saveStateOnClose = false;
                    if(data.success === true){
                        $pagealrt.show().html('<div class="alert alert-info"><strong>Sent!</strong> Your message has been sent.</div>');
                        window.setTimeout(function(){
                            $pagealrt.hide();
                        }, 3000);
                        $modal.modal('hide');
                    }else{
                        $modalmsg.show().html('<span class="text-danger">'+ data.message +'</span>');
                        enableMessageForm();
                    }
                },
                error: function(e){
                    saveStateOnClose = false;
                    $modalmsg.show().html('<span class="text-danger">'+ e.status +': '+ e.statusText +'</span>');
                }
            });
        };

        var splitRecipientString = function (str){
            var splits = str.split(' '),
                names = [];
            for(var i=0; i<splits.length;++i){
                if(splits[i].search(/^[A-Z0-9_]{3,20}$/i) == 0){
                    names.push(splits[i]);
                }
            }
            return names;
        };

        var addRecipientLabel = function(recipient, style){
            var id = recipient.toLowerCase();
            style = ['recipient', style];
            if(!$recipientscont.find('.recipient[data-recipient="'+ id +'"]').get(0)){
                $recipientscont.append(
                 '<span class="'+ style.join(' ') +'" data-recipient="'+ id +'">\
                    <span class="recipient-name">'+ recipient +'</span>\
                    <i class="glyphicon glyphicon-remove remove-recipient" title="Remove"></i>\
                  </span>'
                );
            }
        };

        var getRecipientLabels = function(){
            var names = [];
            $recipientscont.find('.recipient').each(function(){
                names.push( $(this).data('recipient') );
            });
            return names;
        };

        $modal.on('keydown', function (e){
            $modalmsg.hide();
        });

        $modal.on('shown.bs.modal', function (e){
            $(this).find('input#compose-recipients').focus();
        });

        $modal.on('hidden.bs.modal', function (e){
            if(!saveStateOnClose){
                resetForm();
            }
        });

        $modal.on('click', '.remove-recipient', function(){
            $(this).closest('.recipient').remove();
        });

        $modal.on('change', 'input#compose-recipients', function(e){
            var recipients = splitRecipientString( $(this).val() );
            $recipients.val('');
            for(var i=0; i<recipients.length; ++i){
                addRecipientLabel( recipients[i] );
            }
        });

        $modal.on('keypress', 'input#compose-recipients', function(e){
            var keycheck = /[\;\:\,\']/i,
                key = e.which,
                KEYCODE_SPACE = 32,
                KEYCODE_ENTER = 13;
            if(key == KEYCODE_SPACE || key == KEYCODE_ENTER || keycheck.test(String.fromCharCode(key))){
                var recipients = splitRecipientString( $(this).val() );
                $recipients.val('');
                for(var i=0; i<recipients.length; ++i){
                    addRecipientLabel( recipients[i] );
                }
                e.preventDefault();
                e.stopPropagation();
            }
            $recipients.focus();
        });

        $clsbtn.on('click', function (e){
            $modal.modal('hide');
        });

        $submitbtn.on('click', function (e){
            sendMessage();
        });

        $message.on('keydown', function (e) {
            if (e.ctrlKey && e.keyCode == 13) {
                sendMessage();
                e.preventDefault();
                e.stopPropagation();
            }
        });

        $usergroups.on('click', '.groups a', function(){
            addRecipientLabel( $(this).text(), 'group' );
        });

        var msgmodal = $('#compose');

        $('.message-list').on('click', '.message-header,.message-summary', function(){
            $(this).closest('.message').toggleClass('message-active message-hidden');
        });

        $('.message-reply').on('click', '#reply-toggle', function(){
            msgmodal.unbind('shown.bs.modal').on('shown.bs.modal', function(){
                $(this).find('textarea').focus();
            });
            msgmodal.find('#composeLabel').text('Reply ...');
            msgmodal.find('.modal-recipients,.modal-settings,.modal-user-groups').hide();

            $recipients.val('');
            $recipientscont.empty();
            addRecipientLabel( $(this).data('replyto') );
        });

        $('.message-list').each(function(){
            var showFullMessage = function(){
                $(this).closest('.message').addClass('message-active').removeClass('message-hidden');
            };
            $(this).on('click', '.message-summary', showFullMessage);
        });
        
    });

});

// INBOX
$(function(){

    var inboxtable = $('table#inbox');

    var activateSelector = function(){
        $(this).find('i').attr('class', 'fa fa-dot-circle-o');
        $(this).addClass('active');
    };

    var deactivateSelector = function(){
        $(this).find('i').attr('class', 'fa fa-circle-o');
        $(this).removeClass('active');
    };

    var toggleRowSelector = function(e){
        var self = $(this);
        e.preventDefault();
        e.stopPropagation();
        if(self.hasClass('active')){
            deactivateSelector.apply(self);
        }else{
            activateSelector.apply(self);
        }
    };

    var toggleRowClick = function(e){
        var self = $(this),
            userid = self.data('id');
        if(userid != undefined){
            e.preventDefault();
            e.stopPropagation();
            window.location.href = '/profile/messages/'+ userid;
        }
    };

    var pressedTableRow = function(){
        $(this).addClass('pressed');
    };

    var releasedTableRow = function(){
        $(this).removeClass('pressed');
    };

    inboxtable.each(function(i, el){
        $(el)
            .on('click', 'tbody tr', toggleRowClick)
            .on('click', 'tbody td.selector', toggleRowSelector)
            .on('mousedown', 'tbody tr', pressedTableRow)
            .on('mouseup', 'tbody tr', releasedTableRow);
    });

    $('#mark-all').on('click', function(e){
        e.preventDefault();
        $.ajax({
            url: '/profile/messages/openall',
            success: function(){
                window.location.reload();
            }
        });
    });

});

// message
$(function(){
    var showmax = 10, 
        messages = $('.message-list .message');
    if(messages.length > showmax){
        var oldmessages = messages.slice(0, -showmax);
        oldmessages.wrapAll(
            '<div class="collapsed-group">\
                <div class="collapsed-group-label"><a href="#"><i class="fa fa-chevron-circle-down"></i> show '+ oldmessages.length +' older messages</a></div>\
            </div>'
        );
    }
    $('.message-list').on('click', '.collapsed-group', function(e){
        $(this).addClass('active');
        e.preventDefault();
    });
    $(window).on('load', function(){
        $('.message-reply').each(function(){
            var lastelement = $(this),
                offset = lastelement.offset().top + lastelement.outerHeight(true) - $(window).height();
            $('html,body').animate({scrollTop:offset},5);
        });
    });
});