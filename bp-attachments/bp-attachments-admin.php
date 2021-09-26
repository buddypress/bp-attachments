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
		1
	);
}
add_action( 'admin_menu', 'bp_attachments_admin_menu' );

/**
 * Register JavaScripts and Styles for WP Admin context.
 *
 * @since 1.0.0
 */
function bp_attachments_admin_register_scripts() {
	$bp_attachments = buddypress()->attachments;

	wp_register_script(
		'bp-attachments-uploader',
		$bp_attachments->js_url . 'uploader/index.js',
		array(
			'wp-element',
			'wp-components',
			'wp-compose',
			'wp-i18n',
			'wp-data',
			'wp-api-fetch',
			'lodash',
		),
		$bp_attachments->version,
		true
	);

	wp_register_style(
		'bp-attachments-admin',
		$bp_attachments->assets_url . 'admin/style.css',
		array( 'dashicons', 'wp-components' ),
		$bp_attachments->version
	);
}
add_action( 'bp_admin_enqueue_scripts', 'bp_attachments_admin_register_scripts', 1 );

/**
 * Inline styles for the settings page.
 *
 * @since 1.0.0
 */
function bp_attachments_admin_enqueue_common_scripts() {
	wp_add_inline_style(
		'bp-admin-common-css',
		'.settings_page_bp-components tr.attachments td.plugin-title span:before {
			content: "\f104";
		}'
	);
}
add_action( 'bp_admin_enqueue_scripts', 'bp_attachments_admin_enqueue_common_scripts', 20 );

/**
 * Display the BuddyPress Media Admin screen.
 *
 * @since 1.0.0
 */
function bp_attachments_admin_media() {
	// Style.
	wp_enqueue_style( 'bp-attachments-admin' );

	// JavaScript.
	wp_enqueue_script( 'bp-attachments-uploader' );

	// Preload the current user's data.
	$preload_logged_in_user = array_reduce(
		array( '/buddypress/v1/members/me?context=edit' ),
		'rest_preload_api_request',
		array()
	);

	// Create the Fetch API Preloading middleware.
	wp_add_inline_script(
		'wp-api-fetch',
		sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload_logged_in_user ) ),
		'after'
	);
	?>

	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Community Media Library', 'bp-attachments' ); ?></h1>
		<div id="bp-media-admin-page-title-actions"></div>
		<hr class="wp-header-end">

		<div id="bp-media-uploader"></div>

		<?php bp_attachments_get_javascript_templates(); ?>
	</div>

	<?php
}
