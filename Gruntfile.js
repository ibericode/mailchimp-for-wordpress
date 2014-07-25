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
				options: {
					loadPath: 'assets/sass/partials',
					style: 'expanded'
				},
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

		watch: {
			js:  {
				files: 'assets/js/*.js',
				tasks: [ 'uglify' ]
			},
			css: {
				files: 'assets/css/*.css',
				tasks: [ 'cssmin' ]
			},
			scss: {
				files: 'assets/sass/*.scss',
				tasks: [ 'sass' ]
			}
		}
	});

	// load plugins
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-cssmin');

	// register at least this one task
	grunt.registerTask('default', [ 'uglify', 'sass', 'cssmin' ]);

};