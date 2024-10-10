<?php
/**
 * Class for the Redux importer used in the One Click Demo Import plugin.
 *
 * @see https://wordpress.org/plugins/wpforms-lite/
 * @package ocdi
 */

namespace OCDI;

class WPFormsImporter {

	/**
	 * The path to the import file.
	 *
	 * @since 3.3.0
	 *
	 * @var string
	 */
	private $import_file_path = false;

	/**
	 * The OneClickDemoImport instance.
	 *
	 * @since 3.3.0
	 *
	 * @var OneClickDemoImport
	 */
	private $ocdi;

	/**
	 * Constructor.
	 *
	 * @since 3.3.0
	 *
	 * @param string $import_file_path The path to the import file.
	 */
	public function __construct( $import_file_path ) {

		$this->import_file_path = $import_file_path;
		$this->ocdi             = OneClickDemoImport::get_instance();
	}

	/**
	 * Import WPForms data.
	 *
	 * @since 3.3.0
	 */
	public function import() {

		// WPForms plugin is not active!
		if ( ! class_exists( 'WPForms' ) || ! function_exists( 'wpforms' )  ) {
			$this->log_error( esc_html__( 'The WPForms plugin is not activated, so the WPForms import was skipped!', 'one-click-demo-import' ) );
			return;
		}

		$wpforms_api = method_exists( wpforms(), 'obj' ) ? wpforms()->obj( 'api' ) : wpforms()->get("api");

		if ( ! is_a( $wpforms_api, "WPForms\API" ) ) {
			$this->log_error( esc_html__( 'The WPForms plugin\'s version is not >= v1.8.6, so the WPForms import was skipped!', 'one-click-demo-import' ) );
			return;
		}

		$import = $wpforms_api->import_forms( $this->import_file_path );

		if ( is_wp_error( $import ) ) {
			$this->log_error( sprintf( 'WPForms import failed: %1$s', $import->get_error_message() ) );
			return;
		}

		Helpers::append_to_file(
			esc_html__( 'WPForms import finished successfully!', 'one-click-demo-import' ),
			$this->ocdi->get_log_file_path(),
			esc_html__( 'Importing WPForms' , 'one-click-demo-import' )
		);
	}

	/**
	 * Log error message.
	 *
	 * @since 3.3.0
	 *
	 * @param string $error_message The error message.
	 */
	private function log_error( $error_message ) {

		// Add any error messages to the frontend_error_messages variable in OCDI main class.
		$this->ocdi->append_to_frontend_error_messages( $error_message );

		// Write error to log file.
		Helpers::append_to_file(
			$error_message,
			$this->ocdi->get_log_file_path(),
			esc_html__( 'Importing WPForms' , 'one-click-demo-import' )
		);
	}
}
