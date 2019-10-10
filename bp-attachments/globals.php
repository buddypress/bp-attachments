<?php
/**
 * Functions about globals
 *
 * @package   BP Attachments
 * @subpackage \bp-attachments\globals
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Set plugin's globals.
 *
 * @since 1.0.0
 */
function bp_attachments_globals() {
	$bp_attachments = bp_attachments();

    // Version.
	$bp_attachments->version = '1.0.0-alpha';

    // Paths
	$bp_attachments->inc_path      = plugin_dir_path( __FILE__ );
    $bp_attachments->templates_dir = trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . 'templates';

    // URLs.
    $bp_attachments->templates_url = plugins_url( 'templates/', dirname( __FILE__ ) );
    $bp_attachments->js_url        = plugins_url( 'js/', dirname( __FILE__ ) );
    $bp_attachments->assets_url    = plugins_url( 'assets/', dirname( __FILE__ ) );
	$bp_attachments->dist_url      = plugins_url( 'dist/', dirname( __FILE__ ) );
}
add_action( 'bp_loaded', 'bp_attachments_globals', 1 );
