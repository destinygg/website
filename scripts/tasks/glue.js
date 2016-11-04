const fs = require('fs');
const del = require('del');
const exec = require('child_process').exec;

const glueEmoticons = function (cb) {
    del('assets/emotes/emotes.css');
    exec(['glue', 'assets/emotes/emoticons', '--sprite-namespace= --namespace=chat-emote.chat-emote --css=assets/emotes --css-template=assets/emotes/emoticons.jinja --img=assets/emotes --url=../../emotes/ --pseudo-class-separator=_'].join(' '), function(err, stdout, stderr) {
        if (err || stderr) throw err;
        fs.renameSync('assets/emotes/emoticons.css', 'assets/emotes/emoticons.scss');
        cb();
    });
};

const glueIcons = function (cb) {
    del('assets/icons/icons.css');
    exec(['glue', 'assets/icons/icons', '--sprite-namespace= --namespace=icon --css=assets/icons --css-template=assets/icons/icons.jinja --img=assets/icons --url=../../icons/ --pseudo-class-separator=_'].join(' '), function(err, stdout, stderr) {
        if (err || stderr) throw err;
        fs.renameSync('assets/icons/icons.css', 'assets/icons/icons.scss');
        cb();
    });
};

glueEmoticons(function(){
    console.log('Completed emoticons');
});
glueIcons(function(){
    console.log('Completed icons');
});