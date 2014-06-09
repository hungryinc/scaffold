var gulp = require('gulp');
var less = require('gulp-less');
var path = require('path');
var watchify = require('watchify');
var source = require('vinyl-source-stream')
var rename = require('gulp-rename');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var imagemin = require('gulp-imagemin');
var minifyHTML = require('gulp-minify-html');
var minifyCSS = require('gulp-minify-css');
var browserify = require('gulp-browserify');
var replace = require('gulp-replace');
var plumber = require('gulp-plumber');
var gutil = require('gulp-util');
var livereload = require('gulp-livereload');
var protractor = require("gulp-protractor").protractor;

var paths = {
    scripts: 'app/src/scripts/**/*',
    less: 'app/src/less/**/*',
    html: 'app/src/html/**/*',
    images: 'app/src/img/**/*'
}

var onError = function(err) {
    gutil.beep();
    console.error(err.message);
}

gulp.task('less', function() {
    gulp.src('app/src/less/scaffold.less')
        .pipe(plumber({
            errorHandler: onError
        }))
        .pipe(less({
            paths: [path.join(__dirname, 'less', 'includes')],
            sourceMap: true
        }))
        .pipe(gulp.dest('app/release/styles'))
        .pipe(livereload());
});

gulp.task('less:build', ['less'], function() {
    gulp.src('app/src/less/scaffold.css')
        .pipe(minifyCSS())
        .pipe(gulp.dest('app/release/styles'));
});

gulp.task('html', function() {
    return gulp.src(paths.html)
        .pipe(minifyHTML({
            comments: false,
            spare: true,
            empty: true
        }))
        .pipe(gulp.dest('app/release/html'))
        .pipe(livereload());
});

gulp.task('copy', function() {
    gulp.src(paths.images)
        .pipe(gulp.dest('app/release/img'))
});


gulp.task('watchscripts', function() {
    var bundler, rebundle;

    bundler = watchify('./app/src/scripts/app.js', {
        basedir: __dirname
    });

    rebundle = function() {
        var stream = bundler.bundle({
            debug: true
        });

        stream.on('error', function(err) {
            gutil.log(gutil.colors.red(err));
        })
        stream = stream.pipe(source('app.js'))

        gutil.log('Bundled', gutil.colors.cyan("watchscripts"));

        return stream.pipe(gulp.dest('app/release/scripts'))
            .pipe(livereload());
    };

    bundler.on('update', rebundle);
    return rebundle();
});

gulp.task('scripts', function() {
    // Single entry point to browserify
    gulp.src('app/src/scripts/app.js')
        .pipe(plumber({
            errorHandler: onError
        }))
        .pipe(browserify({
            debug: false,
        }))
        .pipe(uglify({
            mangle: false,
            compress: {
                drop_console: true,
                dead_code: true
            }
        }))
        .pipe(gulp.dest('app/release/scripts'))
});

gulp.task('protractor', function() {
    gulp.src(["app/tests/e2e/**/*.spec.js"])
        .pipe(protractor({
            configFile: "app/tests/protractor.config.js",
        }))
        .on('error', function(e) {
            cosnole.log(e)
            throw e
        })
});

gulp.task('watch', function() {
    gulp.watch(paths.less, ['less']);
    gulp.watch('app/imports/greyscale/*.less', ['less']);
    // gulp.watch(paths.scripts, ['scripts']);
    gulp.watch(paths.html, ['html']);
});

// The default task (called when you run `gulp` from cli)
gulp.task('default', ['less', 'watchscripts', 'html', 'copy', 'watch']);
gulp.task('build', ['less:build', 'scripts', 'html', 'copy']);
gulp.task('test', ['protractor']);