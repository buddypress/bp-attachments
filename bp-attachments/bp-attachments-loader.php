<?php
/**
 * BP Attachments Component.
 *
 * A media component, for others !
 *
 * @package BP Attachments
 * @subpackage Component
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Main Attachments Class.
 *
 * @since BP Attachments (1.0.0)
 */
class BP_Attachments_Component extends BP_Component {

	public $component_terms;
	public $attachments_count;
	public $attachments_link;

	/**
	 * Start the attachments component setup process.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function __construct() {
		parent::start(
			'attachments',
			__( 'Attachments', 'bp-attachements' ),
			bp_attachments_loader()->plugin_dir
		);

		$this->actions();
	}

	/**
	 * Include component files.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function includes( $includes = array() ) {
		// Files to include
		$includes = array(
			'actions',
			'screens',
			'filters',
			'ajax',
			'classes',
			'template',
			'functions',
			'parts'
		);

		if ( bp_is_active( 'groups' ) ) {
			$includes[] = 'groups';
		}

		if ( is_admin() ) {
			$includes[] = 'admin';
		}

		parent::includes( $includes );
	}

	/**
	 * Set up component global variables.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function setup_globals( $args = array() ) {
		$bp = buddypress();

		// Define a slug, if necessary
		if ( ! defined( 'BP_ATTACHMENTS_SLUG' ) )
			define( 'BP_ATTACHMENTS_SLUG', $this->id );

		// All globals for attachments component.
		$args = array(
			'slug'                  => BP_ATTACHMENTS_SLUG,
			'has_directory'         => false,
			'notification_callback' => 'bp_attachements_format_notifications',
		);

		parent::setup_globals( $args );
	}

	/**
	 * Run specific actions to create post type/taxonomy...
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function actions() {

		// register upload dir
		add_action( 'bp_' . $this->id . '_setup_globals', array( $this, 'register_upload_dir' ), 10 );

		// This should be done by component themselves.. Forcing here for tests reason
		add_action( 'bp_groups_setup_globals', array( $this, 'register_groups' ), 10 );

		/**
		 * Maybe this should be done earlier like direclty bailing at start method
		 * Using post types for BP Attachment, might be a trouble if a child blog
		 * wants to use this component.. (switch_to_blog() ?)
		 */ 
		if ( get_current_blog_id() == bp_get_root_blog_id() ) {
			// register bp_attachments post type
			add_action( 'bp_init', array( $this, 'register_post_types' ) );
			// register bp_component taxonomy
			add_action( 'bp_init', array( $this, 'register_taxonomies' ) );
		}

		add_action( 'bp_' . $this->id . '_register_taxonomies', array( $this, 'term_nav' ), 10 );

		add_action( 'bp_screens', array( $this, 'setup_current_term' ), 10 );

