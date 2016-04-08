<?php

// Require helpers file containing the helper functions.
require_once dirname( __FILE__ ) . '/../inc/class-ocdi-helpers.php';

class OCDIHelpersTest extends WP_UnitTestCase {

	function test_helper_validate_import_file_info() {

		// Test empty array input
		$this->assertEquals( array(), OCDI_Helpers::validate_import_file_info( array() ) );
	}
}

