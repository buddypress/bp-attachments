<?php
/**
 * Attachments CRUD class.
 *
 * @package BP Attachments
 * @subpackage Classes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Attachments "CRUD" Class.
 *
 * Create is handled by the Upload Class
 *
 * @since BP Attachments (1.0.0)
 */
class BP_Attachments {
	public $id;
	public $user_id;
	public $title;
	public $description;
	public $mime_type;
	public $guid;
	public $status;
	public $item_ids;
	public $components;
	public $attachment;

	/**
	 * Constructor.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	function __construct( $id = 0 ){
		if ( ! empty( $id ) ) {
			$this->id = $id;
			$this->populate();
		}
	}

	/**
	 * request an item id
	 *
	 * @uses get_post()
	 * @uses wp_get_object_terms()
	 */
	public function populate() {
		$this->attachment  = get_post( $this->id );
		$this->user_id     = $this->attachment->post_author;
		$this->title       = $this->attachment->post_title;
		$this->description = $this->attachment->post_content;
		$this->mime_type   = $this->attachment->post_mime_type;
		$this->guid        = $this->attachment->guid;
		$this->status      = $this->attachment->post_status;
		$this->components  = wp_get_object_terms( $this->id, 'bp_component', array( 'fields' => 'all' ) );

		if ( ! empty( $this->components ) ) {
			$this->item_ids = new stdClass();
			foreach( (array) $this->components as $component ) {
				$this->item_ids->{$component->slug} = get_post_meta( $this->id, "_bp_{$component->slug}_id" );
			}
		}

	}

	/**
	 * Update an attachment.
	 *
	 * @since BP Attachments (1.0.0)
	 * @todo changing from inherit (public) to private
	 * This will need to move files from one dir to another
	 */
	public function update() {
		$this->id          = apply_filters_ref_array( 'bp_attachments_id_before_update',          array( $this->id,          &$this ) );
		$this->user_id     = apply_filters_ref_array( 'bp_attachments_user_id_before_update',     array( $this->user_id,     &$this ) );
		$this->title       = apply_filters_ref_array( 'bp_attachments_title_before_update',       array( $this->title,       &$this ) );
		$this->description = apply_filters_ref_array( 'bp_attachments_description_before_update', array( $this->description, &$this ) );
		$this->status      = apply_filters_ref_array( 'bp_attachments_status_before_update',      array( $this->status,      &$this ) );
		$this->item_ids    = apply_filters_ref_array( 'bp_attachments_item_ids_before_update',    array( $this->item_ids,    &$this ) );
		$this->components  = apply_filters_ref_array( 'bp_attachments_components_before_update',  array( $this->components,  &$this ) );

		// Use this, not the filters above
		do_action_ref_array( 'bp_attachments_before_update', array( &$this ) );

		if ( ! $this->id || ! $this->user_id || ! $this->title )
			return false;

		if ( ! $this->status )
			$this->status = 'inherit';

		/**
		 * If the status changed, we'll need to move files
		 */

		$update_args = array(
			'ID'		     => $this->id,
			'post_author'	 => $this->user_id,
			'post_title'	 => $this->title,
			'post_content'	 => $this->description,
			'post_type'		 => 'bp_attachment',
			'post_status'	 => $this->status
		);

		$updated = wp_update_post( $update_args );

		$result = false;

		if ( $updated ) {
			$result = $this->title;
		}

		do_action_ref_array( 'bp_attachments_after_update', array( &$this ) );

		return $result;
	}

	/**
	 * The selection query
	 *
	 * @since BP Attachments (1.0.0)
	 * @param array $args arguments to customize the query
	 * @uses bp_parse_args
	 */
	public static function get( $args = array() ) {

		$defaults = array(
			'item_ids'        => array(), // one or more item ids regarding the component (eg group_ids, message_ids )
			'component'	      => false,   // groups / messages / blogs / xprofile...
			'show_private'    => false,   // wether to include private attachment
			'user_id'	      => false,   // the author id of the attachment
			'post_id'         => false,
			'per_page'	      => 20,
			'page'		      => 1,
			'search'          => false,
			'exclude'		  => false,   // comma separated list or array of attachment ids.
			'orderby' 		  => 'modified',
			'order'           => 'DESC',
		);

		$r = bp_parse_args( $args, $defaults, 'attachments_query_args' );
		extract( $r );

		$attachment_status = 'inherit';

		if ( ! empty( $show_private ) )
			$attachment_status = array( 'inherit', 'private' );

		$query_args = array(
			'post_status'	 => $attachment_status,
			'post_type'	     => 'bp_attachment',
			'posts_per_page' => $per_page,
			'paged'		     => $page,
			'orderby' 		 => $orderby,
			'order'          => $order,
		);

		if ( ! empty( $user_id ) )
			$query_args['author'] = $user_id;

		if ( ! empty( $exclude ) ) {
			if ( ! is_array( $exclude ) )
				$exclude = explode( ',', $exclude );

			$query_args['post__not_in'] = $exclude;
		}

		if ( ! empty( $post_id ) ) {
			$query_args['post_parent'] = $post_id;
		}

		if ( ! empty( $component ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'bp_component',
					'field' => 'slug',
					'terms' => $component
				)
			);

			// component is defined, we can zoom on specific ids
			if ( ! empty( $item_ids ) ) {
				// We really want an array!
				$item_ids = (array) $item_ids;

				$query_args['meta_query'] = array(
					array(
						'key'     => "_bp_{$component}_id",
						'value'   => $item_ids,
						'compare' => 'IN',
					)
				);
			}
		}

		$attachments = new WP_Query( $query_args );

