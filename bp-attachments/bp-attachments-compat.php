<?php
/**
 * BP Attachments Compatibility functions.
 *
 * Make sure the plugin can run the same way with BuddyPress > 12.0 & < 12.0
 *
 * @package \bp-attachments\bp-attachments-compat
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compatibility function to get the user slug.
 *
 * @since 2.0.0
 *
 * @param integer $user_id The user ID.
 * @return string The user slug.
 */
function bp_attachments_get_user_slug( $user_id = 0 ) {
	$user_name = '';
	if ( function_exists( 'bp_core_get_query_parser' ) ) {
		$user_name = bp_members_get_user_slug( $user_id );
	} else {
		$user_name = bp_core_get_username( $user_id );
	}

	return $user_name;
}

/**
 * Compatibility function to get the user URL.
 *
 * @since 2.0.0
 *
 * @param integer $user_id The user ID.
 * @return string The user URL.
 */
function bp_attachments_get_user_url( $user_id = 0 ) {
	$user_url = '';
	if ( function_exists( 'bp_core_get_query_parser' ) ) {
		$user_url = bp_members_get_user_url( $user_id );
	} else {
		$user_url = bp_core_get_user_domain( $user_id );
	}

	return $user_url;
}

/**
 * Compatibility function to get the displayed user URL.
 *
 * @since 2.0.0
 *
 * @param array $path_chunks {
 *     An array of arguments. Optional.
 *
 *     @type string $single_item_component        The component slug the action is relative to.
 *     @type string $single_item_action           The slug of the action to perform.
 *     @type array  $single_item_action_variables An array of additional informations about the action to perform.
 * }
 * @return string The displayed user URL.
 */
function bp_attachments_displayed_user_url( $path_chunks = array() ) {
	$user_url = '';

	if ( function_exists( 'bp_core_get_query_parser' ) ) {
		$user_url = bp_displayed_user_url( bp_members_get_path_chunks( $path_chunks ) );
	} else {
		$user_url = bp_displayed_user_domain();

		if ( $path_chunks ) {
			$action_variables = end( $path_chunks );
			if ( is_array( $action_variables ) ) {
				array_pop( $path_chunks );
				$path_chunks = array_merge( $path_chunks, $action_variables );
			}

			$user_url = trailingslashit( $user_url ) . trailingslashit( implode( '/', $path_chunks ) );
		}
	}

	return $user_url;
}

/**
 * Compatibility function to get the logged in user URL.
 *
 * @since 2.0.0
 *
 * @param array $path_chunks {
 *     An array of arguments. Optional.
 *
 *     @type string $single_item_component        The component slug the action is relative to.
 *     @type string $single_item_action           The slug of the action to perform.
 *     @type array  $single_item_action_variables An array of additional informations about the action to perform.
 * }
 * @return string The logged in user URL.
 */
function bp_attachments_loggedin_user_url( $path_chunks = array() ) {
	$user_url = '';

	if ( function_exists( 'bp_core_get_query_parser' ) ) {
		$user_url = bp_loggedin_user_url( bp_members_get_path_chunks( $path_chunks ) );
	} else {
		$user_url = bp_loggedin_user_domain();

		if ( $path_chunks ) {
			$action_variables = end( $path_chunks );
			if ( is_array( $action_variables ) ) {
				array_pop( $path_chunks );
				$path_chunks = array_merge( $path_chunks, $action_variables );
			}

			$user_url = trailingslashit( $user_url ) . trailingslashit( implode( '/', $path_chunks ) );
		}
	}

	return $user_url;
}
