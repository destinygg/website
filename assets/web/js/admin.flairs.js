$('#flair-content').each(function(){

    const flairid = $(this).data('id');
    const uploadaction = $(this).data('upload');
    const uploadurl = $(this).data('cdn') + /flairs/;

    $('.delete-emote').on('click', () => {
        if (confirm('This cannot be undone. Are you sure?')) {
            $('#delete-form').submit();
        }
    });

    const imageId = $('input[name="imageId"]');
    const fileinput = $('#file-input');
    const uploadinput = $('.image-view-add');
    const imageview = $('.image-view-primary');

    function uploadImage(data, success, failure) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadaction, true);
        xhr.addEventListener('error', err => failure(err));
        xhr.addEventListener('abort', () => failure('aborted'));
        xhr.addEventListener('load', () => {
            const res = JSON.parse(xhr.responseText)[0];
            if (!res['error']) {
                imageview.html('<img width="'+ res['width'] +'" height="'+ res['height'] +'" src="'+ uploadurl + res['name'] +'" />');
                success(res);
            } else {
                failure(res['error']);
            }
        });
        xhr.send(data);
    }

    function beginUpload() {
        const data = new FormData();
        data.append('files[]', fileinput[0].files[0]);
        uploadinput.removeClass('success').removeClass('error').addClass('busy');
        uploadImage(data,
            res => {
                uploadinput.addClass('success').removeClass('busy');
                imageId.val(res['id']);
            },
            err => {
                uploadinput.addClass('error').removeClass('busy');
                console.error(err);
            }
        );
        fileinput.value = '';
    }

    fileinput.on('change', e => {
        e.preventDefault();
        beginUpload();
    });
    uploadinput.on('click', e => {
        e.preventDefault();
        fileinput.trigger('click')
    });

});