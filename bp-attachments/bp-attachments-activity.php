<?php
/**
 * BP Attachments Activity Functions.
 *
 * @package \bp-attachments\bp-attachments-activity
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The BP Attachments plugin needs BuddyPress Activity Block functions.
add_filter( 'bp_is_activity_blocks_active', '__return_true' );

/**
 * Register JavaScripts and Styles for Activity Front-End context.
 *
 * @since 1.0.0
 */
function bp_attachments_activity_register_front_end_assets() {
	$bp_attachments = buddypress()->attachments;

	// Let the theme customize the Media Library styles.
	$css = bp_attachments_locate_template_asset( 'css/attachments-media-activity.css' );
	if ( isset( $css['uri'] ) ) {
		wp_register_style(
			'bp-attachments-activity',
			$css['uri'],
			array(),
			$bp_attachments->version
		);
	}

	wp_register_script(
		'bp-attachments-activity',
		$bp_attachments->js_url . 'front-end/activity.js',
		array(
			'wp-dom-ready',
			'wp-i18n',
			'lodash',
			'bp-nouveau-activity-post-form',
		),
		$bp_attachments->version,
		true
	);

	$path = sprintf(
		'/%1$s/%2$s/%3$s',
		bp_rest_namespace(),
		bp_rest_version(),
		buddypress()->attachments->id
	);

	wp_localize_script(
		'bp-attachments-activity',
		'bpAttachmentsActivitySettings',
		array(
			'path'            => ltrim( $path, '/' ),
			'root'            => esc_url_raw( get_rest_url() ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'allowedExtTypes' => bp_attachments_get_allowed_media_exts( '', true ),
		)
	);
}

/**
 * Registers the Nouveau Activity Action button.
 *
 * @since 1.0.0
 *
 * @param array $buttons The array containing the Nouveau button params.
 * @return array The array containing the Nouveau button params.
 */
function bp_attachments_activity_button( $buttons = array() ) {
	// Only add the button for logged in users.
	if ( ! is_user_logged_in() ) {
		return $buttons;
	}

	return array_merge(
		$buttons,
		array(
			'bpAttachments' => array(
				'id'      => 'bpAttachments',
				'caption' => __( 'Attach Media', 'bp-attachments' ),
				'icon'    => 'dashicons-admin-media',
				'order'   => 10,
				'handle'  => 'bp-attachments-activity',
			),
		)
	);
}

/**
 * Print Activity Attachment JS templates into page's footer.
 *
 * @since 1.0.0
 */
function bp_attachments_activity_print_js_templates() {
	bp_attachments_get_javascript_template( 'media-preview' );
}

/**
 * Only print Activity Attachment JS templates once the Post Form is loaded.
 *
 * @since 1.0.0
 */
function bp_attachments_activity_after_post_form() {
	add_action( 'wp_footer', 'bp_attachments_activity_print_js_templates' );
}

/**
 * Eventually attach a medium to an activity.
 *
 * @since 1.0.0
 *
 * @param array $args The arguments used to post an activity update.
 * @return array The arguments used to post an activity update.
 */
function bp_attachments_activity_attach_media( $args = array() ) {
	if ( 'nouveau' === bp_get_theme_compat_id() && isset( $_POST['_bp_attachments_medium_url'] ) ) { // phpcs:ignore
		$medium_url = esc_url_raw( wp_unslash( $_POST['_bp_attachments_medium_url'] ) ); // phpcs:ignore

		if ( ! $medium_url ) {
			return $args;
		}

		$medium_pathinfo = bp_attachments_get_medium_path( $medium_url, true );

		// Validate the medium.
		$medium       = bp_attachments_get_medium( $medium_pathinfo['id'], $medium_pathinfo['path'] );
		$medium_block = '';
		if ( isset( $medium->media_type ) ) {
			switch ( $medium->media_type ) {
				case 'image':
				case 'audio':
				case 'video':
					$medium_block = bp_attachments_get_serialized_block(
						array(
							'blockName' => sprintf( 'bp/%s-attachment', $medium->media_type ),
							'attrs'     => array(
								'align' => 'center',
								'url'   => $medium_url,
								'src'   => $medium->links['src'],
							),
						)
					);
					break;
				default:
					$medium_block = bp_attachments_get_serialized_block(
						array(
							'blockName' => 'bp/file-attachment',
							'attrs'     => array(
								'url'       => $medium_url,
								'name'      => $medium->name,
								'mediaType' => $medium->media_type,
							),
						)
					);
					break;
			}
		}

		$content = '';
		if ( isset( $args['content'] ) && $args['content'] ) {
			$content = bp_attachments_get_serialized_block(
				array(
					'innerContent' => array( '<p>' . $args['content'] . '</p>' ),
				)
			);
		}

		if ( $medium_block ) {
			$text_block = $content;

			// By default the Medium block is appended to the Activity text block.
			$content .= "\n" . $medium_block;

			/**
			 * Filter here to edit how the attached media is added to the activity content.
			 *
			 * @since 1.1.0
			 *
			 * @param string $content      The Activity text block with the appended Medium block.
			 * @param string $text_block   The serialized Activity text block.
			 * @param string $medium_block The serialized Medium block.
			 * @param array  $args         The arguments used to post an activity update.
			 */
			$args['content'] = apply_filters( 'bp_attachments_activity_attach_media', $content, $text_block, $medium_block, $args );
		}
	}

	return $args;
}
add_filter( 'bp_before_activity_post_update_parse_args', 'bp_attachments_activity_attach_media' );
add_filter( 'bp_before_groups_post_update_parse_args', 'bp_attachments_activity_attach_media' );

/**
 * Loads the Activity Attachments button only in Nouveau & for the activity context.
 *
 * @since 1.0.0
 */
function bp_attachments_activity_loader() {
	if ( 'nouveau' !== bp_get_theme_compat_id() || ( ! bp_is_current_component( 'activity' ) && ! bp_is_group_activity() ) || bp_is_single_activity() ) {
		return;
	}

	add_filter( 'bp_nouveau_activity_buttons', 'bp_attachments_activity_button' );
	add_action( 'bp_attachments_register_front_end_assets', 'bp_attachments_activity_register_front_end_assets', 3 );
	add_action( 'bp_after_activity_post_form', 'bp_attachments_activity_after_post_form' );
}
add_action( 'bp_screens', 'bp_attachments_activity_loader' );

/**
 * Bump BP Nouveau scripts registration at `bp_init` just like it's the case for block themes.
 *
 * @since 1.0.0
 */
function bp_attachments_activity_nouveau_register_scripts() {
	if ( 'nouveau' !== bp_get_theme_compat_id() || ( ! bp_is_current_component( 'activity' ) && ! bp_is_group_activity() ) || bp_is_single_activity() ) {
		return;
	}

	if ( ! current_theme_supports( 'block-templates' ) ) {
		$nouveau = bp_nouveau();
		remove_action( 'bp_enqueue_community_scripts', array( $nouveau, 'register_scripts' ), 2 );
		add_action( 'bp_init', array( $nouveau, 'register_scripts' ), 20 );
	}
}
add_action( 'bp_init', 'bp_attachments_activity_nouveau_register_scripts', 19 );

/**
 * Looks inside a saved activity content to eventually attached its ID to found media.
 *
 * @since 1.0.0
 * @todo This function should use `bp_attachments_update_medium_attached_items()`
 *
 * @param BP_Activity_Activity $activity The saved activity object.
 */
function bp_attachments_set_media_blocks_attached_activity( $activity = null ) {
	if ( ! isset( $activity->id ) || ! isset( $activity->content ) ) {
		return;
	}

	if ( has_blocks( $activity->content ) ) {
		$blocks = parse_blocks( $activity->content );

		foreach ( $blocks as $block ) {
			if ( in_array( $block['blockName'], bp_attachments_block_get_supported_block_names(), true ) && isset( $block['attrs']['url'] ) ) {
				$medium_path = bp_attachments_get_medium_path( $block['attrs']['url'] );

				if ( $medium_path ) {
					$medium_id   = wp_basename( trim( $block['attrs']['url'], '/' ) );
					$medium_json = trailingslashit( $medium_path ) . $medium_id . '.json';

					bp_attachments_update_medium_attached_items( $medium_json, 'activity', $activity->id );
				}
			}
		}
	}
}
add_action( 'bp_activity_after_save', 'bp_attachments_set_media_blocks_attached_activity', 10, 1 );
