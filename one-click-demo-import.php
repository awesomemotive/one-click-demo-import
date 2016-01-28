<?php
/*
Plugin Name: One Click Demo Import
Plugin URI: http://www.proteusthemes.com
Description: WordPress import made easy. Theme authors: Enable simple demo import for your theme demo data.
Version: 0.1-alpha
Author: ProteusThemes
Author URI: http://www.proteusthemes.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Text Domain: pt-ocdi
*/

// Path/URL to root of this plugin, with trailing slash
define( 'PT_OCDI_PATH', apply_filters( 'pt-ocdi/plugin_dir_path', plugin_dir_path( __FILE__ ) ) );
define( 'PT_OCDI_URL', apply_filters( 'pt-ocdi/plugin_dir_url', plugin_dir_url( __FILE__ ) ) );

// Current version of the plugin
define( 'PT_OCDI_VERSION', apply_filters( 'pt-ocdi/version', '1.0.0' ) );

/**
 * One Click Demo Import class, so we don't have to worry about namespaces
 */
class PT_One_Click_Demo_Import {

	function __construct() {
		// Actions
		add_action( 'admin_menu', array( $this, 'create_plugin_page' ) );
	}

	/**
	 * Creates the plugin page and a submenu item in WP Appearance menu
	 *
	 * @since 0.1-alpha
	 */
	function create_plugin_page() {
		add_theme_page( 'One Click Demo Import', 'Import Demo Data', 'switch_themes', 'pt-one-click-demo-import', array( $this, 'display_plugin_page' ) );
	}

	/**
	 * Plugin page display
	 *
	 * @since 0.1-alpha
	 */
	function display_plugin_page() {
	?>
		<div class="wrap">
			<h2><span class="dashicons dashicons-download" style="line-height: 29px;"></span> Import Demo Data</h2>
		</div>
	<?php
	}
}

new PT_One_Click_Demo_Import();