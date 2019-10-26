<?php
/**
 * BP Attachments Templates.
 *
 * @package BP Attachments
 * @subpackage \bp-attachments\bp-attachments-templates
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

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
