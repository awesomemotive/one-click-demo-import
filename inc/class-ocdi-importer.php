<?php

/*
 * SINGLETON
 * Class for declaring the importer used in the One Click Demo Import plugin
 */

if ( ! class_exists( 'OCDI_Importer' ) ) {
	class OCDI_Importer {

		private static $instance;
		private $importer, $importer_options, $logger;

		/**
		 * Private constructor to prevent creating a new instance of the *Singleton* via the `new` operator from outside of this class.
		 */
		private function __construct() {
			// Include files that are needed for WordPress Importer v2
			$this->include_require_files();

			// Importer options array
			$this->importer_options = apply_filters( 'pt-ocdi/importer_options', array(
				'fetch_attachments' => true,
			) );

			// Set the WordPress Importer v2 as the importer used in this plugin
			// More: https://github.com/humanmade/WordPress-Importer
			$this->importer = new WXR_Importer( $this->importer_options );

			// Set the default logger
			$this->logger = new OCDI_Logger();
			$this->logger->min_level = apply_filters( 'pt-ocdi/logger_min_level', 'debug' );
			$this->importer->set_logger( $this->logger );
		}

		/*
		 * Include required files
		 */
		private function include_require_files() {
			if ( ! class_exists( 'WP_Importer' ) ) {
				defined( 'WP_LOAD_IMPORTERS' ) || define( 'WP_LOAD_IMPORTERS', true );
				require ABSPATH . '/wp-admin/includes/class-wp-importer.php';
			}
			require PT_OCDI_PATH . 'inc/class-ocdi-logger.php';
			require PT_OCDI_PATH . 'vendor/humanmade/WordPress-Importer/class-wxr-importer.php';
		}

		/*
		 * Static function for retrieving or instantiation of this class - Singleton
		 */
		public static function get_instance() {
			if ( null === static::$instance ) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		/**
		 * Imports demo data from a WordPress export file
		 *
		 * @param xml file, file with WordPress demo export data
		 */
		public function import( $data_file ) {
			$this->importer->import( $data_file );
		}

		/**
		 * Set the logger used in the import
		 *
		 * @param object logger, logger instance
		 */
		public function set_logger( $logger ) {
			$this->importer->set_logger( $logger );
		}

		/**
		 * Private clone method to prevent cloning of the instance of the *Singleton* instance.
		 *
		 * @return void
		 */
		private function __clone() {}

		/**
		 * Private unserialize method to prevent unserializing of the *Singleton* instance.
		 *
		 * @return void
		 */
		private function __wakeup() {}

	}
}