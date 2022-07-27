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
 * Get & load the required JavaScript templates.
 *
 * @since 1.0.0
 */
function bp_attachments_get_javascript_templates() {
	// Temporarly overrides the BuddyPress Template Stack.
	add_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );

	// Load the template parts.
	bp_get_template_part( 'common/js-templates/attachments/media-item' );

	// Stop overidding the BuddyPress Template Stack.
	remove_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );
}

/**
 * Is this a BP Attachments item action request?
 *
 * @since 1.0.0
 *
 * @return bool True if it's a BP Attachments item action request. False otherwise.
 */
function bp_attachments_current_media_action() {
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
function bp_attachments_is_media_view() {
	$retval = false;

	if ( ! is_null( bp_attachments_get_queried_object() ) && 'view' === bp_attachments_current_media_action() ) {
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
function bp_attachments_is_media_download() {
	$retval = false;

	if ( ! is_null( bp_attachments_get_queried_object() ) && 'download' === bp_attachments_current_media_action() ) {
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
function bp_attachments_is_media_embed() {
	$retval = false;

	if ( ! is_null( bp_attachments_get_queried_object() ) && 'embed' === bp_attachments_current_media_action() ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Outputs the displayed user media library.
 *
 * @since 1.0.0
 */
function bp_attachements_output_personal_template() {
	?>
	<p>TBD</p>
	<?php
}

/**
 * Sets the hook to handle the display of the user media library.
 *
 * @since 1.0.0
 */
function bp_attachements_set_personal_template() {
	add_action( 'bp_template_content', 'bp_attachements_output_personal_template' );
}
add_action( 'bp_attachments_personal_screen', 'bp_attachements_set_personal_template' );

/**
 * Sets the Attachments content dummy post.
 *
 * @since 1.0.0
 */
function bp_attachments_set_dummy_post() {
	// Use the Attachments directory title by default.
	$title = bp_get_directory_title( 'attachments' );

	$medium_action = bp_attachments_current_media_action();

	// Downloads are intercepted before in the loading process.
	if ( $medium_action && ! bp_attachments_is_media_download() ) {
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
	add_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );

	// By default the Attachments directory template.
	$template = 'attachments/index';

	$medium_action = bp_attachments_current_media_action();

	// Downloads are intercepted before in the loading process.
	if ( $medium_action && ! bp_attachments_is_media_download() ) {
		$template = 'attachments/single/' . $medium_action;
	}

	$content_template = bp_buffer_template_part( $template, null, false );

	// Stop overriding the BuddyPress Template Stack.
	remove_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );

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
 * Add inline styles for BP Attachments embeds.
 *
 * @since 1.0.0
 */
function bp_attachments_media_embed_inline_styles() {
	if ( ! bp_attachments_is_media_embed() ) {
		return;
	}

	// Temporarly overrides the BuddyPress Template Stack.
	add_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );

	$css = bp_locate_template_asset( 'css/attachments-media-embeds.css' );

	// Stop overriding the BuddyPress Template Stack.
	remove_filter( 'bp_get_template_stack', 'bp_attachments_get_template_stack' );

	// Bail if file wasn't found.
	if ( false === $css ) {
		return;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions
	$css = file_get_contents( $css['file'] );

	printf( '<style type="text/css">%s</style>', wp_kses( $css, array( "\'", '\"' ) ) );
}
add_action( 'embed_head', 'bp_attachments_media_embed_inline_styles', 20 );

/**
 * Gets the owner URL for the BP Attachments media.
 *
 * @since 1.0.0
 *
 * @return string The owner URL for the BP Attachments media.
 */
function bp_attachments_media_get_owner_url() {
	$url    = '#';
	$medium = bp_attachments_get_queried_object();

	if ( isset( $medium->owner_id ) && $medium->owner_id ) {
		$url = bp_core_get_user_domain( $medium->owner_id );
	}

	return $url;
}

/**
 * Outputs the owner URL for the BP Attachments media.
 *
 * @since 1.0.0
 */
function bp_attachments_media_owner_url() {
	echo esc_url( bp_attachments_media_get_owner_url() );
}

/**
 * Gets the owner Avatar for the BP Attachments media.
 *
 * @since 1.0.0
 *
 * @return string HTML output for the owner Avatar for the BP Attachments media.
 */
function bp_attachments_media_get_owner_avatar() {
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
 * Outputs the owner avatar for the BP Attachments media.
 *
 * @since 1.0.0
 */
function bp_attachments_media_owner_avatar() {
	echo wp_kses(
		bp_attachments_media_get_owner_avatar(),
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
 * Gets the owner mention name for the BP Attachments media.
 *
 * @since 1.0.0
 *
 * @return string The owner mention name for the BP Attachments media.
 */
function bp_attachments_get_media_owner_mentionname() {
	$mention_name = '';
	$medium       = bp_attachments_get_queried_object();

	if ( isset( $medium->owner_id ) && $medium->owner_id ) {
		$mention_name = bp_activity_get_user_mentionname( $medium->owner_id );
	}

	return $mention_name;
}

/**
 * Outputs the owner mention name for the BP Attachments media.
 *
 * @since 1.0.0
 */
function bp_attachments_media_owner_mentionname() {
	echo esc_html( bp_attachments_get_media_owner_mentionname() );
}

/**
 * Gets the action made by the owner about the BP Attachments media.
 *
 * @since 1.0.0
 *
 * @return string HTML output for the action made by the owner about the BP Attachments media.
 */
function bp_attachments_media_get_action() {
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
 * Outputs the action made by the owner about the BP Attachments media.
 *
 * @since 1.0.0
 */
function bp_attachments_media_action() {
	echo wp_kses(
		bp_attachments_media_get_action(),
		array(
			'a' => array(
				'class' => true,
				'href'  => true,
			),
		)
	);
}

/**
 * Gets the BP Attachments media view url.
 *
 * @since 1.0.0
 *
 * @return string The BP Attachments media view url.
 */
function bp_attachments_media_get_view_url() {
	$url    = '#';
	$medium = bp_attachments_get_queried_object();

	if ( isset( $medium->links['view'] ) && $medium->links['view'] ) {
		$url = $medium->links['view'];
	}

	return $url;
}

/**
 * Outputs the BP Attachments media view url.
 *
 * @since 1.0.0
 */
function bp_attachments_media_view_url() {
	echo esc_url( bp_attachments_media_get_view_url() );
}

/**
 * Gets the BP Attachments media last modified date.
 *
 * @since 1.0.0
 *
 * @return string The BP Attachments media last modified date.
 */
function bp_attachments_media_get_modified_date() {
	$modified_date = '';
	$medium        = bp_attachments_get_queried_object();

	if ( isset( $medium->last_modified ) && $medium->last_modified ) {
		$modified_date = date_i18n( get_option( 'time_format' ) . ' - ' . get_option( 'date_format' ), $medium->last_modified );
	}

	return $modified_date;
}

/**
 * Outputs the BP Attachments media last modified date.
 *
 * @since 1.0.0
 */
function bp_attachments_media_modified_date() {
	echo esc_html( bp_attachments_media_get_modified_date() );
}

/**
 * Gets the BP Attachments media description.
 *
 * @since 1.0.0
 *
 * @param bool $return_bool Whether to return a boolean or the description string.
 * @return bool|string the BP Attachments media description.
 */
function bp_attachments_media_get_description( $return_bool = false ) {
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
 * Checks if the BP Attachments media has a description.
 *
 * @since 1.0.0
 *
 * @return bool True if the BP Attachments media has a description. False otherwise.
 */
function bp_attachments_media_has_description() {
	return true === bp_attachments_media_get_description( true );
}

/**
 * Gets the BP Attachments media title.
 *
 * @since 1.0.0
 *
 * @return string The BP Attachments media title.
 */
function bp_attachments_media_get_title() {
	$title  = '';
	$medium = bp_attachments_get_queried_object();

	if ( isset( $medium->title ) && $medium->title ) {
		$title = $medium->title;
	}

	return $title;
}

/**
 * Outputs the BP Attachments media title.
 *
 * @since 1.0.0
 */
function bp_attachments_media_title() {
	echo esc_html( bp_attachments_media_get_title() );
}

/**
 * Outputs the BP Attachments media description.
 *
 * @since 1.0.0
 */
function bp_attachments_media_description() {
	echo esc_html( bp_attachments_media_get_description() );
}

/**
 * Outputs classes for the BP Attachment media container.
 *
 * @since 1.0.0
 */
function bp_attachment_media_classes() {
	$classes = array( 'bp-embed-media', 'wp-embed-featured-image' );
	$medium  = bp_attachments_get_queried_object();

	if ( isset( $medium->orientation ) && 'landscape' === $medium->orientation ) {
		$classes[] = 'rectangular';
	} else {
		$classes[] = 'square';
	}

	echo implode( ' ', array_map( 'sanitize_html_class', $classes ) );
}

/**
 * Renders the BP Attachments media.
 *
 * @since 1.0.0
 */
function bp_attachments_media_render() {
	$output = '';
	$medium = bp_attachments_get_queried_object();

	if ( ! isset( $medium->media_type ) || ! $medium->media_type ) {
		return;
	}

	switch ( $medium->media_type ) {
		case 'image':
			$output = sprintf(
				'<img src="%1$s" />',
				esc_url( $medium->vignette )
			);
			break;
		default:
			$output = sprintf(
				'<img src="%1$s" />',
				esc_url( $medium->icon )
			);
			break;
	}

	echo $output; // phpcs:ignore
}
