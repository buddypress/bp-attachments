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
		'component'       => '',
		'item_type'       => 'attachment',
		'action'          => 'bp_attachments_upload',
		'file_id'         => 'bp_attachment_file'
	), 'attachments_ajax_upload' );

	// We don't categorized members as a bp_component term
	if ( 'members' == $r['component'] ) {
		unset( $r['component'] );
	}
	$cap_args = false;

	if ( ! empty( $r['component'] ) ) {
		$cap_args = array( 'component' => $r['component'], 'item_id' => $r['item_id'] );
	}

	// capability check
	if ( ! bp_attachments_loggedin_user_can( 'publish_bp_attachments', $cap_args ) )
		wp_die();

	$attachment_id = bp_attachments_handle_upload( $r );

	if ( is_wp_error( $attachment_id ) ) {
		echo json_encode( array(
			'success' => false,
			'data'    => array(
				'message'  => $attachment_id->get_error_message(),
				'filename' => $_FILES['bp_attachment_file']['name'],
			)
		) );

		wp_die();
	}

	if ( ! $attachment = bp_attachments_prepare_attachment_for_js( $attachment_id ) )
		wp_die();

	echo json_encode( array(
		'success' => true,
		'data'    => $attachment,
	) );

	wp_die();
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
