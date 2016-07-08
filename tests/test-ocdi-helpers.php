<?php

// Require helpers file containing the helper functions.
require_once dirname( __FILE__ ) . '/../inc/class-ocdi-helpers.php';

class OCDIHelpersTest extends WP_UnitTestCase {

	function test_helper_validate_import_file_info() {

		// Test empty array input
		$import_files    = array();
		$expected_output = array();
		$this->assertEquals( $expected_output, OCDI_Helpers::validate_import_file_info( $import_files ) );

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
		$this->assertEquals( $expected_output, OCDI_Helpers::validate_import_file_info( $import_files ) );

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
		$this->assertEquals( $expected_output, OCDI_Helpers::validate_import_file_info( $import_files ) );

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
				'import_file_name' => 'Invalid Demo import',
				'import_file_link' => 'http://www.your_domain.com/ocdi/invalid-demo-content.xml',
			)
		);
		$expected_output = array();
		$this->assertEquals( $expected_output, OCDI_Helpers::validate_import_file_info( $import_files ) );
	}

	function test_helper_download_import_files() {

		// Test with empty file url. Should return WP_Error obj.
		$import_files = array(
			'import_file_name' => 'Import file',
			'import_file_url'  => '',
		);
		$expected_output = new WP_Error(
			'url_or_local_file_not_defined',
			'"import_file_url" or "local_import_file" for <strong>Import file</strong> are not defined!'
		);
		$this->assertEquals( $expected_output , OCDI_Helpers::download_import_files( $import_files ) );

	}
}
