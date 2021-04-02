<?php
/**
 * Main One Click Demo Import plugin class/file.
 *
 * @package ocdi
 */

namespace OCDI;

/**
 * One Click Demo Import class, so we don't have to worry about namespaces.
 */
class OneClickDemoImport {
	/**
	 * The instance *Singleton* of this class
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * The instance of the OCDI\Importer class.
	 *
	 * @var object
	 */
	public $importer;

	/**
	 * The instance of the OCDI\PluginInstaller class.
	 *
	 * @var PluginInstaller object
	 */
	public $plugin_installer;

	/**
	 * The resulting page's hook_suffix, or false if the user does not have the capability required.
	 *
	 * @var boolean or string
	 */
	private $plugin_page;

	/**
	 * Holds the verified import files.
	 *
	 * @var array
	 */
	public $import_files;

	/**
	 * The path of the log file.
	 *
	 * @var string
	 */
	public $log_file_path;

	/**
	 * The index of the `import_files` array (which import files was selected).
	 *
	 * @var int
	 */
	private $selected_index;

	/**
	 * The paths of the actual import files to be used in the import.
	 *
	 * @var array
	 */
	private $selected_import_files;

	/**
	 * Holds any error messages, that should be printed out at the end of the import.
	 *
	 * @var string
	 */
	public $frontend_error_messages = array();

	/**
	 * Was the before content import already triggered?
	 *
	 * @var boolean
	 */
	private $before_import_executed = false;

	/**
	 * Make plugin page options available to other methods.
	 *
	 * @var array
	 */
	private $plugin_page_setup = array();

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return OneClickDemoImport the *Singleton* instance.
	 */
	public static function get_instance() {
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
		add_action( 'wp_ajax_ocdi_upload_manual_import_files', array( $this, 'upload_manual_import_files_callback' ) );
		add_action( 'wp_ajax_ocdi_import_demo_data', array( $this, 'import_demo_data_ajax_callback' ) );
		add_action( 'wp_ajax_ocdi_import_customizer_data', array( $this, 'import_customizer_data_ajax_callback' ) );
		add_action( 'wp_ajax_ocdi_after_import_data', array( $this, 'after_all_import_data_ajax_callback' ) );
		add_action( 'after_setup_theme', array( $this, 'setup_plugin_with_filter_data' ) );
		add_action( 'user_admin_notices', array( $this, 'start_notice_output_capturing' ), 0 );
		add_action( 'admin_notices', array( $this, 'start_notice_output_capturing' ), 0 );
		add_action( 'all_admin_notices', array( $this, 'finish_notice_output_capturing' ), PHP_INT_MAX );
		add_action( 'admin_init', array( $this, 'redirect_from_old_default_admin_page' ) );
	}


	/**
	 * Private clone method to prevent cloning of the instance of the *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {}


	/**
	 * Empty unserialize method to prevent unserializing of the *Singleton* instance.
	 *
	 * @return void
	 */
	public function __wakeup() {}


	/**
	 * Creates the plugin page and a submenu item in WP Appearance menu.
	 */
	public function create_plugin_page() {
		$this->plugin_page_setup = Helpers::get_plugin_page_setup_data();

		$this->plugin_page = add_submenu_page(
			$this->plugin_page_setup['parent_slug'],
			$this->plugin_page_setup['page_title'],
			$this->plugin_page_setup['menu_title'],
			$this->plugin_page_setup['capability'],
			$this->plugin_page_setup['menu_slug'],
			Helpers::apply_filters( 'ocdi/plugin_page_display_callback_function', array( $this, 'display_plugin_page' ) )
		);

		// Register the old default settings page, so we can redirect to the new one and not break any existing links.
		add_submenu_page(
			'',
			$this->plugin_page_setup['page_title'],
			$this->plugin_page_setup['menu_title'],
			$this->plugin_page_setup['capability'],
			'pt-one-click-demo-import'
		);

		register_importer( $this->plugin_page_setup['menu_slug'], $this->plugin_page_setup['page_title'], $this->plugin_page_setup['menu_title'], Helpers::apply_filters( 'ocdi/plugin_page_display_callback_function', array( $this, 'display_plugin_page' ) ) );
	}

