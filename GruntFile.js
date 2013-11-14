module.exports = function(grunt) {
	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		// Clean tmp directory
		clean: {
			build: ['tmp/**/*']
		},

		// Minify Javascript
		uglify: {
			options: {
				mangle: false
			},
			build: {
				files: {

					// Web JS
					'static/web/js/destiny.min.js': [
						'static/web/js/utils.js',
						'static/web/js/destiny.js',
						'static/web/js/feed.js',
						'static/web/js/twitch.js',
						'static/web/js/ui.js'
					],

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

					// Errors CSS
					'static/errors/css/style.min.css' : [
						'static/errors/css/style.css'
					],

					// Web CSS
					'static/web/css/style.min.css' : [
						'static/web/css/style.css',
						'static/web/css/flags.css'
					],

					'static/web/css/bigscreen.min.css' : [
						'static/web/css/bigscreen.css'
					],

					// Chat CSS
					'static/chat/css/style.min.css' : [
						'static/chat/css/style.css',
						'static/chat/css/emoticons.css',
						'static/chat/css/flair.css'
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
			}
		},

		// "Glue" image sprites
		glue: {
			// Emoticons - you should run less:emoticons after running this
			emoticons: {
				src: 'scripts/emotes/emoticons',
				options: '--sprite-namespace= --namespace=chat-emote.chat-emote --css=scripts/emotes/css --img=scripts/emotes --url=../img/ --each-template="%(class_name)s{background-position:%(x)s %(y)s;width:%(width)s;height:%(height)s;margin-top:-%(height)s;}" --optipng --crop'
			}
		},
		
		// Copy the emoticon.png to static dir
		copy: {
			emoticons: {
				files: [
					{expand: true, flatten: true, src: 'scripts/emotes/css/emoticons.css', dest: 'static/chat/css/', filter: 'isFile'},
					{expand: true, flatten: true, src: 'scripts/emotes/emoticons.png', dest: 'static/chat/img/', filter: 'isFile'}
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
				tasks : [ 'build' ],
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
	
	// Build static resources
	grunt.registerTask('build', [
		'emoticons',
		'uglify:build',
		'less:build'
	]);
	
	// Default
	grunt.registerTask('default', [
		'build'
	]);
};