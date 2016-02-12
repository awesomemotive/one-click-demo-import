<?php

// Include required files
require PT_OCDI_PATH . 'vendor/humanmade/WordPress-Importer/class-logger.php';
require PT_OCDI_PATH . 'vendor/humanmade/WordPress-Importer/class-logger-cli.php';

class OCDI_Logger extends WP_Importer_Logger_CLI {
	public $log_messages = array();

	/**
	 * Overwritten log function from WP_Importer_Logger_CLI for better formating.
	 *
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function log( $level, $message, array $context = array() ) {
		// Save log messages in an array
		$this->save_logs( $level, $message, $context = array() );

		if ( $this->level_to_numeric( $level ) < $this->level_to_numeric( $this->min_level ) ) {
			return;
		}

		printf(
			'[%s] %s' . '<br>',
			strtoupper( $level ),
			$message
		);
	}


	/**
	 * Log messages in an array
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function save_logs( $level, $message, array $context = array() ) {
		$this->log_messages[] = array(
			'timestamp' => time(),
			'level'     => $level,
			'message'   => $message,
			'context'   => $context,
		);
	}


	/**
	 * Format all log messages, prepare for log file output
	 *
	 * @return string, formated log messages
	 */
	private function format_all_log_messages( ) {
		ob_start();
			foreach ($this->log_messages as $message) {
				printf(
					'[%s]: %s' . PHP_EOL,
					strtoupper( $message['level'] ),
					$message['message']
				);
			}
		return ob_get_clean();
	}


	/**
	 * Write all log messages to a log file and display it in WP media section
	 *
	 * @return null
	 */
	public function create_log_file() {
		// Setup filename path to save the content
		$upload_dir = wp_upload_dir();

		$upload_path = apply_filters( 'pt-ocdi/upload_file_path', trailingslashit( $upload_dir['path'] ) );
		$file_path = $upload_path . apply_filters( 'pt-ocdi/log_file_prefix', 'log_file_' ) . date( 'Y-m-d__H-i-s' ) . apply_filters( 'pt-ocdi/log_file_suffix_and_file_extension', '.txt' );

		return OCDI_Helpers::write_to_file( $this->format_all_log_messages(), $file_path );
	}

}
