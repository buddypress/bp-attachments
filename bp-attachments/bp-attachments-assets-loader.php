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

	wp_register_script(
		'bp-attachments-admin',
		$bp_attachments->js_url . 'admin/index.js',
		array(
			'wp-dom-ready',
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

	wp_register_script(
		'bp-attachments-playlist',
		$bp_attachments->js_url . 'front-end/playlist.js',
		array(
			'wp-dom-ready',
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
add_action( 'bp_enqueue_community_scripts', 'bp_attachments_enqueue_front_end_assets' );

/**
 * Enqueue styles for BP Attachments views.
 *
 * @since 1.0.0
 */
function bp_attachments_enqueue_medium_view_style() {
	if ( ! bp_attachments_is_medium_view() ) {
		return;
	}

	// Temporarly overrides the BuddyPress Template Stack.
	bp_attachments_start_overriding_template_stack();

	$css = bp_locate_template_asset( 'css/attachments-media-view.css' );

	// Stop overriding the BuddyPress Template Stack.
	bp_attachments_stop_overriding_template_stack();

	// Bail if file wasn't found.
	if ( false === $css ) {
		return;
	}

	// If this replacement happens, this means the current template pack has not found the corresponding style.
	$css['uri'] = str_replace( bp_attachments_get_templates_dir(), bp_attachments_get_templates_url(), $css['uri'] );

	wp_enqueue_style(
		'bp-attachments-media-view',
		$css['uri'],
		array(),
		bp_attachments_get_version()
	);
	wp_enqueue_style( 'wp-block-post-author' );

	if ( bp_attachments_is_media_playlist_view() ) {
		wp_enqueue_style( 'wp-mediaelement' );
		wp_add_inline_style(
			'wp-mediaelement',
			'.bp-attachments-medium .wp-playlist { display: flex; justify-content: space-evenly; }
			.bp-attachments-medium .wp-playlist video { width: 60%; }
			.bp-attachments-medium .wp-playlist .wp-playlist-tracks { width: 38%; }'
		);
		wp_enqueue_script( 'bp-attachments-playlist' );

		$qv           = bp_attachments_get_queried_vars( 'data' );
		$dir_rel_path = implode(
			'/',
			array_intersect_key(
				$qv,
				array(
					'visibility'        => true,
					'relative_path'     => true,
					'current_directory' => true,
				)
			)
		);

		$endpoint = add_query_arg(
			array(
				'directory' => $dir_rel_path,
				'object'    => $qv['object'],
				'user_id'   => $qv['item_id'],
			),
			sprintf(
				'/%1$s/%2$s/attachments',
				bp_rest_namespace(),
				bp_rest_version()
			)
		);

		// Preloads Playlist/Album/folder items.
		$preload_data = array_reduce(
			array( $endpoint ),
			'rest_preload_api_request',
			array()
		);

		wp_add_inline_script(
			'bp-attachments-playlist',
			'window.bpAttachmentsPlaylistItems = ' . wp_json_encode( current( $preload_data ) ) . ';',
			'before'
		);

		add_action( 'wp_footer', 'wp_underscore_playlist_templates', 0 );
	}
}
add_action( 'bp_enqueue_community_scripts', 'bp_attachments_enqueue_medium_view_style', 20 );

/**
 * Add inline styles for BP Attachments embeds.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_embed_inline_styles() {
	if ( ! bp_attachments_is_medium_embed() ) {
		return;
	}

	// Temporarly overrides the BuddyPress Template Stack.
	bp_attachments_start_overriding_template_stack();

	$css = bp_locate_template_asset( 'css/attachments-media-embeds.css' );

	// Stop overriding the BuddyPress Template Stack.
	bp_attachments_stop_overriding_template_stack();

	// Bail if file wasn't found.
	if ( false === $css ) {
		return;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions
	$css = file_get_contents( $css['file'] );

	printf( '<style type="text/css">%s</style>', wp_kses( $css, array( "\'", '\"' ) ) );
}
add_action( 'embed_head', 'bp_attachments_medium_embed_inline_styles', 20 );
