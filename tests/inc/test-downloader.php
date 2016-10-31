<?php

namespace OCDI;

class DownloaderTest extends \WP_UnitTestCase {
	/**
	 * Class setup.
	 */
	function setUp() {
		parent::setUp();

		// Downloader with the default WP uploads directory.
		$this->downloader = new Downloader();

		// Default download path.
		$upload_dir         = wp_upload_dir();
		$this->default_path = trailingslashit( $upload_dir['path'] );
	}

	/**
	 * Testing the get_download_directory_path method.
	 */
	function test_get_download_directory_path() {
		// Test default path (no argument when initializing Downloader class).
		$this->assertEquals( $this->default_path, $this->downloader->get_download_directory_path() );

		// Test invalid path.
		$downloader2 = new Downloader( 'test' );
		$this->assertEquals( $this->default_path, $downloader2->get_download_directory_path() );

		// Test a valid path.
		$path = plugin_dir_path( __FILE__ );
		$downloader3 = new Downloader( $path );
		$this->assertEquals( $path, $downloader3->get_download_directory_path() );
	}

	/**
	 * Testing the set_download_directory_path method.
	 */
	function test_set_download_directory_path() {
		// Test empty path.
		$downloader1 = new Downloader( '' );
		$downloader1->set_download_directory_path( '' );
		$this->assertEquals( $this->default_path, $downloader1->get_download_directory_path() );

		// Test invalid path.
		$downloader1->set_download_directory_path( 'test' );
		$this->assertEquals( $this->default_path, $downloader1->get_download_directory_path() );

		// Test a valid path.
		$path = plugin_dir_path( __FILE__ );
		$downloader1->set_download_directory_path( $path );
		$this->assertEquals( $path, $downloader1->get_download_directory_path() );
	}

	/**
	 * Testing the get_error_from_response method.
	 * Private function.
	 */
	function test_get_error_from_response() {
		$expected = array(
			'error_code'    => 'test_error',
			'error_message' => 'Error test',
		);

		// Test response with array.
		$method_response = \TestHelpers::invoke_method(
			$this->downloader,
			'get_error_from_response',
			array(
				array(
					'response' => array(
						'code'    => 'test_error',
						'message' => 'Error test',
					),
				),
			)
		);
		$this->assertEquals( $expected, $method_response );

		// Test response with WP_error object.
		$method_response = \TestHelpers::invoke_method(
			$this->downloader,
			'get_error_from_response',
			array(
				new \WP_Error(
					'test_error',
					'Error test'
				)
			)
		);
		$this->assertEquals( $expected, $method_response );
	}

	/**
	 * Testing the get_content_from_url method.
	 * Private function.
	 */
	function test_get_content_from_url() {
		// Empty URL parameter.
		$expected_code   = 'missing_url';
		$method_response = \TestHelpers::invoke_method(
			$this->downloader,
			'get_content_from_url',
			array(
				''
			)
		);

		$this->assertTrue( is_wp_error( $method_response ) );
		$this->assertEquals( $expected_code, $method_response->get_error_code() );

		// Invalid URL parameter.
		$expected_code   = 'download_error';
		$method_response = \TestHelpers::invoke_method(
			$this->downloader,
			'get_content_from_url',
			array(
				'http://invalid-url.com'
			)
		);

		$this->assertTrue( is_wp_error( $method_response ) );
		$this->assertEquals( $expected_code, $method_response->get_error_code() );

		// Valid URL parameter - a test file with the content of "test".
		$expected = 'test';
		$method_response = \TestHelpers::invoke_method(
			$this->downloader,
			'get_content_from_url',
			array(
				'https://raw.githubusercontent.com/proteusthemes/one-click-demo-import/aa2fbfccbc3331ac46e64ebba33c4cf58b1c39a8/tests/data/test-files/test.txt'
			)
		);

		$this->assertEquals( $expected, $method_response );
	}

	/**
	 * Testing the download_file method.
	 */
	function test_download_file() {
		$expected = $this->default_path . 'test.txt';
		$file     = $this->downloader->download_file( 'https://raw.githubusercontent.com/proteusthemes/one-click-demo-import/aa2fbfccbc3331ac46e64ebba33c4cf58b1c39a8/tests/data/test-files/test.txt', 'test.txt' );

		$this->assertEquals( $expected, $file );
		$this->assertTrue( file_exists( $file ) );

		// Delete the downloaded file.
		unlink( $file );
	}
}
