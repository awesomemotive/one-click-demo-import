<?php
/*
Plugin Name: One Click Demo Import
Plugin URI: http://www.proteusthemes.com
Description: WordPress import made easy. Theme authors: Enable simple demo import for your theme demo data.
Version: 0.1-alpha
Author: ProteusThemes
Author URI: http://www.proteusthemes.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Text Domain: pt-ocdi
*/

// Path/URL to root of this plugin, with trailing slash
define( 'PT_OCDI_PATH', apply_filters( 'pt-ocdi/plugin_dir_path', plugin_dir_path( __FILE__ ) ) );
define( 'PT_OCDI_URL', apply_filters( 'pt-ocdi/plugin_dir_url', plugin_dir_url( __FILE__ ) ) );

// Current version of the plugin
define( 'PT_OCDI_VERSION', apply_filters( 'pt-ocdi/version', '1.0.0' ) );

/**
 * One Click Demo Import class, so we don't have to worry about namespaces
 */
class PT_One_Click_Demo_Import {

	private $importer;

	function __construct() {
		// Include files
		require PT_OCDI_PATH . 'inc/class-ocdi-importer.php';

		// Create importer instance *Singleton*
		$this->importer = OCDI_Importer::get_instance();

		// Actions
		add_action( 'admin_menu', array( $this, 'create_plugin_page' ) );
		add_action( 'wp_ajax_ocdi_import_data', array( $this, 'ocdi_import_data_callback' ) );
	}

	/**
	 * Creates the plugin page and a submenu item in WP Appearance menu
	 *
	 * @since 0.1-alpha
	 */
	function create_plugin_page() {
		add_theme_page( 'One Click Demo Import', 'Import Demo Data', 'switch_themes', 'pt-one-click-demo-import', array( $this, 'display_plugin_page' ) );
	}

	/**
	 * Plugin page display
	 *
	 * @since 0.1-alpha
	 */
	function display_plugin_page() {
	?>
		<div class="wrap">
			<h2><span class="dashicons  dashicons-download" style="line-height: 30px;"></span> One Click Demo Import</h2>
			<p>General description of demo import goes here...</p>

			<button class="panel-save  button-primary  js-ocdi-import-data">Import Demo Data</button>

			<div class="js-ocdi-ajax-response"></div>

			<script>
				jQuery( function ( $ ) {
					'use strict';
					$( '.js-ocdi-import-data' ).on( 'click', function () {

						var file = '<?php echo esc_js( PT_OCDI_PATH . "demo-import-files/demo-import-post-page-image.xml" ); ?>';

						var data = {
							'action': 'ocdi_import_data',
							'file': file
						};

						$.ajax({
							method: 'POST',
							url: ajaxurl,
							data: data,
							beforeSend: function() {
								$( '.js-ocdi-import-data' ).after( '<p class="js-ocdi-ajax-loader" style="font-width: bold; font-size: 1.5em;"><span class="spinner" style="display: inline-block; float: none; visibility: visible;"></span> Importing now, please wait!</p>' );
							},
							complete: function() {
								$( '.js-ocdi-ajax-loader' ).remove();
							}
						})
						.done( function( response ) {
							$( '.js-ocdi-ajax-response' ).append( response );
						})
						.fail( function( error ) {
							$( '.js-ocdi-ajax-response' ).append( error );
						});

					} );
				} );
			</script>

		</div>
	<?php
	}

	function ocdi_import_data_callback() {
		$file = $_POST['file'];

		echo '<p>Import file used: ' . $file . '</p>';

		$this->importer->import( $file );

		wp_die(); // this is required to terminate immediately and return a proper response
	}

}

new PT_One_Click_Demo_Import();