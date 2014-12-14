module.exports = function(grunt) {
    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        // Clean tmp directory
        clean: {
            build: ['tmp/**/*']
        },

        // Minify Javascript
        uglify: {
            
            // Common external libraries
            libs: {
                options: {
                    mangle           : false,
                    sourceMap        : 'static/vendor/libs.min.map',
                    sourceMappingURL : 'libs.min.map',
                    sourceMapPrefix  : 2,
                    preserveComments : 'all'
                },
                files: {
                    'static/vendor/libs.min.js': [
                        'static/vendor/overthrow/overthrow.min.js',
                        'static/vendor/jquery/jquery-1.10.2.min.js',
                        'static/vendor/jquery.cookie/jquery.cookie.js',
                        'static/vendor/jquery.mousewheel/jquery.mousewheel.min.js',
                        'static/vendor/jquery.validate/jquery.validate.min.js',
                        'static/vendor/jquery.nanoscroller-0.8.4/jquery.nanoscroller.min.js',
                        'static/vendor/bootstrap-3.1.1/js/bootstrap.js',
                        'static/vendor/moment/moment-2.4.0.min.js'
                    ]
                }
            },
            
            // General web libs
            web: {
                options: {
                    mangle           : false,
                    preserveComments : 'some', //will preserve all comments that start with a bang (!) or include a closure compiler style
                    sourceMap        : 'static/web/js/destiny.min.map',
                    sourceMappingURL : 'destiny.min.map',
                    sourceMapPrefix  : 3 
                },
                files: {
                    'static/web/js/destiny.min.js': [
                        'static/web/js/utils.js',
                        'static/web/js/destiny.js',
                        'static/web/js/feed.js',
                        'static/web/js/ui.js'
                    ]
                }
            },
            
            // Messages libs
            messages: {
                options: {
                    mangle           : false,
                    preserveComments : 'some',
                    sourceMap        : 'static/web/js/messages.min.map',
                    sourceMappingURL : 'messages.min.map',
                    sourceMapPrefix  : 3
                },
                files: {
                    'static/web/js/messages.min.js': [
                        'static/web/js/messages.js'
                    ]
                }
            },
            
            // Chat libs
            chat: {
                options: {
                    mangle           : false,
                    preserveComments : 'some',
                    sourceMap        : 'static/chat/js/chat.min.map',
                    sourceMappingURL : 'chat.min.map',
                    sourceMapPrefix  : 3
                },
                files: {
                    'static/chat/js/chat.min.js': [
                        'scripts/chat/tld.js',
                        'static/chat/js/autocomplete.js',
                        'static/chat/js/formatters.js',
                        'static/chat/js/hints.js',
                        'static/chat/js/menu.js',
                        'static/chat/js/gui.js',
                        'static/chat/js/chat.js'
                    ]
                }
            }

            
        },

        // Compress and compile CSS (LESS not required)
        less : {
            build : {
                options : {
                    compress     : true,
                    yuicompress  : true,
                    optimization : 2
                },
                files : {

                    // Web CSS
                    'static/web/css/style.min.css' : [
                        'static/web/css/style.css'
                    ],

                    // Messages CSS
                    'static/web/css/messages.min.css' : [
                        'static/web/css/messages.css'
                    ],

                    // Errors CSS
                    'static/errors/css/style.min.css' : [
                        'static/vendor/bootstrap-3.1.1/css/bootstrap.css',
                        'static/errors/css/style.css'
                    ],

                    // Chat CSS
                    'static/chat/css/style.min.css' : [
                        'static/vendor/jquery.nanoscroller-0.8.4/nanoscroller.css',
                        'static/chat/css/style.css',
                        'scripts/emotes/emoticons.css',
                        'scripts/icons/icons.css'
                    ]
                }
            }
        },

        // "Glue" image sprites
        glue: {
            // Emoticons - you should run less:emoticons after running this
            emoticons: {
                src     : 'scripts/emotes/emoticons',
                options : '--sprite-namespace= --namespace=chat-emote.chat-emote --css=scripts/emotes --css-template=scripts/emotes/emoticons.jinja --img=scripts/emotes --url=../img/ --crop --pseudo-class-separator=_'
            },
            icons: {
                src     : 'scripts/icons/icons',
                options : '--sprite-namespace= --namespace=icon --css=scripts/icons --img=scripts/icons --css-template=scripts/icons/icons.jinja --url=../img/ --pseudo-class-separator=_'
            },
        },
        tldFetcher: {
            options: {
                targetFile: 'scripts/chat/tld.js'
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
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');
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
        'less:build'
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