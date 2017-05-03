<?php

namespace OCDI;

class HelpersTest extends \WP_UnitTestCase {
	/**
	 * Class setup.
	 */
	function setUp() {
		parent::setUp();

		// Helpers class object for static private calls.
		$this->helpers = new Helpers();

		// Default download path.
		$upload_dir         = wp_upload_dir();
		$this->default_path = trailingslashit( $upload_dir['path'] );
	}


	/**
	 * Test the validate_import_file_info method.
	 */
	function test_validate_import_file_info() {
		// Test empty array input
		$import_files    = array();
		$expected_output = array();
		$this->assertEquals( $expected_output, Helpers::validate_import_file_info( $import_files ) );

		// Test valid array
		$import_files = array(
			array(
				'import_file_name'       => 'Demo Import 1',
				'import_file_url'        => 'http://www.your_domain.com/ocdi/demo-content.xml',
				'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets.json'
			),
			array(
				'import_file_name'       => 'Demo Import 2',
				'import_file_url'        => 'http://www.your_domain.com/ocdi/demo-content2.xml',
				'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets2.json'
			),
		);
		$expected_output = array(
			array(
				'import_file_name'       => 'Demo Import 1',
				'import_file_url'        => 'http://www.your_domain.com/ocdi/demo-content.xml',
				'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets.json'
			),
			array(
				'import_file_name'       => 'Demo Import 2',
				'import_file_url'        => 'http://www.your_domain.com/ocdi/demo-content2.xml',
				'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets2.json'
			),
		);
		$this->assertEquals( $expected_output, Helpers::validate_import_file_info( $import_files ) );

		// Test valid array with one invalid item
		$import_files = array(
			array(
				'import_file_name'       => 'Demo Import 1',
				'import_file_url'        => 'http://www.your_domain.com/ocdi/demo-content.xml',
				'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets.json'
			),
			array(
				'import_file_title'      => 'Invalid Demo import',
				'import_file_link'       => 'http://www.your_domain.com/ocdi/invalid-demo-content.xml',
				'import_widget_file_url' => 'http://www.your_domain.com/ocdi/invalid-widgets.json'
			),
			array(
				'import_file_name' => 'Demo Import 2',
				'import_file_url'  => 'http://www.your_domain.com/ocdi/demo-content2.xml',
			),
		);
		$expected_output = array(
			array(
				'import_file_name'       => 'Demo Import 1',
				'import_file_url'        => 'http://www.your_domain.com/ocdi/demo-content.xml',
				'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets.json'
			),
			array(
				'import_file_name' => 'Demo Import 2',
				'import_file_url'  => 'http://www.your_domain.com/ocdi/demo-content2.xml',
			),
		);
		$this->assertEquals( $expected_output, Helpers::validate_import_file_info( $import_files ) );

		// Test invalid array
		$import_files = array(
			array(
				'import_file_title'      => 'Invalid Demo import',
				'import_file_link'       => 'http://www.your_domain.com/ocdi/invalid-demo-content.xml',
				'import_widget_file_url' => 'http://www.your_domain.com/ocdi/invalid-widgets.json'
			),
			array(
				'import_file_title' => 'Invalid Demo import',
				'import_file_url'   => 'http://www.your_domain.com/ocdi/invalid-demo-content.xml',
			),
			array(
				'import_file_name' => 'Valid demo import without any import files',
				'import_file_link' => 'http://www.your_domain.com/ocdi/invalid-demo-content.xml',
			)
		);
		$expected_output = array(
			array(
				'import_file_name' => 'Valid demo import without any import files',
				'import_file_link' => 'http://www.your_domain.com/ocdi/invalid-demo-content.xml',
			)
		);
		$this->assertEquals( $expected_output, Helpers::validate_import_file_info( $import_files ) );
	}


