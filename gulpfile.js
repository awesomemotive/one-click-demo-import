/**
 * Load plugins.
 */
import autoprefixer from 'gulp-autoprefixer';
import debug from 'gulp-debug';
import { createRequire } from "module";
const require = createRequire(import.meta.url);

const gulp = require( 'gulp' ),
	cached = require( 'gulp-cached' ),
	sass = require('gulp-sass')(require('sass')),
	sourcemaps = require( 'gulp-sourcemaps' ),
	rename = require( 'gulp-rename' ),
	uglify = require( 'gulp-uglify' ),
	copy = require( 'gulp-copy' ),
	readme = require( 'gulp-readme-to-markdown' ),
	replace = require( 'gulp-replace' ),
	packageJSON = require( './package.json' ),
	exec = require( 'child_process' ).exec;

const plugin = {
	name: 'One Click Demo Import',
	slug: 'one-click-demo-import',
	files: [
		'**',
		// Exclude all the files/dirs below. Note the double negate (when ! is used inside the exclusion) - we may actually need some things.
		'!bin/**',
		'!bin/',
		'!docs/**',
		'!docs/',
		'!node_modules/**',
		'!node_modules/',
		'!tests/**',
		'!tests/',
		'!**/*.map',
		'!LICENSE',
		'!assets/**/*.scss',
		'!**/bin/**',
		'!**/bin',
		'!**/tests/**',
		'!**/*.md',
		'!**/*.sh',
		'!**/*.rst',
		'!**/*.xml',
		'assets/demo-content/*.xml',
		'!**/*.yml',
		'!**/*.dist',
		'!**/*.json',
		'vendor/**/*.json',
		'vendor/**/*.md',
		'vendor/**/LICENSE',
		'!**/*.lock',
		'!**/gulpfile.js',
		'!**/.eslintrc.js',
		'!**/.eslintignore.js',
		'!**/.editorconfig',
		'!**/.gitignore',
		'!**/AUTHORS',
		'!**/Copying',
		'!**/Dockerfile',
		'!**/Makefile',
	],
	scss: [
		'assets/css/**/*.scss',
	],
	js: [
		'assets/js/*.js',
		'!assets/js/*.min.js',
	],
	files_replace_ver: [
		"**/*.php",
		"**/*.js",
		"!**/*.min.js",
		"!languages/**",
		"!node_modules/**",
		"!vendor/**",
		"!gulpfile.js",
	],
};

/**
 * Compile SCSS to CSS, compress.
 */
gulp.task( 'css', function () {
	return gulp.src( plugin.scss )
		// UnMinified file.
		.pipe( cached( 'processCSS' ) )
		.pipe( sourcemaps.init() )
		.pipe( sass( { outputStyle: 'expanded' } ).on( 'error', sass.logError ) )
		.pipe( autoprefixer() )
		.pipe( rename( function ( path ) {
			path.dirname = '/assets/css';
			path.extname = '.css';
		} ) )
		.pipe( sourcemaps.write() )
		.pipe( gulp.dest( './' ) )
		// Minified file.
		.pipe( sass( { outputStyle: 'compressed' } ).on( 'error', sass.logError ) )
		.pipe( autoprefixer() )
		.pipe( rename( function ( path ) {
			path.dirname = '/assets/css';
			path.extname = '.min.css';
		} ) )
		.pipe( gulp.dest( './' ) )
		.pipe( debug( { title: '[css]' } ) );
} );

/**
 * Compress js.
 */
gulp.task( 'js', function () {
	return gulp.src( plugin.js )
		.pipe( cached( 'processJS' ) )
		.pipe( uglify() ).on( 'error', console.log )
		.pipe( rename( function ( path ) {
			path.dirname = '/assets/js';
			path.basename += '.min';
		} ) )
		.pipe( gulp.dest( '.' ) )
		.pipe( debug( { title: '[js]' } ) );
} );

/**
 * Generate .pot file.
 */
gulp.task( 'pot', function ( cb ) {
	exec(
		'wp i18n make-pot ./ ./languages/one-click-demo-import.pot --slug="one-click-demo-import" --domain="one-click-demo-import" --package-name="One Click Demo Import" --file-comment="" --exclude="node_modules,vendor,tests,bin,docs"',
		function ( err, stdout, stderr ) {
			console.log( stdout );
			console.log( stderr );
			cb( err );
		}
	);
} );

/**
 * Generate readme.md from readme.txt
 */
gulp.task('readme', function() {
	gulp.src([ 'readme.txt' ])
		.pipe(readme({
			details: true,
		}))
		.pipe(gulp.dest('.'));
});

/**
 * Copy production ready plugin folder.
 */
gulp.task( 'copy', function () {
	return gulp.src( plugin.files, { base: '.' } )
		// .pipe( rename( function ( file ) {
		// 	file.dirname = plugin.slug + '/' + file.dirname;
		// } ) )
		.pipe(copy('one-click-demo-import'))
		.pipe( debug( { title: '[copy]' } ) );
} );

/**
 * Replace plugin version with one from package.json in the main plugin file.
 */
gulp.task( 'replace_plugin_file_ver', function () {
	return gulp.src( [ 'one-click-demo-import.php' ] )
		.pipe(
			// File header.
			replace(
				/Version: ((\*)|([0-9]+(\.((\*)|([0-9]+(\.((\*)|([0-9]+)))?)))?))/gm,
				'Version: ' + packageJSON.version
			)
		)
		.pipe( gulp.dest( './' ) );
} );

/**
 * Replace plugin version with one from package.json in @since comments in plugin PHP and JS files.
 */
gulp.task( 'replace_since_ver', function() {
	return gulp.src( plugin.files_replace_ver )
		.pipe(
			replace(
				/@since {VERSION}/g,
				'@since ' + packageJSON.version
			)
		)
		.pipe( gulp.dest( './' ) );
} );

gulp.task( 'replace_ver', gulp.series( 'replace_plugin_file_ver', 'replace_since_ver' ) );

/**
 * Task: build.
 */
gulp.task( 'build', gulp.series( gulp.parallel( 'css', 'js', 'pot' ), 'replace_ver', 'copy' ) );

/**
 * Look out for relevant sass/js changes.
 */
gulp.task( 'watch', function () {
	gulp.watch( plugin.scss, gulp.parallel( 'css' ) );
	gulp.watch( plugin.js, gulp.parallel( 'js' ) );
} );

/**
 * Default.
 */
gulp.task( 'default', gulp.parallel( 'css', 'js' ) );
