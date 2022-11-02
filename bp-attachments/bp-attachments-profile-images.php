<?php
/**
 * BP Attachments Profile Images feature Functions.
 *
 * Profile Images feature includes: profile photo and cover image for
 * members or groups.
 *
 * @package \bp-attachments\bp-attachments-profile-images
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks whether avatar uploads feature is enabled.
 *
 * @since 1.0.0
 *
 * @return bool True if the avatar uploads feature is enabled. False otherwise.
 */
function bp_attachments_is_avatar_uploads_enabled() {
	return ! (int) bp_get_option( 'bp-disable-avatar-uploads' );
}

/**
 * Only disable BuddyPress Member's avatar upload feature.
 *
 * @since 1.0.0
 *
 * @param bool $retval True if the BP Avatar UI should be loaded. False otherwise.
 * @return bool
 */
function bp_attachments_is_avatar_front_edit( $retval ) {
	if ( true === $retval ) {
		$retval = ! bp_is_user_change_avatar();
	}

	return $retval;
}
add_filter( 'bp_avatar_is_front_edit', 'bp_attachments_is_avatar_front_edit' );


/**
 * Only disable BuddyPress Member's cover image upload feature.
 *
 * @since 1.0.0
 * @todo Finish the feature.
 *
 * @param bool $retval True if the BP Cover Image UI should be loaded. False otherwise.
 * @return bool
 */
function bp_attachments_is_cover_image_front_edit( $retval ) {
	if ( true === $retval ) {
		$retval = ! bp_is_user_change_cover_image();
	}

	return $retval;
}
// phpcs:disable
// add_filter( 'bp_attachments_cover_image_is_edit', 'bp_attachments_is_cover_image_front_edit' );
// phpcs:enable

/**
 * Register JavaScripts and Styles profile images features for Front-End context.
 *
 * @since 1.0.0
 */
function bp_attachments_profile_images_register_front_end_assets() {
	$bp_attachments = buddypress()->attachments;

	wp_register_script(
		'bp-attachments-avatar-editor',
		$bp_attachments->js_url . 'avatar-editor/index.js',
		array(
			'wp-blob',
			'wp-element',
			'wp-components',
			'wp-i18n',
			'wp-dom-ready',
			'wp-data',
			'wp-api-fetch',
			'lodash',
		),
		$bp_attachments->version,
		true
	);

	wp_register_style(
		'bp-attachments-avatar-editor-styles',
		$bp_attachments->assets_url . 'front-end/avatar-editor.css',
		array( 'dashicons', 'wp-components' ),
		$bp_attachments->version
	);
}
add_action( 'bp_attachments_register_front_end_assets', 'bp_attachments_profile_images_register_front_end_assets' );

/**
 * Enqueues the css and js required by profile images features' front-end interfaces.
 *
 * @since 1.0.0
 */
function bp_attachments_profile_images_enqueue_front_end_assets() {
	// Only play with Members & avatars for now.
	$bp_avatar_is_front_edit = bp_is_user_change_avatar() && ! bp_core_get_root_option( 'bp-disable-avatar-uploads' );

	if ( $bp_avatar_is_front_edit ) {
		wp_enqueue_style( 'bp-attachments-avatar-editor-styles' );

		$avatar = bp_get_displayed_user_avatar(
			array(
				'type' => 'full',
				'html' => 'false',
			)
		);

		$full_avatar_width  = bp_core_avatar_full_width();
		$full_avatar_height = bp_core_avatar_full_height();

		if ( $avatar ) {
			wp_add_inline_style(
				'bp-attachments-avatar-editor-styles',
				'
				#buddypress #item-header-cover-image #item-header-avatar {
					background-image: url( ' . $avatar . ' );
				}

				#buddypress #item-header-cover-image #item-header-avatar #bp-avatar-editor {
					width: ' . $full_avatar_width . 'px;
					height: ' . $full_avatar_height . 'px;
				}
				'
			);
		}

		wp_enqueue_script( 'bp-attachments-avatar-editor' );

		/**
		 * Add a setting to inform whether the Media Library is used form
		 * the Community Media Library Admin screen or not.
		 */
		$settings = apply_filters(
			'bp_attachments_avatar_editor',
			array(
				'isAdminScreen'     => is_admin(),
				'maxUploadFileSize' => bp_core_avatar_original_max_filesize(),
				'allowedExtTypes'   => bp_attachments_get_allowed_types( 'avatar' ),
				'displayedUserId'   => bp_displayed_user_id(),
				'avatarFullWidth'   => $full_avatar_width,
				'avatarFullHeight'  => $full_avatar_height,
			)
		);

		wp_add_inline_script(
			'bp-attachments-avatar-editor',
			'window.bpAttachmentsAvatarEditorSettings = ' . wp_json_encode( $settings ) . ';'
		);
	}
}
add_action( 'bp_attachments_register_front_end_assets', 'bp_attachments_profile_images_enqueue_front_end_assets' );

