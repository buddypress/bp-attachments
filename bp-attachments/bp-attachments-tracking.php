<?php
/**
 * BP Attachments Tracking Functions.
 *
 * Tracking attachments is performed using the BP Activity table.
 *
 * @package \bp-attachments\bp-attachments-tracking
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the Attachments tracking database table.
 *
 * @since 1.0.0
 */
function bp_attachments_tracking_get_table() {
	/**
	 * Filter here to use another DB table.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table The DB table name to use.
	 */
	return apply_filters( 'bp_attachments_tracking_get_table', bp_core_get_table_prefix() . 'bp_activity' );
}

/**
 * Returns the Attachments tracking meta database table.
 *
 * @since 1.0.0
 */
function bp_attachments_tracking_get_meta_table() {
	/**
	 * Filter here to use another DB meta table.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table The DB meta table name to use.
	 */
	return apply_filters( 'bp_attachments_tracking_get_meta_table', bp_core_get_table_prefix() . 'bp_activity_meta' );
}

/**
 * Returns the tracking action using a Block format.
 *
 * NB: the Tracked Medium type stored into the action field of the BP Activity table.
 *
 * @since 1.0.0
 *
 * @param string $media_type The media type. It can be `image`, `video`, `audio` or `file`.
 * @return string The serialized as Block media type.
 */
function bp_attachments_tracking_get_action( $media_type = 'file' ) {
	if ( ! in_array( $media_type, array( 'image', 'video', 'audio', 'file' ), true ) ) {
		$media_type = 'file';
	}

	return bp_attachments_get_serialized_block(
		array(
			'blockName'    => 'bp/attachments-action',
			'innerContent' => array(),
			'attrs'        => array(
				'type' => $media_type,
			),
		)
	);
}

/**
 * Returns the Tracked Medium type stored into the action field of the BP Activity table.
 *
 * @since 1.0.0
 *
 * @param string $action The serialized Tracked Medium type stored into the action field of the BP Activity table.
 * @return string The rendered Tracked Medium type stored into the action field of the BP Activity table.
 */
function bp_attachments_tracking_render_action( $action = '' ) {
	$type   = 'file';
	$blocks = parse_blocks( stripslashes_deep( $action ) );
	$block  = reset( $blocks );

	if ( isset( $block['attrs']['type'] ) && $block['attrs']['type'] ) {
		$type = $block['attrs']['type'];
	}

	return $type;
}

/**
 * Renders a Tracked medium.
 *
 * @since 1.0.0
 *
 * @param string $content The serialized Tracked Medium content.
 * @return string The rendered Tracked Medium content.
 */
function bp_attachments_tracking_render_media( $content = '' ) {
	$blocks = parse_blocks( stripslashes_deep( $content ) );
	$output = '';

	foreach ( $blocks as $block ) {
		$output .= render_block( $block );
	}

	return $output;
}

/**
 * Inserts a new record into the Media Tracking table.
 *
 * @todo The `component` field should be filled according to the name of the uploads subdir (eg: `members`, `friends`, `groups`).
 *
 * @since 1.0.0
 *
 * @param BP_Medium $medium The BP Medium object.
 * @return int|false The inserted Media Tracking ID. False on failure.
 */
function bp_attachments_tracking_record_created_medium( $medium ) {
	global $wpdb;

	// Folders are not tracked for now.
	if ( ! isset( $medium->owner_id, $medium->media_type, $medium->links, $medium->visibility ) || 'folder' === $medium->media_type ) {
		return false;
	}

	$inserted = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		bp_attachments_tracking_get_table(),
		array(
			'user_id'       => $medium->owner_id,
			'component'     => buddypress()->members->id,
			'type'          => 'uploaded_attachment',
			'action'        => bp_attachments_tracking_get_action( $medium->media_type ),
			'content'       => bp_attachments_get_serialized_medium_block( $medium ),
			'primary_link'  => $medium->links['view'],
			'item_id'       => 0,
			'date_recorded' => bp_core_current_time(),
			'hide_sitewide' => 'public' !== $medium->visibility,
		),
		array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d' )
	);

	if ( ! $inserted ) {
		return false;
	}

	return $wpdb->insert_id;
}
add_action( 'bp_attachments_created_media', 'bp_attachments_tracking_record_created_medium', 10, 1 );

