import $ from 'jquery'
import {debounce} from 'throttle-debounce'

(function () {

    const moment = require('moment');
    const GraphUtil = {};

    GraphUtil.prepareGraphData = function prepareGraphData(data, property, timeRange, timeUnit) {
        let graphData = {};
        switch (timeUnit.toUpperCase()) {
            case 'DAYS':
                graphData = GraphUtil.fillGraphDates(data, property, timeRange, timeUnit, 'YYYY-MM-DD', 'MM/D', '');
                break;
            case 'MONTHS':
                graphData = GraphUtil.fillGraphDates(data, property, timeRange, timeUnit, 'YYYY-MM', 'YY/MM', '-01');
                break;
            case 'YEARS':
                graphData = GraphUtil.fillGraphDates(data, property, timeRange, timeUnit, 'YYYY', 'YYYY', '-01-01');
                break;
        }
        return graphData;
    };

    GraphUtil.fillGraphDates = function fillGraphDates(data, property, timeRange, timeUnit, format1, format2, addon) {
        const dataSet = [],
            dataLabels = [],
            dates = [],
            a = moment({hour: 1}).subtract(timeRange, timeUnit),
            b = moment({hour: 1});
        for (let m = a; m.isBefore(b) || m.isSame(b); m.add(1, timeUnit)) {
            dates.push(m.format(format1) + addon);
            dataLabels.push(m.format(format2));
            dataSet.push(0);
        }
        for (let i = 0; i < data.length; ++i) {
            let x = dates.indexOf(data[i].date);
            if (x !== -1)
                dataSet[x] = data[i][property];
        }
        return {
            labels: dataLabels,
            data: dataSet
        };
    };

    GraphUtil.formatCurrency = function formatCurrency(label) {
        return '$' + label.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

    window.GraphUtil = GraphUtil;

    $('form.filter-form').each(function() {
        const form = $(this);
        form.find('select').on('change', () => {
            form.submit();
        });
        form.find('button[type="reset"]').on('click', function(){
            form.find('select').val('');
            form.find('input[type="text"]').val('');
            form.submit();
        });
        form.find('.pagination .page-link').on('click', function() {
            form.find('input[name="page"]').val($(this).data('page'));
            form.submit();
            return false;
        });

        form.find('table[data-sort]').each(function(){
            const tbl = $(this), sort = tbl.data('sort'),
                column = tbl.find(`thead td[data-sort="${sort}"]`),
                caret = $(`<i class="fas fa-caret-up mr-1"></i>`);
            let order = tbl.data('order');
            const applyCaret = order => {
                return order === 'ASC' ? caret.removeClass('fa-caret-down').addClass('fa-caret-up') : caret.removeClass('fa-caret-up').addClass('fa-caret-down')
            };
            tbl.on('click', 'td[data-sort]', function(){
                const $this = $(this);
                if ($this.hasClass('active')) {
                    order = order === 'ASC' ? 'DESC':'ASC';
                }
                form.find('input[name="sort"]').val($this.data('sort'));
                form.find('input[name="order"]').val(order);
                form.submit();
                return false;
            });
            column.prepend(applyCaret(order)).addClass('active');
        });
    });

    $('#users-table').each(function(){
        const $banUserBtn = $('#ban-users-btn'),
            $banUsersModal = $('#ban-users-modal'),
            $deleteUserBtn = $('#delete-users-btn'),
            $deleteUsersModal = $('#delete-users-modal'),
            $tbl = $(this);
        let selected = [];

        const getActiveSelectors = function() {
            return $tbl.find('tbody td.selector.active').toArray()
        };

        const toggleToolsBasedOnSelection = function() {
            selected = getActiveSelectors();
            const someSelected = selected.length > 0
            $banUserBtn.toggleClass('disabled', !someSelected);
            $deleteUserBtn.toggleClass('disabled', !someSelected);
        };

        const activateSelector = function($el){
            $el.addClass('active')
                .find('i')
                .attr('class', 'far fa-dot-circle');
            toggleToolsBasedOnSelection()
        };

        const deactivateSelector = function($el) {
            $el.removeClass('active')
                .find('i')
                .attr('class', 'far fa-circle');
            toggleToolsBasedOnSelection()
        };

        const toggleRowSelector = function() {
            const $td = $(this)
            if ($td.hasClass('active')) {
                deactivateSelector($td)
            } else {
                activateSelector($td)
            }
            return false
        };

        const toggleAllRowSelector = function(e) {
            $tbl.find('.selector').each(function(){
                const $el = $(this);
                if ($el.hasClass('active')) {
                    deactivateSelector($el)
                } else {
                    activateSelector($el)
                }
            });
            return false
        };

        $tbl.on('click', 'tbody td.selector', toggleRowSelector)
            .on('click', 'thead td.selector', toggleAllRowSelector);
        toggleToolsBasedOnSelection()

        $banUsersModal.find('form').on('submit', function(){
            $banUsersModal.find('button').addClass('disabled');
            $(this).append(selected.map(e => $(e).data('id')).map(e => `<input type="hidden" name="selected[]" value="${e}" />`));
        });
        $banUsersModal.on('show.bs.modal', function(){
            $banUsersModal.find('.modal-message')
                .html(`Do you want to Ban <strong>${selected.length}</strong> users? This cannot be undone.`);
            $banUsersModal.find('[name="reason"]').focus()
        });
        $banUsersModal.on('shown.bs.modal', () => $banUsersModal.find('[name="reason"]').focus());
        $banUserBtn.on('click', function(){
            $(this).parent().toggleClass('show');
            $banUsersModal.modal('show');
            return false;
        });

        $deleteUsersModal.find('form').on('submit', function(){
            $deleteUsersModal.find('button').addClass('disabled');
            $(this).append(selected.map(e => $(e).data('id')).map(e => `<input type="hidden" name="selected[]" value="${e}" />`));
        });
        $deleteUsersModal.on('show.bs.modal', function(){
            $deleteUsersModal.find('.modal-message')
                .html(`Do you want to Delete <strong>${selected.length}</strong> users? This cannot be undone.`);
        });
        $deleteUserBtn.on('click', function(){
            $(this).parent().toggleClass('show');
            $deleteUsersModal.modal('show');
            return false;
        });
    });

    $('.color-select').on('change keyup', 'input[type="text"]', function(){
        $(this).prev().css({
            'background-color': this.value,
            'border-color': this.value,
        });
    });

    $('#emote-search').each(function(){
        const emoteSearch = $(this),
            emoteGrid = $('#emote-grid'),
            emotes = emoteGrid.find('.image-grid-item');
        const debounced = debounce(50, false, () => {
            const search = emoteSearch.val();
            if (search != null && search.trim() !== '') {
                emotes.each((i, v) => {
                    $(v).toggleClass('hidden', !(v.getAttribute('data-prefix').toLowerCase().indexOf(search.toLowerCase()) > -1))
                });
            } else {
                emotes.removeClass('hidden');
            }
        });
        emoteSearch.on('keydown', e => debounced(e));

        $('.preview-icon').on('click', function(){
            const emoteId = $(this).data('id');
            const modal = $('#emotePreviewModal').modal({show: false});
            $.ajax({
                method: 'get',
                url: '/admin/emotes/'+ emoteId +'/preview',
                success: function(data) {
                    const frame = $(data);
                    modal.find('.modal-body').empty().append(frame);
                    modal.modal({show: true});
                }
            });
        });

    });

    $('#flair-search').each(function(){
        const emoteSearch = $(this),
            emoteGrid = $('#flair-grid'),
            emotes = emoteGrid.find('.image-grid-item');
        const debounced = debounce(50, false, () => {
            const search = emoteSearch.val();
            if (search != null && search.trim() !== '') {
                emotes.each((i, v) => {
                    $(v).toggleClass('hidden', !(v.getAttribute('data-name').toLowerCase().indexOf(search.toLowerCase()) > -1))
                });
            } else {
                emotes.removeClass('hidden');
            }
        });
        emoteSearch.on('keydown', e => debounced(e));
    });

    $('#emoteEditPreviewBtn').on('click', function(){
        const form = $(this).closest('form'),
            modal = $('#emotePreviewModal').modal({show: false}),
            prefix = form.find('#inputPrefix').val(),
            styles = form.find('#inputStyles').val(),
            imageId = form.find('#inputImage').val();
        $.ajax({
            method: 'post',
            url: '/admin/emotes/preview',
            data: {prefix, styles, imageId},
            success: function(data) {
                const frame = $(data);
                modal.find('.modal-body').empty().append(frame);
                modal.modal({show: true});
            }
        });
    });

    $('#delete-ban').click(function() {
        return confirm('Are you sure?');
    });

    $(document)
        .on('dragover', () => { $('body').addClass('drag-over') })
        .on('dragleave drop', () => { $('body').removeClass('drag-over') });


})();

(function(){
    const fileInput = $('#file-input');
    const imageViews = $('.image-view.image-view-upload');

    function uploadImage(imageView, data, success, failure) {
        const uploadaction = imageView.data('upload');
        const uploadurl = imageView.data('cdn');
        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadaction, true);
        xhr.addEventListener('error', err => failure(err));
        xhr.addEventListener('abort', () => failure('aborted'));
        xhr.addEventListener('load', () => {
            try {
                const res = JSON.parse(xhr.responseText)[0];
                if (res['error']) {
                    return failure(res['error']);
                }
                const img = imageView.find('img')[0] || $('<img />')[0];
                img.setAttribute('width', res['width']);
                img.setAttribute('height', res['height']);
                img.setAttribute('src', uploadurl + res['name']);
                imageView.find('input[name="imageId"]').val(res['id']);
                imageView.prepend(img);
                success(res);
            } catch (e) {
                failure(e);
            }
        });
        xhr.send(data);
    }

    function beginUpload(imageView, file) {
        const data = new FormData();
        data.append('files[]', file);
        imageView.removeClass('success error').addClass('busy');
        uploadImage(imageView, data,
            () => { imageView.addClass('success').removeClass('busy') },
            () => { imageView.addClass('error').removeClass('busy') }
        );
        fileInput.value = '';
    }

    function dropHandler(ev) {
        const data = ev.originalEvent.dataTransfer,
            target = $(ev.currentTarget);
        let file = null;
        if (data.items) {
            if (data.items.length > 0 && data.items[0].kind === 'file') {
                file = data.items[0].getAsFile();
            }
        } else if (data.files.length > 0) {
            file = data.files[0];
        }
        if (file !== null) {
            beginUpload(target, file);
        }
    }

    let imageTarget = null;
    imageViews
        .on('click', e => {
            e.preventDefault();
            imageTarget = $(e.currentTarget);
            fileInput.trigger('click');
        })
        .on('dragover', e => {
            e.preventDefault();
        })
        .on('drop', e => {
            e.preventDefault();
            dropHandler(e);
        });

    fileInput.on('change', e => {
        e.preventDefault();
        beginUpload(imageTarget, fileInput[0].files[0]);
        imageTarget = null;
    });
})();

