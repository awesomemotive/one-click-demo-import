<?php

// Include required files
require PT_OCDI_PATH . 'vendor/humanmade/WordPress-Importer/class-logger.php';
require PT_OCDI_PATH . 'vendor/humanmade/WordPress-Importer/class-logger-cli.php';

class OCDI_Logger extends WP_Importer_Logger_CLI {
	public $error_output = '';

	/**
	 * Overwritten log function from WP_Importer_Logger_CLI.
	 *
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function log( $level, $message, array $context = array() ) {
		// Save error messages for front-end display
		$this->error_output( $level, $message, $context = array() );

		if ( $this->level_to_numeric( $level ) < $this->level_to_numeric( $this->min_level ) ) {
			return;
		}

		printf(
			'[%s] %s' . PHP_EOL,
			strtoupper( $level ),
			$message
		);
	}


	/**
	 * Save messages for error output.
	 * Only the messages greater then Error
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function error_output( $level, $message, array $context = array() ) {
		if ( $this->level_to_numeric( $level ) < $this->level_to_numeric( 'warning' ) ) {
			return;
		}

		$this->error_output .= sprintf(
			'[%s] %s' . '<br>',
			strtoupper( $level ),
			$message
		);

	}

}
