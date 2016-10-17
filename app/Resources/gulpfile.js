var gulp        = require('gulp');
var rimraf      = require('gulp-rimraf');
var concat      = require('gulp-concat');
var sass        = require('gulp-sass');
var sourcemaps  = require('gulp-sourcemaps');
var scsslint    = require('gulp-scss-lint');
var jshint      = require('gulp-jshint');
var util        = require('gulp-util');
var uglify      = require('gulp-uglify');
var merge       = require('merge-stream');

var config = {
    production: !!util.env.production
};

/**
 * Clean css, js, font and image directory before creating new files
 */
gulp.task('clean', function() {
    return gulp.src([
            '../../web/mealz.css',
            '../../web/mealz.js',
            '../../web/fonts',
            '../../web/images'
        ], {
            read: false
        })
        .pipe(rimraf({
            force: true
        }))
});

/**
 * Hint .js files using jshint and jshint-stylish
 */
gulp.task('jshint', function() {
    gulp.src('js/**/*.js')
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'));
});

/**
 * Concat all used js files into one
 */
gulp.task('js', function() {
    gulp.src([
            'bower_components/jquery/dist/jquery.js',
            'bower_components/fancybox/source/jquery.fancybox.pack.js',
            'bower_components/datatables.net/js/jquery.dataTables.js',
            'bower_components/switchery/dist/switchery.min.js',
            'js/**/*.js'
        ])
        .pipe(concat('mealz.js'))
        .pipe(config.production ? uglify() : util.noop())
        .pipe(gulp.dest('../../web/'));
});

/**
 * Compile SCSS to CSS
 */
gulp.task('css', function() {

    var sassStream = gulp.src(['./sass/**/*.scss', '!./sass/helpers/_glyphicons.scss'])
        .pipe(scsslint({
            'config': 'scsslint.yml'
        }))
        .pipe(config.production ? util.noop() : sourcemaps.init())
        .pipe(sass(config.production ? {outputStyle: 'compressed'} : util.noop()).on('error', sass.logError))
        .pipe(config.production ? util.noop() : sourcemaps.write());

    var cssStream = gulp.src(['bower_components/switchery/dist/switchery.min.css'])
    var cssFancybox = gulp.src(['bower_components/fancybox/source/jquery.fancybox.css'])

    return merge(sassStream, cssStream, cssFancybox)
        .pipe(concat('mealz.css'))
        .pipe(gulp.dest('../../web/'));
});

/**
 * Copy fonts to /web/fonts directory
 */
gulp.task('fonts', ['clean'], function() {
    gulp.src('./sass/fonts/**/*')
        .pipe(gulp.dest('../../web/fonts/'))
});

/**
 * Copy favicon to /web directory
 */
gulp.task('favicon', function() {
    gulp.src(['./favicon/**/*'])
        .pipe(gulp.dest('../../web/'))
});

/**
 * Copy images to /web/images directory
 */
gulp.task('images', ['clean'], function() {
    gulp.src(['./images/**/*'])
        .pipe(gulp.dest('../../web/images/'))
});

/**
 * Default watch task
 */
gulp.task('watch', function() {
    gulp.watch(['./sass/**/*', 'js/**/*.js'], ['default']);
});


/**
 * Default task for watching, just js & css
 */
gulp.task('default', ['jshint', 'js', 'css']);

/**
 * Task to build the whole stuff, including fonts and images
 * Run "gulp build --production" on production environment
 */
gulp.task('build', ['clean', 'fonts', 'images','favicon', 'jshint', 'js', 'css']);
