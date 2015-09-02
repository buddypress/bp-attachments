<?php
/**
 * BP Attachments Admin.
 *
 * A media component, for others !
 *
 * @package BP Attachments
 * @subpackage Administration
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'BP_Attachment_Admin' ) ) :
/**
 * Load BP Attachments admin area.
 *
 * @package BP Attachments
 * @subpackage Administration
 *
 * @since BP Attachments (1.0.0)
 */
class BP_Attachments_Admin {
	/**
	 * Setup BP Attachments Admin.
	 *
	 * @access public
	 * @since BP Attachments (1.0.0)
	 *
	 * @uses buddypress() to get BuddyPress main instance
	 */
	public static function register_attachments_admin() {
		if( ! is_admin() )
			return;

		$bp = buddypress();

		if( empty( $bp->attachments->admin ) ) {
			$bp->attachments->admin = new self;
		}

		return $bp->attachments->admin;
	}

	/**
	 * Constructor method.
	 *
	 * @access public
	 * @since BP Attachments (1.0.0)
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Set admin-related globals.
	 *
	 * @access private
	 * @since BP Attachments (1.0.0)
	 */
	private function setup_globals() {
		$bp = buddypress();

		// The Attachments Admin screen id
		$this->attachment_screen_id = '';

		// BuddyPress components settings page
		$this->settings_screen_id = 'settings_page_bp-components';
	}

	/**
	 * Set admin-related actions and filters.
	 *
	 * @access private
	 * @since BP Attachments (1.0.0)
	 */
	private function setup_actions() {

		/** Actions ***************************************************/
		add_action( "load-{$this->settings_screen_id}",       array( $this, 'update_terms' )        );
		add_action( "admin_head-{$this->settings_screen_id}", array( $this, 'extra_css'    )        );

		// Add menu item to all users menu
		add_action( bp_core_admin_hook(),                     array( $this, 'admin_menus'  ),  5    );
		add_action( 'bp_admin_init',                          array( $this, 'activate' )            );

		// Moderate activity attachments
		add_action( 'load-toplevel_page_bp-activity',         array( $this, 'activity_admin_load' ),         100    );
		add_action( 'load-toplevel_page_bp-activity-network', array( $this, 'activity_admin_load' ),         100    );
		add_filter( 'bp_activity_admin_comment_row_actions',  array( $this, 'intercept_activity_item' ),      10, 2 );
		add_filter( 'bp_get_activity_content_body',           array( $this, 'append_activity_attachments' ),  10, 1 );


		/** Filters ***************************************************/
		// Respect BuddyPress Component Order
		add_filter( 'bp_admin_menu_order',                    array( $this, 'menu_order'   ), 10, 1 );
	}

	/**
	 * Register "bp_component" terms.
	 *
	 * Depending on the components activated, creates
	 * a term for the component that supports attachments
	 * For example purpose, we're forcing the groups
	 * component to support attachments.
	 * @see BP_Attachments_Component::register_groups()
	 *
	 * @access public
	 * @since BP Attachments (1.0.0)
	 */
	public function update_terms() {
		$bp = buddypress();

		// get existing terms
		$terms = get_terms( 'bp_component', array( 'hide_empty' => 0, 'fields' => 'id=>slug' ) );

		// get active components
		$active_components = $bp->active_components;

		// exclude attachments component
		$active_components = array_diff_key( $active_components, array( 'attachments' => 0 ) );
		$active_components = array_keys( $active_components );

		// Used to set terms name
		$bp_menu_items = bp_nav_menu_get_loggedin_pages();
		$component_terms = array();

		foreach( $active_components as $component  ) {
			$name ='';

			/**
			 * component who whish to use attachments should
			 * set their can_attachments global to true
			 */
			if ( empty( $bp->{$component}->can_attachments ) )
				continue;

			$component = ( 'xprofile' == $component ) ? 'profile' : $component;

			if ( isset( $bp_menu_items[ $component ]->post_title ) ) {
				$name = $bp_menu_items[ $component ]->post_title;
			} else {
				$name = $bp->{$component}->name;
			}

			if ( ! empty( $name ) ) {
				$component_terms[ $component ] = array( 'name' => $name, 'slug' => $bp->{$component}->slug );
			}

		}

		// init the terms to add
		$terms_toadd = array();

		// Create from active components
		if ( empty( $terms ) ) {
			$terms_toadd = $component_terms;

		// Update in case a component has been activated
		} else {
			$terms_created = array_flip( $terms );
			$terms_toadd = array_diff_key( $component_terms, $terms_created );
		}

		if ( empty( $terms_toadd ) )
			return;

		// Using terms makes it possible to attach an attachment to several components
		foreach( $terms_toadd as $component ) {
			wp_insert_term( $component['name'], 'bp_component', array( 'slug' => $component['slug'] ) );
		}
	}