	/**
	 * Plugin page display.
	 * Output (HTML) is in another file.
	 */
	public function display_plugin_page() {

		if ( isset( $_GET['step'] ) && 'install-plugins' === $_GET['step'] ) {
			require_once OCDI_PATH . 'views/install-plugins.php';

			return;
		}

		if ( isset( $_GET['step'] ) && 'create-content' === $_GET['step'] ) {
			require_once OCDI_PATH . 'views/create-content.php';

			return;
		}

		if ( isset( $_GET['step'] ) && 'import' === $_GET['step'] ) {
			require_once OCDI_PATH . 'views/import.php';

			return;
		}

		require_once OCDI_PATH . 'views/plugin-page.php';
	}


	/**
	 * Enqueue admin scripts (JS and CSS)
	 *
	 * @param string $hook holds info on which admin page you are currently loading.
	 */
	public function admin_enqueue_scripts( $hook ) {
		// Enqueue the scripts only on the plugin page.
		if ( $this->plugin_page === $hook || ( 'admin.php' === $hook && $this->plugin_page_setup['menu_slug'] === esc_attr( $_GET['import'] ) ) ) {
			wp_enqueue_script( 'ocdi-main-js', OCDI_URL . 'assets/js/main.js' , array( 'jquery' ), OCDI_VERSION );

			// Get theme data.
			$theme = wp_get_theme();

			wp_localize_script( 'ocdi-main-js', 'ocdi',
				array(
					'ajax_url'         => admin_url( 'admin-ajax.php' ),
					'ajax_nonce'       => wp_create_nonce( 'ocdi-ajax-verification' ),
					'import_files'     => $this->import_files,
					'wp_customize_on'  => Helpers::apply_filters( 'ocdi/enable_wp_customize_save_hooks', false ),
					'theme_screenshot' => $theme->get_screenshot(),
					'missing_plugins'  => $this->plugin_installer->get_missing_plugins(),
					'plugin_url'       => OCDI_URL,
					'import_url'       => $this->get_plugin_settings_url( [ 'step' => 'import' ] ),
					'texts'            => array(
						'missing_preview_image'    => esc_html__( 'No preview image defined for this import.', 'one-click-demo-import' ),
						'dialog_title'             => esc_html__( 'Are you sure?', 'one-click-demo-import' ),
						'dialog_no'                => esc_html__( 'Cancel', 'one-click-demo-import' ),
						'dialog_yes'               => esc_html__( 'Yes, import!', 'one-click-demo-import' ),
						'selected_import_title'    => esc_html__( 'Selected demo import:', 'one-click-demo-import' ),
						'installing'               => esc_html__( 'Installing...', 'one-click-demo-import' ),
						'importing'                => esc_html__( 'Importing...', 'one-click-demo-import' ),
						'successful_import'        => esc_html__( 'Successfully Imported!', 'one-click-demo-import' ),
						'install_plugin'           => esc_html__( 'Install Plugin', 'one-click-demo-import' ),
						'installed'                => esc_html__( 'Installed', 'one-click-demo-import' ),
						'import_failed'            => esc_html__( 'Import Failed', 'one-click-demo-import' ),
						'import_failed_subtitle'   => esc_html__( 'Whoops, there was a problem importing your content.', 'one-click-demo-import' ),
						'plugin_install_failed'    => esc_html__( 'Looks like some of the plugins failed to install. Please try again. If this issue persists, please manually install the failing plugins and come back to this step to import the theme demo data.', 'one-click-demo-import' ),
						'content_filetype_warn'    => esc_html__( 'Invalid file type detected! Please select an XML file for the Content Import.', 'one-click-demo-import' ),
						'widgets_filetype_warn'    => esc_html__( 'Invalid file type detected! Please select a JSON or WIE file for the Widgets Import.', 'one-click-demo-import' ),
						'customizer_filetype_warn' => esc_html__( 'Invalid file type detected! Please select a DAT file for the Customizer Import.', 'one-click-demo-import' ),
						'redux_filetype_warn'      => esc_html__( 'Invalid file type detected! Please select a JSON file for the Redux Import.', 'one-click-demo-import' ),
					),
				)
			);

			wp_enqueue_style( 'ocdi-main-css', OCDI_URL . 'assets/css/main.css', array() , OCDI_VERSION );
		}
	}


