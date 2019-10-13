<?php
/**
 * BP Attachments Functions.
 *
 * @package BP Attachments
 * @subpackage \bp-attachments\bp-attachments-functions
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get the plugin version.
 *
 * @since 1.0.0
 *
 * @return string The plugin version.
 */
function bp_attachments_get_version() {
	return buddypress()->attachments->version;
}

/**
 * Is this a plugin install?
 *
 * @since 1.0.0
 *
 * @return boolean True if it's an install. False otherwise.
 */
function bp_attachments_is_install() {
	return ! bp_get_option( '_bp_attachments_version' );
}

/**
 * Is this a plugin update?
 *
 * @since 1.0.0
 *
 * @return boolean True if it's an update. False otherwise.
 */
function bp_attachments_is_update() {
	$db_version = bp_get_option( '_bp_attachments_version' );
	$version    = bp_attachments_get_version();

	return version_compare( $version, $db_version, '<' );
}

/**
 * Get the media uploads dir for the requested visibility type.
 *
 * @since 1.0.0
 *
 * @param string $type `public` ou `private`.
 * @return array       The uploads dir data.
 */
function bp_attachments_get_media_uploads_dir( $type = 'public' ) {
	if ( 'public' !== $type && 'private' !== $type ) {
		$type = 'public';
	}

	$bp_uploads_dir = bp_attachments_uploads_dir_get();
	$subdir         = '/' . $type;

	$uploads = array_merge(
		$bp_uploads_dir,
		array(
			'path'   => $bp_uploads_dir['basedir'] . $subdir,
			'url'    => $bp_uploads_dir['baseurl'] . $subdir,
			'subdir' => $subdir,
			'error'  => false,
		)
	);

	unset( $uploads['dir'] );
	return $uploads;
}

/**
 * Get the root folder for private media.
 *
 * @since 1.0.0
 */
function bp_attachments_get_private_uploads_dir() {
	return bp_attachments_get_media_uploads_dir( 'private' );
}

/**
 * Get the root folder for public media.
 *
 * @since 1.0.0
 */
function bp_attachments_get_public_uploads_dir() {
	return bp_attachments_get_media_uploads_dir( 'public' );
}
