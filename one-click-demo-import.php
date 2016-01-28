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

	private $importer;

	function __construct() {
		// Include files
		require PT_OCDI_PATH . 'inc/class-ocdi-importer.php';

		// Create importer instance *Singleton*
		$this->importer = OCDI_Importer::get_instance();

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
			<h2><span class="dashicons dashicons-download" style="line-height: 30px;"></span> One Click Demo Import</h2>

			<form method="post" class="js-one-click-import-form">
				<input type="hidden" name="demononce" value="<?php echo wp_create_nonce('radium-demo-code'); ?>" />
				<input name="reset" class="panel-save button-primary" type="submit" value="Import Demo Data" />
				<input type="hidden" name="action" value="demo-data" />
			</form>

			<script>
				jQuery( function ( $ ) {
					'use strict';
					$( '.js-one-click-import-form' ).on( 'submit', function () {
						$( this ).append( '<p style="font-width: bold; font-size: 1.5em;"><span class="spinner" style="display: inline-block; float: none; visibility: visible;"></span> Importing now, please wait!</p>' );
						$( this ).find( '.panel-save' ).attr( 'disabled', true );
					} );
				} );
			</script>

		</div>
	<?php

		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

		if( 'demo-data' == $action && check_admin_referer('radium-demo-code' , 'demononce')){

			$file = PT_OCDI_PATH ."demo-import-files/demo-import-post-page-image.xml";
			echo $file . "<br><br>";

			$this->importer->set_logger( new WP_Importer_Logger_CLI() );
			$this->importer->import( $file );

		}
	}

}

new PT_One_Click_Demo_Import();