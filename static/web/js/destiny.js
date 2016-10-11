destiny = {
    cdn: (function() {
            var scripts = document.getElementsByTagName('script'),
                src = scripts[scripts.length - 1].src;
            return src.substring(0, src.indexOf('/web/js/destiny.min.js'));
        })(),
    token:      '',
    baseUrl:    '/',
    timeout:    15000,
    fn:         {}
};