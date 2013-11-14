module.exports = function(grunt) {
	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		// Clean tmp directory
		clean: {
			build: ['tmp/**/*']
		},

		// Minify Javascript
		uglify: {
			web: {
				options: {
					mangle: false,
					sourceMap: 'static/web/js/destiny.min.map',
					sourceMappingURL: 'destiny.min.map'
				},
				files: {
					// Web JS
					'static/web/js/destiny.min.js': [
						'static/web/js/utils.js',
						'static/web/js/destiny.js',
						'static/web/js/feed.js',
						'static/web/js/twitch.js',
						'static/web/js/ui.js'
					]
				}
			},
			chat: {
				options: {
					mangle: false,
					sourceMap: 'static/chat/js/engine.min.map',
					sourceMappingURL: 'engine.min.map'
				},
				files: {
					// Chat JS
					'static/chat/js/engine.min.js': [
						'static/chat/js/autocomplete.js',
						'static/chat/js/scroll.mCustom.js',
						'static/chat/js/chat.menu.js',
						'static/chat/js/formatters.js',
						'static/chat/js/hints.js',
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
					compress : true,
					yuicompress : true,
					optimization : 2
				},
				files : {

					// Web CSS
					'static/web/css/style.min.css' : [
						'static/web/css/style.css',
						'static/web/css/flags.css'
					],

					// Big screen
					'static/web/css/bigscreen.min.css' : [
						'static/web/css/bigscreen.css'
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
					compress : true,
					yuicompress : true,
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
					compress : true,
					yuicompress : true,
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
				src: 'scripts/emotes/emoticons',
				options: '--sprite-namespace= --namespace=chat-emote.chat-emote --css=scripts/emotes/css --img=scripts/emotes --url=../img/ --each-template="%(class_name)s{background-position:%(x)s %(y)s;width:%(width)s;height:%(height)s;margin-top:-%(height)s;}" --optipng --crop'
			},
			icons: {
				src: 'scripts/icons/icons',
				options: '--sprite-namespace= --namespace=icon --css=scripts/icons/css --img=scripts/icons --url=../img/ --optipng'
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
				commit: false,
				createTag: false,
				push: false
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