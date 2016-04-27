<?php

/*
Plugin Name: One Click Demo Import
Plugin URI: http://www.proteusthemes.com
Description: Import your content, widgets and theme settings with one click. Theme authors! Enable simple demo import for your theme demo data.
Version: 1.0.3
Author: ProteusThemes
Author URI: http://www.proteusthemes.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Text Domain: pt-ocdi
*/

// Block direct access to the main plugin file.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Path/URL to root of this plugin, with trailing slash.
define( 'PT_OCDI_PATH', plugin_dir_path( __FILE__ ) );
define( 'PT_OCDI_URL', plugin_dir_url( __FILE__ ) );

// Current version of the plugin.
define( 'PT_OCDI_VERSION', '1.0.3' );

// Include files.
require PT_OCDI_PATH . 'inc/class-ocdi-helpers.php';
require PT_OCDI_PATH . 'inc/class-ocdi-importer.php';
require PT_OCDI_PATH . 'inc/class-ocdi-widget-importer.php';
require PT_OCDI_PATH . 'inc/class-ocdi-logger.php';

/**
 * One Click Demo Import class, so we don't have to worry about namespaces.
 */
class PT_One_Click_Demo_Import {

	/**
	 * @var $instance the reference to *Singleton* instance of this class
	 */
	private static $instance;

