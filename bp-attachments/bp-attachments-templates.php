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
 * Get Attachments Templates directory.
 *
 * Templates in this directory are used as default templates.
 *
 * @since 1.0.0
 */
function bp_attachments_get_templates_dir() {
	return buddypress()->attachments->templates_dir;
}

/**
 * Get Attachments Templates url.
 *
 * @since 1.0.0
 */
function bp_attachments_get_templates_url() {
	return buddypress()->attachments->templates_url;
}

/**
 * Temporarly add the BP Attachments templates directory to the BuddyPress
 * Templates stack.
 *
 * @since 1.0.0
 *
 * @param array $stack The BuddyPress Templates stack.
 * @return array       The same Templates stack including the BP Attachments directory.
 */
function bp_attachments_get_template_stack( $stack = array() ) {
	return array_merge( $stack, array( bp_attachments_get_templates_dir() ) );
}

/**
 * Start filtering the template stack to include BP Attachments templates dir.
 *
 * @since 1.0.0
 */
function bp_attachments_start_overriding_template_stack() {
	add_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );
}

/**
 * Stop filtering the template stack to exclude BP Attachments templates dir.
 *
 * @since 1.0.0
 */
function bp_attachments_stop_overriding_template_stack() {
	remove_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );
}

/**
 * Get & load the required JavaScript templates.
 *
 * @since 1.0.0
 *
 * @param string $name The template name to use.
 */
function bp_attachments_get_javascript_template( $name = 'media-item' ) {
	// Temporarly overrides the BuddyPress Template Stack.
	bp_attachments_start_overriding_template_stack();

	// Load the template parts.
	bp_get_template_part( 'common/js-templates/attachments/' . $name );

	// Stop overidding the BuddyPress Template Stack.
	bp_attachments_stop_overriding_template_stack();
}

/**
 * Print Media Library JavaScript Templates into the footer.
 *
 * @since 1.0.0
 */
