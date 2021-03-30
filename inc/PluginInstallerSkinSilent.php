<?php
/**
 * Plugin Installer Skin class - responsible for not displying any info while installing plugins.
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
class PluginInstallerSkinSilent extends \WP_Upgrader_Skin {

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
	 * Empty out the error HTML content.
	 *
	 * @param string|WP_Error $errors A string or WP_Error object of the install error/s.
	 */
	public function error( $errors ) {}
}

