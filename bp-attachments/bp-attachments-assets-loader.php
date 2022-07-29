<?php
/**
 * BP Attachments Assets loader.
 *
 * @package \bp-attachments\bp-attachments-assets-loader
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register JavaScripts and Styles for WP Admin context.
 *
 * @since 1.0.0
 */
function bp_attachments_register_admin_assets() {
	$bp_attachments = buddypress()->attachments;

	wp_register_script(
		'bp-attachments-media-library',
		$bp_attachments->js_url . 'media-library/index.js',
		array(
			'wp-element',
			'wp-components',
			'wp-compose',
			'wp-i18n',
			'wp-dom-ready',
			'wp-data',
			'wp-api-fetch',
			'wp-url',
			'lodash',
			'wp-hooks',
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
add_action( 'bp_admin_enqueue_scripts', 'bp_attachments_register_admin_assets', 1 );

/**
 * Inline styles for the WP Admin BuddyPress settings page.
 *
 * @since 1.0.0
 */
function bp_attachments_enqueue_admin_common_assets() {
	wp_add_inline_style(
		'bp-admin-common-css',
		'.settings_page_bp-components tr.attachments td.plugin-title span:before {
			content: "\f104";
		}'
	);
}
add_action( 'bp_admin_enqueue_scripts', 'bp_attachments_enqueue_admin_common_assets', 20 );

/**
 * Register JavaScripts and Styles for Front-End context.
 *
 * @since 1.0.0
 */
function bp_attachments_register_front_end_assets() {
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
add_action( 'bp_enqueue_scripts', 'bp_attachments_register_front_end_assets', 1 );

/**
 * Enqueues the css and js required by the front-end interfaces.
 *
 * @since 1.0.0
 */
function bp_attachments_enqueue_front_end_assets() {
	// Only play with Members for now.
	$bp_avatar_is_front_edit = bp_is_user_change_avatar() && ! bp_core_get_root_option( 'bp-disable-avatar-uploads' );

	if ( $bp_avatar_is_front_edit ) {
		wp_enqueue_style( 'bp-attachments-avatar-editor-styles' );

		$avatar = bp_get_displayed_user_avatar(
			array(
				'type' => 'full',
				'html' => 'false',
			)
		);

		if ( $avatar ) {
			wp_add_inline_style(
				'bp-attachments-avatar-editor-styles',
				'
				#buddypress #item-header-cover-image #item-header-avatar {
					background-image: url( ' . $avatar . ' );
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
			)
		);

		wp_add_inline_script(
			'bp-attachments-avatar-editor',
			'window.bpAttachmentsAvatarEditorSettings = ' . wp_json_encode( $settings ) . ';'
		);
	}
}
add_action( 'bp_enqueue_community_scripts', 'bp_attachments_enqueue_front_end_assets' );