	/**
	 * AJAX callback method for uploading the manual import files.
	 */
	public function upload_manual_import_files_callback() {
		Helpers::verify_ajax_call();

		if ( empty( $_FILES ) ) {
			wp_send_json_error( esc_html__( 'Manual import files are missing! Please select the import files and try again.', 'one-click-demo-import' ) );
		}

		// Create a date and time string to use for demo and log file names.
		Helpers::set_demo_import_start_time();

		// Define log file path.
		$this->log_file_path = Helpers::get_log_path();

		$this->selected_index = 0;

		// Get paths for the uploaded files.
		$this->selected_import_files = Helpers::process_uploaded_files( $_FILES, $this->log_file_path );

		// Set the name of the import files, because we used the uploaded files.
		$this->import_files[ $this->selected_index ]['import_file_name'] = esc_html__( 'Manually uploaded files', 'one-click-demo-import' );

		// Save the initial import data as a transient, so the next import call (in new AJAX call) can use that data.
		Helpers::set_ocdi_import_data_transient( $this->get_current_importer_data() );

		wp_send_json_success();
	}


	/**
	 * Main AJAX callback function for:
	 * 1). prepare import files (uploaded or predefined via filters)
	 * 2). execute 'before content import' actions (before import WP action)
	 * 3). import content
	 * 4). execute 'after content import' actions (before widget import WP action, widget import, customizer import, after import WP action)
	 */
	public function import_demo_data_ajax_callback() {
		// Try to update PHP memory limit (so that it does not run out of it).
		ini_set( 'memory_limit', Helpers::apply_filters( 'ocdi/import_memory_limit', '350M' ) );

		// Verify if the AJAX call is valid (checks nonce and current_user_can).
		Helpers::verify_ajax_call();

		// Is this a new AJAX call to continue the previous import?
		$use_existing_importer_data = $this->use_existing_importer_data();

		if ( ! $use_existing_importer_data ) {
			// Create a date and time string to use for demo and log file names.
			Helpers::set_demo_import_start_time();

			// Define log file path.
			$this->log_file_path = Helpers::get_log_path();

			// Get selected file index or set it to 0.
			$this->selected_index = empty( $_POST['selected'] ) ? 0 : absint( $_POST['selected'] );

			/**
			 * 1). Prepare import files.
			 * Manually uploaded import files or predefined import files via filter: ocdi/import_files
			 */
			if ( ! empty( $_FILES ) ) { // Using manual file uploads?
				// Get paths for the uploaded files.
				$this->selected_import_files = Helpers::process_uploaded_files( $_FILES, $this->log_file_path );

				// Set the name of the import files, because we used the uploaded files.
				$this->import_files[ $this->selected_index ]['import_file_name'] = esc_html__( 'Manually uploaded files', 'one-click-demo-import' );
			}
			elseif ( ! empty( $this->import_files[ $this->selected_index ] ) ) { // Use predefined import files from wp filter: ocdi/import_files.

				// Download the import files (content, widgets and customizer files).
				$this->selected_import_files = Helpers::download_import_files( $this->import_files[ $this->selected_index ] );

				// Check Errors.
				if ( is_wp_error( $this->selected_import_files ) ) {
					// Write error to log file and send an AJAX response with the error.
					Helpers::log_error_and_send_ajax_response(
						$this->selected_import_files->get_error_message(),
						$this->log_file_path,
						esc_html__( 'Downloaded files', 'one-click-demo-import' )
					);
				}

				// Add this message to log file.
				$log_added = Helpers::append_to_file(
					sprintf( /* translators: %s - the name of the selected import. */
						__( 'The import files for: %s were successfully downloaded!', 'one-click-demo-import' ),
						$this->import_files[ $this->selected_index ]['import_file_name']
					) . Helpers::import_file_info( $this->selected_import_files ),
					$this->log_file_path,
					esc_html__( 'Downloaded files' , 'one-click-demo-import' )
				);
			}
			else {
				// Send JSON Error response to the AJAX call.
				wp_send_json( esc_html__( 'No import files specified!', 'one-click-demo-import' ) );
			}
		}

		// Save the initial import data as a transient, so other import parts (in new AJAX calls) can use that data.
		Helpers::set_ocdi_import_data_transient( $this->get_current_importer_data() );

		if ( ! $this->before_import_executed ) {
			$this->before_import_executed = true;

			/**
			 * 2). Execute the actions hooked to the 'ocdi/before_content_import_execution' action:
			 *
			 * Default actions:
			 * 1 - Before content import WP action (with priority 10).
			 */
			Helpers::do_action( 'ocdi/before_content_import_execution', $this->selected_import_files, $this->import_files, $this->selected_index );
		}

		/**
		 * 3). Import content (if the content XML file is set for this import).
		 * Returns any errors greater then the "warning" logger level, that will be displayed on front page.
		 */
		if ( ! empty( $this->selected_import_files['content'] ) ) {
			$this->append_to_frontend_error_messages( $this->importer->import_content( $this->selected_import_files['content'] ) );
		}

		/**
		 * 4). Execute the actions hooked to the 'ocdi/after_content_import_execution' action:
		 *
		 * Default actions:
		 * 1 - Before widgets import setup (with priority 10).
		 * 2 - Import widgets (with priority 20).
		 * 3 - Import Redux data (with priority 30).
		 */
		Helpers::do_action( 'ocdi/after_content_import_execution', $this->selected_import_files, $this->import_files, $this->selected_index );

		// Save the import data as a transient, so other import parts (in new AJAX calls) can use that data.
		Helpers::set_ocdi_import_data_transient( $this->get_current_importer_data() );

		// Request the customizer import AJAX call.
		if ( ! empty( $this->selected_import_files['customizer'] ) ) {
			wp_send_json( array( 'status' => 'customizerAJAX' ) );
		}

		// Request the after all import AJAX call.
		if ( false !== Helpers::has_action( 'ocdi/after_all_import_execution' ) ) {
			wp_send_json( array( 'status' => 'afterAllImportAJAX' ) );
		}

		// Send a JSON response with final report.
		$this->final_response();
	}