/**
 * Override the profile images features templates when needed.
 *
 * @since 1.0.0
 *
 * @param bool  $is_overriding Whether the template is being overriden.
 * @param array $templates     The list of requested template parts, passed by reference.
 * @return bool True if the template is being overriden. False otherwise.
 */
function bp_attachments_profile_images_template_part_overrides( $is_overriding = false, &$templates = array() ) {
	if ( in_array( 'members/single/profile/change-avatar.php', $templates, true ) ) {
		$is_overriding = true;
		array_unshift( $templates, 'members/single/profile/edit-avatar.php' );
	} elseif ( in_array( 'members/single/cover-image-header.php', $templates, true ) && bp_is_user_change_avatar() ) {
		$is_overriding = true;
		array_unshift( $templates, 'members/single/cover-image-header-edit.php' );
	} elseif ( in_array( 'members/single/member-header.php', $templates, true ) && bp_is_user_change_avatar() ) {
		$is_overriding = true;
		array_unshift( $templates, 'members/single/member-header-edit.php' );
	}

	// phpcs:disable
	/*
	 * @todo cover image overrides.
	 *
	 * elseif ( in_array( 'members/single/profile/change-cover-image.php', $templates, true ) ) {
	 *	$is_overriding = true;
	 *  array_unshift( $templates, 'members/single/profile/edit-cover-image.php' );
	 * } elseif ( in_array( 'members/single/cover-image-header.php', $templates, true ) && bp_is_user_change_cover_image() ) {
	 *	$is_overriding = true;
	 *	array_unshift( $templates, 'members/single/cover-image-header-edit.php' );
	 * }
	 */
	// phpcs:enable

	return $is_overriding;
}
add_filter( 'bp_attachments_template_part_overrides', 'bp_attachments_profile_images_template_part_overrides', 10, 2 );

/**
 * BuddyPress Template Hook's wrapper firing before change-avatar content.
 *
 * @since 1.0.0
 */
function bp_attachments_before_edit_avatar_content() {
	/**
	 * Fires before the display of profile avatar upload content.
	 *
	 * @since BuddyPress 1.1.0
	 */
	do_action( 'bp_before_profile_avatar_upload_content' );
}

/**
 * BuddyPress Template Hook's wrapper firing before change-avatar content.
 *
 * @since 1.0.0
 */
function bp_attachments_after_edit_avatar_content() {
	/**
	 * Fires after the display of profile avatar upload content.
	 *
	 * @since BuddyPress 1.1.0
	 */
	do_action( 'bp_after_profile_avatar_upload_content' );
}

/**
 * BuddyPress Template Hook's wrapper firing before member's header.
 *
 * @since 1.0.0
 */
function bp_attachments_before_member_header() {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		/**
		 * Fires before the display of a member's header.
		 *
		 * @since BuddyPress 1.2.0
		 */
		do_action( 'bp_before_member_header' );
	}
}

/**
 * BuddyPress Template Hook's wrapper firing after member's header.
 *
 * @since 1.0.0
 */
function bp_attachments_after_member_header() {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		/**
		 * Fires after the display of a member's header.
		 *
		 * @since BuddyPress 1.2.0
		 */
		do_action( 'bp_after_member_header' );
		?>
		<div id="template-notices" role="alert" aria-atomic="true">
			<?php
			/** This action is documented in bp-templates/bp-legacy/buddypress/activity/index.php */
			do_action( 'template_notices' );
			?>
		</div>
		<?php
	}
}

