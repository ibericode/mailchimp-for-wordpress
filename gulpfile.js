const gulp = require('gulp')
const rename = require('gulp-rename')
const cleancss = require('gulp-clean-css')
const wpPot = require('gulp-wp-pot')
const webpack = require('webpack')
const webpackConfig = require('./webpack.config.js')

gulp.task('css', function () {
  return gulp.src('./assets/src/css/*.css')
    .pipe(cleancss())
    .pipe(rename({ extname: '.css' }))
    .pipe(gulp.dest('./assets/css'))
})

gulp.task('js', function () {
  return new Promise((resolve, reject) => {
    webpack(webpackConfig, (err, stats) => {
      if (err) return reject(err)
      if (stats.hasErrors()) return reject(new Error(stats.compilation.errors.join('\n')))
      resolve()
    })
  })
})

gulp.task('images', function () {
  return gulp.src('assets/src/img/*')
    .pipe(gulp.dest('assets/img'))
})

gulp.task('pot', function () {
  return gulp.src('includes/**/**/*.php')
    .pipe(wpPot({ domain: 'mailchimp-for-wp' }))
    .pipe(gulp.dest('languages/mailchimp-for-wp.pot'))
})

gulp.task('watch', function () {
  gulp.watch('./assets/src/css/*.css', gulp.series('css'))
  gulp.watch('./assets/src/js/**/*.js', gulp.series('js'))
})

gulp.task('default', gulp.series('css', 'js', 'images', 'pot'))
