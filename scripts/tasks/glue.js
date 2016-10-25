let fs = require('fs');
let del = require('del');
let exec = require('child_process').exec;

let glueEmoticons = function (cb) {
    del('assets/emotes/emotes.css');
    exec(['glue', 'assets/emotes/emoticons', '--sprite-namespace= --namespace=chat-emote.chat-emote --css=assets/emotes --css-template=assets/emotes/emoticons.jinja --img=assets/emotes --url=./ --crop --pseudo-class-separator=_'].join(' '), function(err, stdout, stderr) {
        if (err) throw err;
        if (stderr) {
            console.log('Error: ' + stderr);
            cb();
            return false;
        }
    });
};

let glueIcons = function (cb) {
    del('assets/icons/icons.css');
    exec(['glue', 'assets/icons/icons', '--sprite-namespace= --namespace=icon --css=assets/icons --img=assets/icons --css-template=assets/icons/icons.jinja --url=./ --pseudo-class-separator=_'].join(' '), function(err, stdout, stderr) {
        if (err) throw err;
        if (stderr) {
            cb();
            return false;
        }
    });
};

glueEmoticons(function(){
    console.log('Completed emoticons');
});
glueIcons(function(){
    console.log('Completed icons');
});