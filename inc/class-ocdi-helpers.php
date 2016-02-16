<?php

/*
 * Class with static helper functions
 */

class OCDI_Helpers {

	/**
	 * Filter through the array of import files and get rid of those who do not comply
	 *
	 * @param $import_files, array, list of arrays with import file details
	 *
	 * @return array, list of filtered arrays
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
	 * @param $import_file_info, array, array with import file details
	 *
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
	 * @param $import_file_info, array, with import file details
	 *
	 * @return array, array of path to the downloaded files or echos an error with wp_die
	 */
	public static function download_import_files( $import_file_info ) {

		$downloaded_files = array();

		// Retrieve content from the URL
		$demo_import_content = self::get_content_from_url( $import_file_info['import_file_url'], $import_file_info['import_file_name'] );

		// Setup filename path to save the content
		$upload_dir            = wp_upload_dir();
		$upload_path           = apply_filters( 'pt-ocdi/upload_file_path', trailingslashit( $upload_dir['path'] ) );
		$demo_import_file_path = $upload_path . apply_filters( 'pt-ocdi/downloaded_import_file_prefix', 'demo-import-file_' ) . date( 'Y-m-d__H-i-s' ) . apply_filters( 'pt-ocdi/downloaded_import_file_suffix_and_file_extension', '.xml' );

		// Write content to the file and return the file path on successful write.
		$downloaded_files['data'] = self::write_to_file( $demo_import_content, $demo_import_file_path );

		// Get widgets file as well. If defined!
		if ( ! empty( $import_file_info['import_widget_file_url'] ) ) {

			$import_widgets_file_path    = $upload_path . apply_filters( 'pt-ocdi/downloaded_import_file_prefix', 'demo-import-file_' ) . date( 'Y-m-d__H-i-s' ) . apply_filters( 'pt-ocdi/downloaded_widgets_file_suffix_and_file_extension', '.json' );
			$demo_import_widgets_content = self::get_content_from_url( $import_file_info['import_widget_file_url'], $import_file_info['import_file_name'] );
			$downloaded_files['widgets'] = self::write_to_file( $demo_import_widgets_content, $import_widgets_file_path );

		}

		return $downloaded_files;

	}


	/**
	 * Write content to a file
	 *
	 * @param $content, content to be saved to the file
	 * @param $file_path, file path where the content should be saved
	 *
	 * @return string, path to the saved file or echos an error with wp_die
	 */
	public static function write_to_file( $content, $file_path ) {

		// Check if the filesystem method is 'direct', if not display an error
		if ( 'direct' === get_filesystem_method() ) {

			// Get user credentials for WP filesystem API
			$demo_import_page_url = wp_nonce_url( 'themes.php?page=pt-one-click-demo-import', 'pt-one-click-demo-import' );

			if ( false === ( $creds = request_filesystem_credentials( $demo_import_page_url, '', false, false, null ) ) ) {

				wp_die(
					sprintf(
						__( '%sAn error occurred while retrieving writing permissions to your server (could not retrieve WP filesystem credentials)!%s', 'pt-ocdi' ),
						'<div class="error"><p>',
						'</p></div>'
					)
				);

			}

			// Now we have credentials, try to get the wp_filesystem running
			if ( ! WP_Filesystem( $creds ) ) {

				wp_die(
					sprintf(
						__( '%sYour WordPress login credentials don\'t allow to use WP_Filesystem!%s', 'pt-ocdi' ),
						'<div class="error"><p>',
						'</p></div>'
					)
				);

			}


			// By this point, the $wp_filesystem global should be working, so let's use it to create a file
			global $wp_filesystem;

			if ( ! $wp_filesystem->put_contents( $file_path, $content, FS_CHMOD_FILE ) ) {

				wp_die(
					sprintf(
						__( '%sAn error occurred while writing file to your server! Tried file path was: %s. %s', 'pt-ocdi' ),
						'<div class="error"><p>',
						$file_path,
						'</p></div>'
					)
				);

			}
			else {

				return $file_path;

			}

		}
		else {

			wp_die(
				sprintf(
					__( '%sThis WordPress page does not have %sdirect%s write file access. This plugin needs it in order to save the demo import xml file to the upload directory of your site. You can change this setting with this instruction: %s.%s', 'pt-ocdi' ),
					'<div class="error"><p>',
					'<strong>',
					'</strong>',
					'<a href="http://gregorcapuder.com/wordpress-how-to-set-direct-filesystem-method/" target="_blank">How to set <strong>direct</strong> filesystem method</a>',
					'</p></div>'
				)
			);

		}

	}