	/**
	 * AJAX callback for importing the customizer data.
	 * This request has the wp_customize set to 'on', so that the customizer hooks can be called
	 * (they can only be called with the $wp_customize instance). But if the $wp_customize is defined,
	 * then the widgets do not import correctly, that's why the customizer import has its own AJAX call.
	 */
	public function import_customizer_data_ajax_callback() {
		// Verify if the AJAX call is valid (checks nonce and current_user_can).
		Helpers::verify_ajax_call();

		// Get existing import data.
		if ( $this->use_existing_importer_data() ) {
			/**
			 * Execute the customizer import actions.
			 *
			 * Default actions:
			 * 1 - Customizer import (with priority 10).
			 */
			Helpers::do_action( 'ocdi/customizer_import_execution', $this->selected_import_files );
		}

		// Request the after all import AJAX call.
		if ( false !== Helpers::has_action( 'ocdi/after_all_import_execution' ) ) {
			wp_send_json( array( 'status' => 'afterAllImportAJAX' ) );
		}

		// Send a JSON response with final report.
		$this->final_response();
	}


	/**
	 * AJAX callback for the after all import action.
	 */
	public function after_all_import_data_ajax_callback() {
		// Verify if the AJAX call is valid (checks nonce and current_user_can).
		Helpers::verify_ajax_call();

		// Get existing import data.
		if ( $this->use_existing_importer_data() ) {
			/**
			 * Execute the after all import actions.
			 *
			 * Default actions:
			 * 1 - after_import action (with priority 10).
			 */
			Helpers::do_action( 'ocdi/after_all_import_execution', $this->selected_import_files, $this->import_files, $this->selected_index );
		}

		// Send a JSON response with final report.
		$this->final_response();
	}


