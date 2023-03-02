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

	/**
	 * Perform complementary install tasks.
	 *
	 * @since 1.0.0
	 */
	do_action( 'bp_attachments_install' );
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
add_action( 'bp_admin_init', 'bp_attachments_version_updater', 1001 );

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

/**
 * Adds a link to BP Attachments options when needed.
 *
 * @since 1.0.0
 *
 * @param array  $links Links array in which we would prepend our link.
 * @param string $file  Current plugin basename.
 * @return array Processed links.
 */
function bp_attachments_admin_plugin_action_links( $links = array(), $file = '' ) {
	// Return normal links if not BP Attachments.
	if ( 'bp-attachments/class-bp-attachments.php' !== $file ) {
		return $links;
	}

	$settings_page = 'options-general.php';
	if ( bp_core_do_network_admin() ) {
		$settings_page = 'settings.php';
	}

	$options_link = add_query_arg(
		array(
			'page' => 'bp-settings',
		),
		bp_get_admin_url( $settings_page )
	);

	// Add a links to Attachments options.
	return array_merge(
		$links,
		array(
			'settings' => '<a href="' . esc_url( $options_link ) . '#bp-attachments">' . esc_html__( 'Settings', 'bp-attachments' ) . '</a>',
		)
	);
}
add_filter( 'plugin_action_links', 'bp_attachments_admin_plugin_action_links', 10, 2 );
add_filter( 'network_admin_plugin_action_links', 'bp_attachments_admin_plugin_action_links', 10, 2 );
