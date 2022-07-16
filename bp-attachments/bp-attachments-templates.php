<?php
/**
 * BP Attachments Templates.
 *
 * @package \bp-attachments\bp-attachments-templates
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Attachments Templates directory.
 *
 * Templates in this directory are used as default templates.
 *
 * @since 1.0.0
 */
function bp_attachments_get_templates_dir() {
	return buddypress()->attachments->templates_dir;
}

/**
 * Temporarly add the BP Attachments templates directory to the BuddyPress
 * Templates stack.
 *
 * @since 1.0.0
 *
 * @param array $stack The BuddyPress Templates stack.
 * @return array       The same Templates stack including the BP Attachments directory.
 */
function bp_attachments_get_template_stack( $stack = array() ) {
	return array_merge( $stack, array( bp_attachments_get_templates_dir() ) );
}

/**
 * Get & load the required JavaScript templates.
 *
 * @since 1.0.0
 */
function bp_attachments_get_javascript_templates() {
	// Temporarly overrides the BuddyPress Template Stack.
	add_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );

	// Load the template parts.
	bp_get_template_part( 'common/js-templates/attachments/media-item' );

	// Stop overridding the BuddyPress Template Stack.
	remove_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );
}

/**
 * Outputs the displayed user media library.
 *
 * @since 1.0.0
 */
function bp_attachements_output_personal_template() {
	?>
	<p>TBD</p>
	<?php
}

/**
 * Sets the hook to handle the display of the user media library.
 *
 * @since 1.0.0
 */
function bp_attachements_set_personal_template() {
	add_action( 'bp_template_content', 'bp_attachements_output_personal_template' );
}
add_action( 'bp_attachments_personal_screen', 'bp_attachements_set_personal_template' );
