<?php
/**
 * BP Attachments Classes.
 *
 * A media component, for users, groups, and soon as many component as it can!
 *
 * @package Attachments
 * @subpackage Classes
 */

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

/**
 * Attachments Browser Class.
 *
 * This class is used to create the
 * "BP Media Editor" for attachments
 * or Avatars
 *
 * @since BP Attachments (1.0.0)
 */
class BP_Attachments_Browser {

	private static $settings = array();

	private function __construct() {}

	/**
	 * Set the BP Media Editor settings
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function set( $browser_id, $settings ) {
		$set = bp_parse_args( $settings,  array(
			'item_id'         => 0,                                     // what is the item id ?
			'component'       => 'members',                             // what is the component groups/xprofile/members/messages ?
			'item_type'       => 'avatar',                              // avatar / image custom..
			'post_id'         => 0,
			'btn_caption'     => __( 'Edit Avatar', 'bp-attachments' ), // the button caption
			'btn_class'       => 'bp_avatar',                           // the button class
			'file_data_name'  => 'bp_attachment_file',                  // the name of the $_FILE to upload
			'multi_selection' => false,                                 // allow multiple file upload ?
			'action'          => 'bp_attachments_upload',               // the ajax action to deal with the file
			'callback'        => false,
			'callback_id'     => false,
		), 'attachments_browser_args' );

		self::$settings = array_merge( $set, array( 'bp_attachments_button_id' => '#' . $browser_id ) );
		return $set;
	}

	/**
	 * Display the button to launch the BP Media Editor
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function browser( $browser_id, $settings = array() ) {
		$set = self::set( $browser_id, $settings );

		$args = false;
		if ( ! empty( $set['component'] ) && ! empty( $set['item_id'] ) )
			$args = wp_array_slice_assoc( $set, array( 'component', 'item_id' ) );

		if ( bp_attachments_current_user_can( 'publish_bp_attachments', $args ) ) {
			// Need some extra attribute to the wrapper
			add_filter( 'bp_button_attachments_create-' . $set['component'] . '-' . $set['item_type'], array( __CLASS__, 'btn_extra_attribute' ), 10, 4 );

			bp_button( array( 
				'id'                => 'create-' . $set['component'] . '-' . $set['item_type'], 
				'component'         => 'attachments',
				'must_be_logged_in' => true,
				'block_self'        => false,
				'wrapper_id'        => $browser_id,
				'wrapper_class'     => $set['btn_class'],
				'link_class'        => 'add-' .  $set['item_type'], 
				'link_href'         => '#', 
				'link_title'        => $set['btn_caption'], 
				'link_text'         => $set['btn_caption']
			) );

			remove_filter( 'bp_button_attachments_create-' . $set['component'] . '-' . $set['item_type'], array( __CLASS__, 'btn_extra_attribute' ), 10, 4 );

			// do not forget the fallback form if js is disabled or bp-media-editor out of service..
			if ( ! is_admin() ) {
				add_action( 'bp_attachments_uploader_fallback', array( __CLASS__, 'fallback_uploader' ), 10 );
			}
		}
		
		self::browser_settings( $browser_id );
	}

	/**
	 * This filter temporarly hide the button
	 *
	 * If no problem avoided the BP Media Editor to load
	 * then the javascript media-editor.js will show the button
	 * and remove the fallback uploader
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function btn_extra_attribute( $contents = '', $button = null, $before = '', $after ='' ) {
		$before = substr( $before, 0, -1 );
		$contents = str_replace( $before, $before .' style="display:none"', $contents );

		return $contents;
	}

	/**
	 * Fallback uploader when no-js
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function fallback_uploader() {
		$settings = self::$settings;
		?>
		<form action="" method="post" id="attachment-upload-form" class="standard-form" enctype="multipart/form-data">
			<p id="attachment-upload">
				<input type="file" name="<?php echo esc_attr( $settings['file_data_name'] );?>" id="file" />
				<input type="submit" name="bp_attachment_upload" id="upload" value="<?php esc_attr_e( 'Upload', 'bp-attachments' ); ?>" />

				<?php if ( 'avatar' == $settings['item_type'] ) :?>
					<input type="hidden" name="item_type" id="item-type" value="<?php echo esc_attr( $settings['item_type'] );?>" />
				<?php endif ;?>

				<?php if ( ! empty( $settings['component'] ) ) :?>
					<input type="hidden" name="component" id="component" value="<?php echo esc_attr( $settings['component'] );?>" />
				<?php endif ;?>

				<?php if ( ! empty( $settings['item_id'] ) ) :?>
					<input type="hidden" name="item_id" id="item-id" value="<?php echo esc_attr( $settings['item_id'] );?>" />
				<?php endif ;?>

				<?php if ( ! empty( $settings['post_id'] ) ) :?>
					<input type="hidden" name="post_id" id="post-id" value="<?php echo esc_attr( $settings['post_id'] );?>" />
				<?php endif ;?>
				<input type="hidden" name="action" id="action" value="<?php echo esc_attr( $settings['action'] );?>" />
				<input type="hidden" name="file_data" id="file-data" value="<?php echo esc_attr( $settings['file_data_name'] );?>" />
				<?php wp_nonce_field( 'bp_attachments_upload', 'bp_attachments_upload_nonce' ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Filter the Media Editor strings, settings & params
	 * and enqueue the needed scripts
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function browser_settings( $browser_id ) {
		$settings = self::$settings;

		$args = array();
		// Need to attach to a custom post types, let's use WordPress built in attachment features
		if ( ! empty( self::$settings['post_id'] ) ) {
			$args = array( 'post' => absint( self::$settings['post_id'] ) );
		}

		// media view filters
		add_filter( 'media_view_strings', array( __CLASS__, 'media_view_strings' ), 10, 1 );
		add_filter( 'media_view_settings', array( __CLASS__, 'media_view_settings' ), 10, 1 );

		// Plupload filters
		add_filter( 'plupload_default_settings', array( __CLASS__, 'plupload_settings' ), 10, 1 );
		add_filter( 'plupload_default_params',   array( __CLASS__, 'plupload_params' ), 10, 1 );

		// jcrop in case of avatar
		if ( 'avatar' == self::$settings['item_type'] ) {
			wp_enqueue_style( 'jcrop' );
			wp_enqueue_script( 'jcrop', array( 'jquery' ) );
		}

		// time to enqueue scripts
		wp_enqueue_media( $args );
		wp_enqueue_script( 'bp-media-editor', bp_attachments_loader()->plugin_js . 'media-editor.js', array( 'media-editor' ), bp_attachments_loader()->version, true );
	}

	/**
	 * Custom strings for the BP Media Editor
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function media_view_strings( $strings ) {
		$bp_attachments_strings = array( 'bp_attachments' => array() );

		if ( 'avatar' == self::$settings['item_type'] ) {
			$title = 'groups' == self::$settings['component'] ? __( 'Group Avatar', 'bp-attachments' ) : __( 'Avatar', 'bp-attachments' );
			$bp_attachments_strings = array( 'bp_attachments' => array(
				'title'     => $title,
				'uploadtab' => __( 'Upload Avatar', 'bp-attachments' ),
				'croptab'   => __( 'Crop Avatar', 'bp-attachments' ),
			) );
		}

		if ( 'attachment' == self::$settings['item_type'] ) {
			$title = 'groups' == self::$settings['component'] ? __( 'Group Attachments', 'bp-attachments' ) : __( 'Attachments', 'bp-attachments' );
			$bp_attachments_strings = array( 'bp_attachments' => array(
				'title'       => $title,
				'uploadtab'   => __( 'Upload Attachments', 'bp-attachments' ),
				'managetab'   => __( 'Manage Attachments', 'bp-attachments' ),
				'diapoTitle'  => __( 'Diaporama', 'bp-attachments' ),
				'nextCaption' => __( 'Next >', 'bp-attachments' ),
				'prevCaption' => __( '< Prev', 'bp-attachments' ),
			) );
		}

		if ( ! self::$settings['multi_selection'] ) {
			$bp_attachments_strings['bp_attachments'] = array_merge(
				$bp_attachments_strings['bp_attachments'],
				array( 'files_error' => __( 'One file at a time', 'bp-attachments' ) )
			);
		}
		
		$bp_attachments_strings['bp_attachments'] = apply_filters( 'bp_attachments_strings', $bp_attachments_strings['bp_attachments'], self::$settings['bp_attachments_button_id'] );
		$bp_attachments_strings['bp_attachments'] = array_filter( $bp_attachments_strings['bp_attachments'] );

		$strings = array_merge( $strings, $bp_attachments_strings );

		return $strings;
	}

	/**
	 * Custom settings for the BP Media Editor
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function media_view_settings( $settings ) {
		$component = self::$settings['component'];

		$settings['bp_attachments'] = array( 
			'item_id'   => self::$settings['item_id'], 
			'item_type' => $component,
			'button_id' => self::$settings['bp_attachments_button_id'], 
		);

		if ( 'avatar' == self::$settings['item_type'] ) {
			$settings['bp_attachments'] = array_merge( 
				$settings['bp_attachments'],
				array(
					'full_h'     => bp_core_avatar_full_height(),
					'full_w'     => bp_core_avatar_full_width(),
					'object'     => 'groups' == $component ? 'group' : 'user',
					'avatar_dir' => 'groups' == $component ? 'group-avatars' : 'avatars',
				)
			);
		}

		return $settings;
	}

	/**
	 * Custom settings for plupload
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function plupload_settings( $settings ) {
		$settings['url'] = admin_url( 'admin-ajax.php' );
		$settings['file_data_name'] = self::$settings['file_data_name'];
		$settings['multi_selection'] = self::$settings['multi_selection'];

		return $settings;
	}

	/**
	 * Custom params for plupload
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function plupload_params( $params ) {
		$params = array(
			'action'      => self::$settings['action'],
			'item_id'     => self::$settings['item_id'], 
			'component'   => self::$settings['component'],
			'item_type'   => self::$settings['item_type'],
			'post_id'     => self::$settings['post_id'],
			'_bpnonce'    => wp_create_nonce( 'bp_attachments_' . self::$settings['item_type'] ),
			'callback'    => self::$settings['callback'],
			'callback_id' => self::$settings['callback_id'],
		);

		return $params;
	}
}

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
		foreach ( get_intermediate_image_sizes() as $size ) {
			if ( $intermediate = image_get_intermediate_size( $attachment_id, $size ) )
				$intermediate_sizes[] = $intermediate;
		}

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

/**
 * Attachments Capability Class.
 *
 * This class is used to create the
 * arguments to check in the capability
 * mapping filter
 *
 * @since BP Attachments (1.0.0)
 */
class BP_Attachments_Can {
	public function __construct( $args = array() ) {
		if ( empty( $args ) )
			return false;

		$r = bp_parse_args( $args, array(
			'component'     => '',
			'item_id'       => '',
			'attachment_id' => ''
		), 'attachments_can_args' );

		foreach( $r as $key => $value ) {
			$this->{$key} = $value;
		}
	}
}