		// Capabilities
		add_filter( 'map_meta_cap', 'bp_attachments_map_meta_caps', 10, 4 );
	}

	/**
	 * Set upload dirs
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function register_upload_dir() {
		$upload_data = bp_attachments_set_upload_dir();
		
		if( ! empty( $upload_data ) && is_array( $upload_data ) ) {
			foreach ( $upload_data as $key => $data ) {
				// adding the uploads dir and url to Attachments component global.
				buddypress()->{$this->id}->{$key} = $data;
			}
		}
	}

	/**
	 * Force the group component to support attachment
	 * 
	 * This should be done in Groups Component loader..
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function register_groups() {
		$bp = buddypress();

		if ( ! bp_is_active( 'groups' ) )
			return;

		if ( empty( $bp->groups->can_attachments ) )
			$bp->groups->can_attachments = true;
	}

	/**
	 * Set up component navigation.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		$user_id = bp_displayed_user_id();

		if ( empty( $user_id ) ) {
			$user_id = bp_loggedin_user_id();
		}

		// Users attchments count
		$this->attachments_count = BP_Attachments::count( array( 'user_id' => $user_id ) );
		$class    = ( 0 === absint( $this->attachments_count['total'] ) ) ? 'no-count' : 'count';

		$main_nav = array(
			'name'                => sprintf( __( 'Attachments <span class="%s">%s</span>', 'bp-attachments' ), esc_attr( $class ), number_format_i18n( $this->attachments_count['total'] ) ),
			'slug'                => $this->slug,
			'position'            => 80,
			'screen_function'     => 'bp_attachments_screen_my_attachments',
			'default_subnav_slug' => 'my-attachments',
			'item_css_id'         => $this->id
		);

		// Stop if there is no user displayed or logged in
		if ( ! is_user_logged_in() && ! bp_displayed_user_id() )
			return;

		// Determine user to use
		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			return;
		}

		// User link
		$this->attachments_link = trailingslashit( $user_domain . $this->slug );

		// Add the subnav items to the attachments nav item if we are using a theme that supports this
		$sub_nav[] = array(
			'name'            => sprintf( __( 'All <span class="%s">%s</span>', 'bp-attachments' ), esc_attr( $class ), number_format_i18n( $this->attachments_count['total'] ) ),
			'slug'            => 'my-attachments',
			'parent_url'      => $this->attachments_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'bp_attachments_screen_my_attachments',
			'position'        => 10
		);

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up the component entries in the WordPress Admin Bar.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		$bp = buddypress();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$user_domain = bp_loggedin_user_domain();
			$attachments_link = trailingslashit( $user_domain . $this->slug );

			// Add the "My Account" sub menus
			$wp_admin_nav[] = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Attachments', 'bp-attachments' ),
				'href'   => $attachments_link
			);

			// My Attachments
			$wp_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-all',
				'title'  => __( 'All Attachments', 'bp-attachments' ),
				'href'   => $attachments_link
			);

			// Add submenus for components that support attachments (terms)
			if ( ! empty( $this->component_terms ) ) {
				foreach( $this->component_terms as $component ) {
					$wp_admin_nav[] = array(
						'parent' => 'my-account-' . $this->id,
						'id'     => 'my-account-' . $this->id . '-' .$component->slug,
						'title'  => $component->name,
						'href'   => trailingslashit( $attachments_link . $component->slug )
					);
				}
			}
		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Register the Attachment post type
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function register_post_types() {

		$bp_attachments_labels = array(
			'name'	             => __( 'BuddyPress Attachments',                                             'bp-attachments' ),
			'singular'           => _x( 'Attachment',                    'bp-attachments singular',           'bp-attachments' ),
			'menu_name'          => _x( 'Attachments',                   'bp-attachments menu name',          'bp-attachments' ),
			'all_items'          => _x( 'All Attachments',               'bp-attachments all items',          'bp-attachments' ),
			'singular_name'      => _x( 'Attachment',                    'bp-attachments singular name',      'bp-attachments' ),
			'add_new'            => _x( 'Add New Attachment',            'bp-attachments add new',            'bp-attachments' ),
			'edit_item'          => _x( 'Edit Attachment',               'bp-attachments edit item',          'bp-attachments' ),
			'new_item'           => _x( 'New Attachment',                'bp-attachments new item',           'bp-attachments' ),
			'view_item'          => _x( 'View Attachment',               'bp-attachments view item',          'bp-attachments' ),
			'search_items'       => _x( 'Search Attachments',            'bp-attachments search items',       'bp-attachments' ),
			'not_found'          => _x( 'No Attachments Found',          'bp-attachments not found',          'bp-attachments' ),
			'not_found_in_trash' => _x( 'No Attachments Found in Trash', 'bp-attachments not found in trash', 'bp-attachments' )
		);
		
		$bp_attachments_args = array(
			'label'	            => _x( 'Attachments',                    'bp-attachments label',              'bp-attachments' ),
			'labels'            => $bp_attachments_labels,
			'public'            => false,
			'rewrite'           => false,
			'show_ui'           => false, // As BP Attachments can be activated on the network, we need to use a specific UI
			'show_in_admin_bar' => false,
			'show_in_nav_menus' => false,
			'capabilities'      => bp_attachments_get_attachment_caps(),
			'capability_type'   => array( 'bp_attachment', 'bp_attachments' ),
			'delete_with_user'  => true,
			'supports'          => array( 'title', 'author' )
		);

		// Register the post type for attachements.
		register_post_type( 'bp_attachment', $bp_attachments_args );

		parent::register_post_types();
	}

	/**
	 * Register the Component taxonomy
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function register_taxonomies() {
		$labels = array(
			'name'              => _x( 'BuddyPress Components', 'bp-attachments taxonomy general name',  'bp-attachments' ),
			'singular_name'     => _x( 'Component',             'bp-attachments taxonomy singular name', 'bp-attachments' ),
			'search_items'      => _x( 'Search Components',     'bp-attachments search items',           'bp-attachments' ),
			'all_items'         => _x( 'All Components',        'bp-attachments all items',              'bp-attachments' ),
			'parent_item'       => _x( 'Parent Component',      'bp-attachments parent item',            'bp-attachments' ),
			'parent_item_colon' => _x( 'Parent Component:',     'bp-attachments parent item colon',      'bp-attachments' ),
			'edit_item'         => _x( 'Edit Component',        'bp-attachments edit item',              'bp-attachments' ),
			'update_item'       => _x( 'Update Component',      'bp-attachments update item',            'bp-attachments' ),
			'add_new_item'      => _x( 'Add New Component',     'bp-attachments add new item',           'bp-attachments' ),
			'new_item_name'     => _x( 'New Component Name',    'bp-attachments new item name',          'bp-attachments' ),
			'menu_name'         => _x( 'BP Component',          'bp-attachments menu name',              'bp-attachments' ),
		);

		$args = array(
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => false,
			'rewrite'               => false,
			'capabilities'          => bp_attachments_get_component_caps(),
			'update_count_callback' => '_update_generic_term_count',
		);

		// Register the taxonomy for attachements.
		register_taxonomy( 'bp_component', array( 'bp_attachment' ), $args );

		parent::register_taxonomies();
	}

	/**
	 * Add term nav to component subnav & to component Admin bar menu
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function term_nav() {
		$this->component_terms = get_terms( 'bp_component', array( 'hide_empty' => 1, 'fields' => 'all' ) );

		$position = 10;

		foreach( $this->component_terms as $component ) {
			$position += 10;

			$count = 0;
			if ( ! empty( $this->attachments_count[ $component->term_id ]['total'] ) ) {
				$count = absint( $this->attachments_count[ $component->term_id ]['total'] );
			}
			$class    = ( $count ) ? 'no-count' : 'count';

			bp_core_new_subnav_item( array(
				'name' 		      => sprintf( '%s <span class="%s">%s</span>', $component->name, esc_attr( $class ), number_format_i18n( $count ) ),
				'slug' 		      => $component->slug,
				'parent_slug'     => $this->slug,
				'parent_url' 	  => $this->attachments_link,
				'screen_function' => 'bp_attachments_screen_my_attachments',
				'position' 	      => $position,
			) );

		}
	}

	/**
	 * Define the current attachment action in user's screen
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function setup_current_term() {
		$bp = buddypress();

		$action = 'my-attachments' != bp_current_action() ? bp_current_action() : false;

		$bp->{$this->id}->current_component = $action;
	}
}

/**
 * Bootstrap the Attachments component.
 */
function bp_setup_attachments() {
	buddypress()->attachments = new BP_Attachments_Component();
}
add_action( 'bp_setup_components', 'bp_setup_attachments', 6 );
