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
define( 'PT_OCDI_VERSION', apply_filters( 'pt-ocdi/version', '0.1-alpha' ) );

/**
 * One Click Demo Import class, so we don't have to worry about namespaces
 */
class PT_One_Click_Demo_Import {

	private $importer, $plugin_page;

	function __construct() {
		// Include files
		require PT_OCDI_PATH . 'inc/class-ocdi-importer.php';

		// Create importer instance *Singleton*
		$this->importer = OCDI_Importer::get_instance();

		// Actions
		add_action( 'admin_menu', array( $this, 'create_plugin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_ocdi_import_data', array( $this, 'ocdi_import_data_callback' ) );
	}


	/**
	 * Creates the plugin page and a submenu item in WP Appearance menu
	 *
	 * @since 1.0.0
	 */
	function create_plugin_page() {
		$this->plugin_page = add_theme_page( 'One Click Demo Import', 'Import Demo Data', 'switch_themes', 'pt-one-click-demo-import', array( $this, 'display_plugin_page' ) );
	}


	/**
	 * Plugin page display
	 *
	 * @since 1.0.0
	 */
	function display_plugin_page() {
	?>
		<div class="wrap  ocdi">
			<h2 class="ocdi__title"><span class="dashicons  dashicons-download"></span> One Click Demo Import</h2>
			<p>TODO: General description of demo import goes here...</p>

			<button class="panel-save  button-primary  js-ocdi-import-data  ocdi__button">Import Demo Data</button>

			<div class="js-ocdi-ajax-response  ocdi__response"></div>
		</div>
	<?php
	}


	/**
	 * Enqueue admin scripts (JS and CSS)
	 *
	 * @since 1.0.0
	 */
	function admin_enqueue_scripts( $hook ) {
		// enqueue the scripts only on the plugin page
		if ( $this->plugin_page === $hook ) {
			wp_enqueue_script( 'ocdi-main-js', PT_OCDI_URL . 'assets/js/main.js' , array( 'jquery' ), PT_OCDI_VERSION );

			wp_localize_script( 'ocdi-main-js', 'ocdi',
				array(
					'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'ajax_nonce' => wp_create_nonce( 'ocdi-ajax-verification' ),
					'file'       => PT_OCDI_PATH . 'demo-import-files/demo-import-post-page-image.xml'
				)
			);

			wp_enqueue_style( 'ocdi-main-css', PT_OCDI_URL . 'assets/css/main.css', array() , PT_OCDI_VERSION );
		}
	}


	/**
	 * AJAX callback function
	 *
	 * @since 1.0.0
	 */
	function ocdi_import_data_callback() {
		check_ajax_referer( 'ocdi-ajax-verification', 'security' );

		$file = $_POST['file'];

		echo '<p>Import file used: ' . $file . '</p>';

		$this->importer->import( $file );

		wp_die();
	}

}

new PT_One_Click_Demo_Import();