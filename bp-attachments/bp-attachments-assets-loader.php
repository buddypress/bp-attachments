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
 * Locates a BP Attachments asset into templates / Template pack.
 *
 * @since 1.0.0
 *
 * @param string $asset The asset to locate.
 * @return array|false An array containing the uri & patch to the asset. False if no asset was found.
 */
function bp_attachments_locate_template_asset( $asset = '' ) {
	$asset_data = false;

	if ( ! $asset ) {
		return $asset_data;
	}

	// Temporarly overrides the BuddyPress Template Stack.
	bp_attachments_start_overriding_template_stack();

	$asset_data = bp_locate_template_asset( $asset );

	// Stop overriding the BuddyPress Template Stack.
	bp_attachments_stop_overriding_template_stack();

	// If this replacement happens, this means the current template pack has not found the corresponding style.
	if ( isset( $asset_data['uri'] ) ) {
		$asset_data['uri'] = str_replace( bp_attachments_get_templates_dir(), bp_attachments_get_templates_url(), $asset_data['uri'] );
	}

	return $asset_data;
}

/**
 * Register assets common to WP Admin & Front-end context.
 *
 * @since 1.0.0
 */
function bp_attachments_register_common_assets() {
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
			'wp-preferences',
		),
		$bp_attachments->version,
		true
	);

	wp_register_style(
		'bp-attachments-media-list-styles',
		$bp_attachments->assets_url . 'front-end/media-list.css',
		array(),
		$bp_attachments->version
	);

	wp_register_style(
		'bp-attachments-media-library',
		$bp_attachments->assets_url . 'media-library/style.css',
		array( 'dashicons', 'wp-components', 'bp-attachments-media-list-styles' ),
		$bp_attachments->version
	);
}
add_action( 'bp_admin_enqueue_scripts', 'bp_attachments_register_common_assets', 1 );
add_action( 'bp_enqueue_scripts', 'bp_attachments_register_common_assets', 1 );

/**
 * Register JavaScripts and Styles for WP Admin context.
 *
 * @since 1.0.0
 */
function bp_attachments_register_admin_assets() {
	$bp_attachments = buddypress()->attachments;

	wp_register_script(
		'bp-attachments-admin',
		$bp_attachments->js_url . 'admin/index.js',
		array(
			'wp-dom-ready',
		),
		$bp_attachments->version,
		true
	);
}
add_action( 'bp_admin_enqueue_scripts', 'bp_attachments_register_admin_assets', 2 );

/**
 * Enqueues the media library UI.
 *
 * @since 1.0.0
 */
function bp_attachments_enqueue_media_library() {
	// JavaScript.
	wp_enqueue_script( 'bp-attachments-media-library' );

	// Preload the current user's data & attachments one.
	$preloaded_data = array_reduce(
		array(
			'/buddypress/v1/members/me?context=edit',
			sprintf( '/buddypress/v1/attachments?context=%s', is_admin() ? 'edit' : 'view' ),
		),
		'rest_preload_api_request',
		array()
	);

	// Create the Fetch API Preloading middleware.
	wp_add_inline_script(
		'wp-api-fetch',
		sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preloaded_data ) ),
		'after'
	);

	// Community Media library settings.
	$settings = apply_filters(
		'bp_attachments_media_library_settings',
		array(
			'isAdminScreen'         => is_admin(),
			'maxUploadFileSize'     => bp_attachments_get_max_upload_file_size( 'media' ),
			'allowedExtByMediaList' => bp_attachments_get_exts_by_medialist(),
			'allowedExtTypes'       => bp_attachments_get_allowed_media_exts( '', true ),
		)
	);

	wp_add_inline_script(
		'bp-attachments-media-library',
		'window.bpAttachmentsMediaLibrarySettings = ' . wp_json_encode( $settings ) . ';'
	);
}

/**
 * Register JavaScripts and Styles for Front-End context.
 *
 * @since 1.0.0
 */
