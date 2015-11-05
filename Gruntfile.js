module.exports = function(grunt) {

	// Config
	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		// define tasks
		uglify: {
			files: {
				expand: true,
				src: [ 'assets/js/*.js', '!assets/js/*.min.js' ],  // source files
				ext: '.min.js'   // replace .js with .min.js
			}
		},

		sass: {
			dist: {
				files: [{
					expand: true,
					flatten: true,
					src: 'assets/sass/*.scss',
					dest: 'assets/css/',
					ext: '.css'
				}]
			}
		},

		cssmin: {
			minify: {
				expand: true,
				src: [ 'assets/css/*.css', '!assets/css/*.min.css' ],
				ext: '.min.css'
			}
		},

		// I18n
		addtextdomain: {
			options: {
				textdomain: 'mailchimp-for-wp'
			},
			php: {
				files: {
					src: [
						'includes/*.php'
					]
				}
			}
		},

		browserify: {

			admin: {
				src: ['assets/js/src/admin.js'],
				dest: 'assets/js/admin.js'
			},

			api: {
				src: ['assets/js/src/api.js'],
				dest: 'assets/js/api.js'
			}

		},

		watch: {
			browserify: {
				files: [ 'assets/js/src/*.js', 'assets/js/src/*/*.js' ],
				tasks: [ 'browserify' ]
			},
			js:  {
				files: [ 'assets/js/*.js', '!assets/js/*min.js' ],
				tasks: [ 'uglify' ]
			},
			css: {
				files: [ 'assets/css/*.css', '!assets/css/*.min.css' ],
				tasks: [ 'cssmin' ]
			},
			scss: {
				files: [ 'assets/sass/**' ],
				tasks: [ 'sass' ]
			}
		}
	});

	// load plugins
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-browserify');

	// register at least this one task
	grunt.registerTask('default', [ 'browserify', 'uglify', 'sass', 'cssmin' ]);

};