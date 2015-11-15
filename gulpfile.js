'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var uglify = require('gulp-uglify');
var rename = require("gulp-rename");
var cssmin = require('gulp-cssmin');
var source = require('vinyl-source-stream');
var browserify = require('browserify');
var es         = require('event-stream');

var files = {
	sass: './assets/sass/*.scss',
	js: [ './assets/js/*.js', '!./assets/js/*.min.js' ],
	css: [ './assets/css/*.css', '!./assets/css/*.min.css' ],
	browserify: [ './assets/js/src/api.js', './assets/js/src/admin.js' ]
};

gulp.task('default', ['sass', 'uglify', 'cssmin']);

gulp.task('sass', function () {
	return gulp.src(files.sass)
		.pipe(sass().on('error', sass.logError))
		.pipe(gulp.dest('./assets/css'));
});

gulp.task('cssmin',['sass'], function() {
	return gulp.src(files.css)
		.pipe(cssmin())
		.pipe(rename({suffix: '.min'}))
		.pipe(gulp.dest("./assets/css"));
});

gulp.task('browserify', function () {

	// map them to our stream function
	var tasks = files.browserify.map(function(entry) {
		var file = entry.split('/').pop();
		return browserify({ entries: [entry] })
			.bundle()
			.pipe(source(file))
			// rename them to have "bundle as postfix"
			.pipe(rename({ extname: '.js' }))
			.pipe(gulp.dest('./assets/js'));
	});

	// create a merged stream
	return es.merge.apply(null, tasks);
});


gulp.task('uglify', ['browserify'], function() {
	return gulp.src(files.js)
		.pipe(uglify())
		.pipe(rename({ extname: '.min.js' }))
		.pipe(gulp.dest('./assets/js'));
});

gulp.task('watch', function () {
	gulp.watch( files.sass, ['sass']);
	gulp.watch( files.browserify, ['browserify']);
	gulp.watch( files.js, [ 'uglify' ]);
	gulp.watch(files.css, [ 'cssmin' ]);
});
