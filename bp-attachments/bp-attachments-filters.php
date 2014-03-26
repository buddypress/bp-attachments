<?php
/**
 * BP Attachments Filters.
 *
 * A media component, for others !
 *
 * @package BP Attachments
 * @subpackage Filters
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Map capabilities
 *
 * @since BP Attachments (1.0.0)
 *
 * @todo really need to check this !
 * 
 * @param  array   $caps    
 * @param  string  $cap     
 * @param  integer $user_id 
 * @param  array   $args    
 * @return array   $caps           
 */
function bp_attachments_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	// What capability is being checked?
	switch ( $cap ) {

		/** Reading ***********************************************************/

		case 'read_private_bp_attachments' :

			if ( ! empty( $args[0] ) ) {
				// Get the post
				$_post = get_post( $args[0] );
				if ( ! empty( $_post ) ) {

					// Get caps for post type object
					$post_type = get_post_type_object( $_post->post_type );
					$caps      = array();

					// Allow author to edit his attachment
					if ( $user_id == $_post->post_author ) {
						$caps[] = 'read';

					// @todo private attachments will need other rules

					// Admins can always edit
					} else if ( user_can( $user_id, 'manage_options' ) ) {
						$caps = array( 'manage_options' );
					} else {
						$caps[] = $post_type->cap->edit_others_posts;
					}

				}
			}
			

			break;

		/** Publishing ********************************************************/

		case 'publish_bp_attachments' :

			if ( bp_is_my_profile() ) {
				$caps = array( 'read' );
			}

			if ( ! empty( $args[0] ) && is_a( $args[0], 'BP_Attachments_Can' ) ) {

				if ( ! empty( $args[0]->component ) && ! empty( $args[0]->item_id ) ){
					switch( $args[0]->component ) {
						case 'groups':
							if( groups_is_user_member( $user_id, $args[0]->item_id ) )
								$caps = array( 'read' );
							break;

						// and so on for other components
					}
				}

			}

			// Admins can always publish
			if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		/** Editing ***********************************************************/

		case 'edit_bp_attachments'        :

			if ( bp_is_my_profile() ) {
				$caps = array( 'read' );
			}

			if ( ! empty( $args[0] ) && is_a( $args[0], 'BP_Attachments_Can' ) ) {

				if ( ! empty( $args[0]->component ) && ! empty( $args[0]->item_id ) ){
					switch( $args[0]->component ) {
						case 'groups':
							if( groups_is_user_admin( $user_id, $args[0]->item_id ) )
								$caps = array( 'read' );
							break;

						// and so on for other components
					}
				}

			}

			// Admins can always edit
			if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		// Used primarily in wp-admin
		case 'edit_others_bp_attachments' :

			// Admins can always edit
			if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		// Used everywhere
		case 'edit_bp_attachment' :

			// Get the post
			$_post = get_post( $args[0] );
			if ( ! empty( $_post ) ) {

				// Get caps for post type object
				$post_type = get_post_type_object( $_post->post_type );
				$caps      = array();

				// Allow author to edit his attachment
				if ( $user_id == $_post->post_author ) {
					$caps[] = 'read';

				// Admins can always edit
				} else if ( user_can( $user_id, 'manage_options' ) ) {
					$caps = array( 'manage_options' );
				} else {
					$caps[] = $post_type->cap->edit_others_posts;
				}

			}

			break;

		/** Deleting **********************************************************/

		case 'delete_bp_attachment' :

			// Get the post
			$_post = get_post( $args[0] );
			if ( ! empty( $_post ) ) {

				// Get caps for post type object
				$post_type = get_post_type_object( $_post->post_type );
				$caps      = array();

				// Allow author to edit his attachment
				if ( $user_id == $_post->post_author ) {
					$caps[] = 'read';

				// Admins can always edit
				} else if ( user_can( $user_id, 'manage_options' ) ) {
					$caps = array( 'manage_options' );
				} else {
					$caps[] = $post_type->cap->delete_others_posts;
				}
			}

			break;

		// Moderation override
		case 'delete_bp_attachments'        :
		case 'delete_others_bp_attachments' :

			// Moderators can always delete
			if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		/** Admin *************************************************************/

		case 'bp_attachments_moderate' :

			// Admins can always moderate
			if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		/** bp component term ************************************************/
		case 'manage_bp_components'    :
		case 'edit_bp_components'      :
		case 'delete_bp_components'    :
		case 'bp_attachments_components_admin' :

			// Admins can always edit
			if ( user_can( $user_id, 'manage_options' ) ) {
				$caps = array( 'manage_options' );
			}

			break;

		// This should be improved..
		case 'assign_bp_components'   :
			if ( is_user_logged_in() ) {
				$caps = array( 'read' );
			}
			break;

	}

	return apply_filters( 'bp_attachments_map_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Custom filter for WordPress image_downsize
 *
 * @since BP Attachments (1.0.0)
 * 
 * @see bp_attachments_get_thumbnail() template function
 */
function bp_attachments_image_downsize( $output = '', $id = 0, $size = 'medium' ) {
	$img_url = bp_attachments_get_attachment_url( $id );
	$meta = wp_get_attachment_metadata($id);
	$width = $height = 0;
	$is_intermediate = false;
	$img_url_basename = wp_basename($img_url);

	// try for a new style intermediate size
	if ( $intermediate = image_get_intermediate_size($id, $size) ) {
		$img_url = str_replace($img_url_basename, $intermediate['file'], $img_url);
		$width = $intermediate['width'];
		$height = $intermediate['height'];
		$is_intermediate = true;
	}
	elseif ( $size == 'thumbnail' ) {
		// fall back to the old thumbnail
		if ( ($thumb_file = wp_get_attachment_thumb_file($id)) && $info = getimagesize($thumb_file) ) {
			$img_url = str_replace($img_url_basename, wp_basename($thumb_file), $img_url);
			$width = $info[0];
			$height = $info[1];
			$is_intermediate = true;
		}
	}
	if ( !$width && !$height && isset( $meta['width'], $meta['height'] ) ) {
		// any other type: use the real image
		$width = $meta['width'];
		$height = $meta['height'];
	}

	if ( $img_url) {
		// we have the actual image size, but might need to further constrain it if content_width is narrower
		list( $width, $height ) = image_constrain_size_for_editor( $width, $height, $size );

		return array( $img_url, $width, $height, $is_intermediate );
	}
	return false;
}

/**
 * Override the change-avatar user template
 *
 * @since BP Attachments (1.0.0)
 * 
 * @param  array  $templates
 * @param  string $slug
 * @return array  $templates
 */
function bp_attachments_member_avatar_template( $templates = array(), $slug = '' ) {

	if ( ! bp_is_user_change_avatar() )
		return $templates;

	if ( 'members/single/profile/change-avatar' == $slug )
		$templates = array_merge( array( 'members/single/plugins.php' ), $templates );
	
	return $templates;
}
add_filter( 'bp_get_template_part', 'bp_attachments_member_avatar_template', 10, 2 );
