<?php
/**
 * BP Attachments Blocks.
 *
 * @package \bp-attachments\bp-attachments-blocks
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block rendering functions common helper to get medium data.
 *
 * @since 1.0.0
 *
 * @param array $attributes The block attributes.
 * @return array The medium and the block wrapper attributes.
 */
function bp_attachments_get_block_attachment_data( $attributes = array() ) {
	$attachment_data = array();
	$attrs           = bp_parse_args(
		$attributes,
		array(
			'src'             => '',
			'url'             => '',
			'align'           => '',
			'attachment_type' => '',
		)
	);

	// The `url` attribute is required as it lets us check the image still exists.
	if ( ! $attrs['url'] ) {
		return $attachment_data;
	}

	$medium_data = bp_attachments_get_medium_path( $attrs['url'], true );
	if ( ! isset( $medium_data['id'], $medium_data['path'] ) ) {
		return $attachment_data;
	}

	// Validate and get the Medium object.
	$medium = bp_attachments_get_medium( $medium_data['id'], $medium_data['path'] );
	if ( 'file' !== $attrs['attachment_type'] && ( ! isset( $medium->media_type ) || $attrs['attachment_type'] !== $medium->media_type ) ) {
		return $attachment_data;
	}

	$extra_wrapper_attributes = array();
	if ( in_array( $attrs['align'], array( 'center', 'left', 'right' ), true ) ) {
		$extra_wrapper_attributes = array( 'class' => 'align' . $attrs['align'] );
	}

	return array(
		'medium'             => $medium,
		'wrapper_attributes' => get_block_wrapper_attributes( $extra_wrapper_attributes ),
	);
}

/**
 * Callback function to render the Image Attachment Block.
 *
 * NB: using such a callback will help us make sure the attached image still
 * exists at the place it was when attached to the object before trying
 * to render it.
 *
 * @since 1.0.0
 *
 * @param array $attributes The block attributes.
 * @return string           HTML output.
 */
function bp_attachments_render_image_attachment( $attributes = array() ) {
	$attributes['attachment_type'] = 'image';
	$attachment_data               = bp_attachments_get_block_attachment_data( $attributes );

	if ( ! isset( $attachment_data['medium'] ) || ! isset( $attachment_data['wrapper_attributes'] ) ) {
		return null;
	}

	// Return the `bp/image-attachment` output.
	return sprintf(
		'<figure %1$s>
			<a href="%2$s"><img src="%3$s" alt="" /></a>
		</figure>',
		$attachment_data['wrapper_attributes'],
		esc_url( $attachment_data['medium']->links['view'] ),
		esc_url_raw( $attachment_data['medium']->links['src'] )
	);
}

/**
 * Callback function to render the Video Attachment Block.
 *
 * NB: using such a callback will help us make sure the attached video still
 * exists at the place it was when attached to the object before trying
 * to render it.
 *
 * @since 1.0.0
 *
 * @param array $attributes The block attributes.
 * @return string           HTML output.
 */
function bp_attachments_render_video_attachment( $attributes = array() ) {
	$attributes['attachment_type'] = 'video';
	$attachment_data               = bp_attachments_get_block_attachment_data( $attributes );

	if ( ! isset( $attachment_data['medium'] ) || ! isset( $attachment_data['wrapper_attributes'] ) ) {
		return null;
	}

	// Return the `bp/video-attachment` output.
	return sprintf(
		'<figure %1$s>
			<video controls="controls" preload="metadata" src="%2$s" />
		</figure>',
		$attachment_data['wrapper_attributes'],
		esc_url_raw( $attachment_data['medium']->links['src'] )
	);
}

/**
 * Callback function to render the Audio Attachment Block.
 *
 * NB: using such a callback will help us make sure the attached audio still
 * exists at the place it was when attached to the object before trying
 * to render it.
 *
 * @since 1.0.0
 *
 * @param array $attributes The block attributes.
 * @return string           HTML output.
 */
function bp_attachments_render_audio_attachment( $attributes = array() ) {
	$attributes['attachment_type'] = 'audio';
	$attachment_data               = bp_attachments_get_block_attachment_data( $attributes );

	if ( ! isset( $attachment_data['medium'] ) || ! isset( $attachment_data['wrapper_attributes'] ) ) {
		return null;
	}

	// Return the `bp/audio-attachment` output.
	return sprintf(
		'<figure %1$s>
			<audio controls="controls" preload="metadata" src="%2$s" />
		</figure>',
		$attachment_data['wrapper_attributes'],
		esc_url_raw( $attachment_data['medium']->links['src'] )
	);
}

/**
 * Callback function to render the File Attachment Block.
 *
 * NB: using such a callback will help us make sure the attached file still
 * exists at the place it was when attached to the object before trying
 * to render it.
 *
 * @since 1.0.0
 *
 * @param array $attributes The block attributes.
 * @return string           HTML output.
 */
function bp_attachments_render_file_attachment( $attributes = array() ) {
	$attributes['attachment_type'] = 'file';
	$attachment_data               = bp_attachments_get_block_attachment_data( $attributes );

	if ( ! isset( $attachment_data['medium'] ) || ! isset( $attachment_data['wrapper_attributes'] ) ) {
		return null;
	}

	$title = $attachment_data['medium']->icon;
	if ( isset( $attributes['name'] ) && $attributes['name'] ) {
		$title = $attributes['name'];
	}

	// Return the `bp/file-attachment` output.
	return sprintf(
		'<div %1$s>
			<div class="bp-attachment-file-icon">
				<a href="%2$s">
					<img src="%3$s" />
				</a>
			</div>
			<div class="bp-attachment-file-content">
				<div class="bp-attachment-file-title">
					<a href="%2$s">%4$s</a>
				</div>
				<a href="%5$s" class="wp-element-button bp-attachments-button">%6$s</a>
			</div>
		</div>',
		$attachment_data['wrapper_attributes'],
		esc_url_raw( $attachment_data['medium']->links['view'] ),
		esc_url_raw( $attachment_data['medium']->icon ),
		wp_kses(
			$title,
			array(
				'strong' => true,
				'em'     => true,
			)
		),
		esc_url_raw( $attachment_data['medium']->links['download'] ),
		esc_html__( 'Download', 'bp-attachments' )
	);
}
