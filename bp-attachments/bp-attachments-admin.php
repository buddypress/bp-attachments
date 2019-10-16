<?php
/**
 * BP Attachments Admin.
 *
 * @package BP Attachments
 * @subpackage \bp-attachments\bp-attachments-admin
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Install the plugin.
 *
 * @since 1.0.0
 */
function bp_attachments_install() {
	// 1. Install the private uploads dir.
	add_filter( 'upload_dir', 'bp_attachments_get_private_uploads_dir' );

	$private_uploads = wp_upload_dir();
	$private_dir     = trailingslashit( $private_uploads['path'] );

	remove_filter( 'upload_dir', 'bp_attachments_get_private_uploads_dir' );

	if ( ! file_exists( $private_dir . '/.htaccess' ) ) {
		// Include admin functions to get access to insert_with_markers().
		require_once ABSPATH . 'wp-admin/includes/misc.php';

		$home = trailingslashit( get_option( 'home' ) );
		$base = wp_parse_url( $home, PHP_URL_PATH );

		// Defining the rule: users need to be logged in to access private media.
		$rules = array(
			'<IfModule mod_rewrite.c>',
			'RewriteEngine On',
			sprintf( 'RewriteBase %s', $base ),
			'RewriteCond %{HTTP_COOKIE} !^.*wordpress_logged_in.*$ [NC]',
			'RewriteRule  .* wp-login.php [NC,L]',
			'</IfModule>',
		);

		// Create the .htaccess file.
		insert_with_markers( $private_dir . '/.htaccess', 'BP Attachments', $rules );
	}

	// 2. Install the public uploads dir.
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
		__( 'User Media', 'bp-attachments' ),
		__( 'BuddyPress', 'bp-attachments' ),
		'upload_files',
		'bp-user-media',
		'bp_attachments_admin_media'
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
			'wp-i18n',
			'wp-api-fetch',
			'lodash',
		),
		$bp_attachments->version,
		true
	);

	wp_register_style(
		'bp-attachments-admin',
		$bp_attachments->assets_url . 'admin/style.css',
		array(),
		$bp_attachments->version
	);
}
add_action( 'bp_admin_enqueue_scripts', 'bp_attachments_admin_register_scripts', 1 );

/**
 * Display the BuddyPress Media Admin screen.
 *
 * @since 1.0.0
 */
function bp_attachments_admin_media() {
	wp_enqueue_script( 'bp-attachments-uploader' );
	wp_enqueue_style( 'bp-attachments-admin' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'User Media', 'bp-attachments' ); ?></h1>
		<div id="bp-media-uploader"></div>
	</div>
	<?php
}
