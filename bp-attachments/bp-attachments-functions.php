<?php
/**
 * BP Attachments functions
 *
 * @package BP Attachments
 * @subpackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get a single attachment
 *
 * @since BP Attachments (1.0.0)
 *
 * @param  integer $attachment_id
 * @return BP_Attachments
 */
function bp_attachments_get_attachment( $attachment_id = 0 ) {
	if ( empty( $attachment_id ) )
		return false;

	$attachment = new BP_Attachments( $attachment_id );

	return apply_filters( 'bp_attachments_get_attachment', $attachment );
}

/**
 * Get a single attachment
 *
 * @since BP Attachments (1.0.0)
 *
 * @param  array $args
 * @return array of BP_Attachments
 */
function bp_attachments_get_attachments( $args = array() ) {

	$defaults = array(
		'item_ids'        => array(), // one or more item ids regarding the component (eg group_ids, message_ids )
		'component'	      => false,   // groups / messages / blogs / xprofile...
		'show_private'    => false,   // wether to include private attachment
		'user_id'	      => false,   // the author id of the attachment
		'per_page'	      => 20,
		'page'		      => 1,
		'search'          => false,
		'exclude'		  => false,   // comma separated list or array of attachment ids.
		'orderby' 		  => 'ID',
		'order'           => 'DESC',
	);

	$r = bp_parse_args( $args, $defaults, 'attachments_get_args' );

	$attachments = wp_cache_get( 'bp_attachments_attachments', 'bp' );

	if ( empty( $attachments ) ) {
		$attachments = BP_Attachments::get( array(
			'item_ids'        => (array) $r['item_ids'],
			'component'	      => $r['component'],
			'show_private'    => (bool) $r['show_private'],
			'user_id'	      => $r['user_id'],
			'per_page'	      => $r['per_page'],
			'page'		      => $r['page'],
			'search'          => $r['search'],
			'exclude'		  => $r['exclude'],
			'orderby' 		  => $r['orderby'],
			'order'           => $r['order'],
		) );

		wp_cache_set( 'bp_attachments_attachments', $attachments, 'bp' );
	}

	return apply_filters_ref_array( 'bp_attachments_get_attachments', array( &$attachments, &$r ) );
}

/**
 * Upload attachment
 *
 * @since BP Attachments (1.0.0)
 *
 * @param  array $args
 * @return int the id of the created attachment
 */
function bp_attachments_handle_upload( $args = array() ) {

	$r = bp_parse_args( $args, array(
		'item_id'         => 0,
		'component'       => '',
		'item_type'       => 'attachment',
		'action'          => 'bp_attachments_upload',
		'file_id'         => 'bp_attachment_file',
	), 'attachments_handle_upload_args' );

	$attachment_upload = BP_Attachments_Upload::start( $r );

	return $attachment_upload->attachment_id;
}

/**
 * Delete attachment
 *
 * @since BP Attachments (1.0.0)
 *
 * @param  int the id of the attachment
 * @return mixed false/title of the deleted attachment
 */
function bp_attachments_delete_attachment( $attachment_id = 0 ) {
	if ( empty( $attachment_id ) ) {
		return false;
	}

	if ( ! bp_attachments_loggedin_user_can( 'delete_bp_attachment', $attachment_id ) ) {
		return false;
	}

	$deleted = BP_Attachments::delete( $attachment_id );

	if ( ! empty( $deleted->post_title ) ) {
		return $deleted->post_title;
	} else {
		return false;
	}
}

/**
 * Delete attachment
 *
 * @since BP Attachments (1.0.0)
 *
 * @param  array $args
 * @return mixed false/title of the updated attachment
 */
