$('#flair-content').each(function(){

    $('.delete-item').on('click', () => {
        if (confirm('This cannot be undone. Are you sure?')) {
            $('#delete-form').submit();
        }
    });


});