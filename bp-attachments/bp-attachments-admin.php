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
 * Get the BP Attachments DB tables schema.
 *
 * @since 1.0.0
 *
 * @return array The list of SQL commands to perform during installation.
 */
function bp_attachments_get_media_schema() {
	if ( ! function_exists( 'wp_get_db_schema' ) ) {
		require_once ABSPATH . 'wp-admin/includes/schema.php';
	}

	$wp_db = bp_attachments_get_db();
	$sql   = array();

	$wp_db_schema    = wp_get_db_schema( 'blog' );
	$charset_collate = $wp_db->get_charset_collate();
	$bp_prefix       = bp_core_get_table_prefix();

	// First the object table.
	preg_match( "#CREATE TABLE {$wp_db->posts} \((.*?)\) {$charset_collate};#is", $wp_db_schema, $posts_schema );
	$object_sql_lines = array();

	if ( $posts_schema[1] ) {
		$sql_lines      = explode( ",\n", $posts_schema[1] );
		$remove_fields  = 'post_excerpt comment_status ping_status post_password guid to_ping pinged menu_order';
		$replace_fields = 'post_content_filtered comment_count';

		foreach ( $sql_lines as $sql_line ) {
			$first_token = strtok( trim( $sql_line, " \n\t" ), ' ' );

			if ( false !== strpos( $remove_fields, $first_token ) ) {
				continue;
			}

			if ( false !== strpos( $replace_fields, $first_token ) ) {
				$sql_line = str_replace( array( 'post_content_filtered', 'comment_count' ), array( 'uploads_relative_path', 'download_count' ), $sql_line );
			}

			if ( ! in_array( $sql_line, $object_sql_lines, true ) ) {
				$object_sql_lines[] = "\t" . trim( $sql_line, "\n\t" );
			}
		}
	}

	// Then the meta table.
	preg_match( "#CREATE TABLE {$wp_db->postmeta} \((.*?)\) {$charset_collate};#is", $wp_db_schema, $postmeta_schema );
	$meta_sql_lines = '';

	if ( $postmeta_schema[1] ) {
		preg_match( "#post_id (.*) '0',#is", $postmeta_schema[1], $post_id );

		if ( $post_id[0] ) {
			$meta_sql_lines = str_replace(
				$post_id[0],
				str_replace( 'post_id', 'media_id', $post_id[0] ) . "\n\tobject_type varchar(50) NOT NULL default '',",
				$postmeta_schema[1]
			);
		}
	}

	return array(
		"CREATE TABLE {$bp_prefix}bp_attachments (\n" . join( ",\n", $object_sql_lines ) . "\n) {$charset_collate}",
		"CREATE TABLE {$bp_prefix}bp_attachment_meta (" . $meta_sql_lines . ") {$charset_collate}",
	);
}

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
