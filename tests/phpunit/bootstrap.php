<?php
/**
 * PHPUnit bootstrap file
 *
 * @package BP Attachments\tests\phpunit\bootstrap
 *
 * @since 1.0.0
 */

// If we're running in WP's build directory, ensure that WP knows that, too.
if ( 'build' === getenv( 'LOCAL_DIR' ) ) {
	define( 'WP_RUN_CORE_TESTS', true );
}

$_tests_dir = null;

// Should we use wp-phpunit?
if ( getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) ) {
	require_once dirname( __FILE__, 3 ) . '/vendor/autoload.php';

	if ( getenv( 'WP_PHPUNIT__DIR' ) ) {
		$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );
	}
}

// Defines WP_TEST_DIR & WP_DEVELOP_DIR if not already defined.
if ( is_null( $_tests_dir ) ) {
	$wp_develop_dir = getenv( 'WP_DEVELOP_DIR' );
	if ( ! $wp_develop_dir ) {
		if ( defined( 'WP_DEVELOP_DIR' ) ) {
			$wp_develop_dir = WP_DEVELOP_DIR;
		} else {
			$wp_develop_dir = dirname( __FILE__, 7 );
		}
	}

	$_tests_dir = $wp_develop_dir . '/tests/phpunit';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	die( "The WordPress PHPUnit test suite could not be found.\n" );
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

if ( ! defined( 'BP_ATTACHMENTS_TESTS_DIR' ) ) {
	$bpa_tests_dir = getenv( 'BP_ATTACHMENTS_TESTS_DIR' );
	if ( $bpa_tests_dir ) {
		define( 'BP_ATTACHMENTS_TESTS_DIR', $bpa_tests_dir );
	} else {
		define( 'BP_ATTACHMENTS_TESTS_DIR', dirname( __FILE__ ) );
	}
}

/**
 * Forces the Attachment component to be active.
 *
 * @since 2.0.0
 *
 * @param bool   $retval    Whether or not a given component has been activated by the admin.
 * @param string $component Current component being checked.
 * @return bool Whether or not a given component has been activated by the admin.
 */
function _bp_attachments_is_active( $retval = false, $component = '' ) {
	if ( 'attachments' === $component ) {
		$retval = true;
	}

	return $retval;
}
tests_add_filter( 'bp_is_active', '__return_true' );

/**
 * Load the BP Attachments plugin.
 *
 * @since 1.0.0
 */
function _load_bp_attachments_plugin() {
	add_filter( 'bp_rest_api_is_available', '__return_false' );

	// Make sure BP is installed and loaded first.
	require BP_TESTS_DIR . '/includes/loader.php';

	// Load our plugin.
	require_once dirname( __FILE__ ) . '/../../class-bp-attachments.php';

	// Set version.
	bp_update_option( '_bp_attachments_version', BP_ATTACHMENTS_VERSION );
}
tests_add_filter( 'muplugins_loaded', '_load_bp_attachments_plugin' );

// Start up the WP testing environment.
require_once $_tests_dir . '/includes/bootstrap.php';

// Load the REST controllers.
require_once $_tests_dir . '/includes/testcase-rest-controller.php';

// Load the BP test files.
echo "Loading BuddyPress testcase...\n";
require_once BP_TESTS_DIR . '/includes/testcase.php';