$(function(){

    $('#emote-content').each(function(){

        const emoteid = $(this).data('id');

        $('.delete-item').on('click', () => {
            if (confirm('This cannot be undone. Are you sure?')) {
                $('#delete-form').submit();
            }
        });

        let mustCheckPrefix = true;
        const inputPrefix = $('#inputPrefix'),
            inputTheme = $('#inputTheme'),
            emoteForm = $('#emote-form');

        function validateEmote() {
            inputPrefix.removeClass('is-invalid');
            $.ajax({
                url: '/admin/emotes/prefix',
                data: {id: emoteid, theme: inputTheme.val(), prefix: inputPrefix.val()},
                method: 'post',
                success: res => {
                    if (res['exists']) {
                        mustCheckPrefix = true;
                        inputPrefix.addClass('is-invalid');
                    } else {
                        mustCheckPrefix = false;
                        emoteForm.submit();
                    }
                },
                error: () => {
                    mustCheckPrefix = true;
                    inputPrefix.addClass('is-invalid');
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

    $('#flair-content').each(function(){
        $('.delete-item').on('click', () => {
            if (confirm('This cannot be undone. Are you sure?')) {
                $('#delete-form').submit();
            }
        });
    });

    $('#theme-content').each(function(){
        $('.delete-item').on('click', () => {
            if (confirm('This cannot be undone. Are you sure?')) {
                $('#delete-form').submit();
            }
        });
    });

    $('#themeSelect').on('change', function(){
        window.location.href = '/admin/emotes?theme=' + $(this).val();
    });

});

$(function() {
    const $userDetailsForm = $('form#user-details');
    const $usernameWarningModal = $('#username-warning-modal');
    const $usernameInput = $('#inputUsername');

    $userDetailsForm.submit(function(event) {
        event.preventDefault();

        $.get({
            url: '/profile/username/similaremotes',
            data: { 'username': $usernameInput.val() },
            success: function(response) {
                if ('error' in response) {
                    $(document).alertDanger(response['error']['message'], {delay: 3000});
                    return;
                }

                const emotes = response['data']['emotes'];
                if (emotes.length) {
                    // Display modal that warns the user if there are similar emotes.
                    $usernameWarningModal.find('.modal-body p .username').text($usernameInput.val());
                    $usernameWarningModal.find('.modal-body p .emotes').text(emotes.join(', '));
                    $usernameWarningModal.modal('show');
                } else {
                    // Remove this submit handler and submit the form normally.
                    $userDetailsForm.off('submit').submit();
                }
            }
        });
    });

    $usernameWarningModal.find('button#update-anyway').click(function() {
        $('input#skipEmoteCheck').val('true');
        $userDetailsForm.off('submit').submit();
    });
});