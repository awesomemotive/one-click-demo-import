<?php
/**
 * Plugin Installer Skin class - responsible for displying info while installing plugins.
 *
 * @package ocdi
 */

namespace OCDI;

if ( ! class_exists( '\Plugin_Upgrader', false ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
}

/**
 * WordPress class extended for on-the-fly plugin installations.
 */
class PluginInstallerSkin extends \WP_Upgrader_Skin {

	/**
	 * Empty out the header of its HTML content.
	 */
	public function header() {}

	/**
	 * Empty out the footer of its HTML content.
	 */
	public function footer() {}

	/**
	 * Empty out the footer of its HTML content.
	 *
	 * @param string $string
	 * @param mixed  ...$args Optional text replacements.
	 */
	public function feedback( $string, ...$args ) {}

	/**
	 * Empty out JavaScript output that calls function to decrement the update counts.
	 *
	 * @param string $type Type of update count to decrement.
	 */
	public function decrement_update_count( $type ) {}

	/**
	 * Instead of outputting HTML for errors, json_encode the errors and send them
	 * back to the Ajax script for processing.
	 *
	 * @param string|WP_Error $errors A string or WP_Error object of the install error/s.
	 */
	public function error( $errors ) {
		if ( empty( $errors ) ) {
			return;
		}

		if ( is_string( $errors ) ) {
			wp_send_json_error( $errors );
		} elseif ( is_wp_error( $errors ) && $errors->has_errors() ) {
			if ( $errors->get_error_data() && is_string( $errors->get_error_data() ) ) {
				wp_send_json_error( $message . ' ' . esc_html( strip_tags( $errors->get_error_data() ) ) );
			} else {
				wp_send_json_error( $message );
			}
		}
	}
}

