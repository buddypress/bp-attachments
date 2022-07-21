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
 * Is this a BP Attachments item action request?
 *
 * @since 1.0.0
 *
 * @return bool True if it's a BP Attachments item action request. False otherwise.
 */
function bp_attachments_current_media_action() {
	$action = get_query_var( 'bp_attachments_item_action' );

	if ( $action ) {
		$action = bp_attachments_get_item_action_key( $action );
	}

	return $action;
}

/**
 * Is this a BP Attachments item view request?
 *
 * @since 1.0.0
 *
 * @return bool True if it's a BP Attachments item view request. False otherwise.
 */
function bp_attachments_is_media_view() {
	$retval = false;

	if ( ! is_null( bp_attachments_get_queried_object() ) && 'view' === bp_attachments_current_media_action() ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Is this a BP Attachments item download request?
 *
 * @since 1.0.0
 *
 * @return bool True if it's a BP Attachments item download request. False otherwise.
 */
function bp_attachments_is_media_download() {
	$retval = false;

	if ( ! is_null( bp_attachments_get_queried_object() ) && 'download' === bp_attachments_current_media_action() ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Is this a BP Attachments item embed request?
 *
 * @since 1.0.0
 *
 * @return bool True if it's a BP Attachments item embed request. False otherwise.
 */
function bp_attachments_is_media_embed() {
	$retval = false;

	if ( ! is_null( bp_attachments_get_queried_object() ) && 'embed' === bp_attachments_current_media_action() ) {
		$retval = true;
	}

	return $retval;
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

/**
 * Sets the Attachments content dummy post.
 *
 * @since 1.0.0
 */
function bp_attachments_set_dummy_post() {
	// Use the Attachments directory title by default.
	$title = bp_get_directory_title( 'attachments' );

	$medium_action = bp_attachments_current_media_action();

	// Downloads are intercepted before in the loading process.
	if ( $medium_action && ! bp_attachments_is_media_download() ) {
		$medium = bp_attachments_get_queried_object();
		$title  = $medium->title;
	}

	bp_theme_compat_reset_post(
		array(
			'ID'             => 0,
			'post_title'     => $title,
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'page',
			'post_status'    => 'publish',
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
	add_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );

	// By default the Attachments directory template.
	$template = 'attachments/index';

	$medium_action = bp_attachments_current_media_action();

	// Downloads are intercepted before in the loading process.
	if ( $medium_action && ! bp_attachments_is_media_download() ) {
		$template = 'attachments/single/' . $medium_action;
	}

	$content_template = bp_buffer_template_part( $template, null, false );

	// Stop overridding the BuddyPress Template Stack.
	remove_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );

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
