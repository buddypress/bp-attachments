<?php
/**
 * BP Attachments Cache Functions.
 *
 * @package \bp-attachments\bp-attachments-cache
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clear the cache medium.
 *
 * @since 1.0.0
 *
 * @param object $medium_data Data about the medium.
 */
function bp_attachments_clear_cache( $medium_data ) {
	// Visibility's root uploads directory.
	$uploads_dir = bp_attachments_get_media_uploads_dir( $medium_data->visibility );

	// Set the relative path.
	$relative_path = trim( str_replace( $uploads_dir['path'], '', $medium_data->abspath ), '/' );

	wp_cache_delete( $medium_data->visibility . '/' . $relative_path . '/' . $medium_data->id, 'bp_attachments' );
}
add_action( 'bp_attachments_deleted_medium', 'bp_attachments_clear_cache', 10, 1 );
add_action( 'bp_attachments_updated_medium', 'bp_attachments_clear_cache', 10, 1 );