function bp_attachments_update_attachment( $args = array() ) {
	$r = bp_parse_args( $args, array(
		'id'          => 0,
		'title'       => '',
		'description' => '',
		'privacy'     => 'inherit',
		'component'   => array(),
		'terms'       => array(),
		'ajax'        => false
	), 'attachments_update_attachment_args' );

	extract( $r, EXTR_SKIP );

	if ( empty( $id ) )
		return false;

	if ( ! bp_attachments_loggedin_user_can( 'edit_bp_attachment', $id ) )
			return false;

	$attachment = new BP_Attachments( $id );

	if ( empty( $attachment ) )
		return false;

	if ( empty( $title ) )
		$title = $attachment->title;

	$attachment->title       = $title;
	$attachment->description = $description;
	$attachment->status      = $privacy;

	if ( empty( $ajax ) ) {

		$prev_item_ids = ! empty( $attachment->item_ids ) ? $attachment->item_ids : false;

		if ( ! empty( $terms ) ) {
			$terms = explode( ',', $terms );
			// Let's handle the item ids here
			foreach ( $terms as $key => $term ) {
				if ( empty( $component[ $term ] ) ) {
					// delete all !
					delete_post_meta( $id, "_bp_{$term}_id" );
					unset( $terms[ $key ] );
				} else {
					if ( empty( $prev_item_ids->{$term} ) ) {
						foreach( $component[ $term ] as $item_id )
							add_post_meta( $id, "_bp_{$term}_id", $item_id );

					} else {
						$to_delete = array_diff( $prev_item_ids->{$term}, $component[ $term ] );
						$to_add    = array_diff( $component[ $term ], $prev_item_ids->{$term} );

						if ( ! empty( $to_delete ) ){
							// Delete item ids
							foreach ( $to_delete as $item_id )
								delete_post_meta( $id, "_bp_{$term}_id", $item_id );
						}

						if ( ! empty( $to_add ) ){
							// Delete item ids
							foreach ( $to_add as $item_id )
								add_post_meta( $id, "_bp_{$term}_id", $item_id );
						}
					}
				}
			}

			if ( empty( $terms ) )
				$terms = null;

			wp_set_object_terms( $id, $terms, 'bp_component' );

		} else {
			wp_set_object_terms( $id, null, 'bp_component' );
		}

	}

	return $attachment->update();
}

/**
 * Launch the BP Media Editor
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_browser( $browser_id, $settings = array() ) {
	BP_Attachments_Browser::browser( $browser_id, $settings );
}

/**
 * Retrieve the URL for an attachment.
 *
 * This is an adapted version of wp_get_attachment_url()
 *
 * @since BP Attachments (1.0.0)
 *
 * @param int $post_id Attachment ID.
 * @return string
 */
function bp_attachments_get_attachment_url( $post_id = 0 ) {
	$post_id = (int) $post_id;
	if ( !$post = get_post( $post_id ) )
		return false;

	if ( 'bp_attachment' != $post->post_type )
		return false;

	$url = '';
	if ( $file = get_post_meta( $post->ID, '_wp_attached_file', true) ) { //Get attached file
		if ( ($uploads = wp_upload_dir()) && false === $uploads['error'] ) { //Get upload directory
			if ( 0 === strpos($file, $uploads['basedir']) ) //Check that the upload base exists in the file location
				$url = str_replace($uploads['basedir'], $uploads['baseurl'], $file); //replace file location with url location
			elseif ( false !== strpos($file, 'wp-content/uploads') )
				$url = $uploads['baseurl'] . substr( $file, strpos($file, 'wp-content/uploads') + 18 );
			else
				$url = $uploads['baseurl'] . "/$file"; //Its a newly uploaded file, therefor $file is relative to the basedir.
		}
	}

	if ( empty($url) ) //If any of the above options failed, Fallback on the GUID as used pre-2.7, not recommended to rely upon this.
		$url = get_the_guid( $post->ID );

	$url = apply_filters( 'bp_attachments_get_attachment_url', $url, $post->ID );

	if ( empty( $url ) )
		return false;

	return $url;
}

