<?php
/**
 * BP Attachments Loader.
 *
 * @package \bp-attachments\bp-attachments-loader
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks whether it's fine to use experimental features or not.
 *
 * @since 1.1.0
 *
 * @return boolean True to use experimental features. False otherwise.
 */
function bp_attachments_use_experimental_features() {
	$bp_is_src = defined( 'BP_SOURCE_SUBDIRECTORY' ) && BP_SOURCE_SUBDIRECTORY === 'src';

	/**
	 * Filter here to allow/disallow experimental features.
	 *
	 * @since 1.1.0
	 *
	 * @param boolean $bp_is_src Whether the BuddyPress development version is in used or not.
	 */
	return (bool) apply_filters( 'bp_attachments_use_experimental_features', $bp_is_src );
}

/**
 * Get Add-on's optional features.
 *
 * @since 1.1.0
 *
 * @return array The list of supported optional features.
 */
function bp_attachments_get_features() {
	$features = array( 'tracking' );

	if ( bp_attachments_use_experimental_features() ) {

		// New Avatar UI needs Cover Images for now.
		if ( ! bp_disable_cover_image_uploads() ) {
			$features[] = 'profile-images';
		}
	}

	return $features;
}

/**
 * Set up the BP Attachments component.
 *
 * @since 1.0.0
 */
function bp_attachments_component() {
	if ( ! bp_is_active( 'attachments' ) ) {
		return;
	}

	buddypress()->attachments = new BP_Attachments_Component();
}
add_action( 'bp_setup_components', 'bp_attachments_component', 6 );

/**
 * Include the Attachments component to BuddyPress ones.
 *
 * @since 1.0.0
 *
 * @param array  $components The list of available BuddyPress components.
 * @param string $type       The component's type (optional, required, retired..).
 * @return array             The list of available BuddyPress components, including the Attachments one.
 */
function bp_attachments_get_component_info( $components = array(), $type = '' ) {
	if ( 'optional' !== $type ) {
		return $components;
	}

	return array_merge(
		$components,
		array(
			'attachments' => array(
				'title'       => __( 'Attachments', 'bp-attachments' ),
				'description' => __( 'Empower your community with user generated media.', 'bp-attachments' ),
			),
		)
	);
}
add_filter( 'bp_core_get_components', 'bp_attachments_get_component_info', 10, 2 );

/**
 * Inline styles for the WP Admin BuddyPress settings page.
 *
 * @since 1.0.0
 */
function bp_attachments_enqueue_admin_common_assets() {
	wp_add_inline_style(
		'bp-admin-common-css',
		'.settings_page_bp-components tr.attachments td.plugin-title span:before {
			content: "\f104";
		}'
	);
}
add_action( 'bp_admin_enqueue_scripts', 'bp_attachments_enqueue_admin_common_assets', 20 );
