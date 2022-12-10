<?php
/**
 * BP Attachments Messages Functions.
 *
 * @package \bp-attachments\bp-attachments-messages
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue JavaScripts for Messages Front-End context.
 *
 * @since 1.0.0
 */
function bp_attachments_messages_enqueue_front_end_assets() {
	$bp_attachments = buddypress()->attachments;

	// Let the theme customize the Media Library styles.
	$css = bp_attachments_locate_template_asset( 'css/attachments-media-messages.css' );
	if ( isset( $css['uri'] ) ) {
		wp_enqueue_style(
			'bp-attachments-messages',
			$css['uri'],
			array(),
			$bp_attachments->version
		);
	}

	wp_enqueue_script(
		'bp-attachments-messages',
		$bp_attachments->js_url . 'front-end/messages.js',
		array(
			'wp-dom-ready',
			'wp-i18n',
			'lodash',
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
		'bp-attachments-messages',
		'bpAttachmentsMessagesSettings',
		array(
			'path'            => ltrim( $path, '/' ),
			'root'            => esc_url_raw( get_rest_url() ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'allowedExtTypes' => bp_attachments_get_allowed_media_exts( '', true ),
			'userId'          => bp_displayed_user_id(),
		)
	);

	add_action( 'wp_footer', 'bp_attachments_messages_print_js_templates' );
}

/**
 * Print Messages Attachment JS templates into page's footer.
 *
 * @since 1.0.0
 */
function bp_attachments_messages_print_js_templates() {
	bp_attachments_get_javascript_template( 'media-preview' );
}

/**
 * Eventually attach a medium to a message.
 *
 * @since 1.0.0
 *
 * @param array $args The arguments used to post a private message.
 * @return array The arguments used to post a private message.
 */
function bp_attachments_messages_attach_media( $args = array() ) {
	if ( 'nouveau' === bp_get_theme_compat_id() && isset( $_POST['meta']['_bp_attachments_medium_url'] ) ) { // phpcs:ignore
		$medium_url = esc_url_raw( wp_unslash( $_POST['meta']['_bp_attachments_medium_url'] ) ); // phpcs:ignore

		if ( ! $medium_url ) {
			return $args;
		}

		$medium_pathinfo = bp_attachments_get_medium_path( $medium_url, true );

		// Validate the medium.
		$medium = bp_attachments_get_medium( $medium_pathinfo['id'], $medium_pathinfo['path'] );

		$dashicon = 'dashicons-media-';
		if ( 'image' === $medium->media_type ) {
			$dashicon = 'dashicons-format-image';
		} else {
			$dashicon .= esc_html( $medium->media_type );
		}

		if ( isset( $medium->media_type ) ) {
			$args['content']                   .= "\n" . sprintf(
				'<figure class="bp-attachments-messages-attached-media">
					<span class="dashicons %1$s"></span> <a href="%2$s">%3$s</a>
				</figure>',
				$dashicon,
				esc_url_raw( $medium->links['download'] ),
				esc_html( $medium->title )
			);
			$args['bp_attachments_medium_info'] = $medium_pathinfo;
		}
	}

	return $args;
}
add_filter( 'bp_before_messages_new_message_parse_args', 'bp_attachments_messages_attach_media' );

/**
 * Attached a medium to a messages thread.
 *
 * @since 1.0.0
 *
 * @param BP_Messages_Message $message Message object. Passed by reference.
 * @param array               $r       Parsed function arguments.
 */
function bp_attachments_set_media_attached_messages_thread( $message, $r ) {
	if ( ! isset( $message->thread_id ) || ! isset( $r['bp_attachments_medium_info']['id'] ) || ! isset( $r['bp_attachments_medium_info']['path'] ) ) {
		return;
	}

	$medium_id   = $r['bp_attachments_medium_info']['id'];
	$medium_path = $r['bp_attachments_medium_info']['path'];
	$medium_json = trailingslashit( $medium_path ) . $medium_id . '.json';

	bp_attachments_update_medium_attached_items( $medium_json, 'messages', $message->thread_id );
}
add_action( 'messages_message_sent', 'bp_attachments_set_media_attached_messages_thread', 10, 2 );

/**
 * Prepend a button HTML markup to the WP Editor used by the Messages UI.
 *
 * @since 1.0.0
 *
 * @param string $editor WP_Editor output.
 * @return string WP_Editor output.
 */
function bp_attachments_messages_prepend_button( $editor = '' ) {
	return '<div class="bp-attachments-messages-button-container">
		<button class="dashicons dashicons-admin-media bp-attachments-messages-file" type="button"></button>
	</div>' . $editor;
}

/**
 * Include the `<figure>` tag to Messsages allowed tags.
 *
 * @since 1.0.0
 *
 * @param array $allowed_tags The Messages allowed tags.
 * @return array The Messages allowed tags.
 */
function bp_attachments_messages_allowed_tags( $allowed_tags = array() ) {
	return array_merge(
		$allowed_tags,
		array(
			'figure' => array(
				'class' => true,
			),
		)
	);
}
add_filter( 'bp_messages_allowed_tags', 'bp_attachments_messages_allowed_tags' );

/**
 * Loads the Messages Attachments button only in Nouveau & for the messages context.
 *
 * @since 1.0.0
 */
function bp_attachments_messages_loader() {
	if ( 'nouveau' !== bp_get_theme_compat_id() || ! bp_is_current_component( 'messages' ) ) {
		return;
	}

	add_action( 'bp_attachments_enqueue_front_end_assets', 'bp_attachments_messages_enqueue_front_end_assets' );
	add_filter( 'the_editor', 'bp_attachments_messages_prepend_button' );
}
add_action( 'bp_screens', 'bp_attachments_messages_loader' );
