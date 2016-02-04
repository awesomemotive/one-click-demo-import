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

	private $importer, $plugin_page, $importer_options, $logger_min_level, $import_files;

	function __construct() {
		// Include files
		require PT_OCDI_PATH . 'inc/class-ocdi-importer.php';

		// Actions
		add_action( 'admin_menu', array( $this, 'create_plugin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_ocdi_import_data', array( $this, 'import_data_ajax_callback' ) );
		add_action( 'after_setup_theme', array( $this, 'setup_plugin_with_filter_data' ) );
	}


	/**
	 * Creates the plugin page and a submenu item in WP Appearance menu
	 */
	function create_plugin_page() {
		$this->plugin_page = add_theme_page( 'One Click Demo Import', 'Import Demo Data', 'switch_themes', 'pt-one-click-demo-import', array( $this, 'display_plugin_page' ) );
	}


	/**
	 * Plugin page display
	 */
	function display_plugin_page() {
	?>
		<div class="wrap  ocdi">
			<h2 class="ocdi__title"><span class="dashicons  dashicons-download"></span><?php esc_html_e( 'One Click Demo Import', 'pt-ocdi' ); ?></h2>
			<p>
				<?php esc_html_e( 'TODO: General description of demo import goes here...', 'pt-ocdi' ); ?>
			</p>

			<p>
				<?php if ( empty( $this->import_files ) ) : ?>
					<div class="error  below-h2">
						<p>
							<?php esc_html_e( 'There are no import files available!', 'pt-ocdi' ); ?>
						</p>
					</div>
				<?php else : ?>
				<select id="demo-import-files">
					<?php foreach ( $this->import_files as $import_file ) : ?>
						<option value="<?php echo esc_url( $import_file['import_file_url'] ); ?>">
							<?php echo esc_html( $import_file['import_file_name'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
			</p>

			<p>
				<button class="panel-save  button-primary  js-ocdi-import-data  ocdi__button" <?php disabled( true, empty( $this->import_files ) ); ?>><?php esc_html_e( 'Import Demo Data', 'pt-ocdi' ); ?></button>
			</p>

			<div class="js-ocdi-ajax-response  ocdi__response"></div>
		</div>
	<?php
	}


	/**
	 * Enqueue admin scripts (JS and CSS)
	 *
	 * @param $hook, holds info on which admin page you are currently looking at
	 */
	function admin_enqueue_scripts( $hook ) {
		// enqueue the scripts only on the plugin page
		if ( $this->plugin_page === $hook ) {
			wp_enqueue_script( 'ocdi-main-js', PT_OCDI_URL . 'assets/js/main.js' , array( 'jquery' ), PT_OCDI_VERSION );

			wp_localize_script( 'ocdi-main-js', 'ocdi',
				array(
					'ajax_url'     => admin_url( 'admin-ajax.php' ),
					'ajax_nonce'   => wp_create_nonce( 'ocdi-ajax-verification' ),
					'file'         => PT_OCDI_PATH . 'demo-import-files/demo-import-post-page-image.xml'
				)
			);

			wp_enqueue_style( 'ocdi-main-css', PT_OCDI_URL . 'assets/css/main.css', array() , PT_OCDI_VERSION );
		}
	}


	/**
	 * AJAX import callback function
	 */
	function import_data_ajax_callback() {
		check_ajax_referer( 'ocdi-ajax-verification', 'security' );

		$file = $_POST['file'];

		echo '<p>Import file used: ' . $file . '</p>';

		$this->importer->import( $file );

		wp_die();
	}


	/**
	 * Get data from filters, after the theme has loaded and instantiate the importer
	 */
	function setup_plugin_with_filter_data() {
		// Get info of import data files
		$this->import_files = apply_filters( 'pt-ocdi/import_files', array() );

		// Importer options array
		$this->importer_options = apply_filters( 'pt-ocdi/importer_options', array(
			'fetch_attachments' => true,
		) );

		// Logger reporting level for the importer
		$this->logger_min_level = apply_filters( 'pt-ocdi/logger_min_level', 'notice' );

		// Create importer instance with proper parameters
		$this->importer = new OCDI_Importer( $this->importer_options, $this->logger_min_level );
	}

}

new PT_One_Click_Demo_Import();