	/**
	 * Private variables used throughout the plugin.
	 */
	private $importer, $plugin_page, $import_files, $logger, $log_file_path;


	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return PT_One_Click_Demo_Import the *Singleton* instance.
	 */
	public static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}


	/**
	 * Class construct function, to initiate the plugin.
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct() {

		// Actions.
		add_action( 'admin_menu', array( $this, 'create_plugin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_ocdi_import_demo_data', array( $this, 'import_demo_data_ajax_callback' ) );
		add_action( 'after_setup_theme', array( $this, 'setup_plugin_with_filter_data' ) );
	}


	/**
	 * Private clone method to prevent cloning of the instance of the *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {}


	/**
	 * Private unserialize method to prevent unserializing of the *Singleton* instance.
	 *
	 * @return void
	 */
	private function __wakeup() {}


	/**
	 * Creates the plugin page and a submenu item in WP Appearance menu.
	 */
	public function create_plugin_page() {
		$this->plugin_page = add_theme_page( 'One Click Demo Import', 'Import Demo Data', 'import', 'pt-one-click-demo-import', array( $this, 'display_plugin_page' ) );
	}


	/**
	 * Plugin page display.
	 */
	public function display_plugin_page() {
	?>

	<div class="ocdi  wrap">
		<h2 class="ocdi__title"><span class="dashicons  dashicons-download"></span><?php esc_html_e( 'One Click Demo Import', 'pt-ocdi' ); ?></h2>

		<?php

		// Display warrning if PHP safe mode is enabled, since we wont be able to change the max_execution_time.
		if ( ini_get( 'safe_mode' ) ) {
			printf(
				esc_html__( '%sWarning: your server is using %sPHP safe mode%s. This means that you might experience server timeout errors.%s', 'pt-ocdi' ),
				'<div class="notice  notice-warning"><p>',
				'<strong>',
				'</strong>',
				'</p></div>'
			);
		}

		// Start output buffer for displaying the plugin intro text.
		ob_start();
		?>

		<div class="ocdi__intro-text">
			<p>
				<?php esc_html_e( 'Importing demo data (post, pages, images, theme settings, ...) is the easiest way to setup your theme. It will allow you to quickly edit everything instead of creating content from scratch. When you import the data, the following things might happen:', 'pt-ocdi' ); ?>
			</p>

			<ul>
				<li><?php esc_html_e( 'No existing posts, pages, categories, images, custom post types or any other data will be deleted or modified.', 'pt-ocdi' ); ?></li>
				<li><?php esc_html_e( 'Posts, pages, images, widgets and menus will get imported.', 'pt-ocdi' ); ?></li>
				<li><?php esc_html_e( 'Please click "Import Demo Data" button only once and wait, it can take a couple of minutes.', 'pt-ocdi' ); ?></li>
			</ul>
		</div>

		<div class="ocdi__intro-text">
			<p><?php esc_html_e( 'Before you begin, make sure all the required plugins are activated.', 'pt-ocdi' ); ?></p>
		</div>

		<?php
			$plugin_intro_text = ob_get_clean();

			// Display the plugin intro text (can be replaced with custom text through the filter below).
			echo wp_kses_post( apply_filters( 'pt-ocdi/plugin_intro_text', $plugin_intro_text ) );
		?>


		<?php if ( empty( $this->import_files ) ) : ?>
			<div class="notice  notice-info  below-h2">
				<p>
					<?php esc_html_e( 'There are no predefined import files available in this theme. Please upload the import files manually!', 'pt-ocdi' ); ?>
				</p>
			</div>
			<p>
				<label for="content-file-upload"><?php esc_html_e( 'Choose a XML file for content import:', 'pt-ocdi' ); ?></label>
				<input id="ocdi__content-file-upload" type="file" name="content-file-upload">
				<br>
				<small><?php esc_html_e( 'optional', 'pt-ocdi' ); ?></small> <label for="widget-file-upload"><?php esc_html_e( 'Choose a WIE or JSON file for widget import:', 'pt-ocdi' ); ?></label>
				<input id="ocdi__widget-file-upload" type="file" name="widget-file-upload">
			</p>
		<?php elseif ( 1 < count( $this->import_files ) ) : ?>
		<p>
			<select id="ocdi__demo-import-files">
				<?php foreach ( $this->import_files as $index => $import_file ) : ?>
					<option value="<?php echo esc_attr( $index ); ?>">
						<?php echo esc_html( $import_file['import_file_name'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php endif; ?>

		<p>
			<button class="ocdi__button  button-primary  js-ocdi-import-data"><?php esc_html_e( 'Import Demo Data', 'pt-ocdi' ); ?></button>
		</p>

		<div class="ocdi__response  js-ocdi-ajax-response"></div>
	</div>

	<?php
	}


	/**
	 * Enqueue admin scripts (JS and CSS)
	 *
	 * @param string $hook holds info on which admin page you are currently loading.
	 */
	public function admin_enqueue_scripts( $hook ) {

		// Enqueue the scripts only on the plugin page.
		if ( $this->plugin_page === $hook ) {
			wp_enqueue_script( 'ocdi-main-js', PT_OCDI_URL . 'assets/js/main.js' , array( 'jquery', 'jquery-form' ), PT_OCDI_VERSION );

			wp_localize_script( 'ocdi-main-js', 'ocdi',
				array(
					'ajax_url'    => admin_url( 'admin-ajax.php' ),
					'ajax_nonce'  => wp_create_nonce( 'ocdi-ajax-verification' ),
					'loader_text' => esc_html__( 'Importing now, please wait!', 'pt-ocdi' ),
				)
			);

			wp_enqueue_style( 'ocdi-main-css', PT_OCDI_URL . 'assets/css/main.css', array() , PT_OCDI_VERSION );
		}
	}


	/**
	 * Main AJAX callback function for:
	 * 1. prepare import files (uploaded or predefined via filters)
	 * 2. import content
	 * 3. before widgets import setup (optional)
	 * 4. import widgets (optional)
	 * 5. after import setup (optional)
	 */
	public function import_demo_data_ajax_callback() {

		// Try to update PHP memory limit (so that it does not run out of it).
		ini_set( 'memory_limit', apply_filters( 'pt-ocdi/import_memory_limit', '350M' ) );

		// Verify if the AJAX call is valid (checks nonce and current_user_can).
		OCDI_Helpers::verify_ajax_call();

		// Error messages displayed on front page.
		$frontend_error_messages = '';

		// Create a date and time string to use for demo and log file names.
		$demo_import_start_time = date( apply_filters( 'pt-ocdi/date_format_for_file_names', 'Y-m-d__H-i-s' ) );

		// Define log file path.
		$this->log_file_path = OCDI_Helpers::get_log_path( $demo_import_start_time );

		// Get selected file index or set it to 0.
		$selected_index = empty( $_POST['selected'] ) ? 0 : absint( $_POST['selected'] );

		/**
		 * 1. Prepare import files.
		 * Manually uploaded import files or predefined import files via filter: pt-ocdi/import_files
		 */
		if ( ! empty( $_FILES ) ) { // Using manual file uploads?

			// Get paths for the uploaded files.
			$selected_import_files = OCDI_Helpers::process_uploaded_files( $_FILES, $this->log_file_path );

			// Set the name of the import files, because we used the uploaded files.
			$this->import_files[ $selected_index ]['import_file_name'] = esc_html__( 'Manually uploaded files', 'pt-ocdi' );
		}
		elseif ( ! empty( $this->import_files[ $selected_index ] ) ) { // Use predefined import files from wp filter: pt-ocdi/import_files.

			// Download the import files (content and widgets files) and save it to variable for later use.
			$selected_import_files = OCDI_Helpers::download_import_files(
				$this->import_files[ $selected_index ],
				$demo_import_start_time
			);

			// Check Errors.
			if ( is_wp_error( $selected_import_files ) ) {

				// Write error to log file and send an AJAX response with the error.
				OCDI_Helpers::log_error_and_send_ajax_response(
					$selected_import_files->get_error_message(),
					$this->log_file_path,
					esc_html__( 'Downloaded files', 'pt-ocdi' )
				);
			}

			// Add this message to log file.
			$log_added = OCDI_Helpers::append_to_file(
				sprintf(
					__( 'The import files for: %s were successfully downloaded!', 'pt-ocdi' ),
					$this->import_files[ $selected_index ]['import_file_name']
				) . OCDI_Helpers::import_file_info( $selected_import_files ),
				$this->log_file_path,
				esc_html__( 'Downloaded files' , 'pt-ocdi' )
			);
		}
		else {

			// Send JSON Error response to the AJAX call.
			wp_send_json( esc_html__( 'No import files specified!', 'pt-ocdi' ) );
		}

		/**
		 * 2. Import content.
		 * Returns any errors greater then the "error" logger level, that will be displayed on front page.
		 */
		$frontend_error_messages .= $this->import_content( $selected_import_files['content'] );

		/**
		 * 3. Before widgets import setup.
		 */
		$action = 'pt-ocdi/before_widgets_import';
		if ( ( false !== has_action( $action ) ) && empty( $frontend_error_messages ) ) {

			// Run the before_widgets_import action to setup other settings.
			$this->do_import_action( $action, $this->import_files[ $selected_index ] );
		}

		/**
		 * 4. Import widgets.
		 */
		if ( ! empty( $selected_import_files['widgets'] ) && empty( $frontend_error_messages ) ) {
			$this->import_widgets( $selected_import_files['widgets'] );
		}

		/**
		 * 5. After import setup.
		 */
		$action = 'pt-ocdi/after_import';
		if ( ( false !== has_action( $action ) ) && empty( $frontend_error_messages ) ) {

			// Run the after_import action to setup other settings.
			$this->do_import_action( $action, $this->import_files[ $selected_index ] );
		}

		// Display final messages (success or error messages).
		if ( empty( $frontend_error_messages ) ) {
			$response['message'] = sprintf(
				__( '%1$s%3$sThat\'s it, all done!%4$s%2$sThe demo import has finished. Please check your page and make sure that everything has imported correctly. If it did, you can deactivate the %3$sOne Click Demo Import%4$s plugin, because it has done its job.%5$s', 'pt-ocdi' ),
				'<div class="notice  notice-success"><p>',
				'<br>',
				'<strong>',
				'</strong>',
				'</p></div>'
			);
		}
		else {
			$response['message'] = $frontend_error_messages . '<br>';
			$response['message'] .= sprintf(
				__( '%1$sThe demo import has finished, but there were some import errors.%2$sMore details about the errors can be found in this %3$s%5$slog file%6$s%4$s%7$s', 'pt-ocdi' ),
				'<div class="notice  notice-error"><p>',
				'<br>',
				'<strong>',
				'</strong>',
				'<a href="' . OCDI_Helpers::get_log_url( $this->log_file_path ) .'" target="_blank">',
				'</a>',
				'</p></div>'
			);
		}

		wp_send_json( $response );
	}


	/**
	 * Import content from an WP XML file.
	 *
	 * @param string $import_file_path path to the import file.
	 */
	private function import_content( $import_file_path ) {

		// This should be replaced with multiple AJAX calls (import in smaller chunks)
		// so that it would not come to the Internal Error, because of the PHP script timeout.
		// Also this function has no effect when PHP is running in safe mode
		// http://php.net/manual/en/function.set-time-limit.php.
		// Increase PHP max execution time.
		set_time_limit( apply_filters( 'pt-ocdi/set_time_limit_for_demo_data_import', 300 ) );

		// Disable import of authors.
		add_filter( 'wxr_importer.pre_process.user', '__return_false' );

		// Disables generation of multiple image sizes (thumbnails) in the content import step.
		if ( ! apply_filters( 'pt-ocdi/regenerate_thumbnails_in_content_import', false ) ) {
			add_filter( 'intermediate_image_sizes_advanced',
				function() {
					return null;
				}
			);
		}

		// Import content.
		if ( ! empty( $import_file_path ) ) {
			ob_start();
				$this->importer->import( $import_file_path );
			$message = ob_get_clean();

			// Add this message to log file.
			$log_added = OCDI_Helpers::append_to_file(
				$message . PHP_EOL . 'MAX EXECUTION TIME = ' . ini_get( 'max_execution_time' ),
				$this->log_file_path,
				esc_html__( 'Importing content' , 'pt-ocdi' )
			);
		}

		// Return any error messages for the front page output (errors, critical, alert and emergency level messages only).
		return $this->logger->error_output;
	}


	/**
	 * Import widgets from WIE or JSON file.
	 *
	 * @param string $widget_import_file_path path to the widget import file.
	 */
	private function import_widgets( $widget_import_file_path ) {

		// Widget import results.
		$results = array();

		// Create an instance of the Widget Importer.
		$widget_importer = new OCDI_Widget_Importer();

		// Import widgets.
		if ( ! empty( $widget_import_file_path ) ) {

			// Import widgets and return result.
			$results = $widget_importer->import_widgets( $widget_import_file_path );
		}

		// Check for errors.
		if ( is_wp_error( $results ) ) {

			// Write error to log file and send an AJAX response with the error.
			OCDI_Helpers::log_error_and_send_ajax_response(
				$results->get_error_message(),
				$this->log_file_path,
				esc_html__( 'Importing widgets', 'pt-ocdi' )
			);
		}

		ob_start();
			$widget_importer->format_results_for_log( $results );
		$message = ob_get_clean();

		// Add this message to log file.
		$log_added = OCDI_Helpers::append_to_file(
			$message,
			$this->log_file_path,
			esc_html__( 'Importing widgets' , 'pt-ocdi' )
		);
	}


	/**
	 * Setup other things in the passed wp action.
	 *
	 * @param string $action the action name to be executed.
	 * @param array  $selected_import with information about the selected import.
	 */
	private function do_import_action( $action, $selected_import ) {

		ob_start();
			do_action( $action, $selected_import );
		$message = ob_get_clean();

		// Add this message to log file.
		$log_added = OCDI_Helpers::append_to_file(
			$message,
			$this->log_file_path,
			$action
		);
	}


	/**
	 * Get data from filters, after the theme has loaded and instantiate the importer.
	 */
	public function setup_plugin_with_filter_data() {

		// Get info of import data files and filter it.
		$this->import_files = OCDI_Helpers::validate_import_file_info( apply_filters( 'pt-ocdi/import_files', array() ) );

		// Importer options array.
		$importer_options = apply_filters( 'pt-ocdi/importer_options', array(
			'fetch_attachments' => true,
		) );

		// Logger options for the logger used in the importer.
		$logger_options = apply_filters( 'pt-ocdi/logger_options', array(
			'logger_min_level' => 'warning',
		) );

		// Configure logger instance and set it to the importer.
		$this->logger            = new OCDI_Logger();
		$this->logger->min_level = $logger_options['logger_min_level'];

		// Create importer instance with proper parameters.
		$this->importer = new OCDI_Importer( $importer_options, $this->logger );
	}
}

$PT_One_Click_Demo_Import = PT_One_Click_Demo_Import::getInstance();
