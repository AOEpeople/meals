var gulp = require('gulp');
var rimraf = require('gulp-rimraf');
var concat = require('gulp-concat');
var sass = require('gulp-sass');

gulp.task('clean', function() {
    return gulp.src(['../../web/mealz.css', '../../web/mealz.js', '../../web/fonts', '../../web/images'], {read: false})
        .pipe(rimraf({force: true}))
});

gulp.task('js', ['clean'], function() {
    gulp.src(['bower_components/bootstrap-sass/assets/javascripts/bootstrap.js', 'js/**/*.js'])
        .pipe(concat('mealz.js'))
        .pipe(gulp.dest('../../web/'));
});

gulp.task('css', ['clean'], function() {
    gulp.src('./sass/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('../../web/'));
});

gulp.task('fonts', ['clean'], function() {
    gulp.src('./sass/fonts/**/*')
        .pipe(gulp.dest('../../web/fonts/'))
});

gulp.task('images', ['clean'], function() {
    gulp.src('./sass/images/**/*')
        .pipe(gulp.dest('../../web/images/'))
});

gulp.task('watch', function() {
    gulp.watch(['./sass/**/*', 'js/**/*.js'], ['default']);
});

gulp.task('default', ['fonts', 'images', 'js', 'css']);