/**
 * Prepare an attachment to be displayed in the BP Media Editor
 *
 * this is an adapted copy of wp_prepare_attachment_for_js()
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_prepare_attachment_for_js( $attachment ) {
	if ( empty( $attachment ) )
		return;

	if ( ! is_a( $attachment, 'WP_Post' ) && is_numeric( $attachment ) ) {
		$get_attachment = bp_attachments_get_attachment( $attachment );

		if ( ! empty( $get_attachment->attachment ) )
			$attachment = $get_attachment->attachment;

	/** return if already ready for js */
	} elseif ( is_array( $attachment ) && ! empty( $attachment['attachment_id'] ) ) {
		return $attachment;
	}

	if ( 'bp_attachment' != $attachment->post_type ) {
		return;
	}

	$meta = wp_get_attachment_metadata( $attachment->ID );
	if ( false !== strpos( $attachment->post_mime_type, '/' ) )
		list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
	else
		list( $type, $subtype ) = array( $attachment->post_mime_type, '' );

	$attachment_url = bp_attachments_get_attachment_url( $attachment->ID );

	$response = array(
		'id'          => $attachment->ID,
		'title'       => $attachment->post_title,
		'filename'    => wp_basename( $attachment->guid ),
		'url'         => $attachment_url,
		'link'        => get_attachment_link( $attachment->ID ),
		'alt'         => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
		'author'      => $attachment->post_author,
		'description' => $attachment->post_content,
		'caption'     => $attachment->post_excerpt,
		'name'        => $attachment->post_name,
		'status'      => $attachment->post_status,
		'uploadedTo'  => $attachment->post_parent,
		'date'        => strtotime( $attachment->post_date_gmt ) * 1000,
		'modified'    => strtotime( $attachment->post_modified_gmt ) * 1000,
		'menuOrder'   => $attachment->menu_order,
		'mime'        => $attachment->post_mime_type,
		'type'        => $type,
		'subtype'     => $subtype,
		'icon'        => wp_mime_type_icon( $attachment->ID ),
		'dateFormatted' => mysql2date( get_option('date_format'), $attachment->post_date ),
		'nonces'      => array(
			'update' => false,
			'delete' => false,
		),
		'editLink'    => false,
		'is_activity' => bp_attachments_is_activity(),
	);

	if ( current_user_can( 'edit_bp_attachment', $attachment->ID ) ) {
		$response['nonces']['update'] = wp_create_nonce( 'update_bp_attachment_' . $attachment->ID );
		$response['editLink'] = bp_attachments_get_edit_link( $attachment->ID, $attachment->post_author );
	}

	if ( current_user_can( 'delete_bp_attachment', $attachment->ID ) ) {
		$response['nonces']['delete'] = wp_create_nonce( 'delete_bp_attachment_' . $attachment->ID );
	}

	if ( $meta && 'image' === $type ) {
		$sizes = array();
		/** This filter is documented in wp-admin/includes/media.php */
		$possible_sizes = apply_filters( 'image_size_names_choose', array(
			'thumbnail' => __('Thumbnail'),
			'medium'    => __('Medium'),
			'large'     => __('Large'),
			'full'      => __('Full Size'),
		) );
		unset( $possible_sizes['full'] );

		// Loop through all potential sizes that may be chosen. Try to do this with some efficiency.
		// First: run the image_downsize filter. If it returns something, we can use its data.
		// If the filter does not return something, then image_downsize() is just an expensive
		// way to check the image metadata, which we do second.
		foreach ( $possible_sizes as $size => $label ) {
			if ( $downsize = apply_filters( 'image_downsize', false, $attachment->ID, $size ) ) {
				if ( ! $downsize[3] )
					continue;
				$sizes[ $size ] = array(
					'height'      => $downsize[2],
					'width'       => $downsize[1],
					'url'         => $downsize[0],
					'orientation' => $downsize[2] > $downsize[1] ? 'portrait' : 'landscape',
				);
			} elseif ( isset( $meta['sizes'][ $size ] ) ) {
				if ( ! isset( $base_url ) )
					$base_url = str_replace( wp_basename( $attachment_url ), '', $attachment_url );

				// Nothing from the filter, so consult image metadata if we have it.
				$size_meta = $meta['sizes'][ $size ];

				// We have the actual image size, but might need to further constrain it if content_width is narrower.
				// Thumbnail, medium, and full sizes are also checked against the site's height/width options.
				list( $width, $height ) = image_constrain_size_for_editor( $size_meta['width'], $size_meta['height'], $size, 'edit' );

				$sizes[ $size ] = array(
					'height'      => $height,
					'width'       => $width,
					'url'         => $base_url . $size_meta['file'],
					'orientation' => $height > $width ? 'portrait' : 'landscape',
				);
			}
		}

		$sizes['full'] = array( 'url' => $attachment_url );

		if ( isset( $meta['height'], $meta['width'] ) ) {
			$sizes['full']['height'] = $meta['height'];
			$sizes['full']['width'] = $meta['width'];
			$sizes['full']['orientation'] = $meta['height'] > $meta['width'] ? 'portrait' : 'landscape';
		}

		$response = array_merge( $response, array( 'sizes' => $sizes ), $sizes['full'] );
	} elseif ( $meta && 'video' === $type ) {
		if ( isset( $meta['width'] ) )
			$response['width'] = (int) $meta['width'];
		if ( isset( $meta['height'] ) )
			$response['height'] = (int) $meta['height'];
	}

	if ( $meta && ( 'audio' === $type || 'video' === $type ) ) {
		if ( isset( $meta['length_formatted'] ) )
			$response['fileLength'] = $meta['length_formatted'];
	}

	return apply_filters( 'bp_attachments_prepare_attachment_for_js', $response, $attachment, $meta );
}

