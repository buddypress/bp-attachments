<?php
/**
 * BP Attachments Templates.
 *
 * @package \bp-attachments\bp-attachments-templates
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks whether the current page is the community media directory or not.
 *
 * @since 1.0.0
 *
 * @return bool True if the current page is the community media directory. False otherwise.
 */
function bp_attachments_is_community_media_directory() {
	$retval = (bool) ( bp_is_directory() && bp_is_current_component( 'attachments' ) && ! bp_current_action() );

	if ( ! $retval && defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_SERVER['HTTP_REFERER'] ) ) {
		$referer = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
		$path    = wp_parse_url( $referer, PHP_URL_PATH );
		$retval  = buddypress()->attachments->root_slug === trim( $path, '/' );
	}

	return $retval;
}

/**
 * Checks whether the current page is the user's personal library or not.
 *
 * @since 1.0.0
 *
 * @return bool True if the current page is the user's personal library. False otherwise.
 */
function bp_attachments_is_user_personal_library() {
	return (bool) ( bp_is_user() && bp_is_current_component( 'attachments' ) );
}

/**
 * Is this a BP Attachments item action request?
 *
 * @since 1.0.0
 *
 * @return bool True if it's a BP Attachments item action request. False otherwise.
 */
function bp_attachments_current_medium_action() {
	$action = get_query_var( 'bp_attachments_item_action' );

	if ( $action ) {
		$action = bp_attachments_get_item_action_key( $action );
	}

	return $action;
}

/**
 * Is the BP Attachments medium's visibility private or public?
 *
 * @since 1.0.0
 *
 * @return string The BP Attachments medium's visibility. Default to `public`.
 */
function bp_attachments_current_medium_visibility() {
	$visibility = get_query_var( 'bp_attachments_visibility' );

	if ( ! $visibility ) {
		$visibility = 'public';
	}

	return $visibility;
}

/**
 * Is this a BP Attachments item view request?
 *
 * @since 1.0.0
 *
 * @return bool True if it's a BP Attachments item view request. False otherwise.
 */
