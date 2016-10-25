let http = require('http');
let fs = require('fs');

let TLD_FETCH_URL = 'http://data.iana.org/TLD/tlds-alpha-by-domain.txt';
let TLD_MIN = 271; // Only update if at least this many found (original count)

var targetFile = 'assets/tld.json';

let cb = function(){
  console.log('Done!');
};

// Asynchronously fetch TLDs, if valid then replace
http.get(TLD_FETCH_URL, function(res) {
    var data = '';
    res.on('data', function(chunk) {
        // Join response till its complete
        data += chunk;
    });
    res.on('end', function() {
        // When response completes parse and replace
        var list = parseTLDs(data);
        console.log(`Retrieved ${list.length} tlds!`);
        fs.writeFile(targetFile, JSON.stringify(list), 'utf8', function(err) {
            if (err) {
                console.error(`IO error writing to ${targetFile} skipping write.`);
            } else {
                console.log(`Wrote new TLDs to file ${targetFile}!`);
            }
            cb();
        });
    });
}).on('error', function(e) {
    console.log("TLD fetch failed, using defaults: " + e.message);
    cb();
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

    list.sort(function(x,y){
        //Sort by size then alphabetically
        if (x.length == y.length ){
            return (x<y?-1:(x>y?1:0));
        } else {
            return y.length - x.length;
        }
    });
    return list;
}