function bp_attachments_register_front_end_assets() {
	$bp_attachments = buddypress()->attachments;

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

	wp_register_script(
		'bp-attachments-list',
		$bp_attachments->js_url . 'front-end/list.js',
		array(
			'wp-dom-ready',
			'lodash',
		),
		$bp_attachments->version,
		true
	);

	// Let the theme customize the Media Library styles.
	$css = bp_attachments_locate_template_asset( 'css/attachments-media-library.css' );
	if ( isset( $css['uri'] ) ) {
		wp_register_style(
			'bp-attachments-media-library-front',
			$css['uri'],
			array( 'bp-attachments-media-library' ),
			$bp_attachments->version
		);
	}

	/**
	 * Hook used to register other optional BP Attachments assets.
	 *
	 * @since 1.0.0
	 */
	do_action( 'bp_attachments_register_front_end_assets' );
}
add_action( 'bp_enqueue_scripts', 'bp_attachments_register_front_end_assets', 2 );

/**
 * Enqueues the css and js required by the front-end interfaces.
 *
 * @since 1.0.0
 */
function bp_attachments_enqueue_front_end_assets() {

	if ( bp_attachments_is_user_personal_library() && bp_is_my_profile() ) {
		// Style.
		wp_enqueue_style( 'bp-attachments-media-library-front' );

		bp_attachments_enqueue_media_library();

		add_action( 'wp_footer', 'bp_attachments_print_media_library_templates', 1 );
	}

	/**
	 * Hook used to register other optional BP Attachments assets.
	 *
	 * @since 1.0.0
	 */
	do_action( 'bp_attachments_enqueue_front_end_assets' );
}
add_action( 'bp_enqueue_community_scripts', 'bp_attachments_enqueue_front_end_assets' );

/**
 * Enqueue styles for BP Attachments views.
 *
 * @since 1.0.0
 */
function bp_attachments_enqueue_medium_view_assets() {
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

	$is_media_playlist = bp_attachments_is_media_playlist_view();

	if ( $is_media_playlist || bp_attachments_is_media_list_view() ) {
		$script_handle = 'bp-attachments-list';
		$qv            = bp_attachments_get_queried_vars( 'data' );
		$dir_rel_path  = implode(
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

		if ( $is_media_playlist ) {
			$script_handle = 'bp-attachments-playlist';

			wp_enqueue_style( 'wp-mediaelement' );
			wp_add_inline_style(
				'wp-mediaelement',
				'.bp-attachments-medium .wp-playlist { display: flex; justify-content: space-evenly; }
				.bp-attachments-medium .wp-audio-playlist { align-items: center; }
				.bp-attachments-medium .wp-playlist audio { display: block; }
				.bp-attachments-medium .wp-playlist #bp-medium-player { width: 60%; }
				.bp-attachments-medium .wp-playlist .wp-playlist-tracks { width: 38%; }'
			);

			add_action( 'wp_footer', 'wp_underscore_playlist_templates', 0 );
		} else {
			$medium_type                           = bp_attachments_get_medium_type();
			$preload_data[ $endpoint ]['template'] = sprintf( 'bp-media-%s', esc_html( $medium_type ) );

			if ( 'album' === $medium_type ) {
				wp_enqueue_style( 'wp-block-gallery' );
				wp_add_inline_style(
					'wp-block-gallery',
					'.bp-attachments-medium #bp-media-list { display: flex; justify-content: space-evenly; gap: 1em; }'
				);
			} else {
				wp_enqueue_style( 'bp-attachments-media-list-styles' );
			}
		}

		wp_enqueue_script( $script_handle );

		wp_add_inline_script(
			$script_handle,
			'window.bpAttachmentsItems = ' . wp_json_encode( current( $preload_data ) ) . ';',
			'before'
		);
	}
}
add_action( 'bp_enqueue_community_scripts', 'bp_attachments_enqueue_medium_view_assets', 20 );

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
