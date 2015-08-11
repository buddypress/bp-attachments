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
}
endif; // class_exists check

// Load the BP Attachments admin
add_action( 'bp_init', array( 'BP_Attachments_Admin','register_attachments_admin' ) );
