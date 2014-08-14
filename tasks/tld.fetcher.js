var http = require('http'),
    fs = require('fs'),
    TLD_FETCH_URL = 'http://data.iana.org/TLD/tlds-alpha-by-domain.txt',
    TLD_MIN = 271; // Only update if atleast this many found (original count)

module.exports = function(grunt) {
    grunt.registerMultiTask('tldFetcher', 'Fetches TLDs from official list, and string replaces them into given file', function() {
        var options = this.options({
            targetFile: false
        });

        if (typeof options.targetFile !== "string") {
            grunt.fail.warn('No/invalid TLD fetch target file.');
            return false;
        }

        if (this.target !== "fetch") {
            grunt.log.writeln('Non fetch target, TLDs will be untouched.');
            return true;
        }

        // Asynchronously fetch TLDs, if valid then replace
        var done = this.async();
        grunt.log.writeln('Fetching TLDs...' + this.data);
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
                    replaceTLDs(options.targetFile, tlds, function() {
                        done();
                    });
                } else {
                    done();
                }
            });
        }).on('error', function(e) {
            grunt.log.writeln("TLD fetch failed, using defaults: " + e.message);
            done();
        });
    });

    function parseTLDs(data) {
        var list = [];

        // Make sure data is string
        if (typeof data !== "string") {
            return null;
        }

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
        if (list.length <= TLD_MIN) {
            return null;
        }

        grunt.log.writeln('Fetched ' + list.length + ' tlds!');
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

    function replaceTLDs(file, tlds, callback) {
        grunt.log.writeln('Writing TLDs to ' + file + '.');

        // Read target file's contents
        fs.readFile(file, 'utf8', function(err, data) {
            if (err) {
                grunt.log.writeln('IO error reading ' + file + ' skipping write.');
                callback();
            }

            // Update TLD regex with updated TLD list
            var result = data.replace(/(\s*tlds\s*=\s*"\(\?:)[^\)]*([^"]*"\s*,)/g, '$1' + tlds + '$2');
            var diff = data.length - result.length;

            // Write replaced contents back to the targetFile
            fs.writeFile(file, result, 'utf8', function(err) {
                if (err) {
                    grunt.log.writeln('IO error writing to ' + file + ' skipping write.');
                } else {
                    grunt.log.writeln('Wrote new tlds to file ' + file + '!');
                }
                callback();
            });
        });
    }
};
