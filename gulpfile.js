'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var uglify = require('gulp-uglify');
var rename = require("gulp-rename");
var cssmin = require('gulp-cssmin');
var source = require('vinyl-source-stream');
var browserify = require('browserify');
var replace = require('gulp-replace');
var minimist = require('minimist');
var merge = require('merge-stream');
var util = require('gulp-util');
var intercept = require('gulp-intercept');
var streamify = require('gulp-streamify');
var git = require('gulp-git');

var defaults = {
	string: 'version',
	default: {
		version: 0
	}
};
var options = minimist(process.argv.slice(2), defaults);

gulp.task('default', ['sass', 'browserify']);

gulp.task('sass', function () {
	var files = './assets/sass/*.scss';

	var stream = gulp.src(files)
		.pipe(sass());

	return merge(

		// create .css
		stream.pipe(gulp.dest('./assets/css')),

		// create .min.css
		stream.pipe(cssmin())
			.pipe(rename({suffix: '.min'}))
			.pipe(gulp.dest("./assets/css"))
	);
});

gulp.task('browserify', function () {

	var files = [
		'./assets/js/src/api.js',
		'./assets/js/src/admin.js',
		'./assets/js/src/integrations-admin.js'
	];

	// setup stream for each bundle
	var bundles = merge(files.map(function(entry) {
		var file = entry.split('/').pop();
		return browserify({ entries: [entry] })
			.bundle()
			.pipe(source(file))
			.pipe(rename({ extname: '.js' }))
			.pipe(gulp.dest('./assets/js'));
	}));

	// write all bundle streams to .min file
	return bundles.pipe(streamify(uglify()))
		.pipe(rename({ extname: '.min.js' }))
		.pipe(gulp.dest('./assets/js'));
});

gulp.task('bump-version', function(cb) {

	if( ! options.version ) {
		util.log(util.colors.red("Please specify a --version argument."));
		return;
	}

	// Bump version in readme.txt
	var readme = gulp.src('./readme.txt', {base: './'})
		.pipe(replace(/Stable tag: .*/i, 'Stable tag: ' + options.version))
		.pipe(intercept(function(file) {
			// Check if a Changelog section is present for this version
			var regex = new RegExp('Changelog [\\s\\S]*\\=\\s' + options.version.replace('.', '\\.') + '\\s', '');
			var match = file.contents.toString().match(regex);
			if(! match) {
				util.beep();
				util.log(util.colors.red("readme.txt does not have a changelog for version " + options.version + " yet."));
			}
			return file;
		}))
		.pipe(gulp.dest('./'));

	// Bump version in main plugin file.
	var plugin = gulp.src('./mailchimp-for-wp.php', {base: './'})
		.pipe(replace(/Version: .*/i, 'Version: ' + options.version))
		.pipe(replace(/define\s*\(\s*['"]MC4WP_VERSION['"]\s*,.+/, "define( 'MC4WP_VERSION', '" + options.version + "' );"))
		.pipe(gulp.dest('./'));

	// Bunp version in composer.json
	var composer = gulp.src('./composer.json', { base: './' })
		.pipe(replace(/\"version\"\:.*/i, '"version": "' + options.version + '",'))
		.pipe(gulp.dest('./'));

	return merge(plugin,readme, composer);
});

gulp.task('watch', function () {
	gulp.watch('./assets/sass/**.scss', ['sass']);
	gulp.watch('./assets/js/src/**.js', ['browserify']);
});