function bp_attachments_print_media_library_templates() {
	bp_attachments_get_javascript_template();
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
 * Overrides specific BuddyPress template parts when needed.
 *
 * @since 1.0.0
 *
 * @param array $templates The list of requested template parts.
 * @return array The list of template parts.
 */
function bp_attachments_template_part_overrides( $templates = array() ) {
	$is_overriding = false;

	if ( in_array( 'members/single/plugins.php', $templates, true ) && bp_attachments_is_user_personal_library() ) {
		$is_overriding = true;
		array_unshift( $templates, 'members/single/attachments.php' );
	} elseif ( in_array( 'members/single/profile/change-avatar.php', $templates, true ) ) {
		$is_overriding = true;
		array_unshift( $templates, 'members/single/profile/edit-avatar.php' );
	} elseif ( in_array( 'members/single/profile/change-cover-image.php', $templates, true ) ) {
		$is_overriding = true;
		array_unshift( $templates, 'members/single/profile/edit-cover-image.php' );
	} elseif ( in_array( 'members/single/cover-image-header.php', $templates, true ) && ( bp_is_user_change_avatar() || bp_is_user_change_cover_image() ) ) {
		$is_overriding = true;
		array_unshift( $templates, 'members/single/cover-image-header-edit.php' );
	} elseif ( in_array( 'members/single/member-header.php', $templates, true ) && bp_is_user_change_avatar() ) {
		$is_overriding = true;
		array_unshift( $templates, 'members/single/member-header-edit.php' );
	}

	if ( $is_overriding ) {
		// Temporarly overrides the BuddyPress Template Stack.
		bp_attachments_start_overriding_template_stack();

		// Wait for the hook `bp_locate_template` to fire to stop overidding the BuddyPress Template Stack.
		add_action( 'bp_locate_template', 'bp_attachments_stop_overriding_template_stack' );
	}

	return $templates;
}
add_filter( 'bp_get_template_part', 'bp_attachments_template_part_overrides', 1, 1 );
add_filter( 'bp_nouveau_member_locate_template_part', 'bp_attachments_template_part_overrides', 1, 1 );

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
 * Sets the Attachments content dummy post.
 *
 * @since 1.0.0
 */
function bp_attachments_set_dummy_post() {
	// Use the Attachments directory title by default.
	$title = bp_get_directory_title( 'attachments' );

	$medium_action = bp_attachments_current_medium_action();

	// Downloads are intercepted before in the loading process.
	if ( $medium_action && ! bp_attachments_is_medium_download() ) {
		$medium = bp_attachments_get_queried_object();
		$title  = $medium->title;
	}

	bp_theme_compat_reset_post(
		array(
			'ID'             => 0,
			'post_title'     => $title,
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'is_page'        => true,
			'comment_status' => 'closed',
		)
	);
}

/**
 * Sets the Attachments content template.
 *
 * @since 1.0.0
 */
function bp_attachments_set_content_template() {
	// Temporarly overrides the BuddyPress Template Stack.
	bp_attachments_start_overriding_template_stack();

	// By default the Attachments directory template.
	$template = 'attachments/index';

	$medium_action = bp_attachments_current_medium_action();

	// Downloads are intercepted before in the loading process.
	if ( $medium_action && ! bp_attachments_is_medium_download() ) {
		$template = 'attachments/single/' . $medium_action;
	}

	$content_template = bp_buffer_template_part( $template, null, false );

	// Stop overriding the BuddyPress Template Stack.
	bp_attachments_stop_overriding_template_stack();

	// Finally return the buffer.
	return $content_template;
}

/**
 * Sets the Attachments directory theme compat screens.
 *
 * @since 1.0.0
 */
function bp_attachments_set_directory_theme_compat() {
	if ( bp_is_current_component( 'attachments' ) && ! bp_is_user() ) {
		add_action( 'bp_template_include_reset_dummy_post_data', 'bp_attachments_set_dummy_post' );
		add_filter( 'bp_replace_the_content', 'bp_attachments_set_content_template' );
	}
}
add_action( 'bp_setup_theme_compat', 'bp_attachments_set_directory_theme_compat' );

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
		$url = bp_core_get_user_domain( $medium->owner_id );
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
				/* translators: %s: member name */
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
		$mention_name = bp_core_get_username( $medium->owner_id );
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
				esc_url( bp_core_get_user_domain( $medium->owner_id ) ),
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
	$description = esc_html( bp_attachments_medium_get_description() );

	if ( is_embed() ) {
		echo $description; // phpcs:ignore WordPress.Security.EscapeOutput
	} else {
		echo wpautop( str_replace( "\n", '<br><br>', $description ) ); // phpcs:ignore WordPress.Security.EscapeOutput
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
	$title = esc_html( bp_attachments_medium_get_title() );

	if ( is_embed() ) {
		printf( '<a href="%1$s">%2$s</a>', esc_url( bp_attachments_medium_get_view_url() ), $title ); // phpcs:ignore WordPress.Security.EscapeOutput
	} else {
		echo $title; // phpcs:ignore WordPress.Security.EscapeOutput
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

	if ( ( isset( $medium->orientation ) && 'landscape' === $medium->orientation ) || 'audio' === $medium->media_type ) {
		$classes[] = 'rectangular';
	} else {
		$classes[] = 'square';
	}

	echo implode( ' ', array_map( 'sanitize_html_class', $classes ) );
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
					<source src="%1$s" type="%2$s">
					<p>%3$s</p>
				</video>',
				esc_url( $medium->links['src'] ),
				esc_attr( $medium->mime_type ),
				sprintf(
					/* translators: %s is the link to download the video */
					esc_html__( 'Your browser does not take in charge this video format. Please %s to play it from your computer', 'bp-attachments' ),
					sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( bp_attachments_get_medium_download_url() ),
						esc_html__( 'download it', 'bp-attachments' )
					)
				)
			);
			break;
		case 'audio' === $medium->media_type && isset( $medium->links['src'] ):
			$output = sprintf(
				'<audio controls="controls" preload="metadata">
					<source src="%1$s" type="%2$s">
					<p>%3$s</p>
				</audio>',
				esc_url( $medium->links['src'] ),
				esc_attr( $medium->mime_type ),
				sprintf(
					/* translators: %s is the link to download the audio */
					esc_html__( 'Your browser does not take in charge this audio format. Please %s to play it from your computer', 'bp-attachments' ),
					sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( bp_attachments_get_medium_download_url() ),
						esc_html__( 'download it', 'bp-attachments' )
					)
				)
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
 * Checks whether avatar uploads feature is enabled.
 *
 * @since 1.0.0
 *
 * @return bool True if the avatar uploads feature is enabled. False otherwise.
 */
function bp_attachments_is_avatar_uploads_enabled() {
	return ! (int) bp_get_option( 'bp-disable-avatar-uploads' );
}

/**
 * BuddyPress Template Hook's wrapper firing before change-avatar content.
 *
 * @since 1.0.0
 */
function bp_attachments_before_edit_avatar_content() {
	/**
	 * Fires before the display of profile avatar upload content.
	 *
	 * @since BuddyPress 1.1.0
	 */
	do_action( 'bp_before_profile_avatar_upload_content' );
}

/**
 * BuddyPress Template Hook's wrapper firing before change-avatar content.
 *
 * @since 1.0.0
 */
function bp_attachments_after_edit_avatar_content() {
	/**
	 * Fires after the display of profile avatar upload content.
	 *
	 * @since BuddyPress 1.1.0
	 */
	do_action( 'bp_after_profile_avatar_upload_content' );
}

/**
 * BuddyPress Template Hook's wrapper firing before member's header.
 *
 * @since 1.0.0
 */
function bp_attachments_before_member_header() {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		/**
		 * Fires before the display of a member's header.
		 *
		 * @since BuddyPress 1.2.0
		 */
		do_action( 'bp_before_member_header' );
	}
}