	/**
	 * One css rule in Admin...
	 *
	 * Only loaded if on BuddyPress components settings
	 *
	 * @access public
	 * @since BP Attachments (1.0.0)
	 */
	public function extra_css() {
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/

			/* Dashicon for BP Attachements */
			.settings_page_bp-components tr.attachments input[type='checkbox'] {
				display:none;
			}

		/*]]>*/
		</style>

		<?php
	}

	/**
	 * Create the Attachments Admin screens.
	 *
	 * @access public
	 * @since BP Attachments (1.0.0)
	 */
	public function admin_menus() {
		// Manage Attachments
		$this->attachment_screen_id = add_menu_page(
			__( 'BP Attachments',  'bp-attachments' ),
			__( 'Attachments', 'bp-attachments' ),
			'bp_moderate',
			'bp-attachments',
			array( &$this, 'attachments_admin' ),
			'dashicons-format-image'
		);

		if ( bp_core_do_network_admin() ) {
			$this->attachment_screen_id .= '-network';
		}

		add_action( "load-{$this->attachment_screen_id}", array( $this, 'attachments_admin_load' ) );
	}

	/**
	 * Reorder the attachments menu to be after BuddyPress components.
	 *
	 * @access public
	 * @since BP Attachments (1.0.0)
	 */
	public function menu_order( $custom_menus = array() ) {
		array_push( $custom_menus, 'bp-attachments' );
		return $custom_menus;
	}

	/**
	 * This will be the place where to init the WP_List_Table.
	 *
	 * @access public
	 * @since BP Attachments (1.0.0)
	 */
	public function attachments_admin_load() {}

	/**
	 * Display Attachments Admin screens.
	 *
	 * @access public
	 * @since BP Attachments (1.0.0)
	 */
	public function attachments_admin() {
		?>
		<div class="wrap"  id="attachments-admin">
			<?php screen_icon( 'media' ); ?>
			<h1>
				<?php esc_html_e( 'Attachments', 'bp-attachments' );?>
			</h1>

			<div>Soon..</div>

		</div><!-- .wrap -->
		<?php
	}

	public function activate() {
		$active = buddypress()->active_components;

		if ( empty( $active['attachments'] ) ) {
			$active['attachments'] = 1;
			bp_update_option( 'bp-active-components', $active );
		}
	}

	public function activity_admin_load() {
		if ( ! did_action( 'bp_activity_admin_load' ) ) {
			return;
		}

		/**
		 * in bp-activity-admin.php There should be an action
		 * just after wp_enqueue_style( 'bp_activity_admin_css' )
		 * to let plugins enqueue their style and scripts
		 */

		// Always add extra css
		wp_add_inline_style( 'bp_activity_admin_css', '
				/* Style adjustments */
				.bp-attachments-full {
					max-width: 100%;
					height: auto;
					margin: 0.6em auto;
					display: block;
				}

				.bp-attachments-bp_attachments_avatar {
					margin: 0.6em;
				}
			'
		);

		/**
		 * in bp-activity-admin.php There should be an action
		 * just after add_meta_box( 'bp_activity_userid' )
		 * to let plugins enqueue their custom metabox
		 */

		// Only add metabox when on a single activity
		$doaction = bp_admin_list_table_current_bulk_action();

		if ( 'edit' !== $doaction || empty( $_GET['aid'] ) ) {
			return;
		}

		add_meta_box(
			'bp_attachments',
			_x( 'Photos', 'activity admin edit screen', 'bp-attachments' ),
			array( $this, 'do_activity_meta_box' ),
			get_current_screen()->id,
			'side',
			'core'
		);
	}

