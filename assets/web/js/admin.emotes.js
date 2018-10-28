$('#emote-content').each(function(){

    const emoteid = $(this).data('id');

    $('.delete-item').on('click', () => {
        if (confirm('This cannot be undone. Are you sure?')) {
            $('#delete-form').submit();
        }
    });

    let mustCheckPrefix = true;
    const inputPrefix = $('#inputPrefix'),
        emoteForm = $('#emote-form');

    function validateEmote() {
        const prefixGroup = inputPrefix.closest('.form-group');
        prefixGroup.removeClass('has-error');
        $.ajax({
            url: '/admin/emotes/prefix',
            data: {id: emoteid, prefix: inputPrefix.val()},
            method: 'post',
            success: res => {
                if (res['error']) {
                    mustCheckPrefix = true;
                    prefixGroup.addClass('has-error');
                } else {
                    mustCheckPrefix = false;
                    emoteForm.submit();
                }
            },
            error: () => {
                mustCheckPrefix = true;
                prefixGroup.addClass('has-error');
            }
        });
    }

    inputPrefix.on('change', () => { mustCheckPrefix = true; })

    emoteForm.on('submit', e => {
        if (mustCheckPrefix) {
            e.preventDefault();
            validateEmote();
        }
    });

});