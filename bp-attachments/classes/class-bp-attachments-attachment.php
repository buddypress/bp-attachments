<?php
/**
 * Attachments Attachment class.
 *
 * @package BP Attachments
 * @subpackage Classes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'BP_Attachment' ) ) :
/**
 * The Attachments Attachment class
 *
 * @since 1.1.0
 */
class BP_Attachments_Attachment extends BP_Attachment {
	/**
	 * The constuctor
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		parent::__construct( array(
			'action'     => 'bp_attachments_attachment_upload',
			'file_input' => 'bp-attachments-attachment-upload',
			'base_dir'   => 'bp-attachments',
		) );
	}

	public function insert_attachment( $upload = array(), $args = array() ) {
		$uploaded = parent::upload( $upload, '', current_time( 'mysql' ) );

		if ( ! empty( $uploaded['error'] ) ) {
			return new WP_Error( 'upload_error', $uploaded['error'] );
		}

		$name_parts = pathinfo( $uploaded['file'] );
		$url        = $uploaded['url'];
		$type       = $uploaded['type'];
		$file       = $uploaded['file'];
		$title      = $name_parts['filename'];
		$content    = '';
		$excerpt    = '';
		$is_image   = false;

		if ( 0 === strpos( $type, 'image/' ) ) {
			$is_image   = true;
			$image_meta = @ wp_read_image_metadata( $file );

			if ( ! empty( $image_meta ) ) {
				if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
					$title = $image_meta['title'];
				}

				if ( trim( $image_meta['caption'] ) ) {
					$excerpt = $image_meta['caption'];
				}
			}
		}

		// Construct the attachment array
		$attachment_data = wp_parse_args( $args, array(
			'post_type'      => 'bp_attachment',
			'post_mime_type' => $type,
			'guid'           => $url,
			'post_parent'    => 0,
			'post_title'     => $title,
			'post_content'   => $content,
			'post_excerpt'   => $excerpt,
			'post_status'    => 'inherit',
			'post_name'      => wp_unique_post_slug( sanitize_title( $title ), 0, 'inherit', 'bp_attachment', 0 ),
			'context'        => 'buddypress',
		) );

		// Save the Attachment
		$attachment_id = wp_insert_post( $attachment_data );

		if ( ! is_wp_error( $attachment_id ) ) {
			update_attached_file( $attachment_id, $file );
			add_post_meta( $attachment_id, '_wp_attachment_context', 'buddypress', true );

			if ( $is_image ) {
				// Add the avatar image sizes
				add_image_size( 'bp_attachments_avatar', bp_core_avatar_full_width(), bp_core_avatar_full_height(), true );
				add_filter( 'intermediate_image_sizes_advanced', array( $this, 'restrict_image_sizes' ), 10, 1 );
			}

			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file ) );

			$icon = wp_mime_type_icon( $attachment_id );

			if ( $is_image ) {
				// Remove it so no other attachments will be affected
				remove_image_size( 'bp_attachments_avatar' );
				remove_filter( 'intermediate_image_sizes_advanced', array( $this, 'restrict_image_sizes' ), 10, 1 );

				add_filter( 'image_downsize', 'bp_attachments_image_downsize', 10, 3 );
				// Try to get the bp_attachments_avatar size
				$thumbnail = wp_get_attachment_image_src( $attachment_id, 'bp_attachments_avatar', true );
				remove_filter( 'image_downsize', 'bp_attachments_image_downsize', 10, 3 );

				if ( ! empty( $thumbnail[0] ) ) {
					$icon = $thumbnail[0];
				}
			}

			// Finally return the avatar to the editor
			return array(
				'attachment_id' => (int) $attachment_id,
				'name'          => esc_html( $title ),
				'icon'          => $icon,
				'url'           => esc_url_raw( $url ),
				'edit_url'      => bp_attachments_get_edit_link( $attachment_id ),
			);
		} else {
			return $attachment_id;
		}
	}

	public function restrict_image_sizes( $sizes = array() ) {
		return array_intersect_key( $sizes, array( 'bp_attachments_avatar' => true ) );
	}

	/**
	 * Set the directory when uploading a file
	 *
	 * @since 1.1.0
	 *
	 * @return array upload data (path, url, basedir...)
	 */
	public function upload_dir_filter() {
		return apply_filters( 'bp_attachments_upload_datas', array(
			'path'    => $this->upload_path . '/public/' . bp_displayed_user_id(),
			'url'     => $this->url . '/public/' . bp_displayed_user_id(),
			'subdir'  => '/public/' . bp_displayed_user_id(),
			'basedir' => $this->upload_path,
			'baseurl' => $this->url,
			'error'   => false
		) );
	}

	/**
	 * Build script datas for the Uploader UI
	 *
	 * @since 1.1.0
	 *
	 * @return array the javascript localization data
	 */
	public function script_data() {
		// Get default script data
		$script_data = parent::script_data();

		$script_data['bp_params'] = array(
			'object'     => 'user',
			'user_id'    => bp_displayed_user_id(),
		);

		// Include our specific css
		$script_data['extra_css'] = array( 'bp-attachments' );

		// Include our specific js
		$script_data['extra_js']  = array( 'bp-attachments' );

		return apply_filters( 'bp_attachments_attachment_script_data', $script_data );
	}
}
endif;