/**
 * BuddyPress Template Hook's wrapper firing after member's header.
 *
 * @since 1.0.0
 */
function bp_attachments_after_member_header() {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		/**
		 * Fires after the display of a member's header.
		 *
		 * @since BuddyPress 1.2.0
		 */
		do_action( 'bp_after_member_header' );
		?>
		<div id="template-notices" role="alert" aria-atomic="true">
			<?php
			/** This action is documented in bp-templates/bp-legacy/buddypress/activity/index.php */
			do_action( 'template_notices' );
			?>
		</div>
		<?php
	}
}

/**
 * Outputs member's header action buttons.
 *
 * @since 1.0.0
 *
 * @param array  $args A list of arguments.
 * @param string $show Used to show both or one of ['nouveau', 'legacy'] buttons.
 */
function bp_attachments_member_header_buttons( $args = array(), $show = 'all' ) {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		if ( 'all' !== $show || 'legacy' !== $show ) {
			return;
		}
		?>
		<div id="item-buttons">
			<?php
			/**
			 * Fires in the member header actions section.
			 *
			 * @since BuddyPress 1.2.6
			 */
			do_action( 'bp_member_header_actions' );
			?>
		</div>
		<?php
	} else {
		if ( 'all' !== $show || 'nouveau' !== $show ) {
			return;
		}
		bp_nouveau_member_header_buttons( $args );
	}
}

/**
 * Outputs member's header actions.
 *
 * @since 1.0.0
 */
function bp_attachments_member_header_actions() {
	if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) {
		?>
		<h2 class="user-nicename">@<?php bp_displayed_user_mentionname(); ?></h2>
		<?php
	}

	if ( bp_displayed_user_use_cover_image_header() ) {
		bp_attachments_member_header_buttons(
			array(
				'container'         => 'ul',
				'button_element'    => 'button',
				'container_classes' => array( 'member-header-actions' ),
			)
		);
	}

	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		?>
		<span class="activity" data-livestamp="<?php bp_core_iso8601_date( bp_get_user_last_activity( bp_displayed_user_id() ) ); ?>">
			<?php bp_last_activity( bp_displayed_user_id() ); ?>
		</span>
		<?php
	}

	/**
	 * Fires before the display of the member's header meta.
	 *
	 * @since BuddyPress 1.2.0
	 */
	do_action( 'bp_before_member_header_meta' );
}

/**
 * Checks whether the member header has meta to output.
 *
 * @since 1.0.0
 *
 * @return bool True if the member's header has meta to output. False otherwise.
 */
function bp_attachments_member_has_meta() {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		return true;
	} else {
		return bp_nouveau_member_has_meta();
	}
}

/**
 * Outputs member's header meta.
 *
 * @since 1.0.0
 */
function bp_attachments_member_meta() {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		if ( bp_is_active( 'activity' ) ) {
			bp_activity_latest_update( bp_displayed_user_id() );
		}
	} else {
		bp_nouveau_member_meta();
	}
}

/**
 * BuddyPress Template Hook's wrapper firing into the member's header meta container.
 *
 * @since 1.0.0
 */
function bp_attachments_member_after_meta() {
	/**
	 * Fires after the group header actions section.
	 *
	 * If you'd like to show specific profile fields here use:
	 * bp_member_profile_data( 'field=About Me' ); -- Pass the name of the field
	 *
	 * @since BuddyPress 1.2.0
	 */
	do_action( 'bp_profile_header_meta' );
}

/**
 * Outputs the list of member types of the displayed member.
 *
 * @since 1.0.0
 */
function bp_attachments_member_type_list() {
	if ( ! in_array( bp_get_theme_package_id(), array( 'nouveau', 'renouveau' ), true ) ) {
		return '';
	}

	bp_member_type_list(
		bp_displayed_user_id(),
		array(
			'label'        => array(
				'plural'   => __( 'Member Types', 'bp-attachments' ),
				'singular' => __( 'Member Type', 'bp-attachments' ),
			),
			'list_element' => 'span',
		)
	);
}

/**
 * BuddyPress Template Hook's wrapper firing before the member's edit cover image content.
 *
 * @since 1.0.0
 */
function bp_attachments_member_before_edit_cover_image() {
	/**
	 * Fires before the display of profile cover image upload content.
	 *
	 * @since 2.4.0
	 */
	do_action( 'bp_before_profile_edit_cover_image' );
}

