<?php
/**
 * BP Attachments Admin.
 *
 * @package \bp-attachments\bp-attachments-admin
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Install the plugin.
 *
 * @since 1.0.0
 */
function bp_attachments_install() {
	// Install the public uploads dir.
	add_filter( 'upload_dir', 'bp_attachments_get_public_uploads_dir' );

	$public_uploads = wp_upload_dir();

	remove_filter( 'upload_dir', 'bp_attachments_get_public_uploads_dir' );
}

/**
 * Checks whether it's needed to install or update the plugin.
 *
 * @since 1.0.0
 */
function bp_attachments_version_updater() {
	$version = bp_attachments_get_version();

	if ( bp_attachments_is_install() || bp_attachments_is_update() ) {
		bp_attachments_install();

		// @todo Put upgrade tasks here when needed & after checking `bp_attachments_is_update()`.

		bp_update_option( '_bp_attachments_version', $version );
	}
}
add_action( 'admin_init', 'bp_attachments_version_updater', 1001 );

/**
 * Add a Sub Menu to the WordPress Media menu.
 *
 * @since 1.0.0
 */
function bp_attachments_admin_menu() {
	$media_page_title = __( 'Community Library', 'bp-attachments' );
	if ( bp_current_user_can( 'bp_moderate' ) ) {
		$media_page_title = __( 'Community Libraries', 'bp-attachments' );
	}

	add_media_page(
		$media_page_title,
		$media_page_title,
		'exist',
		'community-library',
		'bp_attachments_admin_media',
		10
	);
}
add_action( 'admin_menu', 'bp_attachments_admin_menu' );

/**
 * Display the BuddyPress Media Admin screen.
 *
 * @since 1.0.0
 */
function bp_attachments_admin_media() {
	// Style.
	wp_enqueue_style( 'bp-attachments-media-library' );

	// Enqueue the Media Library.
	bp_attachments_enqueue_media_library();

	echo ( '<div class="wrap bp-attachments-media-list" id="bp-media-library"></div>' );
	bp_attachments_get_javascript_template();
}

/**
 * Enqueue JavaScript and Styles for the Mime Types settings.
 *
 * @since 1.0.0
 */
function bp_attachments_admin_load_settings() {
	wp_enqueue_style( 'site-health' );
	wp_add_inline_style(
		'site-health',
		'.health-check-accordion table.widefat { border: none; box-shadow: none; }
		.health-check-accordion table.widefat th.check-column { vertical-align: middle; padding: 0; }'
	);
	wp_enqueue_script( 'bp-attachments-admin' );
}
add_action( 'load-settings_page_bp-settings', 'bp_attachments_admin_load_settings' );
