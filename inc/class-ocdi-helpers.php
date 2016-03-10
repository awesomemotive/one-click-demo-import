<?php
/**
 * Static functions used in the OCDI plugin
 *
 * @package ocdi
 */

/**
 * Class with static helper functions
 */
class OCDI_Helpers {

	/**
	 * Filter through the array of import files and get rid of those who do not comply
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
	 * A simple check for valid import file format
	 *
	 * @param  array $import_file_info array with import file details.
	 * @return boolean
	 */
	private static function is_import_file_info_format_correct( $import_file_info ) {

		if ( empty( $import_file_info['import_file_url'] ) || empty( $import_file_info['import_file_name'] ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Download import files. Content .xml and widgets .json files
	 *
	 * @param  array  $import_file_info array with import file details.
	 * @param  string $start_date string of date and time.
	 * @return array|WP_Error array of paths to the downloaded files or WP_Error object with error message.
	 */
	public static function download_import_files( $import_file_info, $start_date = '' ) {

		$downloaded_files = array();

		// Retrieve content from the URL.
		$demo_import_content = self::get_content_from_url( $import_file_info['import_file_url'], $import_file_info['import_file_name'] );

		// Return from this function if there was an error.
		if ( is_wp_error( $demo_import_content ) ) {
			return $demo_import_content;
		}

		// Setup filename path to save the content.
		$upload_dir            = wp_upload_dir();
		$upload_path           = apply_filters( 'pt-ocdi/upload_file_path', trailingslashit( $upload_dir['path'] ) );
		$demo_import_file_path = $upload_path . apply_filters( 'pt-ocdi/downloaded_import_file_prefix', 'demo-import-file_' ) . $start_date . apply_filters( 'pt-ocdi/downloaded_import_file_suffix_and_file_extension', '.xml' );

		// Write content to the file and return the file path on successful write.
		$downloaded_files['data'] = self::write_to_file( $demo_import_content, $demo_import_file_path );

		// Return from this function if there was an error.
		if ( is_wp_error( $downloaded_files['data'] ) ) {
			return $downloaded_files['data'];
		}

		// Get widgets file as well. If defined!
		if ( ! empty( $import_file_info['import_widget_file_url'] ) ) {
			$import_widgets_file_path    = $upload_path . apply_filters( 'pt-ocdi/downloaded_import_file_prefix', 'demo-import-file_' ) . date( 'Y-m-d__H-i-s' ) . apply_filters( 'pt-ocdi/downloaded_widgets_file_suffix_and_file_extension', '.json' );
			$demo_import_widgets_content = self::get_content_from_url( $import_file_info['import_widget_file_url'], $import_file_info['import_file_name'] );

			// Return from this function if there was an error.
			if ( is_wp_error( $demo_import_widgets_content ) ) {
				return $demo_import_widgets_content;
			}

			$downloaded_files['widgets'] = self::write_to_file( $demo_import_widgets_content, $import_widgets_file_path );

			// Return from this function if there was an error.
			if ( is_wp_error( $downloaded_files['widgets'] ) ) {
				return $downloaded_files['widgets'];
			}
		}

		return $downloaded_files;
	}


	/**
	 * Write content to a file
	 *
	 * @param string $content content to be saved to the file.
	 * @param string $file_path file path where the content should be saved.
	 * @return string|WP_Error path to the saved file or WP_Error object with error message.
	 */
	public static function write_to_file( $content, $file_path ) {

		// Check if the filesystem method is 'direct', if not display an error.
		if ( 'direct' === get_filesystem_method() ) {

			// Get user credentials for WP filesystem API.
			$demo_import_page_url = wp_nonce_url( 'themes.php?page=pt-one-click-demo-import', 'pt-one-click-demo-import' );

			if ( false === ( $creds = request_filesystem_credentials( $demo_import_page_url, '', false, false, null ) ) ) {
				return new WP_error(
					'filesystem_credentials_could_not_be_retrieved',
					__( 'An error occurred while retrieving writing permissions to your server (could not retrieve WP filesystem credentials)!', 'pt-ocdi' )
				);
			}

			// Now we have credentials, try to get the wp_filesystem running.
			if ( ! WP_Filesystem( $creds ) ) {
				return new WP_Error(
					'wrong_login_credentials',
					__( 'Your WordPress login credentials don\'t allow to use WP_Filesystem!', 'pt-ocdi' )
				);
			}

			// By this point, the $wp_filesystem global should be working, so let's use it to create a file.
			global $wp_filesystem;

			if ( ! $wp_filesystem->put_contents( $file_path, $content, FS_CHMOD_FILE ) ) {
				return new WP_Error(
					'failed_writing_file_to_server',
					sprintf(
						__( 'An error occurred while writing file to your server! Tried to write a file to: %s%s.', 'pt-ocdi' ),
						'<br>',
						$file_path
					)
				);
			}
			else {

				// Return the file path on successfull file write.
				return $file_path;
			}
		}
		else {
			return new WP_Error(
				'no_direct_file_write_access',
				sprintf(
					__( 'This WordPress page does not have %sdirect%s write file access. This plugin needs it in order to save the demo import xml file to the upload directory of your site. You can change this setting with these instructions: %s.', 'pt-ocdi' ),
					'<strong>',
					'</strong>',
					'<a href="http://gregorcapuder.com/wordpress-how-to-set-direct-filesystem-method/" target="_blank">How to set <strong>direct</strong> filesystem method</a>'
				)
			);
		}
	}


	/**
	 * Append content to the file
	 *
	 * @param string $content content to be saved to the file.
	 * @param string $file_path file path where the content should be saved.
	 * @param string $separator separates the existing content of the file with the new content.
	 * @return boolean|WP_Error, path to the saved file or WP_Error object with error message.
	 */
	public static function append_to_file( $content, $file_path, $separator = '' ) {

		// Check if the filesystem method is 'direct', if not display an error.
		if ( 'direct' === get_filesystem_method() ) {

			// Get user credentials for WP filesystem API.
			$demo_import_page_url = wp_nonce_url( 'themes.php?page=pt-one-click-demo-import', 'pt-one-click-demo-import' );

			if ( false === ( $creds = request_filesystem_credentials( $demo_import_page_url, '', false, false, null ) ) ) {
				return new WP_error(
					'filesystem_credentials_could_not_be_retrieved',
					__( 'An error occurred while retrieving writing permissions to your server (could not retrieve WP filesystem credentials)!', 'pt-ocdi' )
				);
			}

			// Now we have credentials, try to get the wp_filesystem running.
			if ( ! WP_Filesystem( $creds ) ) {
				return new WP_Error(
					'wrong_login_credentials',
					__( 'Your WordPress login credentials don\'t allow to use WP_Filesystem!', 'pt-ocdi' )
				);
			}

			// By this point, the $wp_filesystem global should be working, so let's use it to create a file.
			global $wp_filesystem;

			$existing_data = $wp_filesystem->get_contents( $file_path );

			if ( ! $wp_filesystem->put_contents( $file_path, $existing_data . $separator . $content, FS_CHMOD_FILE ) ) {
				return new WP_Error(
					'failed_writing_file_to_server',
					sprintf(
						__( 'An error occurred while writing file to your server! Tried to write a file to: %s%s.', 'pt-ocdi' ),
						'<br>',
						$file_path
					)
				);
			}
			else {

				// Return the file path on successfull file write.
				return true;
			}
		}
		else {
			return new WP_Error(
				'no_direct_file_write_access',
				sprintf(
					__( 'This WordPress page does not have %sdirect%s write file access. This plugin needs it in order to save the demo import xml file to the upload directory of your site. You can change this setting with these instructions: %s.', 'pt-ocdi' ),
					'<strong>',
					'</strong>',
					'<a href="http://gregorcapuder.com/wordpress-how-to-set-direct-filesystem-method/" target="_blank">How to set <strong>direct</strong> filesystem method</a>'
				)
			);
		}
	}


	/**
	 * Helper function: get content from an url
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
					__( 'Error occurred! URL for %s%s%s file is not defined!', 'pt-ocdi' ),
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
				'while_fetching_error',
				sprintf(
					__( 'An error occurred while fetching %s%s%s file from the server!%sReason: %s - %s.', 'pt-ocdi' ),
					'<strong>',
					$file_name,
					'</strong>',
					'<br>',
					$response_error['error_code'],
					$response_error['error_message']
				)
			);
		}
		else {

			// Return content retrieved from the URL.
			return wp_remote_retrieve_body( $response );
		}
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
	 * Get data from a file
	 *
	 * @param string $file_path file path where the content should be saved.
	 * @return string $data, content of the file or WP_Error object with error message.
	 */
	public static function data_from_file( $file_path ) {

		// Check if the filesystem method is 'direct', if not display an error.
		if ( 'direct' === get_filesystem_method() ) {

			// Get user credentials for WP filesystem API.
			$demo_import_page_url = wp_nonce_url( 'themes.php?page=pt-one-click-demo-import', 'pt-one-click-demo-import' );

			if ( false === ( $creds = request_filesystem_credentials( $demo_import_page_url, '', false, false, null ) ) ) {
				return new WP_Error(
					'filesystem_credentials_could_not_be_retrieved',
					__( 'An error occurred while retrieving reading permissions to your server (could not retrieve WP filesystem credentials)!', 'pt-ocdi' )
				);
			}

			// Now we have credentials, try to get the wp_filesystem running.
			if ( ! WP_Filesystem( $creds ) ) {
				return new WP_Error(
					'wrong_login_credentials',
					__( 'Your WordPress login credentials don\'t allow to use WP_Filesystem!', 'pt-ocdi' )
				);
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
			else {
				return $data;
			}
		}
		else {
			return new WP_Error(
				'no_direct_file_access',
				sprintf(
					__( 'This WordPress page does not have %sdirect%s file access. This plugin needs it in order to read the demo import files from the upload directory of your site. You can change this setting with these instructions: %s.', 'pt-ocdi' ),
					'<strong>',
					'</strong>',
					'<a href="http://gregorcapuder.com/wordpress-how-to-set-direct-filesystem-method/" target="_blank">How to set <strong>direct</strong> filesystem method</a>'
				)
			);
		}
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

		return $upload_path . apply_filters( 'pt-ocdi/log_file_prefix', 'log_file_' ) . $start_date . apply_filters( 'pt-ocdi/log_file_suffix_and_file_extension', '.txt' );
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
}
