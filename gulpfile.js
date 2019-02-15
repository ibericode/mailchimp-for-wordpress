'use strict';

const gulp = require('gulp');
const sass = require('gulp-sass');
const uglify = require('gulp-uglify');
const rename = require("gulp-rename");
const cssmin = require('gulp-cssmin');
const source = require('vinyl-source-stream');
const browserify = require('browserify');
const merge = require('merge-stream');
const streamify = require('gulp-streamify');
const globby = require('globby');
const buffer = require('vinyl-buffer');
const through = require('through2');
const sourcemaps = require('gulp-sourcemaps');
const wrap = require('gulp-wrap');
const wpPot = require('gulp-wp-pot');

gulp.task('sass', function () {
	let files = './assets/sass/[^_]*.scss';

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
	let bundledStream = through()
		.pipe(buffer());

	globby("./assets/browserify/[^_]*.js").then(function(entries) {
		 merge(entries.map(function(entry) {
             let filename = entry.split('/').pop();
			return browserify({entries: [entry]})
				.transform("babelify", {
					presets: ["@babel/preset-env"],
					plugins: [
						"@babel/plugin-transform-react-jsx",
					]
				})
				.bundle()
				.pipe(source(filename))
				.pipe(wrap('(function () { var require = undefined; var define = undefined; <%=contents%> })();'))
				.pipe(wrap('(function () { var require = undefined; var define = undefined; <%=contents%> })();'))

				// create .js file
				.pipe(rename({ extname: '.js' }))
				.pipe(gulp.dest('./assets/js'));
		})).pipe(bundledStream);
	}).catch(function(err) {
		console.log(err);
	});

	return bundledStream;
});

gulp.task('uglify', gulp.series('browserify', function() {
	return gulp.src(['./assets/js/**/*.js','!./assets/js/**/*.min.js'])
		.pipe(sourcemaps.init({loadMaps: true}))
		.pipe(streamify(uglify()))
		.pipe(rename({extname: '.min.js'}))
		.pipe(sourcemaps.write('./'))
		.pipe(gulp.dest('./assets/js'));
}));

gulp.task('pot', function () {
	const domain = 'mailchimp-for-wp';
    return gulp.src('includes/**/**/*.php')
        .pipe(wpPot({ domain: domain}))
        .pipe(gulp.dest(`languages/${domain}.pot`));
});

gulp.task('watch', function () {
	gulp.watch('./assets/sass/**/*.scss', gulp.series('sass'));
	gulp.watch('./assets/browserify/**/*.js', gulp.series('browserify'));
});

gulp.task('default', gulp.series('sass', 'uglify', 'pot'));
