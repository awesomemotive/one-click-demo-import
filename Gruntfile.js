module.exports = function ( grunt ) {
	// Auto-load the needed grunt tasks
	// require('load-grunt-tasks')(grunt);
	require( 'load-grunt-tasks' )( grunt, { pattern: ['grunt-*'] } );

	var config = {
		tmpdir:                  '.tmp/',
		phpFileRegex:            '[^/]+\.php$',
		phpFileInSubfolderRegex: '.*?\.php$',
		pluginSlug:              'one-click-demo-import',
	};

	// configuration
	grunt.initConfig( {
		pgk: grunt.file.readJSON( 'package.json' ),

		config: config,

		// https://github.com/gruntjs/grunt-contrib-copy
		copy: {
			// create new directory for deployment
			build: {
				expand: true,
				dot:    false,
				dest:   config.pluginSlug + '/',
				src:    [
					'*.php',
					'readme.txt',
					'assets/**',
					'inc/**',
					'languages/**',
					'vendor/**',
					'views/**',
				],
				flatten: false
			}
		},

		// https://www.npmjs.com/package/grunt-wp-i18n
		makepot: {
			plugin: {
				options: {
					domainPath:      'languages/',
					include:         [config.phpFileRegex, '^inc/'+config.phpFileInSubfolderRegex, '^views/'+config.phpFileInSubfolderRegex],
					mainFile:        'one-click-demo-import.php',
					potComments:     'Copyright (C) {year} Awesome Motive \n# This file is distributed under the GPL 2.0.',
					potFilename:     'pt-ocdi.pot',
					potHeaders:      {
						poedit:                 true,
						'report-msgid-bugs-to': 'https://wordpress.org/support/plugin/one-click-demo-import/',
					},
					type:            'wp-plugin',
					updateTimestamp: false,
					updatePoFiles:   true,
				}
			},
		},

		// https://www.npmjs.com/package/grunt-wp-i18n
		addtextdomain: {
			options: {
				updateDomains: true
			},
			target: {
				files: {
					src: [
						'*.php',
						'inc/**/*.php',
					]
				}
			}
		},

		// https://www.npmjs.com/package/grunt-po2mo
		po2mo: {
			files: {
				src:    'languages/*.po',
				expand: true,
			},
		},

		wp_readme_to_markdown: {
			your_target: {
				files: {
					'readme.md': 'readme.txt'
				},
			},
		},

	} );

	// update languages files
	grunt.registerTask( 'plugin_i18n', [
		'addtextdomain',
		'makepot:plugin',
		'po2mo',
	] );

	// update languages files
	grunt.registerTask( 'readme', [
		'wp_readme_to_markdown',
	] );

};
