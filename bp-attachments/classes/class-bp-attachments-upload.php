<?php
/**
 * Attachment Upload class.
 *
 * @package BP Attachments
 * @subpackage Classes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Attachments Upload Class.
 *
 * This class is used to handle upload
 * for components that are using  the
 * "bp_attachment"s post type
 *
 * @since BP Attachments (1.0.0)
 */
class BP_Attachments_Upload {

	protected static $instance = null;

	/**
	 * Construct the uploader.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function __construct( $args = array() ) {
		$this->setup_globals( $args );
		$this->includes();
		$this->setup_actions();
		$this->upload();
		$this->reset_actions();
	}

	/**
	 * Starting point.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function start( $args = array() ) {
		if ( empty( $args['action'] ) || empty( $args['file_id'] ) )
			return;

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self( $args );
		}

		return self::$instance;
	}

	/**
	 * Define globals.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function setup_globals( $args = array() ) {

		$this->attachment_id = 0;

		$r = bp_parse_args( $args,  array(
			'item_id'         => 0,                       // what is the item id ?
			'component'       => '',                      // what is the component groups/members/messages ?
			'item_type'       => 'attachment',            // attachment / avatar
			'action'          => 'bp_attachments_upload', // are specific strings needed ?
			'file_id'         => 'bp_attachment_file',    // the name of the $_FILE to upload
		), 'attachments_uploader_args' );

		foreach( $r as $key => $value ) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Include needed files.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	private function includes() {
		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		require_once( ABSPATH . '/wp-admin/includes/media.php' );
		require_once( ABSPATH . '/wp-admin/includes/image.php' );
	}

	/**
	 * Actions and filters to run
	 * before media_handle_upload() function is fired
	 *
	 * @since BP Attachments (1.0.0)
	 */
	private function setup_actions() {
		// Filters
		add_filter( 'upload_dir',                array( $this, 'upload_dir' ),          10, 1 );
		add_filter( '_wp_relative_upload_path',  array( $this, 'relative_path' ),       10, 2 );
		add_filter( 'wp_insert_attachment_data', array( $this, 'attachment_data' ),     10, 2 );
		// Actions
		add_action( 'add_attachment',            array( $this, 'attachment_metadata' ), 10, 1 );
	}

	/**
	 * Upload!
	 *
	 * @since BP Attachments (1.0.0)
	 */
	private function upload() {
		$attachment_data = array(
			'post_type' => 'bp_attachment',
			'context'   => 'buddypress',
		);

		// components are terms, an attachment can be attached to more than one component
		if ( ! empty( $this->component ) ) {
			$term = get_term_by( 'slug', $this->component, 'bp_component' );

			if ( ! empty( $term ) ) {
				$attachment_data['tax_input'] = array( 'bp_component' => array( $term->term_id ) );
			}
		}

		$this->attachment_id = media_handle_upload( $this->file_id, 0, $attachment_data, array( 'action' => $this->action ) );
	}

	/**
	 * Actions and filters to remove
	 * once media_handle_upload() function has done
	 *
	 * @since BP Attachments (1.0.0)
	 */
	private function reset_actions() {
		// filters
		remove_filter( 'upload_dir',                array( $this, 'upload_dir' ),      10, 1 );
		remove_filter( '_wp_relative_upload_path',  array( $this, 'relative_path' ),   10, 2 );
		remove_filter( 'wp_insert_attachment_data', array( $this, 'attachment_data' ), 10, 2 );
		// actions
		remove_action( 'add_attachment',            array( $this, 'attachment_metadata' ), 10, 1 );
	}

	/**
	 * Filter upload dir
	 *
	 * @since BP Attachments (1.0.0)
	 *
	 * @todo handle private files using the post status
	 * of the "bp_attachment" post type
	 */
	public function upload_dir( $upload_data = array() ) {
		$bp = buddypress();

		//@todo private files!

		$path = $bp->attachments->publicdir . '/' . bp_loggedin_user_id();

		if ( ! file_exists( $path ) ) {
			mkdir( $path );
		}

		$url = $bp->attachments->publicurl . '/' . bp_loggedin_user_id();

		$r = bp_parse_args( array(
			'path'    => $path,
			'url'     => $url,
			'subdir'  => false,
			'basedir' => $path,
			'baseurl' => $url,
		), $upload_data, 'bp_attachments_upload_dir' );

		return $r;
	}

	/**
	 * Filter relative path
	 *
	 * @since BP Attachments (1.0.0)
	 *
	 * @todo handle private files using the post status
	 * of the "bp_attachment" post type
	 */
	public function relative_path( $new_path = '', $path = '' ) {
		$bp = buddypress();

		//@todo private files!

		if ( empty( $bp->attachments->publicdir ) )
			return $new_path;

		if ( false !== strpos( $path, $bp->attachments->publicdir ) ) {
			$new_path = str_replace( $bp->attachments->basedir, '', $path );
			$new_path = basename( $bp->attachments->basedir ) . $new_path;
		}

		return $new_path;
	}

	/**
	 * Set the post type to bp_attachment
	 * instead of attachmnent
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function attachment_data( $data = array(), $object = array() ) {
		if( 'buddypress' == $object['context'] ) {
			$post_name = sanitize_title( $object['post_title'] );
			$post_name = wp_unique_post_slug( $post_name, 0, $data['post_status'], 'bp_attachment', $data['post_parent'] );
			$data = array_merge( $data, array(
				'post_type' => 'bp_attachment',
				'post_name' => $post_name
			) );
		}

		return $data;
	}

	/**
	 * Finally add post meta to link to a single item id of the component
	 *
	 * for instance a group id. It's possible to add an attachment to more
	 * than one single item of the component
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function attachment_metadata( $attachment_id = 0 ) {
		if ( empty( $attachment_id ) )
			return;

		if ( ! empty( $this->component ) && ! empty( $this->item_id ) ) {
			add_post_meta( $attachment_id, "_bp_{$this->component}_id", $this->item_id );
		}
	}
}