const gulp = require('gulp')
const sass = require('gulp-sass')
const uglify = require('gulp-uglify')
const rename = require('gulp-rename')
const cleancss = require('gulp-clean-css')
const source = require('vinyl-source-stream')
const browserify = require('browserify')
const merge = require('merge-stream')
const streamify = require('gulp-streamify')
const sourcemaps = require('gulp-sourcemaps')
const wpPot = require('gulp-wp-pot')

gulp.task('sass', function () {
  const files = './assets/src/sass/[^_]*.scss'

  return gulp.src(files)
    // create .css file
    .pipe(sass())
    .pipe(rename({ extname: '.css' }))
    .pipe(gulp.dest('./assets/css'))

    // create .min.css
    .pipe(cleancss())
    .pipe(rename({ extname: '.min.css' }))
    .pipe(gulp.dest('./assets/css'))
})

gulp.task('browserify', function () {
  const bundles = ['admin.js', 'integrations-admin.js', 'forms.js', 'forms-submitted.js', 'forms-admin.js', 'forms-block.js', 'third-party/placeholders.js']
  return merge(bundles.map(f => browserify({ entries: [`assets/src/js/${f}`] })
    .transform('babelify', {
      presets: ['@babel/preset-env'],
      plugins: [
        '@babel/plugin-transform-react-jsx'
      ]
    })
    .bundle()
    .pipe(source(f))
    .pipe(rename({ extname: '.js' }))
    .pipe(gulp.dest('./assets/js'))
  ))
})

gulp.task('uglify', gulp.series('browserify', function () {
  return gulp.src(['./assets/js/**/*.js', '!./assets/js/**/*.min.js'])
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(streamify(uglify()))
    .pipe(rename({ extname: '.min.js' }))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./assets/js'))
}))

gulp.task('images', function () {
  return gulp.src('assets/src/img/*')
    .pipe(gulp.dest('assets/img'))
})

gulp.task('pot', function () {
  const domain = 'mailchimp-for-wp'
  return gulp.src('includes/**/**/*.php')
    .pipe(wpPot({ domain: domain }))
    .pipe(gulp.dest(`languages/${domain}.pot`))
})

gulp.task('watch', function () {
  gulp.watch('./assets/src/sass/**/*.scss', gulp.series('sass'))
  gulp.watch('./assets/src/js/**/*.js', gulp.series('browserify'))
})

gulp.task('default', gulp.series('sass', 'uglify', 'images', 'pot'))
