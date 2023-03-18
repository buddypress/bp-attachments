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
 * Get the list of supported attachment block names.
 *
 * @since 1.0.0
 *
 * @return array The list of supported attachment block names.
 */
function bp_attachments_block_get_supported_block_names() {
	return buddypress()->attachments->block_names;
}

/**
 * Include BP Attachments Blocks needed information to Block Editor Settings.
 *
 * @since 1.0.0
 *
 * @param array $settings The Block Editor Settings.
 * @return array The Block Editor Settings.
 */
function bp_attachments_block_editor_settings( $settings = array() ) {
	$settings['bpAttachments'] = array(
		'allowedExtByMediaList' => bp_attachments_get_exts_by_medialist(),
		'allowedExtTypes'       => bp_attachments_get_allowed_media_exts( '', true ),
		'mimeTypeImageBaseUrl'  => includes_url( 'images/media' ),
	);

	return $settings;
}
add_filter( 'block_editor_settings_all', 'bp_attachments_block_editor_settings', 10, 1 );
add_filter( 'bp_activity_block_editor_settings', 'bp_attachments_block_editor_settings', 10, 1 );

/**
 * Adds a "Community Media" Block category to house BP Attachments blocks.
 *
 * @since 1.0.0
 *
 * @param array $categories Array of block categories.
 * @return array Array of block categories.
 */
function bp_attachments_block_category( $categories = array() ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'community-media',
				'title' => __( 'Community Media', 'bp-attachments' ),
				'icon'  => 'buddicons-buddypress-logo',
			),
		)
	);
}
add_filter( 'block_categories_all', 'bp_attachments_block_category', 1, 1 );
add_filter( 'bp_activity_block_categories', 'bp_attachments_block_category', 1, 1 );

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

	$return = sprintf(
		'<figure %1$s>
			<a href="%2$s"><img src="%3$s" alt="" /></a>
		</figure>',
		$attachment_data['wrapper_attributes'],
		esc_url( $attachment_data['medium']->links['view'] ),
		esc_url_raw( $attachment_data['medium']->links['src'] )
	);

	/**
	 * Filter here to edit the rendered `bp/image-attachment` block output.
	 *
	 * @since 1.0.0
	 *
	 * @param string $return          The block output.
	 * @param array  $attributes      The block attributes.
	 * @param array  $attachment_data The block attachment's data.
	 */
	return apply_filters( 'bp_attachments_rendered_image_content', $return, $attributes, $attachment_data );
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

	$return = sprintf(
		'<figure %1$s>
			<video controls="controls" preload="metadata" src="%2$s"></video>
			<figcaption class="wp-element-caption">%3$s</figcaption>
		</figure>',
		$attachment_data['wrapper_attributes'],
		esc_url_raw( $attachment_data['medium']->links['src'] ),
		bp_attachments_get_medium_fallback_text( $attachment_data['medium']->links['download'] )
	);

	/**
	 * Filter here to edit the rendered `bp/video-attachment` block output.
	 *
	 * @since 1.0.0
	 *
	 * @param string $return          The block output.
	 * @param array  $attributes      The block attributes.
	 * @param array  $attachment_data The block attachment's data.
	 */
	return apply_filters( 'bp_attachments_rendered_video_content', $return, $attributes, $attachment_data );
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

	$return = sprintf(
		'<figure %1$s>
			<audio controls="controls" preload="metadata" src="%2$s"></audio>
			<figcaption class="wp-element-caption">%3$s</figcaption>
		</figure>',
		$attachment_data['wrapper_attributes'],
		esc_url_raw( $attachment_data['medium']->links['src'] ),
		bp_attachments_get_medium_fallback_text( $attachment_data['medium']->links['download'] )
	);

	/**
	 * Filter here to edit the rendered `bp/audio-attachment` block output.
	 *
	 * @since 1.0.0
	 *
	 * @param string $return          The block output.
	 * @param array  $attributes      The block attributes.
	 * @param array  $attachment_data The block attachment's data.
	 */
	return apply_filters( 'bp_attachments_rendered_audio_content', $return, $attributes, $attachment_data );
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

	if ( ! isset( $attachment_data['medium']->links['download'] ) || ! isset( $attachment_data['wrapper_attributes'] ) ) {
		return null;
	}

	$title = $attachment_data['medium']->icon;
	if ( isset( $attributes['name'] ) && $attributes['name'] ) {
		$title = $attributes['name'];
	}

	$return = sprintf(
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

	/**
	 * Filter here to edit the rendered `bp/file-attachment` block output.
	 *
	 * @since 1.0.0
	 *
	 * @param string $return          The block output.
	 * @param array  $attributes      The block attributes.
	 * @param array  $attachment_data The block attachment's data.
	 */
	return apply_filters( 'bp_attachments_rendered_file_content', $return, $attributes, $attachment_data );
}

/**
 * Serialize an Attachments block.
 *
 * @since 1.0.0
 *
 * @param array $args Parsed Block.
 * @return string The serialized block.
 */
function bp_attachments_get_serialized_block( $args = array() ) {
	$block = bp_parse_args(
		$args,
		array(
			'blockName'    => 'core/paragraph',
			'innerContent' => array(),
			'attrs'        => array(),
		)
	);

	return serialize_block( $block );
}

/**
 * Returns a serialised medium block.
 *
 * @since 1.0.0
 *
 * @param BP_Medium $medium The BP Medium object.
 * @return string The serialized medium block.
 */
function bp_attachments_get_serialized_medium_block( $medium ) {
	$medium_block = '';

	if ( isset( $medium->name, $medium->media_type, $medium->links ) ) {
		$public_media_types_src = array( 'image', 'audio', 'video' );
		$attrs                  = array(
			'url'   => $medium->links['view'],
			'align' => 'center',
		);

		if ( 'public' === $medium->visibility && in_array( $medium->media_type, $public_media_types_src, true ) ) {
			$attrs['src'] = $medium->links['src'];
			$block_name   = sprintf( 'bp/%s-attachment', $medium->media_type );
		} else {
			$attrs['name']      = $medium->name;
			$attrs['mediaType'] = $medium->media_type;
			$block_name         = 'bp/file-attachment';
		}

		$medium_block = bp_attachments_get_serialized_block(
			array(
				'blockName' => $block_name,
				'attrs'     => $attrs,
			)
		);
	}

	/**
	 * Filter here to edit the serialized medium block.
	 *
	 * @since 1.0.0
	 *
	 * @param string    $medium_block The serialized medium block.
	 * @param BP_Medium $medium       The BP Medium object.
	 */
	return apply_filters( 'bp_attachments_get_serialized_medium_block', $medium_block, $medium );
}
