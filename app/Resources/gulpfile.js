var gulp        = require('gulp');
var rimraf      = require('gulp-rimraf');
var concat      = require('gulp-concat');
var sass        = require('gulp-sass');
var sourcemaps  = require('gulp-sourcemaps');
var scsslint    = require('gulp-scss-lint');
var jshint      = require('gulp-jshint');

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
gulp.task('jshint', ['clean'], function() {
    gulp.src('js/**/*.js')
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'));
});

/**
 * Concat all used js files into one
 */
gulp.task('js', ['clean'], function() {
    gulp.src([
            'bower_components/jquery/dist/jquery.js',
            'bower_components/datatables.net/js/jquery.dataTables.js',
            'js/**/*.js'
        ])
        .pipe(concat('mealz.js'))
        .pipe(gulp.dest('../../web/'));
});

/**
 * Compile SCSS to CSS
 */
gulp.task('css', ['clean'], function() {
    gulp.src(['./sass/**/*.scss', '!./sass/helpers/_glyphicons.scss'])
        .pipe(scsslint({
            'config': 'scsslint.yml'
        }))
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(sourcemaps.write())
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
gulp.task('favicon', ['clean'], function() {
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
 */
gulp.task('build', ['fonts', 'images','favicon', 'jshint', 'js', 'css']);
