<?php

// Include required files
require PT_OCDI_PATH . 'vendor/humanmade/WordPress-Importer/class-logger.php';
require PT_OCDI_PATH . 'vendor/humanmade/WordPress-Importer/class-logger-cli.php';

class OCDI_Logger extends WP_Importer_Logger_CLI {
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
		if ( $this->level_to_numeric( $level ) < $this->level_to_numeric( $this->min_level ) ) {
			return;
		}

		printf(
			'[%s] %s' . '<br>',
			strtoupper( $level ),
			$message
		);
	}

}