	/**
	 * Helper function: get content from an url
	 *
	 * @param $url, URL to the content file
	 * @param $file_name, optional, name of the file (used in the error reports)
	 *
	 * @return string|boolean, path to the saved file or echos an error with wp_die and returns false
	 */
	private static function get_content_from_url( $url, $file_name = 'Import file' ) {

		// Test if the URL to the file is defined
		if ( empty( $url ) ) {

			wp_die(
				sprintf(
					__( '%sError occurred! URL for %s%s%s file is not defined!%s', 'pt-ocdi' ),
					'<div class="error"><p>',
					'<strong>',
					$file_name,
					'</strong>',
					'</p></div>'
				)
			);

		}

		// Get file content from the server
		$response = wp_remote_get(
			$url,
			array( 'timeout' => apply_filters( 'pt-ocdi/timeout_for_downloading_import_file', 20 ) )
		);

		if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {

			// collect the right format of error data (array or WP_Error)
			$response_error = self::get_error_from_response( $response );

			wp_die(
				sprintf(
					__( '%sAn error occurred while fetching %s%s%s file from the server!%sReason: %s - %s.%s', 'pt-ocdi' ),
					'<div class="error"><p>',
					'<strong>',
					$file_name,
					'</strong>',
					'</p><p>',
					$response_error['error_code'],
					$response_error['error_message'],
					'</p></div>'
				)
			);

		}
		else {

			// Return content retrieved from the URL
			return wp_remote_retrieve_body( $response );

		}

		return false;
	}


	/**
	 * Helper function: get the right format of response errors
	 *
	 * @param $response, array or WP_Error
	 *
	 * @return array, with error code and error message
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
	 * @param $file_path, file path where the content should be saved
	 *
	 * @return string $data, content of the file
	 */
	public static function data_from_file( $file_path ) {

		// Check if the filesystem method is 'direct', if not display an error
		if ( 'direct' === get_filesystem_method() ) {

			// Get user credentials for WP filesystem API
			$demo_import_page_url = wp_nonce_url( 'themes.php?page=pt-one-click-demo-import', 'pt-one-click-demo-import' );

			if ( false === ( $creds = request_filesystem_credentials( $demo_import_page_url, '', false, false, null ) ) ) {

				wp_die(
					sprintf(
						__( '%sAn error occurred while retrieving reading permissions to your server (could not retrieve WP filesystem credentials)!%s', 'pt-ocdi' ),
						'<div class="error"><p>',
						'</p></div>'
					)
				);

			}

			// Now we have credentials, try to get the wp_filesystem running
			if ( ! WP_Filesystem( $creds ) ) {

				wp_die(
					sprintf(
						__( '%sYour WordPress login credentials don\'t allow to use WP_Filesystem!%s', 'pt-ocdi' ),
						'<div class="error"><p>',
						'</p></div>'
					)
				);

			}


			// By this point, the $wp_filesystem global should be working, so let's use it to read a file
			global $wp_filesystem;

			$data = $wp_filesystem->get_contents( $file_path );

			if ( ! $data ) {

				wp_die(
					sprintf(
						__( '%sAn error occurred while reading a file from your server! Tried file path was: %s. %s', 'pt-ocdi' ),
						'<div class="error"><p>',
						$file_path,
						'</p></div>'
					)
				);

			}
			else {

				return $data;

			}
		}
		else {

			wp_die(
				sprintf(
					__( '%sThis WordPress page does not have %sdirect%s file access. This plugin needs it in order to read the demo import files from the upload directory of your site. You can change this setting with this instruction: %s.%s', 'pt-ocdi' ),
					'<div class="error"><p>',
					'<strong>',
					'</strong>',
					'<a href="http://gregorcapuder.com/wordpress-how-to-set-direct-filesystem-method/" target="_blank">How to set <strong>direct</strong> filesystem method</a>',
					'</p></div>'
				)
			);

		}

	}

}