/**
 * Erases a tracked Attachment when the corresponding medium was deleted.
 *
 * @since 1.0.0
 *
 * @param object $medium A Medium data object.
 * @return bool True on success. False on failure.
 */
function bp_attachments_tracking_erase_deleted_medium( $medium ) {
	global $wpdb;

	// Make sure we have the needed props and we're not dealing with revisions.
	if ( ! isset( $medium->id, $medium->owner_id, $medium->abspath, $medium->visibility ) || preg_match( '#\._revisions#', $medium->abspath ) ) {
		return false;
	}

	$up_dir = bp_attachments_get_media_uploads_dir( $medium->visibility );
	$chunks = explode( '/', trim( str_replace( $up_dir['path'], '', $medium->abspath ), '/' ) );
	$object = array_shift( $chunks );

	// Remove the owner id from chunks.
	array_shift( $chunks );

	$item_action_variables = array( $medium->id );
	if ( array_filter( $chunks ) ) {
		$item_action_variables = array_merge( $chunks, $item_action_variables );
	}

	// Set the primary link.
	$primary_link = bp_attachments_get_medium_url(
		array(
			'visibility'            => $medium->visibility,
			'object'                => $object,
			'object_item'           => bp_attachments_get_user_slug( $medium->owner_id ),
			'item_action'           => bp_attachments_get_item_action_slug( 'view' ),
			'item_action_variables' => $item_action_variables,
		)
	);

	$deleted = $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		bp_attachments_tracking_get_table(),
		array(
			'type'         => 'uploaded_attachment',
			'primary_link' => $primary_link,
		),
		array( '%s', '%s' )
	);

	if ( ! $deleted ) {
		return false;
	}

	return true;
}
add_action( 'bp_attachments_deleted_medium', 'bp_attachments_tracking_erase_deleted_medium', 10, 1 );

/**
 * Retrieves Attachments Tracking records.
 *
 * NB: it only supports public records about the Members component for now.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Associative array of arguments list used to build a medium action URL.
 *
 *     @type string $visibility The media visibility. It can be `public` or `private`. Forced to `public`.
 *     @type string $component  The component the media is attached to. Forced to `members`.
 *     @type string $type       The media type to retrieve. It can be `any`, `image`, `video`, `audio` or `file`.
 *     @type int    $page       Which page of results to fetch. Default: 1.
 *     @type int    $per_page   Number of results per page. Default: 25.
 *     @type string $sort       ASC or DESC. Default: 'DESC'.
 *     @type string $order      Column to order results by.
 * }
 * @return array The list of records matching arguments.
 */
