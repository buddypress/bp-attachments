<?php
/**
 * BP Attachments personal screen.
 *
 * @package BP Attachments
 * @subpackage \bp-attachments\screens\personal
 *
 * @since 1.0.0
 */

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

	/**
	 * Filters the template to load for the "My Media" screen.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Path to the attachments template to load.
	 */
	bp_core_load_template( apply_filters( 'bp_activity_template_my_activity', 'members/single/home' ) );
}
