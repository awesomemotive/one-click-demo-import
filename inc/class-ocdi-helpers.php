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
	 * Download import file
	 *
	 * @param $import_file_info, array, with import file details
	 *
	 * @return string, path to the downloaded file or echos an error with wp_die
	 */
	public static function download_import_file( $import_file_info ) {
		// Test if the URL to the file is defined
		if ( empty( $import_file_info['import_file_url'] ) ) {
			wp_die(
				sprintf(
					__( '%sError occurred! URL for %s%s%s file is not defined!%s', 'pt-ocdi' ),
					'<div class="error"><p>',
					'<strong>',
					$import_file_info['import_file_name'],
					'</strong>',
					'</p></div>'
				)
			);
		}

		// Get file content from the server
		$response = wp_remote_get( $import_file_info['import_file_url'], array( 'timeout' => apply_filters( 'pt-ocdi/timeout_for_downloading_import_file', 20 ) ) );
		if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) {
			// collect the right format of error data (array or WP_Error)
			$response_error = array();

			if ( is_array( $response ) ) {
				$response_error['error_code']    = $response['response']['code'];
				$response_error['error_message'] = $response['response']['message'];
			}
			else {
				$response_error['error_code']    = $response->get_error_code();
				$response_error['error_message'] = $response->get_error_message();
			}

			wp_die(
				sprintf(
					__( '%sAn error occurred while fetching %s%s%s file from the server!%sReason: %s - %s.%s', 'pt-ocdi' ),
					'<div class="error"><p>',
					'<strong>',
					$import_file_info['import_file_name'],
					'</strong>',
					'</p><p>',
					$response_error['error_code'],
					$response_error['error_message'],
					'</p></div>'
				)
			);
		}
		else {
			$response_body = wp_remote_retrieve_body( $response );
		}

		// check if the filesystem method is 'direct', if not display an error
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
						__( '%sYour WordPress login credentials don\'t allow to run WP_Filesystem!%s', 'pt-ocdi' ),
						'<div class="error"><p>',
						'</p></div>'
					)
				);
			}

			// Setup filename path to save the content from
			$upload_dir = wp_upload_dir();

			$upload_path = apply_filters( 'pt-ocdi/upload_file_path', trailingslashit( $upload_dir['path'] ) );
			$filename = $upload_path . apply_filters( 'pt-ocdi/downloaded_import_file_prefix', 'demo-import-file_' ) . date( 'Y-m-d__H-i-s' ) . apply_filters( 'pt-ocdi/downloaded_import_file_suffix_and_file_extension', '.xml' );


			// By this point, the $wp_filesystem global should be working, so let's use it to create a file
			global $wp_filesystem;
			if ( ! $wp_filesystem->put_contents( $filename, $response_body, FS_CHMOD_FILE ) ) {
				wp_die(
					sprintf(
						__( '%sAn error occurred while writing %s%s%s file to your server! Tried file path was: %s. %s', 'pt-ocdi' ),
						'<div class="error"><p>',
						'<strong>',
						$import_file_info['import_file_name'],
						'</strong>',
						$filename,
						'</p></div>'
					)
				);
			}
			else {
				return $filename;
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

}