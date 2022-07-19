<?php
/**
 * BP Attachments directory screen.
 *
 * @package \bp-attachments\screens\directory
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets the Attachments directory and load the corresponding template.
 *
 * @since 1.0.0
 */
function bp_attachments_screen_directory_index() {
	if ( ! bp_is_current_component( 'attachments' ) || bp_is_user() ) {
		return;
	}

	bp_update_is_directory( true, 'attachments' );
	bp_core_load_template( 'attachments/index' );
}
add_action( 'bp_screens', 'bp_attachments_screen_directory_index' );
