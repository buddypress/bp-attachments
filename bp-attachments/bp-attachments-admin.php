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
	add_media_page(
		__( 'Community Library', 'bp-attachments' ),
		__( 'Community Library', 'bp-attachments' ),
		'manage_options', // Restrict the menu to Site Admins during development process.
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
	wp_enqueue_style( 'bp-attachments-admin' );

	// JavaScript.
	wp_enqueue_script( 'bp-attachments-media-library' );

	$is_admin_screen = ! defined( 'IFRAME_REQUEST' ) && is_admin();
	$context         = 'edit';
	if ( ! $is_admin_screen ) {
		$context = 'view';
	}

	// Preload the current user's data.
	$preload_logged_in_user = array_reduce(
		array(
			sprintf( '/buddypress/v1/members/me?context=%s', $context ),
			sprintf( '/buddypress/v1/attachments?context=%s', $context ),
		),
		'rest_preload_api_request',
		array()
	);

	// Create the Fetch API Preloading middleware.
	wp_add_inline_script(
		'wp-api-fetch',
		sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload_logged_in_user ) ),
		'after'
	);

	/**
	 * Add a setting to inform whether the Media Library is used form
	 * the Community Media Library Admin screen or not.
	 */
	$settings = apply_filters(
		'bp_attachments_media_library_admin',
		array(
			'isAdminScreen'         => $is_admin_screen,
			'maxUploadFileSize'     => wp_max_upload_size(),
			'allowedExtByMediaList' => bp_attachments_get_exts_by_medialist(),
			'allowedExtTypes'       => bp_attachments_get_allowed_media_exts( '', true ),
		)
	);

	wp_add_inline_script(
		'bp-attachments-media-library',
		'window.bpAttachmentsMediaLibrarySettings = ' . wp_json_encode( $settings ) . ';'
	);

	echo ( '<div class="wrap" id="bp-media-library"></div>' );
	bp_attachments_get_javascript_templates();
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
		'.health-check-accordion table.widefat { border: none; }
		.health-check-accordion table.widefat th.check-column { vertical-align: middle; padding: 0; }'
	);
	wp_enqueue_script( 'bp-attachments-admin' );
}
add_action( 'load-settings_page_bp-settings', 'bp_attachments_admin_load_settings' );
