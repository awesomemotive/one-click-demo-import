<?php
/**
 * Static functions used in the OCDI plugin.
 *
 * @package ocdi
 */

/**
 * Class with static helper functions.
 */
class OCDI_Helpers {

	/**
	 * Filter through the array of import files and get rid of those who do not comply.
	 *
	 * @param  array $import_files list of arrays with import file details.
	 * @return array list of filtered arrays.
	 */
	public static function validate_import_file_info( $import_files ) {
		$filtered_import_file_info = array();

		foreach ( $import_files as $import_file ) {
			if ( self::is_import_file_info_format_correct( $import_file ) ) {
				$filtered_import_file_info[] = $import_file;
			}
		}

		return $filtered_import_file_info;
	}


	/**
	 * Helper function: a simple check for valid import file format.
	 *
	 * @param  array $import_file_info array with import file details.
	 * @return boolean
	 */
	private static function is_import_file_info_format_correct( $import_file_info ) {
		if ( ( empty( $import_file_info['import_file_url'] ) && empty( $import_file_info['local_import_file'] ) ) || empty( $import_file_info['import_file_name'] ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Download import files. Content .xml and widgets .wie|.json files.
	 *
	 * @param  array  $import_file_info array with import file details.
	 * @param  string $start_date string of date and time.
	 * @return array|WP_Error array of paths to the downloaded files or WP_Error object with error message.
	 */
	public static function download_import_files( $import_file_info, $start_date = '' ) {

		$downloaded_files = array();
		$upload_dir       = wp_upload_dir();
		$upload_path      = apply_filters( 'pt-ocdi/upload_file_path', trailingslashit( $upload_dir['path'] ) );

		// ----- Set content file path -----
		// Check if 'import_file_url' is not defined. That would mean a local file.
		if ( empty( $import_file_info['import_file_url'] ) ) {
			if ( file_exists( $import_file_info['local_import_file'] ) ) {
				$downloaded_files['content'] = $import_file_info['local_import_file'];
			}
			else {
				return new WP_Error(
					'url_or_local_file_not_defined',
					sprintf(
						__( '"import_file_url" or "local_import_file" for %s%s%s are not defined!', 'pt-ocdi' ),
						'<strong>',
						$$import_file_info['import_file_name'],
						'</strong>'
					)
				);
			}
		}
		else {

			// Retrieve demo data content from the URL.
			$demo_import_content = self::get_content_from_url( $import_file_info['import_file_url'], $import_file_info['import_file_name'] );

			// Return from this function if there was an error.
			if ( is_wp_error( $demo_import_content ) ) {
				return $demo_import_content;
			}

			// Setup filename path to save the data content.
			$demo_import_file_path = $upload_path . apply_filters( 'pt-ocdi/downloaded_content_file_prefix', 'demo-content-import-file_' ) . $start_date . apply_filters( 'pt-ocdi/downloaded_content_file_suffix_and_file_extension', '.xml' );

			// Write data content to the file and return the file path on successful write.
			$downloaded_files['content'] = self::write_to_file( $demo_import_content, $demo_import_file_path );

			// Return from this function if there was an error.
			if ( is_wp_error( $downloaded_files['content'] ) ) {
				return $downloaded_files['content'];
			}
		}

		// ----- Set widget file path -----
		// Get widgets file as well. If defined!
		if ( ! empty( $import_file_info['import_widget_file_url'] ) ) {

			// Retrieve widget content from the URL.
			$demo_import_widgets_content = self::get_content_from_url( $import_file_info['import_widget_file_url'], $import_file_info['import_file_name'] );

			// Return from this function if there was an error.
			if ( is_wp_error( $demo_import_widgets_content ) ) {
				return $demo_import_widgets_content;
			}

			// Setup filename path to save the widget content.
			$import_widgets_file_path = $upload_path . apply_filters( 'pt-ocdi/downloaded_widgets_file_prefix', 'demo-widgets-import-file_' ) . $start_date . apply_filters( 'pt-ocdi/downloaded_widgets_file_suffix_and_file_extension', '.json' );

			// Write widget content to the file and return the file path on successful write.
			$downloaded_files['widgets'] = self::write_to_file( $demo_import_widgets_content, $import_widgets_file_path );

			// Return from this function if there was an error.
			if ( is_wp_error( $downloaded_files['widgets'] ) ) {
				return $downloaded_files['widgets'];
			}
		}
		else if ( ! empty( $import_file_info['local_import_widget_file'] ) ) {
			if ( file_exists( $import_file_info['local_import_widget_file'] ) ) {
				$downloaded_files['widgets'] = $import_file_info['local_import_widget_file'];
			}
		}

		// ----- Set customizer file path -----
		// Get customizer import file as well. If defined!
		if ( ! empty( $import_file_info['import_customizer_file_url'] ) ) {

			// Retrieve customizer content from the URL.
			$demo_import_customizer_content = self::get_content_from_url( $import_file_info['import_customizer_file_url'], $import_file_info['import_file_name'] );

			// Return from this function if there was an error.
			if ( is_wp_error( $demo_import_customizer_content ) ) {
				return $demo_import_customizer_content;
			}

			// Setup filename path to save the customizer content.
			$import_customizer_file_path = $upload_path . apply_filters( 'pt-ocdi/downloaded_customizer_file_prefix', 'demo-customizer-import-file_' ) . $start_date . apply_filters( 'pt-ocdi/downloaded_customizer_file_suffix_and_file_extension', '.dat' );

			// Write customizer content to the file and return the file path on successful write.
			$downloaded_files['customizer'] = self::write_to_file( $demo_import_customizer_content, $import_customizer_file_path );

			// Return from this function if there was an error.
			if ( is_wp_error( $downloaded_files['customizer'] ) ) {
				return $downloaded_files['customizer'];
			}
		}
		else if ( ! empty( $import_file_info['local_import_customizer_file'] ) ) {
			if ( file_exists( $import_file_info['local_import_customizer_file'] ) ) {
				$downloaded_files['customizer'] = $import_file_info['local_import_customizer_file'];
			}
		}

		return $downloaded_files;
	}


	/**
	 * Helper function: get content from an url.
	 *
	 * @param string $url URL to the content file.
	 * @param string $file_name optional, name of the file (used in the error reports).
	 * @return string|WP_Error, content from the URL or WP_Error object with error message
	 */
	private static function get_content_from_url( $url, $file_name = 'Import file' ) {

		// Test if the URL to the file is defined.
		if ( empty( $url ) ) {
			return new WP_Error(
				'url_not_defined',
				sprintf(
					__( 'URL for %s%s%s file is not defined!', 'pt-ocdi' ),
					'<strong>',
					$file_name,
					'</strong>'
				)
			);
		}

		// Get file content from the server.
		$response = wp_remote_get(
			$url,
			array( 'timeout' => apply_filters( 'pt-ocdi/timeout_for_downloading_import_file', 20 ) )
		);

		if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {

			// Collect the right format of error data (array or WP_Error).
			$response_error = self::get_error_from_response( $response );

			return new WP_Error(
				'file_fetching_error',
				sprintf(
					__( 'An error occurred while fetching %s%s%s file from the server!%sReason: %s - %s.', 'pt-ocdi' ),
					'<strong>',
					$file_name,
					'</strong>',
					'<br>',
					$response_error['error_code'],
					$response_error['error_message']
				) . '<br>' .
				apply_filters( 'pt-ocdi/message_after_file_fetching_error', '' )
			);
		}

		// Return content retrieved from the URL.
		return wp_remote_retrieve_body( $response );
	}


	/**
	 * Write content to a file.
	 *
	 * @param string $content content to be saved to the file.
	 * @param string $file_path file path where the content should be saved.
	 * @return string|WP_Error path to the saved file or WP_Error object with error message.
	 */
	public static function write_to_file( $content, $file_path ) {

		// Verify WP file-system credentials.
		$verified_credentials = self::check_wp_filesystem_credentials();

		if ( is_wp_error( $verified_credentials ) ) {
			return $verified_credentials;
		}

		// By this point, the $wp_filesystem global should be working, so let's use it to create a file.
		global $wp_filesystem;

		if ( ! $wp_filesystem->put_contents( $file_path, $content ) ) {
			return new WP_Error(
				'failed_writing_file_to_server',
				sprintf(
					__( 'An error occurred while writing file to your server! Tried to write a file to: %s%s.', 'pt-ocdi' ),
					'<br>',
					$file_path
				)
			);
		}

		// Return the file path on successful file write.
		return $file_path;
	}


	/**
	 * Append content to the file.
	 *
	 * @param string $content content to be saved to the file.
	 * @param string $file_path file path where the content should be saved.
	 * @param string $separator_text separates the existing content of the file with the new content.
	 * @return boolean|WP_Error, path to the saved file or WP_Error object with error message.
	 */
	public static function append_to_file( $content, $file_path, $separator_text = '' ) {

		// Verify WP file-system credentials.
		$verified_credentials = self::check_wp_filesystem_credentials();

		if ( is_wp_error( $verified_credentials ) ) {
			return $verified_credentials;
		}

		// By this point, the $wp_filesystem global should be working, so let's use it to create a file.
		global $wp_filesystem;

		$existing_data = '';
		if ( file_exists( $file_path ) ) {
			$existing_data = $wp_filesystem->get_contents( $file_path );
		}

		// Style separator.
		$separator = PHP_EOL . '---' . $separator_text . '---' . PHP_EOL;

		if ( ! $wp_filesystem->put_contents( $file_path, $existing_data . $separator . $content . PHP_EOL ) ) {
			return new WP_Error(
				'failed_writing_file_to_server',
				sprintf(
					__( 'An error occurred while writing file to your server! Tried to write a file to: %s%s.', 'pt-ocdi' ),
					'<br>',
					$file_path
				)
			);
		}

		return true;
	}


	/**
	 * Get data from a file
	 *
	 * @param string $file_path file path where the content should be saved.
	 * @return string $data, content of the file or WP_Error object with error message.
	 */
	public static function data_from_file( $file_path ) {

		// Verify WP file-system credentials.
		$verified_credentials = self::check_wp_filesystem_credentials();

		if ( is_wp_error( $verified_credentials ) ) {
			return $verified_credentials;
		}

		// By this point, the $wp_filesystem global should be working, so let's use it to read a file.
		global $wp_filesystem;

		$data = $wp_filesystem->get_contents( $file_path );

		if ( ! $data ) {
			return new WP_Error(
				'failed_reading_file_from_server',
				sprintf(
					__( 'An error occurred while reading a file from your server! Tried reading file from path: %s%s.', 'pt-ocdi' ),
					'<br>',
					$file_path
				)
			);
		}

		// Return the file data.
		return $data;
	}


	/**
	 * Helper function: check for WP file-system credentials needed for reading and writing to a file.
	 *
	 * @return boolean|WP_Error
	 */
	private static function check_wp_filesystem_credentials() {

		// Check if the file-system method is 'direct', if not display an error.
		if ( ! ( 'direct' === get_filesystem_method() ) ) {
			return new WP_Error(
				'no_direct_file_access',
				sprintf(
					__( 'This WordPress page does not have %sdirect%s write file access. This plugin needs it in order to save the demo import xml file to the upload directory of your site. You can change this setting with these instructions: %s.', 'pt-ocdi' ),
					'<strong>',
					'</strong>',
					'<a href="http://gregorcapuder.com/wordpress-how-to-set-direct-filesystem-method/" target="_blank">How to set <strong>direct</strong> filesystem method</a>'
				)
			);
		}

		// Get plugin page settings.
		$plugin_page_setup = apply_filters( 'pt-ocdi/plugin_page_setup', array(
				'parent_slug' => 'themes.php',
				'page_title'  => esc_html__( 'One Click Demo Import' , 'pt-ocdi' ),
				'menu_title'  => esc_html__( 'Import Demo Data' , 'pt-ocdi' ),
				'capability'  => 'import',
				'menu_slug'   => 'pt-one-click-demo-import',
			)
		);

		// Get user credentials for WP file-system API.
		$demo_import_page_url = wp_nonce_url( $plugin_page_setup['parent_slug'] . '?page=' . $plugin_page_setup['menu_slug'], $plugin_page_setup['menu_slug'] );

		if ( false === ( $creds = request_filesystem_credentials( $demo_import_page_url, '', false, false, null ) ) ) {
			return new WP_error(
				'filesystem_credentials_could_not_be_retrieved',
				__( 'An error occurred while retrieving reading/writing permissions to your server (could not retrieve WP filesystem credentials)!', 'pt-ocdi' )
			);
		}

		// Now we have credentials, try to get the wp_filesystem running.
		if ( ! WP_Filesystem( $creds ) ) {
			return new WP_Error(
				'wrong_login_credentials',
				__( 'Your WordPress login credentials don\'t allow to use WP_Filesystem!', 'pt-ocdi' )
			);
		}

		return true;
	}


	/**
	 * Helper function: get the right format of response errors
	 *
	 * @param array|WP_Error $response array or WP_Error.
	 * @return array, with error code and error message.
	 */
	private static function get_error_from_response( $response ) {
		$response_error = array();

		if ( is_array( $response ) ) {
			$response_error['error_code']    = $response['response']['code'];
			$response_error['error_message'] = $response['response']['message'];
		}
		else {
			$response_error['error_code']    = $response->get_error_code();
			$response_error['error_message'] = $response->get_error_message();
		}

		return $response_error;
	}


	/**
	 * Get log file path
	 *
	 * @param string $start_date date|time|timestamp to use in the log filename.
	 * @return string, path to the log file
	 */
	public static function get_log_path( $start_date = '' ) {

		$upload_dir  = wp_upload_dir();
		$upload_path = apply_filters( 'pt-ocdi/upload_file_path', trailingslashit( $upload_dir['path'] ) );

		$log_path = $upload_path . apply_filters( 'pt-ocdi/log_file_prefix', 'log_file_' ) . $start_date . apply_filters( 'pt-ocdi/log_file_suffix_and_file_extension', '.txt' );

		self::register_file_as_media_attachment( $log_path );

		return $log_path;
	}


	/**
	 * Register file as attachment to the Media page.
	 *
	 * @param string $log_path log file path.
	 * @return void
	 */
	public static function register_file_as_media_attachment( $log_path ) {

		// Check the type of file.
		$log_mimes = array( 'txt' => 'text/plain' );
		$filetype  = wp_check_filetype( basename( $log_path ), apply_filters( 'pt-ocdi/file_mimes', $log_mimes ) );

		// Prepare an array of post data for the attachment.
		$attachment = array(
			'guid'           => self::get_log_url( $log_path ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => apply_filters( 'pt-ocdi/attachment_prefix', esc_html__( 'One Click Demo Import - ', 'pt-ocdi' ) ) . preg_replace( '/\.[^.]+$/', '', basename( $log_path ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		// Insert the file as attachment in Media page.
		$attach_id = wp_insert_attachment( $attachment, $log_path );
	}


	/**
	 * Get log file url
	 *
	 * @param string $log_path log path to use for the log filename.
	 * @return string, url to the log file.
	 */
	public static function get_log_url( $log_path ) {

		$upload_dir = wp_upload_dir();
		$upload_url = apply_filters( 'pt-ocdi/upload_file_url', trailingslashit( $upload_dir['url'] ) );

		return $upload_url . basename( $log_path );
	}


	/**
	 * Check if the AJAX call is valid.
	 */
	public static function verify_ajax_call() {

		check_ajax_referer( 'ocdi-ajax-verification', 'security' );

		// Check if user has the WP capability to import data.
		if ( ! current_user_can( 'import' ) ) {
			wp_die(
				sprintf(
					__( '%sYour user role isn\'t high enough. You don\'t have permission to import demo data.%s', 'pt-ocdi' ),
					'<div class="notice  notice-error"><p>',
					'</p></div>'
				)
			);
		}
	}


	/**
	 * Process uploaded files and return the paths to these files.
	 *
	 * @param array  $uploaded_files $_FILES array form an AJAX request.
	 * @param string $log_file_path path to the log file.
	 * @return array of paths to the content import and widget import files.
	 */
	public static function process_uploaded_files( $uploaded_files, $log_file_path ) {

		// Variable holding the paths to the uploaded files.
		$selected_import_files = array();

		// Upload settings to disable form and type testing for AJAX uploads.
		$upload_overrides = array(
			'test_form' => false,
			'test_type' => false,
		);

		// Handle demo content and widgets file upload.
		$content_file_info     = wp_handle_upload( $_FILES['content_file'], $upload_overrides );
		$widget_file_info      = wp_handle_upload( $_FILES['widget_file'], $upload_overrides );
		$customizer_file_info  = wp_handle_upload( $_FILES['customizer_file'], $upload_overrides );

		if ( empty( $content_file_info['file'] ) || isset( $content_file_info['error'] ) ) {

			// Write error to log file and send an AJAX response with the error.
			self::log_error_and_send_ajax_response(
				__( 'Please upload XML file for content import. If you want to import widgets or customizer settings only, please use Widget Importer & Exporter or the Customizer Export/Import plugin.', 'pt-ocdi' ),
				$log_file_path,
				esc_html__( 'Upload files', 'pt-ocdi' )
			);
		}

		// Set uploaded content file.
		$selected_import_files['content'] = $content_file_info['file'];

		// Process widget import file.
		if ( $widget_file_info && ! isset( $widget_file_info['error'] ) ) {

			// Set uploaded widget file.
			$selected_import_files['widgets'] = $widget_file_info['file'];
		}
		else {

			// Add this error to log file.
			$log_added = self::append_to_file(
				sprintf(
					__( 'Widget file was not uploaded. Error: %s', 'pt-ocdi' ),
					$widget_file_info['error']
				),
				$log_file_path,
				esc_html__( 'Upload files' , 'pt-ocdi' )
			);
		}

		// Process Customizer import file.
		if ( $customizer_file_info && ! isset( $customizer_file_info['error'] ) ) {

			// Set uploaded widget file.
			$selected_import_files['customizer'] = $customizer_file_info['file'];
		}
		else {

			// Add this error to log file.
			$log_added = self::append_to_file(
				sprintf(
					__( 'Customizer file was not uploaded. Error: %s', 'pt-ocdi' ),
					$customizer_file_info['error']
				),
				$log_file_path,
				esc_html__( 'Upload files' , 'pt-ocdi' )
			);
		}

		// Add this message to log file.
		$log_added = self::append_to_file(
			__( 'The import files were successfully uploaded!', 'pt-ocdi' ) . self::import_file_info( $selected_import_files ),
			$log_file_path,
			esc_html__( 'Upload files' , 'pt-ocdi' )
		);

		// Return array with paths of uploaded files.
		return $selected_import_files;
	}


	/**
	 * Get import file information and max execution time.
	 *
	 * @param array $selected_import_files array of selected import files.
	 */
	public static function import_file_info( $selected_import_files ) {
		return PHP_EOL .
		sprintf(
			__( 'Initial max execution time = %s', 'pt-ocdi' ),
			ini_get( 'max_execution_time' )
		) . PHP_EOL .
		sprintf(
			__( 'Files info:%1$sSite URL = %2$s%1$sData file = %3$s%1$sWidget file = %4$s%1$sCustomizer file = %5$s', 'pt-ocdi' ),
			PHP_EOL,
			get_site_url(),
			$selected_import_files['content'],
			empty( $selected_import_files['widgets'] ) ? esc_html__( 'not defined!', 'pt-ocdi' ) : $selected_import_files['widgets'],
			empty( $selected_import_files['customizer'] ) ? esc_html__( 'not defined!', 'pt-ocdi' ) : $selected_import_files['customizer']
		);
	}


	/**
	 * Write the error to the log file and send the AJAX response.
	 *
	 * @param string $error_text text to display in the log file and in the AJAX response.
	 * @param string $log_file_path path to the log file.
	 * @param string $separator title separating the old and new content.
	 */
	public static function log_error_and_send_ajax_response( $error_text, $log_file_path, $separator = '' ) {

		// Add this error to log file.
		$log_added = self::append_to_file(
			$error_text,
			$log_file_path,
			$separator
		);

		// Send JSON Error response to the AJAX call.
		wp_send_json( $error_text );
	}
}