	/**
	 * Send a JSON response with final report.
	 */
	private function final_response() {
		// Delete importer data transient for current import.
		delete_transient( 'ocdi_importer_data' );

		// Display final messages (success or warning messages).
		$response['title'] = esc_html__( 'Import Complete!', 'one-click-demo-import' );
		$response['subtitle'] = '<p>' . esc_html__( 'Congrats, your demo was imported successfully. You can now begin editing your site.', 'one-click-demo-import' ) . '</p>';
		$response['message'] = '<img class="ocdi-imported-content-imported ocdi-imported-content-imported--success" src="' . esc_url( OCDI_URL . 'assets/images/success.svg' ) . '" alt="' . esc_attr__( 'Successful Import', 'one-click-demo-import' ) . '">';

		if ( ! empty( $this->frontend_error_messages ) ) {
			$response['subtitle'] = '<p>' . esc_html__( 'Your import completed, but some things may not have imported properly.', 'one-click-demo-import' ) . '</p>';
			$response['subtitle'] .= sprintf(
				wp_kses(
				/* translators: %s - link to the log file. */
					__( '<p><a href="%s" target="_blank">View error log</a> for more information.</p>', 'one-click-demo-import' ),
					array(
						'p'      => [],
						'a'      => [
							'href'   => [],
							'target' => [],
						],
					)
				),
				Helpers::get_log_url( $this->log_file_path )
			);

			$response['message'] = '<div class="notice notice-warning"><p>' . $this->frontend_error_messages_display() . '</p></div>';
		}

		wp_send_json( $response );
	}


	/**
	 * Get content importer data, so we can continue the import with this new AJAX request.
	 *
	 * @return boolean
	 */
	private function use_existing_importer_data() {
		if ( $data = get_transient( 'ocdi_importer_data' ) ) {
			$this->frontend_error_messages = empty( $data['frontend_error_messages'] ) ? array() : $data['frontend_error_messages'];
			$this->log_file_path           = empty( $data['log_file_path'] ) ? '' : $data['log_file_path'];
			$this->selected_index          = empty( $data['selected_index'] ) ? 0 : $data['selected_index'];
			$this->selected_import_files   = empty( $data['selected_import_files'] ) ? array() : $data['selected_import_files'];
			$this->import_files            = empty( $data['import_files'] ) ? array() : $data['import_files'];
			$this->before_import_executed  = empty( $data['before_import_executed'] ) ? false : $data['before_import_executed'];
			$this->importer->set_importer_data( $data );

			return true;
		}
		return false;
	}


	/**
	 * Get the current state of selected data.
	 *
	 * @return array
	 */
	public function get_current_importer_data() {
		return array(
			'frontend_error_messages' => $this->frontend_error_messages,
			'log_file_path'           => $this->log_file_path,
			'selected_index'          => $this->selected_index,
			'selected_import_files'   => $this->selected_import_files,
			'import_files'            => $this->import_files,
			'before_import_executed'  => $this->before_import_executed,
		);
	}


	/**
	 * Getter function to retrieve the private log_file_path value.
	 *
	 * @return string The log_file_path value.
	 */
	public function get_log_file_path() {
		return $this->log_file_path;
	}


	/**
	 * Setter function to append additional value to the private frontend_error_messages value.
	 *
	 * @param string $additional_value The additional value that will be appended to the existing frontend_error_messages.
	 */
	public function append_to_frontend_error_messages( $text ) {
		$lines = array();

		if ( ! empty( $text ) ) {
			$text = str_replace( '<br>', PHP_EOL, $text );
			$lines = explode( PHP_EOL, $text );
		}

		foreach ( $lines as $line ) {
			if ( ! empty( $line ) && ! in_array( $line , $this->frontend_error_messages ) ) {
				$this->frontend_error_messages[] = $line;
			}
		}
	}


