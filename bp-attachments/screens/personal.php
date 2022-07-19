<?php
/**
 * BP Attachments personal screen.
 *
 * @package \bp-attachments\screens\personal
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the 'My Media' page.
 *
 * @since 1.0.0
 */
function bp_attachments_personal_screen() {
	/**
	 * Fires right before the loading of the "My Media" screen template file.
	 *
	 * @since 1.0.0
	 */
	do_action( 'bp_attachments_personal_screen' );

	bp_core_load_template( 'members/single/home' );
}
