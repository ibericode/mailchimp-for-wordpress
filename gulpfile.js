'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var uglify = require('gulp-uglify');
var rename = require("gulp-rename");
var cssmin = require('gulp-cssmin');
var source = require('vinyl-source-stream');
var browserify = require('browserify');
var replace = require('gulp-replace');
var merge = require('merge-stream');
var streamify = require('gulp-streamify');
var globby = require('globby');
var buffer = require('vinyl-buffer');
var through = require('through2');

gulp.task('default', ['sass', 'browserify', 'uglify']);


gulp.task('sass', function () {
	var files = './assets/sass/[^_]*.scss';

	return gulp.src(files)
		// create .css file
		.pipe(sass())
		.pipe(rename({ extname: '.css' }))
		.pipe(gulp.dest('./assets/css'))

		// create .min.css
		.pipe(cssmin())
		.pipe(rename({extname: '.min.css'}))
		.pipe(gulp.dest("./assets/css"));
});

gulp.task('browserify', function () {

	var bundledStream = through()
		.pipe(buffer());

	globby("./assets/browserify/[^_]*.js").then(function(entries) {
		var stream = merge(entries.map(function(entry) {
			var file = entry.split('/').pop();

			return browserify({entries: [entry]})
				.bundle()
				.pipe(source(file))

				// create .js file
				.pipe(rename({ extname: '.js' }))
				.pipe(gulp.dest('./assets/js'));
		}));

		stream
			.pipe(bundledStream);
	}).catch(function(err) {});

	return bundledStream;
});

gulp.task('uglify', ['browserify'], function() {
	return gulp.src(['./assets/js/*.js','!./assets/js/*.min.js'])
		.pipe(streamify(uglify()))
		.pipe(rename({extname: '.min.js'}))
		.pipe(gulp.dest('./assets/js'));
});

gulp.task('watch', function () {
	gulp.watch('./assets/sass/**.scss', ['sass']);
	gulp.watch('./assets/js/src/**.js', ['browserify']);
	gulp.watch(['./assets/js/*.js','!./assets/js/*.min.js'], ['uglify']);
});
