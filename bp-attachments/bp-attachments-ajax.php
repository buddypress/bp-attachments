<?php
/**
 * BP Attachments Ajax.
 *
 * A media component, for others !
 *
 * @package BP Attachments
 * @subpackage Ajax
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Query attachments for the "BP Media Editor".
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_query() {
	$query = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();
	$query = array_intersect_key( $query, array_flip( array(
		's', 'order', 'orderby', 'posts_per_page', 'paged', 'post_mime_type',
		'post_parent', 'post__in', 'post__not_in', 'item_type', 'item_id', 'component'
	) ) );

	$args = array(
		'show_private'    => false,   // this could be checking bp_is_my_profile() or current user is a group admin
		'user_id'         => bp_loggedin_user_id(),
		'per_page'	      => $query['posts_per_page'],
		'page'		      => $query['paged'],
		'orderby' 		  => $query['orderby'],
		'order'           => $query['order'],
	);

	if ( ! empty( $query['item_id'] ) ) {
		$args['item_ids'] = array( $query['item_id'] );
	}

	if ( ! empty( $query['component'] ) && 'members' != $query['component'] ) {
		$args['component'] = $query['component'];
	}

	if ( ! empty( $query['s'] ) )
		$args['search'] = $query['s'];

	$query = bp_attachments_get_attachments( $args );

	$attachments = array_map( 'bp_attachments_prepare_attachment_for_js', $query['attachments'] );
	$attachments = array_filter( $attachments );

	wp_send_json_success( $attachments );
}
add_action( 'wp_ajax_query_bp_attachments', 'bp_attachments_query' );

/**
 * Process an attachment upload requested in "BP Media Editor".
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_upload() {
	//nonce check
	check_ajax_referer( 'media-form' );

	$r = bp_parse_args( $_REQUEST, array(
		'item_id'         => 0,
		'component'       => 'members',
		'item_type'       => 'attachment',
		'action'          => 'bp_attachments_upload',
		'file_id'         => 'bp-attachments-attachment-upload'
	), 'attachments_ajax_upload' );

	if ( 'bp_attachments_upload' === $_POST['action'] ) {
		$_POST['action'] = 'bp_attachments_attachment_upload';
	}

	$cap_args = false;

	if ( ! empty( $r['component'] ) ) {
		$cap_args = array( 'component' => $r['component'], 'item_id' => $r['item_id'] );
	}

	// capability check
	if ( ! bp_attachments_loggedin_user_can( 'publish_bp_attachments', $cap_args ) ) {
		wp_die();
	}

	$user_id = bp_displayed_user_id();
	if ( ! bp_is_user() ) {
		$user_id = bp_loggedin_user_id();
	}

	$attachment_object = new BP_Attachments_Attachment();
	$response = $attachment_object->insert_attachment( $_FILES, array(
		'post_author'  => $user_id,
		'bp_component' => $r['component'],
		'bp_item_id'   => $r['item_id'],
	) );

	// Error while trying to upload the file
	if ( is_wp_error( $response ) ) {
		bp_attachments_json_response( false, false, array(
			'type'    => 'upload_error',
			'message' => $response->get_error_message(),
		) );
	}

	// Finally return the attachment to the editor
	bp_attachments_json_response( true, false, $response );
}
add_action( 'wp_ajax_bp_attachments_upload', 'bp_attachments_upload' );

/**
 * Delete an attachment from "BP Media Editor".
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_ajax_delete_attachment( $action ) {
	if ( empty( $action ) )
		$action = 'delete_bp_attachment';

	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

	check_ajax_referer( "{$action}_$id" );

	if ( ! bp_attachments_loggedin_user_can( 'delete_bp_attachment', $id ) )
		wp_die( -1 );

	if ( bp_attachments_delete_attachment( $id ) )
		wp_die( 1 );
	else
		wp_die( 0 );
}
add_action( 'wp_ajax_delete_bp_attachment', 'bp_attachments_ajax_delete_attachment' );

/**
 * Update an attachment from "BP Media Editor".
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_ajax_update_attachment() {
	if ( ! isset( $_REQUEST['id'] ) || ! isset( $_REQUEST['changes'] ) )
		wp_send_json_error();

	if ( ! $id = absint( $_REQUEST['id'] ) )
		wp_send_json_error();

	check_ajax_referer( 'update_bp_attachment_' . $id, 'nonce' );

	if ( ! bp_attachments_loggedin_user_can( 'edit_bp_attachment', $id ) )
		wp_send_json_error();

	$changes = $_REQUEST['changes'];

	$update = array( 'id' => $id, 'ajax' => true );

	if ( isset( $changes['title'] ) )
		$update['title'] = $changes['title'];

	if ( isset( $changes['description'] ) )
		$update['description'] = $changes['description'];

	if ( ! bp_attachments_update_attachment( $update ) )
		wp_send_json_error();

	wp_send_json_success();
}
add_action( 'wp_ajax_update_bp_attachment', 'bp_attachments_ajax_update_attachment' );

/**
 * Ajax upload an avatar.
 *
 * @since BuddyPress (2.3.0)
 *
 * @return  string|null A json object containing success data if the upload succeeded
 *                      error message otherwise.
 */
