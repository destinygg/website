'use strict';

var gulp = require('gulp'),
    fs = require('fs'),
    del = require('del'),
    pump = require('pump'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    sourcemaps = require('gulp-sourcemaps'),
    gutil = require('gulp-util'),
    sass = require('gulp-sass'),
    exec = require('child_process').exec;

require('require-dir')('./tasks');

var compileScriptsMessages = function (cb) {
    del('static/web/js/messages.min.js*');
    pump([
        gulp.src('scripts/web/js/messages.js'),
        sourcemaps.init(),
        uglify({preserveComments: 'license'}),
        rename({suffix: '.min'}),
        sourcemaps.write(),
        gulp.dest('static/web/js')
    ], cb);
};

var compileScriptsWeb = function (cb) {
    del('static/web/js/destiny.min.js*');
    pump([
        gulp.src([
            'scripts/web/js/destiny.js',
            'scripts/web/js/ui.js'
        ]),
        sourcemaps.init(),
        concat('destiny.min.js'),
        uglify({preserveComments: 'license'}),
        sourcemaps.write(),
        gulp.dest('static/web/js')
    ], cb);
};

var compileScriptsChat = function (cb) {
    del('static/chat/js/chat.min.js*');
    pump([
        gulp.src([
            'scripts/chat/js/utils.js',
            'scripts/chat/js/autocomplete.js',
            'scripts/chat/js/formatters.js',
            'scripts/chat/js/url.formatter.js',
            'scripts/chat/js/menu.js',
            'scripts/chat/js/gui.js',
            'scripts/chat/js/chat.js'
        ]),
        sourcemaps.init(),
        concat('chat.min.js'),
        uglify({preserveComments: 'license'}),
        sourcemaps.write(),
        gulp.dest('static/chat/js')
    ], cb);
};

var compileScriptsLibs = function (cb) {
    del('static/chat/js/libs.min.js*');
    pump([
        gulp.src([
            'static/vendor/polyfill/notification.js',
            'static/vendor/polyfill/localstorage.js',
            'static/vendor/overthrow/overthrow.min.js',
            'static/vendor/visibility-1.2.3/visibility.min.js',
            'static/vendor/jquery/jquery-1.12.3.min.js',
            'static/vendor/jquery.cookie/jquery.cookie.js',
            'static/vendor/jquery.debounce/jquery.debounce.js',
            'static/vendor/jquery.mousewheel/jquery.mousewheel.min.js',
            'static/vendor/jquery.validate/jquery.validate.min.js',
            'static/vendor/jquery.nanoscroller-0.8.7/jquery.nanoscroller.min.js',
            'static/vendor/bootstrap-3.3.6/js/bootstrap.js',
            'static/vendor/moment/moment-2.13.0.min.js',
            'static/vendor/chart.js/Chart.min.v2.1.3.js'
        ]),
        concat('libs.min.js'),
        uglify({preserveComments: 'license'}),
        gulp.dest('static/vendor')
    ], cb);
};

var glueEmoticons = function (cb) {
    del('scripts/emotes/emotes.css');
    exec(['glue', 'scripts/emotes/emoticons', '--sprite-namespace= --namespace=chat-emote.chat-emote --css=scripts/emotes --css-template=scripts/emotes/emoticons.jinja --img=scripts/emotes --url=../img/ --crop --pseudo-class-separator=_'].join(' '), function(err, stdout, stderr) {
        if (err) throw err;
        if (stderr) {
            gutil.log('Error: ' + stderr);
            cb();
            return false;
        }
        pump([
            gulp.src('scripts/emotes/emoticons.png'),
            gulp.dest('static/chat/img')
        ], cb);
    });
};

var glueIcons = function (cb) {
    del('scripts/icons/icons.css');
    exec(['glue', 'scripts/icons/icons', '--sprite-namespace= --namespace=icon --css=scripts/icons --img=scripts/icons --css-template=scripts/icons/icons.jinja --url=../img/ --pseudo-class-separator=_'].join(' '), function(err, stdout, stderr) {
        if (err) throw err;
        if (stderr) {
            cb();
            return false;
        }
        pump([
            gulp.src('scripts/icons/icons.png'),
            gulp.dest('static/chat/img')
        ], cb);
    });
};

var compileStylesChat = function (cb) {
    del('static/chat/css/style.min.css');
    pump([
        gulp.src([
            'static/vendor/jquery.nanoscroller-0.8.7/nanoscroller.css',
            'scripts/chat/css/style.scss',
            'scripts/emotes/emoticons.css',
            'scripts/icons/icons.css'
        ]),
        concat('style.min.css'),
        sass({
            outputStyle: 'compressed',
            includePaths : ['scripts']
        }),
        gulp.dest('static/chat/css')
    ], cb);
};

var compileStylesOnStreamChat = function (cb) {
    del('static/chat/css/onstream.min.css');
    pump([
        gulp.src('scripts/chat/css/onstream.scss'),
        concat('onstream.min.css'),
        sass({
            outputStyle: 'compressed',
            includePaths : ['scripts']
        }),
        gulp.dest('static/chat/css')
    ], cb);
};

var compileStyleWeb = function (cb) {
    del('static/web/css/style.min.css');
    pump([
        gulp.src(['scripts/web/css/style.scss']),
        concat('style.min.css'),
        sass({
            outputStyle: 'compressed',
            includePaths : ['scripts']
        }),
        gulp.dest('static/web/css')
    ], cb);
};

//gulp.task('scripts:ts:web', compileTypeScriptWeb);
//gulp.task('scripts:ts:chat', compileTypeScriptChat);

gulp.task('glue:emoticons', glueEmoticons);
gulp.task('glue:icons', glueIcons);
gulp.task('scripts:messages', compileScriptsMessages);
gulp.task('scripts:web', compileScriptsWeb);
gulp.task('scripts:chat:nofetch', compileScriptsChat);
gulp.task('scripts:chat:fetch', ['tld:fetch'], compileScriptsChat);
gulp.task('scripts:chat:onscreen', compileStylesOnStreamChat);
gulp.task('scripts:libs', compileScriptsLibs);
gulp.task('sass:chat:noglue', compileStylesChat);
gulp.task('sass:chat:glue', ['glue:emoticons', 'glue:icons'], compileStylesChat);
gulp.task('sass:web', compileStyleWeb);

// entire distribution build
gulp.task('default', [
    'tld:fetch',
    'glue:emoticons',
    'glue:icons',
    'sass:chat:glue',
    'sass:web',
    'scripts:libs',
    'scripts:messages',
    'scripts:chat:fetch',
    'scripts:chat:onscreen',
    'scripts:web'
]);

// convenience, used in watch etc.
gulp.task('static', [
    'sass:chat:noglue',
    'sass:web',
    'scripts:messages',
    'scripts:chat:nofetch',
    'scripts:chat:onscreen',
    'scripts:web'
]);