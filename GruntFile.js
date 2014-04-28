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
						'static/vendor/jquery/jquery-1.10.2.min.js',
						'static/vendor/jquery.cookie/jquery.cookie.js',
						'static/vendor/jquery.mousewheel/jquery.mousewheel.min.js',
						'static/vendor/bootstrap-3.1.1/js/bootstrap.js',
						'static/vendor/moment/moment-2.4.0.min.js',
						'static/vendor/overthrow/overthrow.min.js',
						'static/vendor/nanoscrollerjs/jquery.nanoscroller.js'// We currently dont use the min file, because of custom mods
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
						'static/web/css/style.css',
						'static/web/css/flags.css'
					],

					// Errors CSS
					'static/errors/css/style.min.css' : [
						'static/errors/css/style.css'
					],

					// Chat CSS
					'static/chat/css/style.min.css' : [
						'static/chat/css/style.css',
						'static/chat/css/emoticons.css',
						'static/chat/css/icons.css'
					]
				}
			},
			
			emoticons : {
				options : {
					compress     : true,
					yuicompress  : true,
					optimization : 2
				},
				files : {
					'static/chat/css/emoticons.css' : [
						'scripts/emotes/css/base.css',
						'scripts/emotes/css/emoticons.css'
					]
				}
			},
			
			icons : {
				options : {
					compress     : true,
					yuicompress  : true,
					optimization : 2
				},
				files : {
					'static/chat/css/icons.css' : [
						'scripts/icons/css/base.css',
						'scripts/icons/css/icons.css'
					]
				}
			}
		},

		// "Glue" image sprites
		glue: {
			// Emoticons - you should run less:emoticons after running this
			emoticons: {
				src     : 'scripts/emotes/emoticons',
				options : '--sprite-namespace= --namespace=chat-emote.chat-emote --css=scripts/emotes/css --img=scripts/emotes --url=../img/ --each-template="%(class_name)s{background-position:%(x)s %(y)s;width:%(width)s;height:%(height)s;margin-top:-%(height)s;}" --optipng --crop'
			},
			icons: {
				src     : 'scripts/icons/icons',
				options : '--sprite-namespace= --namespace=icon --css=scripts/icons/css --img=scripts/icons --url=../img/ --optipng'
			}
		},
		
		// Copy the emoticon.png to static dir
		copy: {
			emoticons: {
				files: [
					{expand: true, flatten: true, src: 'scripts/emotes/css/emoticons.css', dest: 'static/chat/css/', filter: 'isFile'},
					{expand: true, flatten: true, src: 'scripts/emotes/emoticons.png', dest: 'static/chat/img/', filter: 'isFile'}
				]
			},
			icons: {
				files: [
					{expand: true, flatten: true, src: 'scripts/icons/css/icons.css', dest: 'static/chat/css/', filter: 'isFile'},
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
		},

		// Watch for file changes, automatically run various build commands
		watch : {
			styles : {
				files : [ 'static/**/*.css', 'static/**/*.js' ],
				tasks : [ 'build:uglify' ],
				options : {
					nospawn : true
				}
			}
		}

	});

	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-glue');
	grunt.loadNpmTasks('grunt-bump');

	// Glue and update emoticons
	grunt.registerTask('emoticons', [
		'glue:emoticons',
		'copy:emoticons',
		'less:emoticons'
	]);

	grunt.registerTask('icons', [
		'glue:icons',
		'copy:icons',
		'less:icons'
	]);
	
	// Build static resources
	grunt.registerTask('build:uglify', [
        'uglify:libs',
		'uglify:web',
		'uglify:chat',
		'less:build'
	]);
	
	// Build static resources
	grunt.registerTask('build', [
		'emoticons',
		'icons',
		'build:uglify'
	]);
	
	// Default
	grunt.registerTask('default', [
		'build'
	]);
};