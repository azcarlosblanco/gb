var gulp = require('gulp'),
	stylus = require('gulp-stylus'),
	gulpif = require('gulp-if'),
	autoprefixer = require('gulp-autoprefixer'),
	minifyCSS = require('gulp-minify-css'),
	uglify = require('gulp-uglify'),
	useref = require('gulp-useref'),
	rename = require('gulp-rename'),
	concat = require('gulp-concat'),
	connect = require('gulp-connect');


gulp.task('vendor', function ()
{
    gulp.src('resources/views/master.html')
        .pipe(useref())
		.pipe( gulpif('*.css', minifyCSS() ) )
		.pipe( gulpif('*.js', uglify() ) )
        .pipe(gulp.dest('assets'));
})

gulp.task('stylus', function ()
{
	gulp.src('resources/stylus/main/main.styl')
		.pipe( stylus({
						compress: false
						})
		)
		.pipe( autoprefixer() )
		.pipe( minifyCSS() )
		.pipe( rename('style.css') )
		.pipe( gulp.dest('assets/css') )
        .pipe( connect.reload() )
})

gulp.task('js', function ()
{
	gulp.src('app/**/*.js')
		.pipe( concat('all.js') )
		.pipe( uglify() )
		.pipe( gulp.dest('assets/js') )
		.pipe( connect.reload() )
})
/* Recursos de Font Awesome */
gulp.task('fontAwesome', function() {
    return gulp.src([
                    'bower_components/font-awesome/fonts/fontawesome-webfont.*'])
            .pipe( gulp.dest('assets/fonts/') );
});

gulp.task('fontBootstrap', function() {
    return gulp.src([
                    'bower_components/bootstrap/dist/fonts/glyphicons-halflings-regular.*'])
            .pipe( gulp.dest('assets/fonts/') );
});
gulp.task('connect', function() {
	connect.server(
	{
		root: './',
		hostname: '0.0.0.0',
		port: 616,
		livereload: true,
		open: true
	})
})

gulp.task( 'watch', function()
{
   gulp.watch( 'resources/stylus/**/*.styl', ['stylus'] );
   // gulp.watch( 'app/**/*.js', ['js'] );
});

gulp.task('start', ['connect' , 'vendor' , 'stylus' , 'fontAwesome' , 'fontBootstrap' , 'watch'] );