<?php
/**
 * BP Attachments Functions.
 *
 * @package BP Attachments
 * @subpackage \bp-attachments\bp-attachments-functions
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get the WPDB API.
 *
 * @since 1.0.0
 *
 * @return wpdb The WPDB API.
 */
function bp_attachments_get_db() {
	$wp_db = null;

	if ( isset( $GLOBALS['wpdb'] ) && is_object( $GLOBALS['wpdb'] ) ) {
		$wp_db = $GLOBALS['wpdb'];
	}

	return $wp_db;
}