/**
 * bp_attachment post type caps
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_get_attachment_caps() {
	return apply_filters( 'bp_attachments_get_attachment_caps', array (
		'edit_posts'          => 'edit_bp_attachments',
		'edit_others_posts'   => 'edit_others_bp_attachments',
		'publish_posts'       => 'publish_bp_attachments',
		'read_private_posts'  => 'read_private_bp_attachments',
		'delete_posts'        => 'delete_bp_attachments',
		'delete_others_posts' => 'delete_others_bp_attachments'
	) );
}

/**
 * bp_component taxonomy caps
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_get_component_caps() {
	return apply_filters( 'bp_attachments_get_component_caps', array (
		'manage_terms' => 'manage_bp_components',
		'edit_terms'   => 'edit_bp_components',
		'delete_terms' => 'delete_bp_components',
		'assign_terms' => 'assign_bp_components'
	) );
}

/**
 * Specific function to check a user's capability
 *
 * @since BP Attachments (1.0.0)
 *
 * @uses BP_Attachments_Can to create the argument to check upon
 * such as :
 * - component
 * - single item id
 * - attachment id
 */
function bp_attachments_loggedin_user_can( $capability = '', $args = false ) {
	if ( ! empty( $args ) && is_array( $args ) )
		$args = new BP_Attachments_Can( $args );

	$blog_id = bp_get_root_blog_id();
	$args = array( $blog_id, $capability, $args );

	$retval = call_user_func_array( 'current_user_can_for_blog', $args );

	return (bool) apply_filters( 'bp_attachments_loggedin_user_can', $retval, $args );
}

/**
 * Build the edit link of an attachement
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_get_edit_link( $attachment_id = 0, $user_id = 0 ) {
	if ( empty( $attachment_id ) )
		return false;

	if ( empty( $user_id ) )
		$user_id = bp_loggedin_user_id();

	$edit_link = trailingslashit( bp_core_get_user_domain( $user_id ) . buddypress()->attachments->slug );
	$edit_link = add_query_arg( array( 'attachment' => $attachment_id, 'action' => 'edit' ), $edit_link );

	return apply_filters( 'bp_attachments_get_edit_link', $edit_link, $attachment_id, $user_id );
}

/**
 * Build the delete link of an attachement
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_get_delete_link( $attachment_id = 0, $user_id = 0 ) {
	if ( empty( $attachment_id ) )
		return false;

	if ( empty( $user_id ) )
		$user_id = bp_loggedin_user_id();

	$delete_link = trailingslashit( bp_core_get_user_domain( $user_id ) . buddypress()->attachments->slug );
	$delete_link = add_query_arg( array( 'attachment' => $attachment_id, 'action' => 'delete' ), $delete_link );

	return apply_filters( 'bp_attachments_get_delete_link', $delete_link, $attachment_id, $user_id );
}

/**
 * Are we in an activity area (Group/User/Directory)
 *
 * @since 1.1.0
 *
 * @return bool True if in an activity area, false otherwise
 */
function bp_attachments_is_activity() {
	return bp_attachments_loader()->is_activity();
}
