module.exports = function(grunt) {
    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        // Clean tmp directory
        clean: {
            build: [
                'tmp/**/*',
                'scripts/emotes/emoticons.css',
                'scripts/icons/icons.css'
            ]
        },

        // Minify Javascript
        uglify: {

            options: {
                sourceMap        : true,
                mangle           : false,
                preserveComments : 'some', //will preserve all comments that start with a bang (!) or include a closure compiler style
                sourceMapRoot    : './'
            },
            
            // Common external libraries
            libs: {
                options: {
                    preserveComments : 'all'
                },
                files: {
                    'static/vendor/libs.min.js': [
                        'static/vendor/overthrow/overthrow.min.js',
                        'static/vendor/jquery/jquery-1.12.3.min.js',
                        'static/vendor/jquery.cookie/jquery.cookie.js',
                        'static/vendor/jquery.debounce/jquery.debounce.js',
                        'static/vendor/jquery.mousewheel/jquery.mousewheel.min.js',
                        'static/vendor/jquery.validate/jquery.validate.min.js',
                        'static/vendor/jquery.nanoscroller-0.8.7/jquery.nanoscroller.min.js',
                        'static/vendor/bootstrap-3.3.6/js/bootstrap.js',
                        'static/vendor/moment/moment-2.13.0.min.js',
                        'static/vendor/chart.js/Chart.min.v2.1.3.js'
                    ]
                }
            },
            
            // General web libs
            web: {
                files: {
                    'static/web/js/destiny.min.js': [
                        'static/web/js/utils.js',
                        'static/web/js/destiny.js',
                        'static/web/js/ui.js'
                    ]
                }
            },
            
            // Messages libs
            messages: {
                files: {
                    'static/web/js/messages.min.js': [
                        'static/web/js/messages.js'
                    ]
                }
            },
            
            // Chat libs
            chat: {
                files: {
                    'static/chat/js/chat.min.js': [
                        'static/chat/js/autocomplete.js',
                        'static/chat/js/formatters.js',
                        'static/chat/js/UrlFormatter.js',
                        'static/chat/js/hints.js',
                        'static/chat/js/menu.js',
                        'static/chat/js/gui.js',
                        'static/chat/js/chat.js'
                    ]
                }
            }
        },

        concat: {
            build: {
                files: {
                    'static/chat/css/style.min.css' : [
                        'static/vendor/jquery.nanoscroller-0.8.7/nanoscroller.css',
                        'static/chat/css/style.scss',
                        'scripts/emotes/emoticons.css',
                        'scripts/icons/icons.css'
                    ]
                }
            }
        },

        sass: {
            build : {
                options : {
                    outputStyle : 'compressed'
                },
                files: {
                    'static/web/css/style.min.css': 'static/web/css/style.scss',
                    'static/web/css/messages.min.css' : 'static/web/css/messages.scss',
                    'static/chat/css/style.min.css': 'static/chat/css/style.min.css'
                }
            }
        },

        glue: {
            emoticons: {
                src     : 'scripts/emotes/emoticons',
                options : '--sprite-namespace= --namespace=chat-emote.chat-emote --css=scripts/emotes --css-template=scripts/emotes/emoticons.jinja --img=scripts/emotes --url=../img/ --crop --pseudo-class-separator=_'
            },
            icons: {
                src     : 'scripts/icons/icons',
                options : '--sprite-namespace= --namespace=icon --css=scripts/icons --img=scripts/icons --css-template=scripts/icons/icons.jinja --url=../img/ --pseudo-class-separator=_'
            }
        },

        tldFetcher: {
            options: {
                templateFile: 'scripts/chat/UrlFormatter.tpl.js',
                targetFile: 'static/chat/js/UrlFormatter.js'
            },
            defaults: {}, // no-op target for convenience
            fetch: {} // does a fetch + replace on targetFile
        },

        // Copy the emoticon.png to static dir
        copy: {
            gluedimages: {
                files: [
                    {expand: true, flatten: true, src: 'scripts/emotes/emoticons.png', dest: 'static/chat/img/', filter: 'isFile'},
                    {expand: true, flatten: true, src: 'scripts/icons/icons.png', dest: 'static/chat/img/', filter: 'isFile'}
                ]
            }
        },

        // Bump the package version bump | bump:minor | bump:major
        bump: {
            options: {
                files: [
                    'package.json',
                    'composer.json'
                ],
                commit    : false,
                createTag : false,
                push      : false
            }
        }

    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-glue');
    grunt.loadNpmTasks('grunt-bump');
    grunt.loadTasks('tasks');

    // Build javascript
    grunt.registerTask('build:js', [
        'uglify:libs',
        'uglify:messages',
        'uglify:web',
        'uglify:chat'
    ]);

    // Build css
    grunt.registerTask('build:css', [
        'concat:build',
        'sass:build'
    ]);

    // Build images, fonts etc
    grunt.registerTask('build:assets', [
        'glue:emoticons',
        'glue:icons',
        'copy:gluedimages'
    ]);

    // Build javascript
    grunt.registerTask('build:static', [
        'build:js',
        'build:css'
    ]);

    // Build task to retrieve all external data
    grunt.registerTask('build:fetch', [
        'tldFetcher:fetch'
    ]);

    // Build all resources
    grunt.registerTask('build', [
        'clean',
        'build:fetch',
        'build:assets',
        'build:static'
    ]);
    
    // Default
    grunt.registerTask('default', [
        'build'
    ]);
};