function bp_attachments_tracking_retrieve_records( $args = array() ) {
	global $wpdb;

	$results = array(
		'media' => null,
		'total' => null,
	);

	$r = array_merge(
		bp_parse_args(
			$args,
			array(
				'page'     => 1,
				'per_page' => 25,
				'sort'     => 'DESC',          // ASC or DESC.
				'order'    => 'date_recorded', // Column to order by.
				'type'     => 'any',
			)
		),
		array(
			'visibility' => 'public',
			'component'  => 'members',
		)
	);

	$table = bp_attachments_tracking_get_table();

	// Sanitize 'order'.
	$order = 'date_recorded';
	$sort  = $r['sort'];
	if ( 'DESC' !== $sort ) {
		$sort = bp_esc_sql_order( $sort );
	}

	switch ( $r['order'] ) {
		case 'user_id':
		case 'component':
		case 'type':
		case 'action':
		case 'content':
		case 'primary_link':
		case 'item_id':
		case 'secondary_item_id':
		case 'hide_sitewide':
		case 'mptt_left':
		case 'mptt_right':
		case 'is_spam':
			break;

		default:
			$order = $r['order'];
			break;
	}

	$sql = array(
		'select'   => sprintf( 'SELECT * FROM %s a', $table ),
		'where'    => array(
			'component'     => $wpdb->prepare( 'a.component = %s', $r['component'] ),
			'activity_type' => 'a.type = \'uploaded_attachment\'',
			'visibility'    => $wpdb->prepare( 'a.hide_sitewide = %d', 'public' !== $r['visibility'] ),
		),
		'order_by' => sprintf( 'ORDER BY a.%1$s %2$s', sanitize_key( $order ), $sort ),
		'limit'    => '',
	);

	if ( $r['page'] && $r['per_page'] ) {
		$page         = (int) $r['page'];
		$per_page     = (int) $r['per_page'];
		$sql['limit'] = $wpdb->prepare( 'LIMIT %d, %d', absint( ( $page - 1 ) * $per_page ), $per_page );
	}

	$supported_types = array( 'image', 'video', 'audio', 'file' );
	if ( 'any' !== $r['type'] && in_array( $r['type'], $supported_types, true ) ) {
		$sql['where']['media_type'] = $wpdb->prepare( 'a.action = %s', bp_attachments_tracking_get_action( $r['type'] ) );
	}

	// Join parts.
	$sql['where'] = 'WHERE ' . implode( ' AND ', $sql['where'] );
	$query        = implode( ' ', $sql );

	// Fetch results and total.
	$results['media'] = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
	$results['total'] = (int) $wpdb->get_var( "SELECT count(DISTINCT a.id) FROM {$table} a {$sql['where']}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL

	return $results;
}

/**
 * Exclude Uploaded attachments from activity loops.
 *
 * @todo This will need to further thoughts. Imho, we should:
 * - Register activity types.
 * - Add Activity Action strings callback.
 * - Leave an option to allow Attachments to be displayed into activity streams.
 *
 * @since 1.0.0
 *
 * @param array $where_conditions Activity loop conditions for the MySQL WHERE statement.
 * @return array Activity loop conditions for the MySQL WHERE statement.
 */
function bp_attachments_tracking_exclude_from_activities( $where_conditions ) {
	if ( isset( $where_conditions['excluded_types'] ) ) {
		preg_match( '/a\.type NOT IN \([^\)](.*?)[^\)]\)/', $where_conditions['excluded_types'], $matches );
		if ( isset( $matches[0], $matches[1] ) && $matches[0] && $matches[1] ) {
			$excluded_types                     = '\'' . trim( $matches[1], '\'' ) . '\'';
			$where_conditions['excluded_types'] = str_replace( $excluded_types, $excluded_types . ', \'uploaded_attachment\'', $where_conditions['excluded_types'] );
		}
	}

	return $where_conditions;
}
add_filter( 'bp_activity_get_where_conditions', 'bp_attachments_tracking_exclude_from_activities', 1, 1 );

/**
 * Register the community directory assets.
 *
 * @since 1.0.0
 */
function bp_attachments_tracking_register_assets() {
	$bp_attachments = buddypress()->attachments;

	wp_register_script(
		'bp-attachments-directory',
		$bp_attachments->js_url . 'front-end/directory.js',
		array(
			'lodash',
			'wp-dom-ready',
			'wp-url',
		),
		$bp_attachments->version,
		true
	);

	// Let the theme customize the Media Directory styles.
	$css = bp_attachments_locate_template_asset( 'css/attachments-media-directory.css' );
	if ( isset( $css['uri'] ) ) {
		wp_register_style(
			'bp-attachments-directory',
			$css['uri'],
			array(),
			$bp_attachments->version
		);
	}
}
add_action( 'bp_attachments_register_front_end_assets', 'bp_attachments_tracking_register_assets', 1 );

/**
 * Removes BP Generic JavaScript dependencies from the Community Media directory.
 *
 * @since 1.0.0
 *
 * @param array $scripts The BP Generic JavaScript dependencies.
 * @return arrray Potentialy less Generic JavaScript dependencies.
 */
function bp_attachments_tracking_unregister_common_scripts( $scripts = array() ) {
	if ( bp_attachments_is_community_media_directory() ) {
		return array_diff_key(
			$scripts,
			array(
				'bp-confirm'          => false,
				'bp-jquery-query'     => false,
				'bp-jquery-cookie'    => false,
				'bp-jquery-scroll-to' => false,
				'jquery-caret'        => false,
				'jquery-atwho'        => false,
				'bp-livestamp'        => false,
			)
		);
	}

	return $scripts;
}
add_filter( 'bp_core_register_common_scripts', 'bp_attachments_tracking_unregister_common_scripts', 0, 1 );

/**
 * Enqueue the community directory assets.
 *
 * @since 1.0.0
 */
function bp_attachments_enqueue_tracking_assets() {
	if ( ! bp_attachments_is_community_media_directory() ) {
		return;
	}

	wp_enqueue_style( 'bp-attachments-directory' );

	$endpoint = sprintf(
		'/%1$s/%2$s/attachments/tracking?_embed',
		bp_rest_namespace(),
		bp_rest_version()
	);

	// Preloads Community media tracked items.
	$preload_data = array_reduce(
		array( $endpoint ),
		'rest_preload_api_request',
		array()
	);

	$settings = array(
		'path'        => ltrim( $endpoint, '/' ),
		'root'        => esc_url_raw( get_rest_url() ),
		'nonce'       => wp_create_nonce( 'wp_rest' ),
		'placeholder' => esc_url_raw( wp_mime_type_icon( 'default' ) ),
		'items'       => current( $preload_data ),
	);

	wp_enqueue_script( 'bp-attachments-directory' );

	wp_add_inline_script(
		'bp-attachments-directory',
		'window.bpAttachmentsDirectorySettings = ' . wp_json_encode( $settings ) . ';',
		'before'
	);
}
add_action( 'bp_enqueue_community_scripts', 'bp_attachments_enqueue_tracking_assets', 30 );

/**
 * Replaces the Audio, Video, and File BP Attachments block output with their icon.
 *
 * @since 1.0.0
 *
 * @param string $output          The block output.
 * @param array  $attributes      The block attributes.
 * @param array  $attachment_data The block attachment's data.
 * @return string The block output.
 */
function bp_attachments_tracking_rendered_media_item_content( $output, $attributes, $attachment_data ) {
	if ( bp_attachments_is_community_media_directory() && isset( $attachment_data['medium']->links['view'], $attachment_data['wrapper_attributes'] ) ) {
		if ( 'bp_attachments_rendered_image_content' !== current_filter() ) {
			$output = sprintf(
				'<figure %1$s>
					<a href="%2$s"><img src="%3$s" alt="" /></a>
				</figure>',
				$attachment_data['wrapper_attributes'],
				esc_url( $attachment_data['medium']->links['view'] ),
				esc_url_raw( $attachment_data['medium']->icon )
			);
		}

		if ( isset( $attachment_data['medium']->title ) ) {
			$output .= sprintf(
				'<div class="bp-media-item-title">
					<a href="%1$s">%2$s</a>
				</div>',
				esc_url( $attachment_data['medium']->links['view'] ),
				esc_html( $attachment_data['medium']->title )
			);
		}
	}

	return $output;
}
add_filter( 'bp_attachments_rendered_audio_content', 'bp_attachments_tracking_rendered_media_item_content', 10, 3 );
add_filter( 'bp_attachments_rendered_video_content', 'bp_attachments_tracking_rendered_media_item_content', 10, 3 );
add_filter( 'bp_attachments_rendered_image_content', 'bp_attachments_tracking_rendered_media_item_content', 10, 3 );
add_filter( 'bp_attachments_rendered_file_content', 'bp_attachments_tracking_rendered_media_item_content', 10, 3 );

/**
 * Get the BP Attachments directory nav items.
 *
 * @since 1.0.0
 *
 * @return array The BP Attachments directory nav items.
 */
function bp_attachments_tracking_get_directory_nav_items() {
	$default_nav_items = array(
		'image' => __( 'Images', 'bp-attachments' ),
		'video' => __( 'Movies', 'bp-attachments' ),
		'audio' => __( 'Sounds', 'bp-attachments' ),
	);

	$allowed_media_types = bp_attachments_get_allowed_media_types();
	$nav_items           = array_intersect_key( $default_nav_items, $allowed_media_types );
	$file_types          = array(
		'document'    => true,
		'spreadsheet' => true,
		'interactive' => true,
		'text'        => true,
		'archive'     => true,
	);

	if ( array_intersect_key( $default_nav_items, $allowed_media_types ) ) {
		$nav_items['file'] = __( 'Other files', 'bp-attachments' );
	}

	/**
	 * Use this filter to remove nav items if needed.
	 *
	 * @since 1.0.0
	 *
	 * @param array $nav_items The BP Attachments directory nav items.
	 */
	return apply_filters( 'bp_attachments_tracking_get_directory_nav_items', $nav_items );
}

/**
 * Output the BP Attachments directory nav.
 *
 * @since 1.0.0
 */
function bp_attachments_tracking_output_directory_nav() {
	$nav_items = bp_attachments_tracking_get_directory_nav_items();
	?>
	<nav id="bp-attachments-nav" class="main-navs bp-navs dir-navs" role="navigation" aria-label="<?php esc_attr_e( 'Attachments Directory menu', 'bp-attachments' ); ?>">
		<ul class="component-navigation">
			<li id="bp-attachments-any" class="selected" data-bp-scope="any" data-bp-object="attachments"><a href="#any-attachments"><?php esc_html_e( 'All Media', 'bp-attachments' ); ?></a></li>

			<?php foreach ( $nav_items as $key_nav => $nav_text ) : ?>
				<li id="bp-attachments-<?php echo esc_attr( $key_nav ); ?>" data-bp-scope="<?php echo esc_attr( $key_nav ); ?>" data-bp-object="attachments"><a href="#<?php echo esc_attr( $key_nav ); ?>-attachments"><?php echo esc_html( $nav_text ); ?></a></li>
			<?php endforeach; ?>

		</ul><!-- .component-navigation -->
	</nav><!-- #bp-attachments-nav -->
	<?php
}

/**
 * Delete rewrite rules once the BP Attachments directory page has been mapped by BuddyPress.
 *
 * Doing so makes sure these rules are regenerated during next front-end page load.
 *
 * @since 1.0.0
 *
 * @param array $old_value The previous list of directory page IDs keyed with corresponding components slug.
 * @param array $value     The updated list of directory page IDs keyed with corresponding components slug.
 */
function bp_attachments_admin_bp_page_mapped_clean_rewrite_rules( $old_value, $value ) {
	if ( isset( $value['attachments'] ) && ! isset( $old_value['attachments'] ) ) {
		delete_option( 'rewrite_rules' );
	}
}
add_action( 'update_option_bp-pages', 'bp_attachments_admin_bp_page_mapped_clean_rewrite_rules', 10, 2 );

/**
 * Delete rewrite rules in case the BP Attachments directory page's slug has been updated.
 *
 * Doing so makes sure these rules are regenerated during next front-end page load.
 *
 * @since 1.0.0
 *
 * @param integer $post_id     The post ID.
 * @param WP_Post $post_after  The updated version of the post object.
 * @param WP_Post $post_before The previous version of the post object.
 */
function bp_attachments_admin_bp_page_updated_clean_rewrite_rules( $post_id, $post_after, $post_before ) {
	if ( ! isset( $post_after->post_name ) || ! isset( $post_before->post_name ) ) {
		return;
	}

	$directory_page_id = bp_core_get_directory_page_id( 'attachments' );

	// There's a directory page and the user changed its slug.
	if ( $directory_page_id && (int) $directory_page_id === (int) $post_id && $post_after->post_name !== $post_before->post_name ) {
		delete_option( 'rewrite_rules' );
	}
}
add_action( 'post_updated', 'bp_attachments_admin_bp_page_updated_clean_rewrite_rules', 10, 3 );

/**
 * Sets a default directory page title for the Community Media directory.
 *
 * @since 1.0.0
 *
 * @param array $titles The BP Components default directory page titles.
 * @return array The BP Components default directory page titles.
 */
function bp_attachments_tracking_set_directory_page_default_title( $titles = array() ) {
	return array_merge(
		$titles,
		array(
			'attachments' => _x( 'Community Media', 'Page title for the Community Media directory.', 'bp-attachments' ),
		)
	);
}
add_filter( 'bp_core_get_directory_page_default_titles', 'bp_attachments_tracking_set_directory_page_default_title' );

/**
 * Creates the Community Media directory page on plugin install.
 *
 * @since 1.0.0
 */
function bp_attachments_tracking_install() {
	if ( function_exists( 'bp_core_get_query_parser' ) ) {
		return;
	}

	bp_core_add_page_mappings(
		array(
			'attachments' => 1,
		)
	);

	$directory_page_id = bp_core_get_directory_page_id( 'attachments' );

	if ( $directory_page_id ) {
		$bp = buddypress();

		// At this stage the Attachments directory page is not added to the BP Global.
		if ( ! isset( $bp->pages->attachments ) ) {
			$bp->pages->attachments     = new stdClass();
			$bp->pages->attachments->id = (int) $directory_page_id;
		}

		delete_option( 'rewrite_rules' );
	}
}
add_action( 'bp_attachments_install', 'bp_attachments_tracking_install' );
