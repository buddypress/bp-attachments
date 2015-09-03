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
		$parameters = array(
			'action'             => 'bp_attachments_attachment_upload',
			'file_input'         => 'bp-attachments-attachment-upload',
			'base_dir'           => 'bp-attachments',
			'required_wp_files'  => array( 'file', 'image' ),
		);

		if ( bp_attachments_is_activity() ) {
			$parameters['allowed_mime_types'] = array( 'jpg', 'gif', 'png' );
		}

		parent::__construct( $parameters );
	}

	/**
	 * Set the directory when uploading a file
	 *
	 * @since 1.1.0
	 *
	 * @return array upload data (path, url, basedir...)
	 */
	public function upload_dir_filter() {
		$user_id = bp_displayed_user_id();

		if ( bp_is_group() || bp_attachments_is_activity() ) {
			$user_id = bp_loggedin_user_id();
		}

		$subdir  = '/public/' . $user_id;
		$time    = current_time( 'mysql' );
		$year    = substr( $time, 0, 4 );
		$month   = substr( $time, 5, 2 );
		$subdir .= "/$year/$month";

		return apply_filters( 'bp_attachments_upload_datas', array(
			'path'    => $this->upload_path . $subdir,
			'url'     => $this->url . $subdir,
			'subdir'  => $subdir,
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

		$user_id = bp_loggedin_user_id();
		if ( bp_is_user() ) {
			$user_id = bp_displayed_user_id();
		}

		$script_data['bp_params'] = array(
			'object'     => 'user',
			'component'  => 'members',
			'user_id'    => $user_id,
			'item_id'    => 0,
		);

		if ( bp_is_group() ) {
			$script_data['bp_params'] = array(
				'object'     => 'group',
				'component'  => 'groups',
				'user_id'    => $user_id,
				'item_id'    => bp_get_current_group_id(),
			);
		}

		$is_activity = bp_attachments_is_activity();

		if ( ! $is_activity ) {
			$script_data['bp_params']['nonce'] = wp_create_nonce( 'bp_fetch_attachments' );
		} else {
			$script_data['bp_params']['nonce'] = wp_create_nonce( 'bp_bulk_delete_attachments' );
		}

		// Build the capability args
		$capabiltiy_args = array_intersect_key( $script_data['bp_params'], array(
			'component' => true,
			'item_id'   => true,
		) );

		/**
		 * Used to check if we should display the uploader
		 */
		$script_data['bp_params']['can_upload'] = bp_attachments_loggedin_user_can( 'publish_bp_attachments', $capabiltiy_args );

		/**
		 * Used to check if loaded into the activity post form
		 */
		$script_data['bp_params']['is_activity'] = $is_activity;

		$extra_css = array( 'bp-attachments' );
		if ( ! $is_activity ) {
			$extra_css = array_merge( array( 'thickbox' ), $extra_css );
		}

		// Include our specific css
		$script_data['extra_css'] = $extra_css;

		$extra_js = array( 'bp-attachments' );
		if ( ! $is_activity ) {
			$extra_js = array_merge( array( 'thickbox' ), $extra_js );
		}

		// Include our specific js
		$script_data['extra_js']  = $extra_js;

		// Add some custom feedback messages
		$script_data['feedback_messages'] = array(
			'no_attachments' => __( 'No items found', 'bp-attachments' ),
			'fetching_error' => __( 'Error while fetching the items, please try again later', 'bp-attachments' ),
			'confirm'        => __( 'Are you sure, this will delete the uploaded attachments', 'bp-attachments' ),
		);

		return apply_filters( 'bp_attachments_attachment_script_data', $script_data );
	}

	public function insert_attachment( $upload = array(), $args = array() ) {
		$uploaded = parent::upload( $upload );

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
			'bp_component'   => 'members',
			'bp_item_id'     => 0,
		) );

		if ( isset( $attachment_data['bp_component'] ) && 'members' !== $attachment_data['bp_component'] ) {
			$term = get_term_by( 'slug', $attachment_data['bp_component'], 'bp_component' );

			if ( ! empty( $term ) ) {
				$attachment_data['tax_input'] = array( 'bp_component' => array( $term->term_id ) );
			}
		}

		// Save the Attachment
		$attachment_id = wp_insert_post( $attachment_data );

		if ( ! is_wp_error( $attachment_id ) ) {
			update_attached_file( $attachment_id, $file );
			add_post_meta( $attachment_id, '_wp_attachment_context', 'buddypress', true );

			// Add the component's single item id
			if ( ! empty( $term->term_id ) ) {
				add_post_meta( $attachment_id, '_bp_' . $attachment_data['bp_component'] . '_id', $attachment_data['bp_item_id'] );
			}

			if ( $is_image ) {
				// Add the avatar image sizes
				add_image_size( 'bp_attachments_avatar', bp_core_avatar_full_width(), bp_core_avatar_full_height(), true );
				add_filter( 'intermediate_image_sizes_advanced', 'bp_attachments_restrict_image_sizes', 10, 1 );
			}

			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $file ) );

			if ( $is_image ) {
				// Remove it so no other attachments will be affected
				remove_image_size( 'bp_attachments_avatar' );
				remove_filter( 'intermediate_image_sizes_advanced', 'bp_attachments_restrict_image_sizes', 10, 1 );

				add_filter( 'image_downsize', 'bp_attachments_image_downsize', 10, 3 );
			}

			add_filter( 'image_size_names_choose', 'bp_attachments_filter_image_sizes', 10, 1 );

			$attachment = bp_attachments_prepare_attachment_for_js( $attachment_id );

			remove_filter( 'image_size_names_choose', 'bp_attachments_filter_image_sizes', 10, 1 );

			if ( empty( $attachment['id'] ) ) {
				return new WP_Error( 'fetch_error', __( 'oops! failed getting the attachment id', 'bp-attachments' ) );
			}

			$attachment['attachment_id'] = $attachment['id'];
			$attachment['uploaded']      = true;
			unset( $attachment['id'] );

			// Finally return the attachment
			return $attachment;
		} else {
			return $attachment_id;
		}
	}

	public static function get( $args = array() ) {

		$defaults = array(
			'item_ids'        => array(), // one or more item ids regarding the component (eg group_ids, message_ids )
			'component'	      => false,   // groups / messages / blogs / xprofile...
			'show_private'    => false,   // wether to include private attachment
			'user_id'	      => false,   // the author id of the attachment
			'post_id'         => false,
			'per_page'	      => 20,
			'page'		      => 1,
			'search'          => false,
			'exclude'		  => false,   // comma separated list or array of attachment ids.
			'orderby' 		  => 'modified',
			'order'           => 'DESC',
		);

		$r = bp_parse_args( $args, $defaults, 'attachments_query_args' );

		$attachment_status = 'inherit';

		if ( ! empty( $r['show_private'] ) ) {
			$attachment_status = array( 'inherit', 'private' );
		}

		$query_args = array(
			'post_status'	 => $attachment_status,
			'post_type'	     => 'bp_attachment',
			'posts_per_page' => $r['per_page'],
			'paged'		     => $r['page'],
			'orderby' 		 => $r['orderby'],
			'order'          => $r['order'],
		);

		if ( ! empty( $r['user_id'] ) )
			$query_args['author'] = $r['user_id'];

		if ( ! empty( $r['exclude'] ) ) {
			if ( ! is_array( $r['exclude'] ) )
				$r['exclude'] = explode( ',', $r['exclude'] );

			$query_args['post__not_in'] = $r['exclude'];
		}

		if ( ! empty( $r['post_id'] ) ) {
			$query_args['post_parent'] = $r['post_id'];
		}

		if ( ! empty( $r['component'] ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'bp_component',
					'field' => 'slug',
					'terms' => $r['component']
				)
			);

			// component is defined, we can zoom on specific ids
			if ( ! empty( $r['item_ids'] ) ) {
				// We really want an array!
				$item_ids = (array) $r['item_ids'];

				$query_args['meta_query'] = array(
					array(
						'key'     => "_bp_{$r['component']}_id",
						'value'   => $r['item_ids'],
						'compare' => 'IN',
					)
				);
			}
		}

		$attachments = new WP_Query( $query_args );

		return array( 'attachments' => $attachments->posts, 'total' => $attachments->found_posts );
	}
}
endif;
