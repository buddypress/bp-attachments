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
	$private_dir      = bp_attachments_get_private_root_dir( true );
	$private_dir_path = sprintf(
		'<code>%s</code>',
		trailingslashit( dirname( bp_attachments_get_document_root() ) ) . 'buddypress-private'
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
					$uploads_dir
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
		<label for="bp-attachments-can-upload-privately"><?php esc_html_e( 'Allow users to upload and share attachments privately.', 'bp-attachments' ); ?></label>
		<?php
	}
}

/**
 * Displays the option to select the allowed media types.
 *
 * @since 1.0.0
 */
function bp_attachments_allowed_media_types_callback() {
	$types         = wp_get_ext_types();
	$allowed_types = array();

	foreach ( array_keys( $types ) as $type ) {
		$allowed_types[ $type ] = bp_attachments_get_allowed_types( $type );
	}

	$i18n_types    = bp_attachments_get_i18n_media_type( $allowed_types );
	$allowed_mimes = bp_attachments_get_allowed_mimes( '' );

	// `bp_get_option()` does not include the default value set during the settings registration.
	$setting      = get_option( '_bp_attachments_allowed_media_types' );
	$printed_mime = array();
	?>
	<div class="health-check-accordion">
		<?php
		foreach ( $i18n_types as $k_type => $i18n_type ) {
			?>
			<h3 class="health-check-accordion-heading">
				<button aria-expanded="false" class="health-check-accordion-trigger" aria-controls="health-check-accordion-block-<?php echo esc_attr( $k_type ); ?>-extensions" type="button">
					<span class="title"><?php echo esc_html( $i18n_type ); ?></span>
					<span class="icon"></span>
				</button>
			</h3>

			<div id="health-check-accordion-block-<?php echo esc_attr( $k_type ); ?>-extensions" class="health-check-accordion-panel" hidden="hidden">
				<table class="form-table widefat" role="presentation">
					<thead>
						<tr>
							<th class="check-column">
								<input
									id="bp-attachments-selectall-<?php echo esc_attr( $k_type ); ?>"
									type="checkbox" class="bp-attachments-selectall"
									data-mime-type="<?php echo esc_attr( $k_type ); ?>"
									<?php checked( true, isset( $setting[ $k_type ] ) ); ?>
								>
							</th>
							<td>
								<label for="bp-attachments-selectall-<?php echo esc_attr( $k_type ); ?>">
									<?php
									echo sprintf(
										/* Translators: %s is the category name of the listed mime types. */
										esc_html__( 'Select/Unselect all %s mime types', 'bp-attachments' ),
										esc_html( strtolower( $i18n_type ) )
									);
									?>
								</label>
							</td>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $allowed_types[ $k_type ] as $bp_type ) {
							if ( isset( $allowed_mimes[ $bp_type ] ) && ! in_array( $allowed_mimes[ $bp_type ], $printed_mime, true ) ) {
								array_push( $printed_mime, $allowed_mimes[ $bp_type ] );
								$sub_type_id = str_replace( $k_type . '/', '', $allowed_mimes[ $bp_type ] );
								?>
								<tr>
									<th class="check-column">
										<input
											id="bp-attachments_mime_type-<?php echo esc_attr( $sub_type_id ); ?>"
											type="checkbox"
											name="_bp_attachments_allowed_media_types[<?php echo esc_attr( $k_type ); ?>][]"
											data-mime-type="<?php echo esc_attr( $k_type ); ?>"
											value="<?php echo esc_attr( $allowed_mimes[ $bp_type ] ); ?>"
											<?php isset( $setting[ $k_type ] ) ? checked( true, in_array( $allowed_mimes[ $bp_type ], $setting[ $k_type ], true ) ) : ''; ?>
										>
									</th>
									<td>
										<label for="bp-attachments_mime_type-<?php echo esc_attr( $sub_type_id ); ?>">
											<?php echo esc_html( $allowed_mimes[ $bp_type ] ); ?>
										</label>
									</td>
								</tr>
								<?php
							}
						}
						?>
					</tbody>
				</table>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}

/**
 * Allowed media types setting sanitize callback.
 *
 * @since 1.0.0
 *
 * @param array $types The submitted mime types.
 */
function bp_attachments_sanitize_allowed_media_types( $types = array() ) {
	if ( ! $types || ! is_array( $types ) ) {
		return array();
	}

	foreach ( array_keys( $types ) as $type ) {
		$allowed_types[ $type ] = array_unique( array_values( bp_attachments_get_allowed_mimes( $type ) ) );
		$types[ $type ]         = array_intersect( $types[ $type ], $allowed_types[ $type ] );
	}

	return $types;
}

/**
 * Adds an empty div containing an anchor before the settings content.
 *
 * @since 1.0.0
 */
function bp_attachments_settings_section_callback() {
	echo '<div id="bp-attachments"></div>';
}

/**
 * Registers BP Attachments settings into BuddyPress Options page.
 *
 * @since 1.0.0
 */
function bp_attachments_register_settings() {
	register_setting(
		'buddypress',
		'_bp_attachments_allowed_media_types',
		array(
			'type'              => 'array',
			'description'       => __( 'BP Attachments settings to store the allowed media types.', 'bp-attachments' ),
			'sanitize_callback' => 'bp_attachments_sanitize_allowed_media_types',
			'show_in_rest'      => false,
			'default'           => bp_attachments_get_default_allowed_media_types(),
		)
	);

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
		'bp_attachments_settings_section_callback',
		'buddypress'
	);

	add_settings_field(
		'_bp_attachments_allowed_media_types',
		__( 'Allowed media types', 'bp-attachments' ),
		'bp_attachments_allowed_media_types_callback',
		'buddypress',
		'bp_attachments_settings_section'
	);

	add_settings_field(
		'_bp_attachments_can_upload_privately',
		__( 'Private media', 'bp-attachments' ),
		'bp_attachments_enable_private_uploads_callback',
		'buddypress',
		'bp_attachments_settings_section'
	);
}
add_action( 'bp_register_admin_settings', 'bp_attachments_register_settings', 30 );
