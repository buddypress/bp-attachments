<?php
/**
 * BP Attachments Settings.
 *
 * @package \bp-attachments\bp-attachments-settings
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays the option to enable private uploads.
 *
 * @since 1.0.0
 */
function bp_attachments_enable_private_uploads_callback() {
	$option           = bp_attachments_can_do_private_uploads();
	$private_dir      = bp_attachments_get_private_root_dir();
	$private_dir_path = sprintf(
		'<code>%s</code>',
		trailingslashit( bp_attachments_get_document_root() ) . 'buddypress-private'
	);
	$uploads_dir      = '<code>/wp-content/uploads</code>';

	if ( is_wp_error( $private_dir ) ) {
		printf( '<p class="attention">%s</p>', esc_html( $private_dir->get_error_message() ) );

		$info = $private_dir->get_error_data();
		if ( $info ) {
			$description = '<ol>';

			// Group id differs.
			if ( isset( $info['gid'] ) ) {
				$message = sprintf(
					/* translators: 1. is The private root directory absolute path. 2. is the uploads relative path. */
					esc_html__( 'The %1$s directory needs to share the same user group than the %2$s one.', 'bp-attachments' ),
					$private_dir_path,
					$uploads_dir
				);

				if ( function_exists( 'posix_getgrgid' ) ) {
					$gdata = posix_getgrgid( $info['gid'] );

					if ( isset( $gdata['name'] ) ) {
						/* translators: 1. The private root directory absolute path. 2. The server's user group */
						$message = sprintf( esc_html__( 'The %1$s directory needs to have "%2$s" as its user group.', 'bp-attachments' ), $private_dir_path, $gdata['name'] );
					}
				}

				$description .= "\n<li>" . $message . '</li>';
			}

			// User id differs.
			if ( isset( $info['uid'] ) ) {
				$message = sprintf(
					/* translators: 1. is The private root directory absolute path. 2. is the uploads relative path. */
					esc_html__( 'The %s directory needs to be owned by the same user than the WordPress uploads one.', 'bp-attachments' ),
					$private_dir_path,
					$uploads_dir,
				);

				if ( function_exists( 'posix_getpwuid' ) ) {
					$udata = posix_getpwuid( $info['uid'] );

					if ( isset( $udata['name'] ) ) {
						/* translators: 1. The private root directory absolute path. 2. The server's user name */
						$message = sprintf( esc_html__( 'The %1$s directory needs be owned by "%2$s".', 'bp-attachments' ), $private_dir_path, $udata['name'] );
					}
				}

				$description .= "\n<li>" . $message . '</li>';
			}

			// Perms differ.
			if ( isset( $info['perms'] ) ) {
				$message = sprintf(
					/* translators: 1. The private root directory absolute path. 2. is the uploads relative path. 3. The WP uploads directory file permissions. */
					esc_html__( 'The %1$s directory needs to share the same permissions than the WordPress %2$s one: %3$s.', 'bp-attachments' ),
					$private_dir_path,
					$uploads_dir,
					substr( decoct( $info['perms'] ), -3 )
				);

				$description .= "\n<li>" . $message . '</li>';
			}

			$description .= "\n</ol>";
			echo wp_kses_post( $description );
		} else {
			printf(
				'<p class="description">%s</p>',
				sprintf(
					/* translators: %s is The private root directory absolute path */
					esc_html__( 'Please make sure the %s exists and is writeable for the server’s user.', 'bp-attachments' ),
					$private_dir_path // phpcs:ignore
				)
			);
		}
	} else {
		?>
		<input id="bp-attachments-can-upload-privately" name="_bp_attachments_can_upload_privately" type="checkbox" value="1" <?php checked( $option ); ?> />
		<label for="bp-attachments-can-upload-privately"><?php esc_html_e( 'Allow users to upload and share attachments privately.', 'buddypress' ); ?></label>
		<?php
	}
}

/**
 * Registers BP Attachments settings into BuddyPress Options page.
 *
 * @since 1.0.0
 */
function bp_attachments_register_settings() {
	register_setting(
		'buddypress',
		'_bp_attachments_can_upload_privately',
		array(
			'type'              => 'string',
			'description'       => __( 'BP Attachments settings to let the admin enable private uploads.', 'bp-attachments' ),
			'sanitize_callback' => 'intval',
			'show_in_rest'      => false,
			'default'           => false,
		)
	);

	add_settings_section(
		'bp_attachments_settings_section',
		'Attachments',
		'__return_empty_string',
		'buddypress'
	);

	add_settings_field(
		'_bp_attachments_can_upload_privately',
		__( 'Private Uploads', 'bp-attachments' ),
		'bp_attachments_enable_private_uploads_callback',
		'buddypress',
		'bp_attachments_settings_section'
	);
}
add_action( 'bp_register_admin_settings', 'bp_attachments_register_settings', 30 );
