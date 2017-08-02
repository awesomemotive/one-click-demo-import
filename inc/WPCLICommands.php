<?php
/**
 * The class for WP-CLI commands for One Click Demo Import plugin.
 *
 * @package ocdi
 */

namespace OCDI;

use WP_CLI;

class WPCLICommands extends \WP_CLI_Command {

	/**
	 * List all predefined demos.
	 */
	public function list( $args, $assoc_args ) {
		$ocdi = OneClickDemoImport::get_instance();

		if ( empty( $ocdi->import_files ) ) {
			WP_CLI::error( 'There are no predefined demo imports for currently active theme!' );
		}

		WP_CLI::success( 'Here are the predefined demo imports:' );

		foreach ( $ocdi->import_files as $index => $import_file ) {
			WP_CLI::log( sprintf(
				'%d -> %s [content: %s, widgets: %s, customizer: %s, redux: %s]',
				$index,
				$import_file['import_file_name'],
				empty( $import_file['import_file_url'] ) && empty( $import_file['local_import_file'] ) ? 'no' : 'yes',
				empty( $import_file['import_widget_file_url'] ) && empty( $import_file['local_import_widget_file'] ) ? 'no' : 'yes',
				empty( $import_file['import_customizer_file_url'] ) && empty( $import_file['local_import_customizer_file'] ) ? 'no' : 'yes',
				empty( $import_file['import_redux'] ) && empty( $import_file['local_import_redux'] ) ? 'no' : 'yes'
			) );
		}
	}


}
