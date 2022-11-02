<?php
/**
 * BP Attachments Template Loader functions.
 *
 * @package \bp-attachments\bp-attachments-template-loader
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
 * Get Attachments Templates url.
 *
 * @since 1.0.0
 */
function bp_attachments_get_templates_url() {
	return buddypress()->attachments->templates_url;
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
 * Start filtering the template stack to include BP Attachments templates dir.
 *
 * @since 1.0.0
 */
function bp_attachments_start_overriding_template_stack() {
	add_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );
}

/**
 * Stop filtering the template stack to exclude BP Attachments templates dir.
 *
 * @since 1.0.0
 */
function bp_attachments_stop_overriding_template_stack() {
	remove_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );
}

/**
 * Get & load the required JavaScript templates.
 *
 * @since 1.0.0
 *
 * @param string $name The template name to use.
 */
function bp_attachments_get_javascript_template( $name = 'media-item' ) {
	// Temporarly overrides the BuddyPress Template Stack.
	bp_attachments_start_overriding_template_stack();

	// Load the template parts.
	bp_get_template_part( 'common/js-templates/attachments/' . $name );

	// Stop overidding the BuddyPress Template Stack.
	bp_attachments_stop_overriding_template_stack();
}

/**
 * Print Media Library JavaScript Templates into the footer.
 *
 * @since 1.0.0
 */
function bp_attachments_print_media_library_templates() {
	bp_attachments_get_javascript_template();
}

/**
 * Overrides specific BuddyPress template parts when needed.
 *
 * @since 1.0.0
 *
 * @param array $templates The list of requested template parts.
 * @return array The list of template parts.
 */
function bp_attachments_template_part_overrides( $templates = array() ) {
	$is_overriding = false;

	if ( in_array( 'members/single/plugins.php', $templates, true ) && bp_attachments_is_user_personal_library() ) {
		$is_overriding = true;
		array_unshift( $templates, 'members/single/attachments.php' );
	} else {
		/**
		 * Filter used to override other optional BP Attachments templates.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $is_overriding Whether the template is being overriden.
		 * @param array $templates The list of requested template parts, passed by reference.
		 */
		$is_overriding = apply_filters_ref_array( 'bp_attachments_template_part_overrides', array( $is_overriding, &$templates ) );
	}

	if ( $is_overriding ) {
		// Temporarly overrides the BuddyPress Template Stack.
		bp_attachments_start_overriding_template_stack();

		// Wait for the hook `bp_locate_template` to fire to stop overidding the BuddyPress Template Stack.
		add_action( 'bp_locate_template', 'bp_attachments_stop_overriding_template_stack' );
	}

	return $templates;
}
add_filter( 'bp_get_template_part', 'bp_attachments_template_part_overrides', 1, 1 );
add_filter( 'bp_nouveau_member_locate_template_part', 'bp_attachments_template_part_overrides', 1, 1 );

/**
 * Sets the Attachments content dummy post.
 *
 * @since 1.0.0
 */
function bp_attachments_set_dummy_post() {
	// Use the Attachments directory title by default.
	$title = bp_get_directory_title( 'attachments' );

	$medium_action = bp_attachments_current_medium_action();

	// Use the BP Attachments queried object global to set the medium title.
	if ( $medium_action ) {
		$medium = bp_attachments_get_queried_object();
		$title  = $medium->title;
	}

	$visibility = bp_attachments_current_medium_visibility();

	bp_theme_compat_reset_post(
		array(
			'ID'             => 0,
			'post_title'     => $title,
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'page',
			'post_status'    => 'private' === $visibility ? 'private' : 'publish',
			'is_page'        => true,
			'comment_status' => 'closed',
		)
	);
}

/**
 * Sets the Attachments content template.
 *
 * @since 1.0.0
 */
function bp_attachments_set_content_template() {
	// Temporarly overrides the BuddyPress Template Stack.
	bp_attachments_start_overriding_template_stack();

	// By default the Attachments directory template.
	$template = 'attachments/index';

	$medium_action = bp_attachments_current_medium_action();

	// Use the current action to set the template name.
	if ( $medium_action ) {
		$template = 'attachments/single/' . $medium_action;
	}

	$content_template = bp_buffer_template_part( $template, null, false );

	// Stop overriding the BuddyPress Template Stack.
	bp_attachments_stop_overriding_template_stack();

	// Finally return the buffer.
	return $content_template;
}

/**
 * Sets the Attachments directory theme compat screens.
 *
 * @since 1.0.0
 */
function bp_attachments_set_directory_theme_compat() {
	if ( bp_is_current_component( 'attachments' ) && ! bp_is_user() ) {
		add_action( 'bp_template_include_reset_dummy_post_data', 'bp_attachments_set_dummy_post' );
		add_filter( 'bp_replace_the_content', 'bp_attachments_set_content_template' );
	}
}
add_action( 'bp_setup_theme_compat', 'bp_attachments_set_directory_theme_compat' );
