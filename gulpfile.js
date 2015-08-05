var gulp = require('gulp');
var sass = require('gulp-sass');
var plumber = require('gulp-plumber');
var livereload = require('gulp-livereload');

gulp.task('default', function() {
    // place code for your default task here
});

gulp.task('watch', ['sass', 'js'], function () {
    var onChange = function (event) {
        console.log('File ' + event.path + ' has been ' + event.type);
        // Tell LiveReload to reload the window
        livereload.changed(event.path);
    };
    // Starts the server
    livereload.listen();

    gulp.watch('./app/Resources/public/css/*.scss', ['sass'])
        .on('change', onChange);

    gulp.watch('./app/Resources/public/js/*.js', ['js'])
        .on('change', onChange);

    gulp.watch('./app/Resources/**/views/**/*.html.twig', ['twig'])
        .on('change', onChange);
});

gulp.task('sass', function() {
    gulp.src(
            [
                'app/Resources/public/css/main.scss',
                'bower_components/basscss/css/basscss.css'
            ]
        )
        .pipe(plumber())
        .pipe(sass())
        .pipe(gulp.dest('web/css'))
        .pipe(livereload());
});

gulp.task('js', function() {
    console.log('js called');
    gulp.src(
        [,
            'bower_components/jQuery/dist/jquery.js',
            'bower_components/princeTable/princeFilter.JQuery.js'
        ]
    )
        .pipe(plumber())
        .pipe(gulp.dest('web/js'))
        .pipe(livereload());
});

gulp.task('twig', function(event) {
    livereload.reload();
});