	/**
	 * Test the is_import_file_info_format_correct method.
	 */
	function test_is_import_file_info_format_correct() {
		// Required parameter are empty (should return false).
		$import_file_info = array(
			'import_file_url'  => '',
			'import_file_name' => '',
		);

		$actual = \TestHelpers::invoke_method(
			$this->helpers,
			'is_import_file_info_format_correct',
			array( $import_file_info )
		);

		$this->assertFalse( $actual );

		// Required parameter (name) is defined (should return true).
		$import_file_info = array(
			'import_file_url'  => '',
			'import_file_name' => 'Name',
		);

		$actual = \TestHelpers::invoke_method(
			$this->helpers,
			'is_import_file_info_format_correct',
			array( $import_file_info )
		);

		$this->assertTrue( $actual );

		// Required parameter (name) is empty (should return false).
		$import_file_info = array(
			'import_file_url'  => 'http://urlhere.com',
			'import_file_name' => '',
		);

		$actual = \TestHelpers::invoke_method(
			$this->helpers,
			'is_import_file_info_format_correct',
			array( $import_file_info )
		);

		$this->assertFalse( $actual );

		// Both required parameter are defined (should return true).
		$import_file_info = array(
			'import_file_url'  => 'http://urlhere.com',
			'import_file_name' => 'Name',
		);

		$actual = \TestHelpers::invoke_method(
			$this->helpers,
			'is_import_file_info_format_correct',
			array( $import_file_info )
		);

		$this->assertTrue( $actual );

		// Both required parameter (local import file example) are defined (should return true).
		$import_file_info = array(
			'local_import_file' => 'path/to/import/file.xml',
			'import_file_name'  => 'Name',
		);

		$actual = \TestHelpers::invoke_method(
			$this->helpers,
			'is_import_file_info_format_correct',
			array( $import_file_info )
		);

		$this->assertTrue( $actual );
	}


	/**
	 * Test the download_import_files method.
	 */
	function test_download_import_files() {
		// Both, import file URL and local path are empty, so an "empty" array should be returned.
		$import_file_info = array(
			'import_file_url'   => '',
			'local_import_file' => '',
			'import_file_name'  => 'Import name',
		);

		$expected = array(
			'content'    => '',
			'widgets'    => '',
			'customizer' => '',
			'redux'      => '',
		);
		$actual = Helpers::download_import_files( $import_file_info );

		$this->assertEquals( $expected, $actual );

		// Local content import file path is set, so it should be returned in the "download" array.
		$local_import_file_path = PT_OCDI_PATH . 'tests/data/import-files/content.xml';
		$import_file_info = array(
			'import_file_url'   => '',
			'local_import_file' => $local_import_file_path,
			'import_file_name'  => 'Import name',
		);

		$expected = array(
			'content'    => $local_import_file_path,
			'widgets'    => '',
			'customizer' => '',
			'redux'      => '',
		);

		$actual = Helpers::download_import_files( $import_file_info );

		$this->assertEquals( $expected, $actual );

		// Local content import file, widgets file and customizer file path is set, so it should be returned in the "download" array.
		$root_local_import_file_path = PT_OCDI_PATH . 'tests/data/import-files/';
		$import_file_info = array(
			'import_file_url'              => '',
			'local_import_file'            => $root_local_import_file_path . 'content.xml',
			'local_import_widget_file'     => $root_local_import_file_path . 'widgets.json',
			'local_import_customizer_file' => $root_local_import_file_path . 'customizer.dat',
			'import_file_name'             => 'Import name',
		);

		$expected = array(
			'content'    => $root_local_import_file_path . 'content.xml',
			'widgets'    => $root_local_import_file_path . 'widgets.json',
			'customizer' => $root_local_import_file_path . 'customizer.dat',
			'redux'      => '',
		);

		$actual = Helpers::download_import_files( $import_file_info );

		$this->assertEquals( $expected, $actual );

		// Set the import start time, to be used in the downloaded filenames.
		$original_demo_import_start_time_value = Helpers::$demo_import_start_time;
		Helpers::set_demo_import_start_time();

		// Content import file URL is set, so it should be returned in the "download" array.
		$import_file_info = array(
			'import_file_url'   => 'https://raw.githubusercontent.com/proteusthemes/one-click-demo-import/aa2fbfccbc3331ac46e64ebba33c4cf58b1c39a8/tests/data/import-files/content.xml',
			'local_import_file' => '',
			'import_file_name'  => 'Import name',
		);

		$expected = array(
			'content' => $this->default_path . 'demo-content-import-file_' . Helpers::$demo_import_start_time . '.xml',
			'widgets'    => '',
			'customizer' => '',
			'redux'      => '',
		);

		$actual = Helpers::download_import_files( $import_file_info );

		$this->assertEquals( $expected, $actual );

		// Content import file URL, widgets URL and customizer URL are set, so the downloaded paths should be returned in the "download" array.
		$import_file_info = array(
			'import_file_url'            => 'https://raw.githubusercontent.com/proteusthemes/one-click-demo-import/aa2fbfccbc3331ac46e64ebba33c4cf58b1c39a8/tests/data/import-files/content.xml',
			'import_widget_file_url'     => 'https://raw.githubusercontent.com/proteusthemes/one-click-demo-import/aa2fbfccbc3331ac46e64ebba33c4cf58b1c39a8/tests/data/import-files/widgets.json',
			'import_customizer_file_url' => 'https://raw.githubusercontent.com/proteusthemes/one-click-demo-import/aa2fbfccbc3331ac46e64ebba33c4cf58b1c39a8/tests/data/import-files/customizer.dat',
			'local_import_file' => '',
			'import_file_name'  => 'Import name',
		);

		$expected = array(
			'content'    => $this->default_path . 'demo-content-import-file_' . Helpers::$demo_import_start_time . '.xml',
			'widgets'    => $this->default_path . 'demo-widgets-import-file_' . Helpers::$demo_import_start_time . '.json',
			'customizer' => $this->default_path . 'demo-customizer-import-file_' . Helpers::$demo_import_start_time . '.dat',
			'redux'      => '',
		);

		$actual = Helpers::download_import_files( $import_file_info );

		$this->assertEquals( $expected, $actual );

		// Reset the $demo_import_start_time in Helpers class to the default value.
		Helpers::$demo_import_start_time = $original_demo_import_start_time_value;

	}


