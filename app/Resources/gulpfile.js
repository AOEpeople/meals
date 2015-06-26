var gulp = require('gulp');
var rimraf = require('gulp-rimraf');
var concat = require('gulp-concat');
var sass = require('gulp-sass');

gulp.task('clean', function() {
    return gulp.src(['../../web/mealz.css', '../../web/mealz.js'], {read: false})
        .pipe(rimraf({force: true}))
});

gulp.task('js', ['clean'], function() {
    gulp.src(['js/**/jquery*.js', 'js/**/*.js'])
        .pipe(concat('mealz.js'))
        .pipe(gulp.dest('../../web/'));
});

gulp.task('css', ['clean'], function() {
    gulp.src('./sass/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('../../web/'));
});

gulp.task('default', ['js', 'css'])

