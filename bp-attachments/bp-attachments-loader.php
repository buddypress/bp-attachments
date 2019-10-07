<?php
/**
 * BP Attachments Loader.
 *
 * @package BP Attachments
 * @subpackage \bp-attachments\bp-attachments-loader
 *
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Set up the BP Attachments component.
 *
 * @since 1.0.0
 */
function bp_attachments_component() {
	buddypress()->attachments = new BP_Attachments_Component();
}
add_action( 'bp_setup_components', 'bp_attachments_component', 6 );
