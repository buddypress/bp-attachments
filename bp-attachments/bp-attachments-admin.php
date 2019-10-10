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
 * Display the BuddyPress Media Admin screen.
 *
 * @since 1.0.0
 */
function bp_attachments_admin_media() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'User Media', 'bp-attachments' ); ?></h1>
	</div>
	<?php
}
