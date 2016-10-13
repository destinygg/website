var http = require('http'),
    fs = require('fs'),
    gulp = require('gulp'),
    gutil = require('gulp-util'),
    TLD_FETCH_URL = 'http://data.iana.org/TLD/tlds-alpha-by-domain.txt',
    TLD_MIN = 271; // Only update if atleast this many found (original count)

gulp.task('tld:fetch', function (cb) {
    //gutil.log("TLD Fetch " + TLD_FETCH_URL);

    var templateFile = 'scripts/chat/UrlFormatter.tpl.js',
        targetFile = 'static/chat/js/UrlFormatter.js';

    // Asynchronously fetch TLDs, if valid then replace
    http.get(TLD_FETCH_URL, function(res) {
        var data = '';
        res.on('data', function(chunk) {
            // Join response till its complete
            data += chunk;
        });
        res.on('end', function() {
            // When response completes parse and replace
            var tlds = parseTLDs(data);
            if (typeof tlds === "string") {
                replaceTLDs(templateFile, targetFile, tlds, function() {
                    cb();
                });
            } else {
                cb();
            }
        });
    }).on('error', function(e) {
        gutil.log("TLD fetch failed, using defaults: " + e.message);
        cb();
    });
});

function parseTLDs(data) {
    // List of pseudo TLD-s we want to support
    var list = ['bit', 'exit', 'gnu', 'i2p', 'local', 'onion', 'zkey'];

    // Make sure data is string
    if (typeof data !== "string")
        return null;

    // Parse response data by line
    var arr = data.split("\n");
    for (var idx in arr) {
        var tld = arr[idx];
        if ((typeof tld !== "string") ||
            (tld.length < 1) ||
            (tld[0] === '#') ||
            (tld.indexOf('XN--') >= 0)) {
            // Ignore invalid/weird TLDs or comment
            continue;
        }
        list.push(tld);
    }

    // Only return fetched list if its larger than default
    if (list.length <= TLD_MIN)
        return null;

    //gutil.log('Fetched ' + list.length + ' tlds!');
    list.sort(function(x,y){
        //Sort by size then alphabetically
        if (x.length == y.length ){
            return (x<y?-1:(x>y?1:0));
        } else {
            return y.length - x.length;
        }
    });
    return list.join("|");
}

function replaceTLDs(tplfile, targetfile, tlds, callback) {
    //gutil.log('Writing TLDs from template ' + tplfile + '.');
    fs.readFile(tplfile, {encoding: 'utf8'}, function(err, data) {
        if (err) {
            //gutil.log('IO error reading from ' + tplfile + ' skipping write.');
            return;
        }
        fs.writeFile(targetfile, data.replace('{{gtld}}', tlds), 'utf8', function(err) {
            if (err) {
                gutil.log('IO error writing to ' + targetfile + ' skipping write.');
            } else {
                //gutil.log('Wrote new TLDs to file ' + targetfile + '!');
            }
            callback();
        });
    });
}