	public function do_activity_meta_box( $activity = null ) {
		/**
		 * For now, let's just display the photos, when/if
		 * https://buddypress.trac.wordpress.org/ticket/6610 will be committed
		 * we'll be able to add some awesome moderating tools :)
		 */

		if ( empty( $activity->id ) ) {
			return false;
		}

		// To zoom photos
		add_thickbox();

		// Get the attachment ids
		$attachments = (array) bp_activity_get_meta( $activity->id, '_bp_attachments_attachment_ids', false );

		if ( empty( $attachments ) ) {
			printf( '<p class="description">%s</p>', esc_html__( 'No Photo attached to this activity', 'bp-attachments' ) );
			return;
		}

		$attachments_metadata = array();
		$output               = '<p class="description">' . esc_html__( 'Click on thumbnails to zoom images', 'bp-attachments' ) . '</p>';

		add_filter( 'image_downsize', 'bp_attachments_image_downsize', 10, 3 );

		// Loop threw attachments
		foreach( $attachments as $attachment_id ) {
			// For a later use
			$full  = wp_get_attachment_image_src( $attachment_id, 'full' );
			$url   = reset( $full );
			$thumb = wp_get_attachment_image( $attachment_id, array( 100, 100 ), false, array( 'class' => 'bp-attachments-bp_attachments_avatar' ) );

			$output .= sprintf( '<a href="%1$s" class="thickbox" rel="%2$s">%3$s</a>',
				esc_url( $url ),
				'activity-attachment-' . intval( $activity->id ),
				$thumb
			);

			// Object to let plugins use wp_list_pluck() ;)
			$attachments_metadata[] = (object) array( 'attachment_id' => $attachment_id, 'url' => $url, 'thumb' => $thumb, 'full' => $full );
		}

		remove_filter( 'image_downsize', 'bp_attachments_image_downsize', 10, 3 );

		echo apply_filters( 'bp_attachments_activity_admin_photos_metabox_content', $output, $attachments_metadata );
	}

	/**
	 * No way to get the activity id using the bp_get_activity_content_body
	 * within the administration as the $activities_template is null
	 */
	public function intercept_activity_item( $actions = '', $activity = null ) {
		$this->current_activity = $activity;
		return $actions;
	}

	public function append_activity_attachments( $activity_content = '' ) {
		// Taking no risk!
		$current_screen = get_current_screen();

		if ( empty( $current_screen->id ) || false === strpos( $current_screen->id, 'bp-activity' ) || empty( $this->current_activity ) ) {
			return $activity_content;
		}

		global $activities_template;
		$reset_activities_template = $activities_template;

		// Faking the activities template so that we can use our template function
		$activities_template = new stdClass();
		$activities_template->activity = (object) $this->current_activity;

		// Let's Use the Edit action of the Activity Administration Screen
		add_filter( 'bp_attachments_activity_append_attachments_more_link', '__return_false' );

		$attachments_output = bp_attachments_activity_append_attachments( '', false );

		remove_filter( 'bp_attachments_activity_append_attachments_more_link', '__return_false' );
		$activities_template = $reset_activities_template;

		return $activity_content . $attachments_output;
	}
}
endif; // class_exists check

// Load the BP Attachments admin
add_action( 'bp_init', array( 'BP_Attachments_Admin','register_attachments_admin' ) );