function bp_attachments_is_medium_view() {
	$retval = false;

	if ( ! is_null( bp_attachments_get_queried_object() ) && 'view' === bp_attachments_current_medium_action() ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Checks whether a user can view a displayed medium.
 *
 * @since 1.0.0
 *
 * @return bool True if a user can view a displayed medium. False otherwise.
 */
function bp_attachments_medium_can_view() {
	$medium = bp_attachments_get_queried_object();

	return bp_attachments_current_user_can( 'read_bp_medium', array( 'bp_medium' => $medium ) );
}
/**
 * Is this a BP Attachments media playlist view request?
 *
 * @since 1.0.0
 *
 * @return bool True if it's a BP Attachments media playlist view request. False otherwise.
 */
function bp_attachments_is_media_playlist_view() {
	$retval         = false;
	$medium         = bp_attachments_get_queried_object();
	$playlist_types = array( 'video_playlist', 'audio_playlist' );

	if ( 'view' === bp_attachments_current_medium_action() && isset( $medium->media_type ) && in_array( $medium->media_type, $playlist_types, true ) ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Is this a BP Attachments media list view request?
 *
 * @since 1.0.0
 *
 * @return bool True if it's a BP Attachments media list view request. False otherwise.
 */
function bp_attachments_is_media_list_view() {
	$retval     = false;
	$medium     = bp_attachments_get_queried_object();
	$list_types = array( 'album', 'folder' );

	if ( 'view' === bp_attachments_current_medium_action() && isset( $medium->media_type ) && in_array( $medium->media_type, $list_types, true ) ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Is this a BP Attachments item download request?
 *
 * @since 1.0.0
 *
 * @return bool True if it's a BP Attachments item download request. False otherwise.
 */
function bp_attachments_is_medium_download() {
	$retval = false;

	if ( ! is_null( bp_attachments_get_queried_object() ) && 'download' === bp_attachments_current_medium_action() ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Is this a BP Attachments item embed request?
 *
 * @since 1.0.0
 *
 * @return bool True if it's a BP Attachments item embed request. False otherwise.
 */
function bp_attachments_is_medium_embed() {
	$retval = false;

	if ( ! is_null( bp_attachments_get_queried_object() ) && 'embed' === bp_attachments_current_medium_action() ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Gets the owner URL for the BP Attachments medium.
 *
 * @since 1.0.0
 *
 * @return string The owner URL for the BP Attachments medium.
 */
function bp_attachments_medium_get_owner_url() {
	$url    = '#';
	$medium = bp_attachments_get_queried_object();

	if ( isset( $medium->owner_id ) && $medium->owner_id ) {
		$url = bp_attachments_get_user_url( $medium->owner_id );
	}

	return $url;
}

/**
 * Outputs the owner URL for the BP Attachments medium.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_owner_url() {
	echo esc_url( bp_attachments_medium_get_owner_url() );
}

/**
 * Gets the owner Avatar for the BP Attachments medium.
 *
 * @since 1.0.0
 *
 * @return string HTML output for the owner Avatar for the BP Attachments medium.
 */
function bp_attachments_medium_get_owner_avatar() {
	$avatar = '';
	$medium = bp_attachments_get_queried_object();

	if ( isset( $medium->owner_id ) && $medium->owner_id ) {
		$avatar = bp_core_fetch_avatar(
			array(
				'item_id' => $medium->owner_id,
				'type'    => 'thumb',
				'width'   => '45',
				'height'  => '45',
				'html'    => true,
				/* translators: %s is the displayed User full name */
				'alt'     => sprintf( __( 'Profile picture of %s', 'bp-attachments' ), bp_core_get_user_displayname( $medium->owner_id ) ),
			)
		);
	}

	return $avatar;
}

/**
 * Outputs the owner avatar for the BP Attachments medium.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_owner_avatar() {
	echo wp_kses(
		bp_attachments_medium_get_owner_avatar(),
		array(
			'img' => array(
				'src'    => true,
				'class'  => true,
				'alt'    => true,
				'width'  => true,
				'height' => true,
			),
		)
	);
}

/**
 * Gets the owner mention name for the BP Attachments medium.
 *
 * @since 1.0.0
 *
 * @return string The owner mention name for the BP Attachments medium.
 */
function bp_attachments_get_medium_owner_mentionname() {
	$mention_name = '';
	$medium       = bp_attachments_get_queried_object();

	if ( isset( $medium->owner_id ) && $medium->owner_id ) {
		$mention_name = bp_attachments_get_user_slug( $medium->owner_id );
	}

	return $mention_name;
}

/**
 * Outputs the owner mention name for the BP Attachments medium.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_owner_mentionname() {
	echo esc_html( bp_attachments_get_medium_owner_mentionname() );
}

/**
 * Gets the owner display name for the BP Attachments medium.
 *
 * @since 1.0.0
 *
 * @return string The owner mention name for the BP Attachments medium.
 */
function bp_attachments_get_medium_owner_displayname() {
	$display_name = '';
	$medium       = bp_attachments_get_queried_object();

	if ( isset( $medium->owner_id ) && $medium->owner_id ) {
		$display_name = bp_core_get_user_displayname( $medium->owner_id );
	}

	return $display_name;
}

/**
 * Outputs the owner mention name for the BP Attachments medium.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_owner_displayname() {
	echo esc_html( bp_attachments_get_medium_owner_displayname() );
}

/**
 * Checks if the BP Attachments medium's owner has a description.
 *
 * @since 1.0.0
 *
 * @return bool True if the BP Attachments medium's owner has a description. False otherwise.
 */
function bp_attachments_medium_owner_has_description() {
	$owner_has_description = false;
	$medium                = bp_attachments_get_queried_object();

	if ( isset( $medium->owner_id ) && $medium->owner_id ) {
		$owner_has_description = (bool) get_the_author_meta( 'description', $medium->owner_id );
	}

	return $owner_has_description;
}

/**
 * Outputs the BP Attachments medium's owner description.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_owner_description() {
	$owner_description = '';
	$medium            = bp_attachments_get_queried_object();

	if ( isset( $medium->owner_id ) && $medium->owner_id ) {
		the_author_meta( 'description', $medium->owner_id );
	}
}

/**
 * Gets the action made by the owner about the BP Attachments medium.
 *
 * @since 1.0.0
 *
 * @return string HTML output for the action made by the owner about the BP Attachments medium.
 */
function bp_attachments_medium_get_action() {
	$action = '';
	$medium = bp_attachments_get_queried_object();

	if ( isset( $medium->owner_id ) && $medium->owner_id ) {
		$action = sprintf(
			/* translators: %s is the user link. */
			__( '%s shared a media.', 'bp-attachments' ),
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( bp_attachments_get_user_url( $medium->owner_id ) ),
				esc_html( bp_core_get_user_displayname( $medium->owner_id ) )
			)
		);
	}

	return $action;
}

/**
 * Outputs the action made by the owner about the BP Attachments medium.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_action() {
	echo wp_kses(
		bp_attachments_medium_get_action(),
		array(
			'a' => array(
				'class' => true,
				'href'  => true,
			),
		)
	);
}

/**
 * Gets the BP Attachments medium view url.
 *
 * @since 1.0.0
 *
 * @return string The BP Attachments medium view url.
 */
function bp_attachments_medium_get_view_url() {
	$url    = '#';
	$medium = bp_attachments_get_queried_object();

	if ( isset( $medium->links['view'] ) && $medium->links['view'] ) {
		$url = $medium->links['view'];
	}

	return $url;
}

/**
 * Outputs the BP Attachments medium view url.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_view_url() {
	echo esc_url( bp_attachments_medium_get_view_url() );
}

/**
 * Gets the BP Attachments medium last modified date.
 *
 * @since 1.0.0
 *
 * @return string The BP Attachments medium last modified date.
 */
function bp_attachments_medium_get_modified_date() {
	$modified_date = '';
	$medium        = bp_attachments_get_queried_object();

	if ( isset( $medium->last_modified ) && $medium->last_modified ) {
		$modified_date = date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), $medium->last_modified );
	}

	return $modified_date;
}

/**
 * Outputs the BP Attachments medium last modified date.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_modified_date() {
	echo esc_html( bp_attachments_medium_get_modified_date() );
}

/**
 * Gets the BP Attachments medium file size.
 *
 * @since 1.0.0
 *
 * @return string The BP Attachments medium file size.
 */
function bp_attachments_medium_get_size() {
	$file_size = '';
	$medium    = bp_attachments_get_queried_object();

	if ( isset( $medium->size ) && $medium->size ) {
		$file_size = bp_attachments_format_file_size( $medium->size );
	}

	return $file_size;
}

/**
 * Outputs the BP Attachments medium file size.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_size() {
	echo esc_html( bp_attachments_medium_get_size() );
}

/**
 * Gets the BP Attachments medium mime type.
 *
 * @since 1.0.0
 *
 * @return string The BP Attachments medium mime type.
 */
function bp_attachments_medium_get_mime_type() {
	$mime_type = '';
	$medium    = bp_attachments_get_queried_object();

	if ( isset( $medium->mime_type ) && $medium->mime_type ) {
		$mime_type = $medium->mime_type;
	}

	return $mime_type;
}

/**
 * Outputs the BP Attachments medium mime type.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_mime_type() {
	echo esc_html( bp_attachments_medium_get_mime_type() );
}

/**
 * Gets the BP Attachments medium description.
 *
 * @since 1.0.0
 *
 * @param bool $return_bool Whether to return a boolean or the description string.
 * @return bool|string the BP Attachments medium description.
 */
function bp_attachments_medium_get_description( $return_bool = false ) {
	$description = '';
	$medium      = bp_attachments_get_queried_object();

	if ( isset( $medium->description ) && $medium->description ) {
		$description = $medium->description;
	}

	if ( $return_bool ) {
		return (bool) $description;
	}

	return $description;
}

/**
 * Checks if the BP Attachments medium has a description.
 *
 * @since 1.0.0
 *
 * @return bool True if the BP Attachments medium has a description. False otherwise.
 */
function bp_attachments_medium_has_description() {
	return true === bp_attachments_medium_get_description( true );
}

/**
 * Outputs the BP Attachments medium description.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_description() {
	$description = bp_attachments_medium_get_description();

	if ( is_embed() ) {
		echo esc_html( $description );
	} else {
		$description = wpautop( str_replace( "\n", '<br><br>', $description ) );
		echo wp_kses( $description, array( 'p' => true ) );
	}
}

/**
 * Gets the BP Attachments medium title.
 *
 * @since 1.0.0
 *
 * @return string The BP Attachments medium title.
 */
function bp_attachments_medium_get_title() {
	$title  = '';
	$medium = bp_attachments_get_queried_object();

	if ( isset( $medium->title ) && $medium->title ) {
		$title = $medium->title;
	}

	return $title;
}

/**
 * Outputs the BP Attachments medium title.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_title() {
	$title = bp_attachments_medium_get_title();

	if ( is_embed() ) {
		printf(
			'<a href="%1$s">%2$s</a>',
			esc_url( bp_attachments_medium_get_view_url() ),
			esc_html( $title )
		);
	} else {
		echo esc_html( $title );
	}
}

/**
 * Outputs classes for the BP Attachment medium container.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_classes() {
	$classes = array( 'bp-embed-media', 'wp-embed-featured-image' );
	$medium  = bp_attachments_get_queried_object();

	if ( ( isset( $medium->orientation ) && 'landscape' === $medium->orientation ) || 'audio' === $medium->media_type || 'video' === $medium->media_type ) {
		$classes[] = 'rectangular';
	} else {
		$classes[] = 'square';
	}

	echo implode( ' ', array_map( 'esc_attr', $classes ) );
}

/**
 * Provide a fallback text including a download link in case the browser can't display the medium.
 *
 * @since 1.0.0
 *
 * @param string $download_url The Attachment medium download URL.
 * @return string HTML Output.
 */
function bp_attachments_get_medium_fallback_text( $download_url = '' ) {
	return sprintf(
		/* translators: %s is the link to download the media */
		esc_html__( 'If your browser does not take in charge this media format. Please %s to play it from your computer.', 'bp-attachments' ),
		sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $download_url ),
			esc_html__( 'download it', 'bp-attachments' )
		)
	);
}

/**
 * Output the fallback text.
 *
 * @since 1.0.0
 *
 * @param string $download_url The Attachment medium download URL.
 */
function bp_attachments_medium_fallback_text( $download_url = '' ) {
	if ( ! $download_url ) {
		$download_url = bp_attachments_get_medium_download_url();
	}

	echo wp_kses( bp_attachments_get_medium_fallback_text( $download_url ), array( 'a' => array( 'href' => true ) ) );
}

/**
 * Returns the BP Attachments queried medium output.
 *
 * @since 1.0.0
 *
 * @return string the medium HTML output.
 */
function bp_attachments_get_medium_output() {
	$output = '';
	$medium = bp_attachments_get_queried_object();

	if ( ! isset( $medium->media_type ) || ! $medium->media_type ) {
		return;
	}

	switch ( true ) {
		case 'image' === $medium->media_type && isset( $medium->links['src'] ):
			$output = sprintf(
				'<img src="%1$s" alt="" />',
				esc_url( $medium->links['src'] )
			);
			break;
		case 'video' === $medium->media_type && isset( $medium->links['src'] ):
			$output = sprintf(
				'<video controls="controls" muted="true" preload="metadata">
					<source src="%s">
				</video>',
				esc_url( $medium->links['src'] )
			);
			break;
		case 'audio' === $medium->media_type && isset( $medium->links['src'] ):
			$output = sprintf(
				'<audio controls="controls" preload="metadata">
					<source src="%s">
				</audio>',
				esc_url( $medium->links['src'] )
			);
			break;
		default:
			$output = sprintf(
				'<img src="%1$s" alt="" />',
				esc_url( $medium->icon )
			);
			break;
	}

	return $output;
}

/**
 * Renders the BP Attachments queried medium.
 *
 * @since 1.0.0
 */
function bp_attachments_render_medium() {
	echo bp_attachments_get_medium_output(); // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Returns the queried medium type.
 *
 * @since 1.0.0
 *
 * @return string The queried medium type.
 */
function bp_attachments_get_medium_type() {
	$type   = '';
	$medium = bp_attachments_get_queried_object();

	if ( isset( $medium->media_type ) ) {
		$type = $medium->media_type;
	}

	return sanitize_file_name( $type );
}

/**
 * Outputs the queried medium type.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_type() {
	echo bp_attachments_get_medium_type(); // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Returns the medium template part according to its visibility and type.
 *
 * @since 1.0.0
 *
 * @return string The part name to use for the queried medium.
 */
function bp_attachments_get_medium_part() {
	$part = '';

	if ( 'private' !== bp_attachments_current_medium_visibility() ) {
		$part = bp_attachments_get_medium_type();
	}

	return $part;
}

/**
 * Returns the queried medium download url.
 *
 * @since 1.0.0
 *
 * @return string The queried medium download url.
 */
function bp_attachments_get_medium_download_url() {
	$url    = '#';
	$medium = bp_attachments_get_queried_object();

	if ( isset( $medium->links['download'] ) ) {
		$url = $medium->links['download'];
	}

	return $url;
}

/**
 * Outputs the queried medium download url.
 *
 * @since 1.0.0
 */
function bp_attachments_medium_download_url() {
	echo esc_url( bp_attachments_get_medium_download_url() );
}

/**
 * Returns the login URL used to ask a vistor identify themself.
 *
 * @since 1.0.0
 *
 * @return string The login URL to use for a displayed private medium.
 */
function bp_attachments_medium_get_login_url() {
	$current_medium_url = bp_attachments_medium_get_view_url();

	if ( bp_attachments_is_medium_download() ) {
		$current_medium_url = bp_attachments_get_medium_download_url();
	}

	return wp_login_url( $current_medium_url );
}