function bp_attachments_attachment_upload() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_die();
	}

	/**
	 * Sending the json response will be different if
	 * the current Plupload runtime is html4
	 */
	$is_html4 = false;
	if ( ! empty( $_POST['html4' ] ) ) {
		$is_html4 = true;
	}

	// Check the nonce
	check_admin_referer( 'bp-uploader' );

	// Init the BuddyPress parameters
	$bp_params = array();

	// We need it to carry on
	if ( ! empty( $_POST['bp_params' ] ) ) {
		$bp_params = $_POST['bp_params' ];
	} else {
		bp_attachments_json_response( false, $is_html4 );
	}

	// Check params
	if ( empty( $bp_params['object'] ) || empty( $bp_params['user_id'] ) || ! isset( $bp_params['item_id'] ) ) {
		bp_attachments_json_response( false, $is_html4 );
	}

	/* @todo improve this */
	$component = 'members';
	if ( 'group' === $bp_params['object'] ) {
		$component = 'groups';
	}

	// capability check
	if ( ! bp_attachments_loggedin_user_can( 'publish_bp_attachments', array( 'component' => $component, 'item_id' => $bp_params['item_id'] ) ) ) {
		bp_attachments_json_response( false, $is_html4 );
	}

	$attachment_object = new BP_Attachments_Attachment();
	$response = $attachment_object->insert_attachment( $_FILES, array(
		'post_author'  => $bp_params['user_id'],
		'bp_component' => $component,
		'bp_item_id'   => $bp_params['item_id'],
	) );

	// Error while trying to upload the file
	if ( is_wp_error( $response ) ) {
		bp_attachments_json_response( false, $is_html4, array(
			'type'    => 'upload_error',
			'message' => $response->get_error_message(),
		) );
	}

	// Finally return the attachment to the editor
	bp_attachments_json_response( true, $is_html4, $response );
}
add_action( 'wp_ajax_bp_attachments_attachment_upload', 'bp_attachments_attachment_upload' );

function bp_attachments_get_files() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_die();
	}

	// Check the nonce
	check_admin_referer( 'bp_fetch_attachments', 'nonce' );

	// Init the BuddyPress parameters
	$args = array();

	// We need it to carry on
	if ( ! empty( $_POST ) ) {
		$args = wp_parse_args( $_POST, array(
			'user_id'        => 0,
			'item_id'        => 0,
			'item_object'    => '',
			'item_component' => '',
			'page'           => 1,
			'per_page'       => 20,
		) );
	} else {
		bp_attachments_json_response( false );
	}

	if ( empty( $args ) ) {
		bp_attachments_json_response( false, false, array(
			'type'    => 'fetch_error',
			'message' => __( 'OOps no args ??', 'bp-attachments' ),
		) );
	}

	if ( ! empty( $args['item_component'] ) && 'members' !== $args['item_component'] ) {
		$args['component'] = $args['item_component'];
		$args['item_ids'] = wp_parse_id_list( $args['item_id'] );

		// Unset the user id as we're in a Groups single item
		unset( $args['user_id'] );
	}

	$query = BP_Attachments_Attachment::get( $args );

	add_filter( 'image_size_names_choose', 'bp_attachments_filter_image_sizes', 10, 1 );
	$attachments = array_map( 'bp_attachments_prepare_attachment_for_js', $query['attachments'] );
	remove_filter( 'image_size_names_choose', 'bp_attachments_filter_image_sizes', 10, 1 );
	$attachments = array_filter( $attachments );

	bp_attachments_json_response( true, false, $attachments );
}
add_action( 'wp_ajax_bp_attachments_get_files', 'bp_attachments_get_files' );
add_action( 'wp_ajax_nopriv_bp_attachments_get_files', 'bp_attachments_get_files' );

function bp_attachments_delete_file() {
	$response = array( 'feedback' => __( 'was not deleted due to an error, please try again later.', 'bp-attachments' ) );

	if ( empty( $_POST['attachment_id'] ) ) {
		bp_attachments_json_response( false, false, $response );
	}

	$attachment_id = (int) $_POST['attachment_id'];

	// Check the nonce
	check_admin_referer( 'delete_bp_attachment_' . $attachment_id, 'nonce' );

	if ( ! bp_attachments_loggedin_user_can( 'delete_bp_attachment', $attachment_id ) ) {
		bp_attachments_json_response( false, false, $response );
	}

	if ( bp_attachments_delete_attachment( $attachment_id ) ) {
		bp_attachments_json_response( true, false, array( 'feedback' => __( 'was successfully deleted.', 'bp-attachments' ) ) );
	} else {
		bp_attachments_json_response( false, false, $response );
	}
}
add_action( 'wp_ajax_bp_attachments_delete_file', 'bp_attachments_delete_file' );

function bp_attachments_remove_file() {
	$response = array( 'feedback' => __( 'was not removed due to an error, please try again later.', 'bp-attachments' ) );

	if ( empty( $_POST['attachment_id'] ) || empty( $_POST['item_id'] ) || empty( $_POST['item_object'] ) ) {
		bp_attachments_json_response( false, false, $response );
	}

	$attachment_id = (int) $_POST['attachment_id'];
	$item_id       = (int) $_POST['item_id'];
	$object        = sanitize_key( $_POST['item_object'] );

	if ( 'group' === $object ) {
		$component = 'groups';
	} else {
		$component = apply_filters( 'bp_attachments_remove_file_for_component_single_item', '', $attachment_id, $object, $item_id );
	}

	// Check the nonce
	check_admin_referer( 'remove_bp_attachment_' . $attachment_id, 'nonce' );

	if ( ! bp_attachments_loggedin_user_can( 'edit_bp_attachments', array( 'component' => $component, 'item_id' => $item_id ) ) ) {
		bp_attachments_json_response( false, false, $response );
	}

	if ( delete_post_meta( $attachment_id, "_bp_{$component}_id", $item_id ) ) {
		bp_attachments_json_response( true, false, array( 'feedback' => __( 'was successfully removed from this group.', 'bp-attachments' ) ) );
	} else {
		bp_attachments_json_response( false, false, $response );
	}
}
add_action( 'wp_ajax_bp_attachments_remove_file', 'bp_attachments_remove_file' );