	/**
	 * Display the frontend error messages.
	 *
	 * @return string Text with HTML markup.
	 */
	public function frontend_error_messages_display() {
		$output = '';

		if ( ! empty( $this->frontend_error_messages ) ) {
			foreach ( $this->frontend_error_messages as $line ) {
				$output .= esc_html( $line );
				$output .= '<br>';
			}
		}

		return $output;
	}


	/**
	 * Get data from filters, after the theme has loaded and instantiate the importer.
	 */
	public function setup_plugin_with_filter_data() {
		if ( ! ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) ) {
			return;
		}

		// Get info of import data files and filter it.
		$this->import_files = Helpers::validate_import_file_info( Helpers::apply_filters( 'ocdi/import_files', array() ) );

		/**
		 * Register all default actions (before content import, widget, customizer import and other actions)
		 * to the 'before_content_import_execution' and the 'ocdi/after_content_import_execution' action hook.
		 */
		$import_actions = new ImportActions();
		$import_actions->register_hooks();

		// Importer options array.
		$importer_options = Helpers::apply_filters( 'ocdi/importer_options', array(
			'fetch_attachments' => true,
		) );

		// Logger options for the logger used in the importer.
		$logger_options = Helpers::apply_filters( 'ocdi/logger_options', array(
			'logger_min_level' => 'warning',
		) );

		// Configure logger instance and set it to the importer.
		$logger            = new Logger();
		$logger->min_level = $logger_options['logger_min_level'];

		// Create importer instance with proper parameters.
		$this->importer = new Importer( $importer_options, $logger );

		// Prepare registered plugins and register AJAX callbacks.
		$this->plugin_installer = new PluginInstaller();
		$this->plugin_installer->init();

		// Prepare registered pre-created demo content pages and the AJAX callback.
		$demo_content_creator = new CreateDemoContent\DemoContentCreator();
		$demo_content_creator->init();
	}

	/**
	 * Getter for $plugin_page_setup.
	 *
	 * @return array
	 */
	public function get_plugin_page_setup() {
		return $this->plugin_page_setup;
	}

	/**
	 * Output the begining of the container div for all notices, but only on OCDI pages.
	 */
	public function start_notice_output_capturing() {
		$screen = get_current_screen();

		if ( false === strpos( $screen->base, $this->plugin_page_setup['menu_slug'] ) ) {
			return;
		}

		echo '<div class="ocdi-notices-wrapper js-ocdi-notice-wrapper">';
	}

	/**
	 * Output the ending of the container div for all notices, but only on OCDI pages.
	 */
	public function finish_notice_output_capturing() {
		if ( is_network_admin() ) {
			return;
		}

		$screen = get_current_screen();

		if ( false === strpos( $screen->base, $this->plugin_page_setup['menu_slug'] ) ) {
			return;
		}

		echo '</div><!-- /.ocdi-notices-wrapper -->';
	}

	/**
	 * Get the URL of the plugin settings page.
	 *
	 * @return string
	 */
	public function get_plugin_settings_url( $query_parameters = [] ) {
		if ( empty( $this->plugin_page_setup ) ) {
			$this->plugin_page_setup = Helpers::get_plugin_page_setup_data();
		}

		$parameters = array_merge(
			array( 'page' => $this->plugin_page_setup['menu_slug'] ),
			$query_parameters
		);

		$url = menu_page_url( $this->plugin_page_setup['parent_slug'], false );

		if ( empty( $url ) ) {
			$url = self_admin_url( $this->plugin_page_setup['parent_slug'] );
		}

		return add_query_arg( $parameters, $url );
	}

	/**
	 * Redirect from the old default OCDI settings page URL to the new one.
	 */
	public function redirect_from_old_default_admin_page() {
		global $pagenow;

		if ( $pagenow == 'themes.php' && isset( $_GET['page'] ) && $_GET['page'] == 'pt-one-click-demo-import' ) {
			wp_safe_redirect( $this->get_plugin_settings_url() );
			exit;
		}
	}
}