/**
 * BuddyPress Template Hook's wrapper firing after the member's edit cover image content.
 *
 * @since 1.0.0
 */
function bp_attachments_member_after_edit_cover_image() {
	/**
	 * Fires after the display of profile cover image upload content.
	 *
	 * @since 2.4.0
	 */
	do_action( 'bp_after_profile_edit_cover_image' );
}

/**
 * Block rendering functions common helper to get medium data.
 *
 * @since 1.0.0
 *
 * @param array $attributes The block attributes.
 * @return array The medium and the block wrapper attributes.
 */
function bp_attachments_get_block_attachment_data( $attributes = array() ) {
	$attachment_data = array();
	$attrs           = bp_parse_args(
		$attributes,
		array(
			'src'             => '',
			'url'             => '',
			'align'           => '',
			'attachment_type' => '',
		)
	);

	// The `url` attribute is required as it lets us check the image still exists.
	if ( ! $attrs['url'] ) {
		return $attachment_data;
	}

	$medium_data = bp_attachments_get_medium_path( $attrs['url'], true );
	if ( ! isset( $medium_data['id'], $medium_data['path'] ) ) {
		return $attachment_data;
	}

	// Validate and get the Medium object.
	$medium = bp_attachments_get_medium( $medium_data['id'], $medium_data['path'] );
	if ( ! isset( $medium->media_type ) || $attrs['attachment_type'] !== $medium->media_type ) {
		return $attachment_data;
	}

	$extra_wrapper_attributes = array();
	if ( in_array( $attrs['align'], array( 'center', 'left', 'right' ), true ) ) {
		$extra_wrapper_attributes = array( 'class' => 'align' . $attrs['align'] );
	}

	return array(
		'medium'             => $medium,
		'wrapper_attributes' => get_block_wrapper_attributes( $extra_wrapper_attributes ),
	);
}

/**
 * Callback function to render the Image Attachment Block.
 *
 * NB: using such a callback will help us make sure the attached image still
 * exists at the place it was when attached to the object before trying
 * to render it.
 *
 * @since 1.0.0
 *
 * @param array $attributes The block attributes.
 * @return string           HTML output.
 */
function bp_attachments_render_image_attachment( $attributes = array() ) {
	$attributes['attachment_type'] = 'image';
	$attachment_data               = bp_attachments_get_block_attachment_data( $attributes );

	if ( ! isset( $attachment_data['medium'] ) || ! isset( $attachment_data['wrapper_attributes'] ) ) {
		return null;
	}

	// Return the `bp/image-attachment` output.
	return sprintf(
		'<figure %1$s>
			<a href="%2$s"><img src="%3$s" alt="" /></a>
		</figure>',
		$attachment_data['wrapper_attributes'],
		esc_url( $attachment_data['medium']->links['view'] ),
		esc_url_raw( $attachment_data['medium']->links['src'] )
	);
}

/**
 * Callback function to render the Video Attachment Block.
 *
 * NB: using such a callback will help us make sure the attached video still
 * exists at the place it was when attached to the object before trying
 * to render it.
 *
 * @since 1.0.0
 *
 * @param array $attributes The block attributes.
 * @return string           HTML output.
 */
function bp_attachments_render_video_attachment( $attributes = array() ) {
	$attributes['attachment_type'] = 'video';
	$attachment_data               = bp_attachments_get_block_attachment_data( $attributes );

	if ( ! isset( $attachment_data['medium'] ) || ! isset( $attachment_data['wrapper_attributes'] ) ) {
		return null;
	}

	// Return the `bp/video-attachment` output.
	return sprintf(
		'<figure %1$s>
			<video controls="controls" preload="metadata" src="%2$s" />
		</figure>',
		$attachment_data['wrapper_attributes'],
		esc_url_raw( $attachment_data['medium']->links['src'] )
	);
}

/**
 * Callback function to render the Audio Attachment Block.
 *
 * NB: using such a callback will help us make sure the attached video still
 * exists at the place it was when attached to the object before trying
 * to render it.
 *
 * @since 1.0.0
 *
 * @param array $attributes The block attributes.
 * @return string           HTML output.
 */
function bp_attachments_render_audio_attachment( $attributes = array() ) {
	$attributes['attachment_type'] = 'audio';
	$attachment_data               = bp_attachments_get_block_attachment_data( $attributes );

	if ( ! isset( $attachment_data['medium'] ) || ! isset( $attachment_data['wrapper_attributes'] ) ) {
		return null;
	}

	// Return the `bp/audio-attachment` output.
	return sprintf(
		'<figure %1$s>
			<audio controls="controls" preload="metadata" src="%2$s" />
		</figure>',
		$attachment_data['wrapper_attributes'],
		esc_url_raw( $attachment_data['medium']->links['src'] )
	);
}