	/**
	 * Test the set_demo_import_start_time method.
	 */
	function test_set_demo_import_start_time() {
		// Should be empty string at the start.
		$this->assertEquals( '', Helpers::$demo_import_start_time );

		Helpers::set_demo_import_start_time();

		// Should not be an empty string anymore.
		$this->assertNotEquals( '', Helpers::$demo_import_start_time );
	}


	/**
	 * Test the write_to_file method.
	 */
	function test_write_to_file() {
		$file_path = $this->default_path . 'testing-write-to-file.txt';

		Helpers::write_to_file( 'Content goes here.', $file_path );

		$this->assertTrue( file_exists( $file_path ) );
	}


	/**
	 * Test the append_to_file method.
	 */
	function test_append_to_file() {
		$file_path = $this->default_path . 'testing-append-to-file.txt';

		$this->assertTrue( Helpers::append_to_file( 'Content goes here.', $file_path, 'separator text' ) );
	}


	/**
	 * Test the data_from_file method.
	 */
	function test_data_from_file() {
		// Non-existing file path.
		$file_path = '';
		$expected  = new \WP_Error( 'failed_reading_file_from_server', '' );
		$actual    = Helpers::data_from_file( $file_path );
		$this->assertEquals( $expected->get_error_code(), $actual->get_error_code() );

		// Valid file path.
		$file_path = PT_OCDI_PATH . 'tests/data/test-files/test.txt';
		$this->assertEquals( 'test', Helpers::data_from_file( $file_path ) );
	}


	/**
	 * Test the get_log_path method.
	 */
	function test_get_log_path() {
		// Set the import start time, to be used in the downloaded filenames.
		$original_demo_import_start_time_value = Helpers::$demo_import_start_time;
		Helpers::set_demo_import_start_time();

		$expected = $this->default_path . 'log_file_' . Helpers::$demo_import_start_time . '.txt';

		$this->assertEquals( $expected, Helpers::get_log_path() );

		// Reset the $demo_import_start_time in Helpers class to the default value.
		Helpers::$demo_import_start_time = $original_demo_import_start_time_value;
	}
}
