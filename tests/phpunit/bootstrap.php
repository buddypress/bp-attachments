<?php
/**
 * PHPUnit bootstrap file
 *
 * @package BP Attachments
 * @subpackage \tests\phpunit\bootstrap
 *
 * @since 1.0.0
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php." . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

if ( ! defined( 'BP_TESTS_DIR' ) ) {
	$bp_tests_dir = getenv( 'BP_TESTS_DIR' );
	if ( $bp_tests_dir ) {
		define( 'BP_TESTS_DIR', $bp_tests_dir );
	} else {
		define( 'BP_TESTS_DIR', dirname( dirname( __FILE__ ) ) . '/../../buddypress/tests/phpunit' );
	}
}

/**
 * Load the BP Attachments plugin.
 *
 * @since 1.0.0
 */
function _load_bp_attachments_plugin() {
	// Make sure BP is installed and loaded first.
	require BP_TESTS_DIR . '/includes/loader.php';

	// Load our plugin.
	require_once dirname( __FILE__ ) . '/../../class-bp-attachments.php';
}
tests_add_filter( 'muplugins_loaded', '_load_bp_attachments_plugin' );

// Start up the WP testing environment.
require_once $_tests_dir . '/includes/bootstrap.php';

// Load the REST controllers.
require_once $_tests_dir . '/includes/testcase-rest-controller.php';

// Load the BP test files.
echo "Loading BuddyPress testcase...\n";
require_once BP_TESTS_DIR . '/includes/testcase.php';
