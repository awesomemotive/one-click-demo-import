<?php

namespace OCDI;

class LoggerTest extends \WP_UnitTestCase {
	/**
	 * Class setup.
	 */
	function setUp() {
		parent::setUp();

		$this->logger = new Logger();
	}


	/**
	 * Test the log method.
	 */
	function test_log() {
		// Default min level is "notice", so an "info" level log message should return null.
		$actual = $this->logger->log( 'info', 'This message should not be returned.' );
		$this->assertNull( $actual );

		// Default min level is "notice", so an "warning" level log message should return a message.
		$expected = '[WARNING] Warning message.' . PHP_EOL;
		ob_start();
			$this->logger->log( 'warning', 'Warning message.' );
		$actual = ob_get_clean();
		$this->assertEquals( $expected, $actual );

		// Change default min level to "info", so an "info" level log message should return a message.
		$this->logger->min_level = 'info';
		$expected = '[INFO] Info message.' . PHP_EOL;
		ob_start();
			$this->logger->log( 'info', 'Info message.' );
		$actual = ob_get_clean();
		$this->assertEquals( $expected, $actual );
	}


	/**
	 * Test the error_output method.
	 */
	function test_error_output() {
		// Min level is "error", so an "info" level log message should return null.
		$actual = $this->logger->log( 'info', 'This message should not be returned.' );
		$this->assertNull( $actual );

		// Min level is "error", so an "error" level log message should return a message.
		$expected = '[ERROR] Error message.<br>';
		$this->logger->error_output( 'error', 'Error message.' );
		$actual = $this->logger->error_output;
		$this->assertEquals( $expected, $actual );
	}
}