/**
 * Outputs member's header action buttons.
 *
 * @since 1.0.0
 *
 * @param array  $args A list of arguments.
 * @param string $show Used to show both or one of ['nouveau', 'legacy'] buttons.
 */
function bp_attachments_member_header_buttons( $args = array(), $show = 'all' ) {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		if ( 'all' !== $show || 'legacy' !== $show ) {
			return;
		}
		?>
		<div id="item-buttons">
			<?php
			/**
			 * Fires in the member header actions section.
			 *
			 * @since BuddyPress 1.2.6
			 */
			do_action( 'bp_member_header_actions' );
			?>
		</div>
		<?php
	} else {
		if ( 'all' !== $show || 'nouveau' !== $show ) {
			return;
		}
		bp_nouveau_member_header_buttons( $args );
	}
}

/**
 * Outputs member's header actions.
 *
 * @since 1.0.0
 */
function bp_attachments_member_header_actions() {
	if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) {
		?>
		<h2 class="user-nicename">@<?php bp_displayed_user_mentionname(); ?></h2>
		<?php
	}

	if ( bp_displayed_user_use_cover_image_header() ) {
		bp_attachments_member_header_buttons(
			array(
				'container'         => 'ul',
				'button_element'    => 'button',
				'container_classes' => array( 'member-header-actions' ),
			)
		);
	}

	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		?>
		<span class="activity" data-livestamp="<?php bp_core_iso8601_date( bp_get_user_last_activity( bp_displayed_user_id() ) ); ?>">
			<?php bp_last_activity( bp_displayed_user_id() ); ?>
		</span>
		<?php
	}

	/**
	 * Fires before the display of the member's header meta.
	 *
	 * @since BuddyPress 1.2.0
	 */
	do_action( 'bp_before_member_header_meta' );
}

/**
 * Checks whether the member header has meta to output.
 *
 * @since 1.0.0
 *
 * @return bool True if the member's header has meta to output. False otherwise.
 */
function bp_attachments_member_has_meta() {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		return true;
	} else {
		return bp_nouveau_member_has_meta();
	}
}

/**
 * Outputs member's header meta.
 *
 * @since 1.0.0
 */
function bp_attachments_member_meta() {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		if ( bp_is_active( 'activity' ) ) {
			bp_activity_latest_update( bp_displayed_user_id() );
		}
	} else {
		bp_nouveau_member_meta();
	}
}

/**
 * BuddyPress Template Hook's wrapper firing into the member's header meta container.
 *
 * @since 1.0.0
 */
function bp_attachments_member_after_meta() {
	/**
	 * Fires after the group header actions section.
	 *
	 * If you'd like to show specific profile fields here use:
	 * bp_member_profile_data( 'field=About Me' ); -- Pass the name of the field
	 *
	 * @since BuddyPress 1.2.0
	 */
	do_action( 'bp_profile_header_meta' );
}

/**
 * Outputs the list of member types of the displayed member.
 *
 * @since 1.0.0
 */
function bp_attachments_member_type_list() {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		return '';
	}

	bp_member_type_list(
		bp_displayed_user_id(),
		array(
			'label'        => array(
				'plural'   => __( 'Member Types', 'bp-attachments' ),
				'singular' => __( 'Member Type', 'bp-attachments' ),
			),
			'list_element' => 'span',
		)
	);
}

/**
 * BuddyPress Template Hook's wrapper firing before the member's edit cover image content.
 *
 * @since 1.0.0
 */
function bp_attachments_member_before_edit_cover_image() {
	/**
	 * Fires before the display of profile cover image upload content.
	 *
	 * @since 2.4.0
	 */
	do_action( 'bp_before_profile_edit_cover_image' );
}

/**
 * BuddyPress Template Hook's wrapper firing after the member's edit cover image content.
 *
 * @since 1.0.0
 */
function bp_attachments_member_after_edit_cover_image() {
	/**
	 * Fires after the display of profile cover image upload content.
	 *
	 * @since 2.4.0
	 */
	do_action( 'bp_after_profile_edit_cover_image' );
}
