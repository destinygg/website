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
						'static/web/css/style.css'
					],

					// Errors CSS
					'static/errors/css/style.min.css' : [
						'static/errors/css/style.css'
					],

					// Chat CSS
					'static/chat/css/style.min.css' : [
						'static/chat/css/style.css',
						'scripts/emotes/emoticons.css',
						'scripts/icons/icons.css'
					],

					// Tournament CSS
					'static/tournament/css/tournament.min.css' : [
						'static/tournament/css/tournament.css',
						'scripts/tournament/icons.css',
						'scripts/tournament/portraits.css',
						'scripts/tournament/sponsors.css'
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
			tournament: {
				src     : 'scripts/tournament/portraits',
				options : '--sprite-namespace= --namespace=t-portrait --css=scripts/tournament --img=scripts/tournament --css-template=scripts/tournament/portraits.jinja --url=../img/ --pseudo-class-separator=_'
			},
			tournamenticons: {
				src     : 'scripts/tournament/icons',
				options : '--sprite-namespace= --namespace=icon --css=scripts/tournament --img=scripts/tournament --css-template=scripts/tournament/tournamenticons.jinja --url=../img/ --pseudo-class-separator=_'
			},
			tournamentsponsors: {
				src     : 'scripts/tournament/sponsors',
				options : '--sprite-namespace= --namespace=sponsor --css=scripts/tournament --img=scripts/tournament --css-template=scripts/tournament/tournamentsponsors.jinja --url=../img/ --pseudo-class-separator=_'
			}
		},
		
		// Copy script files to static dirs
		copy: {
			gluedimages: {
				files: [
					{expand: true, flatten: true, src: 'scripts/emotes/emoticons.png', dest: 'static/chat/img/', filter: 'isFile'},
					{expand: true, flatten: true, src: 'scripts/icons/icons.png', dest: 'static/chat/img/', filter: 'isFile'},
					{expand: true, flatten: true, src: 'scripts/tournament/portraits.png', dest: 'static/tournament/img/', filter: 'isFile'},
					{expand: true, flatten: true, src: 'scripts/tournament/icons.png', dest: 'static/tournament/img/', filter: 'isFile'},
					{expand: true, flatten: true, src: 'scripts/tournament/sponsors.png', dest: 'static/tournament/img/', filter: 'isFile'}
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
	
	// Build static resources
	grunt.registerTask('build', [
		'glue:emoticons',
		'glue:icons',
		'glue:tournament',
		'glue:tournamenticons',
		'glue:tournamentsponsors',
        'uglify:libs',
		'uglify:web',
		'uglify:chat',
		'less:build',
		'copy:gluedimages'
	]);
	
	// Default
	grunt.registerTask('default', [
		'build'
	]);
};