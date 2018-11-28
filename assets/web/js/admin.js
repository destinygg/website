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


    $('#userlist').each(function () {

        let usrlist = $(this),
            userSearchForm = $('#userSearchForm'),
            page = usrlist.data('page'),
            size = usrlist.data('size'),
            feature = usrlist.data('feature'),
            reset = usrlist.find('#resetuserlist'),
            searchEl = userSearchForm.find('input[name="search"]'),
            search = searchEl.val();

        usrlist.find('.pagination').on('click', 'a', function () {
            page = $(this).data('page');
            usrlist.trigger('gridupdate');
            return false;
        });

        userSearchForm.find('select[name="feature"]').on('change', function () {
            page = 1;
            feature = $(this).val();
            search = '';
            usrlist.trigger('gridupdate');
            return false;
        }).val(feature);

        usrlist.find('select[name="size"]').on('change', function () {
            page = 1;
            size = $(this).val();
            usrlist.trigger('gridupdate');
            return false;
        }).val(size);

        reset.on('click', function () {
            size = '';
            page = 1;
            feature = '';
            search = '';
            usrlist.trigger('gridupdate');
            return false;
        });

        usrlist.on('gridupdate', function () {
            window.location.href = '/admin/users/?' +
                'size=' + encodeURIComponent(size) +
                '&page=' + encodeURIComponent(page) +
                '&feature=' + encodeURIComponent(feature) +
                '&search=' + encodeURIComponent(search);
        });

        userSearchForm.on('submit', function () {
            size = '';
            page = 1;
            search = searchEl.val();
            usrlist.trigger('gridupdate');
            return false;
        });

    });

    $('#flairs-selection').each(function(){
        const userId = parseInt(this.getAttribute('data-user'));
        $(this).on('click', 'input[type="checkbox"]', (e) => {
            e.target.setAttribute('disabled', 'disabled');
            $.ajax({
                type: 'post',
                url: `/admin/user/${userId}/toggle/flair`,
                data: {
                    'userId': userId,
                    'name': e.target.getAttribute('value'),
                    'value': e.target.checked ? 1:0
                }
            })
            .always(() => {
                e.target.removeAttribute('disabled');
            })
            /*.fail(() => { todo error handle })*/;
        });
    });

    $('#roles-selection').each(function(){
        const userId = parseInt(this.getAttribute('data-user'));
        $(this).on('click', 'input[type="checkbox"]', (e) => {
            e.target.setAttribute('disabled', 'disabled');
            $.ajax({
                type: 'post',
                url: `/admin/user/${userId}/toggle/role`,
                data: {
                    'userId': userId,
                    'name': e.target.getAttribute('value'),
                    'value': e.target.checked ? 1:0
                }
            })
            .always(() => {
                e.target.removeAttribute('disabled');
            })
            /*.fail(() => { todo error handle })*/;
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
        })
        emoteSearch.on('keydown', e => debounced(e))
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
        })
        emoteSearch.on('keydown', e => debounced(e))
    });

    $(document)
        .on('dragover', () => { $('body').addClass('drag-over') })
        .on('dragleave drop', () => { $('body').removeClass('drag-over') });


})();

$(function(){
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
});

$(function(){
    $('#authBtn').on('click', function(){
        window.location.href = '/streamlabs/authorize';
    });
    $('#testBtn').on('click', function(){
        window.location.href = '/streamlabs/alert/test';
    });
})

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

    $('#flair-content').each(function(){

        $('.delete-item').on('click', () => {
            if (confirm('This cannot be undone. Are you sure?')) {
                $('#delete-form').submit();
            }
        });

    });
});