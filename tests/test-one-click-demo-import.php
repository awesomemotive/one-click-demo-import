<?php
/**
 * Class OneClickDemoImportTest.
 *
 * @package pt-ocdi
 */

/**
 * Main plugin class tests
 */
class OneClickDemoImportTest extends WP_UnitTestCase {
	/**
	 * Class setup.
	 */
	function setUp() {
		parent::setUp();
		$this->ocdi = new OCDI_Plugin();
	}

	/**
	 * Test the plugin main classes.
	 */
	function test_class_is_available() {
		$this->assertTrue( class_exists( 'OCDI_Plugin' ) );
		$this->assertTrue( class_exists( 'OCDI\OneClickDemoImport' ) );
	}

	/**
	 * Test the plugin constants. If they are defined.
	 */
	function test_plugin_constants() {
		$this->assertTrue( defined( 'PT_OCDI_PATH' ), 'Constant PT_OCDI_PATH is not defined!' );
		$this->assertTrue( defined( 'PT_OCDI_URL' ), 'Constant PT_OCDI_URL is not defined!' );

		// Manually call the function to register this constant, because it was not fired with admin_init hook.
		\TestHelpers::invoke_method(
			$this->ocdi,
			'set_plugin_version_constant'
		);
		$this->assertTrue( defined( 'PT_OCDI_VERSION' ), 'Constant PT_OCDI_VERSION is not defined!' );
	}

	/**
	 * Test if the main plugin files exist.
	 */
	function test_plugin_files_exists() {
		$this->assertFileExists( PT_OCDI_PATH . 'one-click-demo-import.php', 'Main plugin file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'inc/CustomizerImporter.php', 'Customizer importer file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'inc/CustomizerOption.php', 'Customizer option file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'inc/Helpers.php', 'Helpers file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'inc/Importer.php', 'Importer file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'inc/Logger.php', 'Logger file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'inc/OneClickDemoImport.php', 'Main file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'inc/WidgetImporter.php', 'Widget importer file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'inc/WXRImporter.php', 'WXRImporter file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'vendor/awesomemotive/wp-content-importer-v2/src/WPImporterLogger.php', 'WP importer v2 logger file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'vendor/awesomemotive/wp-content-importer-v2/src/WPImporterLoggerCLI.php', 'WP importer v2 logger CLI file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'vendor/awesomemotive/wp-content-importer-v2/src/WXRImporter.php', 'WP importer v2 main file is missing!' );
		$this->assertFileExists( PT_OCDI_PATH . 'vendor/awesomemotive/wp-content-importer-v2/src/WXRImportInfo.php', 'WP importer v2 info class file is missing!' );
	}
}