		return array( 'attachments' => $attachments->posts, 'total' => $attachments->found_posts );
	}

	/**
	 * Return the number of attachments depending on the context.
	 *
	 * Used in BuddyPress nav (group & user)
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function count( $args = array() ) {
		global $wpdb;

		$r = bp_parse_args( $args, array(
				'status'    => false,
				'term_id'   => false,
				'user_id'   => false,
				'item_id'   => false,
				'term_slug' => false,
			)
			, 'attachments_count_args'
		);

		$sql = array();
		$detailed_count = array( 'total' => 0 );

		$sql['select'] = "SELECT COUNT( p.ID ) as count, p.post_status as status, t.term_taxonomy_id as term_id";
		$sql['from'] = "FROM {$wpdb->posts} p LEFT JOIN {$wpdb->term_relationships} t ON( p.ID = t.object_id )";
		$sql['where'] = array( $wpdb->prepare( "p.post_type = %s", 'bp_attachment' ) );
		$sql['groupby'] = array( 'p.post_status', 't.term_taxonomy_id' );

		if ( ! empty( $r['status'] ) ) {
			$sql['where'][] = $wpdb->prepare( "p.post_status = %s", $r['status'] );
		}

		if ( ! empty( $r['term_id'] ) ) {
			$sql['where'][] = $wpdb->prepare( "t.term_taxonomy_id = %d", $r['term_id'] );
		}

		if ( ! empty( $r['user_id'] ) ) {
			$sql['where'][] = $wpdb->prepare( "p.post_author = %s", $r['user_id'] );
		}

		if ( ! empty( $r['item_id'] ) && ! empty( $r['term_slug'] ) ) {
			$sql['from'] .= " LEFT JOIN {$wpdb->postmeta} m ON ( p.ID = m.post_id )";
			$sql['where'][] = $wpdb->prepare( "m.meta_key = %s AND m.meta_value = %d", '_bp_' . $r['term_slug'] .'_id', $r['item_id'] );
		}

		// join
		$query = $sql['select'] . ' ' . $sql['from'] . ' WHERE ' . join( ' AND ', $sql['where'] ) . ' GROUP BY ' . join( ',', $sql['groupby'] );

		$results = $wpdb->get_results( $query );

		if ( empty( $results ) ) {
			return $detailed_count;
		}

		foreach( $results as $result ) {
			if ( empty( $result->count ) )
				continue;

			$count = absint( $result->count );

			$detailed_count['total'] += $count;

			if ( ! empty( $result->status ) ) {
				// status
				$detailed_count[ $result->status ] = empty( $detailed_count[ $result->status ] ) ? $count : absint( $detailed_count[ $result->status ] ) + $count;

				// terms
				if ( ! empty( $result->term_id ) ) {
					$detailed_count[ $result->term_id ]['total'] = empty( $detailed_count[ $result->term_id ]['total'] ) ? $count : absint( $detailed_count[ $result->term_id ]['total'] ) + $count;
					$detailed_count[ $result->term_id ][ $result->status ] = empty( $detailed_count[ $result->term_id ][ $result->status ] ) ? $count : absint( $detailed_count[ $result->term_id ][ $result->status ] ) + $count;
				}
			}
		}

		return $detailed_count;
	}

	/**
	 * Delete an attachment
	 *
	 * @since BP Attachments (1.0.0)
	 * @see wp_delete_attachment() this is an adapted version..
	 */
	public static function delete( $attachment_id = 0 ) {
		global $wpdb;

		if( empty( $attachment_id ) )
			return false;

		if ( ! $attachment = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d", $attachment_id ) ) )
			return $attachment;

		if ( 'bp_attachment' != $attachment->post_type )
			return false;

		$meta = wp_get_attachment_metadata( $attachment_id );
		$file = get_attached_file( $attachment_id );

		$intermediate_sizes = array();

		add_image_size( 'bp_attachments_avatar', bp_core_avatar_full_width(), bp_core_avatar_full_height(), true );
		add_filter( 'intermediate_image_sizes_advanced', 'bp_attachments_restrict_image_sizes', 10, 1 );

		foreach ( get_intermediate_image_sizes() as $size ) {
			if ( $intermediate = image_get_intermediate_size( $attachment_id, $size ) )
				$intermediate_sizes[] = $intermediate;
		}

		remove_image_size( 'bp_attachments_avatar' );
		remove_filter( 'intermediate_image_sizes_advanced', 'bp_attachments_restrict_image_sizes', 10, 1 );

		if ( is_multisite() )
			delete_transient( 'dirsize_cache' );

		do_action( 'bp_attachments_before_attachment_delete', $attachment_id );

		wp_delete_object_term_relationships( $attachment_id, get_object_taxonomies( $attachment->post_type ) );

		delete_metadata( 'post', null, '_thumbnail_id', $attachment_id, true ); // delete all for any posts.

		$post_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d", $attachment_id ) );
		foreach ( $post_meta_ids as $mid )
			delete_metadata_by_mid( 'post', $mid );

		$result = $wpdb->delete( $wpdb->posts, array( 'ID' => $attachment_id ) );
		if ( ! $result ) {
			return false;
		}

		do_action( 'bp_attachments_after_attachment_db_delete', $attachment_id );

		$uploadpath = wp_upload_dir();

		if ( ! empty( $meta['thumb'] ) ) {
			$thumbfile = str_replace( basename( $file), $meta['thumb'], $file );
			@ unlink( path_join( $uploadpath['basedir'], $thumbfile ) );
		}

		foreach ( $intermediate_sizes as $intermediate ) {
			@ unlink( path_join( $uploadpath['basedir'], $intermediate['path'] ) );
		}

		if ( ! empty( $file ) )
			@ unlink( $file );

		clean_post_cache( $attachment );

		do_action( 'bp_attachments_after_attachment_files_delete', $attachment_id );

		return $attachment;
	}
}
