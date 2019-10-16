<?php
/**
 * BP Attachments Component.
 *
 * @package BP Attachments
 * @subpackage \bp-attachments\classes\class-bp-attachments-component
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * BP Attachments Component Class.
 *
 * @since 1.0.0
 */
class BP_Attachments_Component extends BP_Component {
	/**
	 * Construct the BP Attachments component.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::start(
			'attachments',
			__( 'Attachments', 'bp-attachments' ),
			plugin_dir_path( dirname( __FILE__ ) )
		);
	}

	/**
	 * Set-up the BP Attachments hooks.
	 *
	 * @since 1.0.0
	 */
	public function setup_actions() {
		parent::setup_actions();

		// src/buddypress does not include the BP REST API.
		if ( ! has_action( 'bp_rest_api_init', array( $this, 'rest_api_init' ), 10 ) ) {
			add_action( 'bp_rest_api_init', array( $this, 'rest_api_init' ), 10 );
		}
	}

	/**
	 * Include component files.
	 *
	 * @see BP_Component::includes() for a description of arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array $includes See BP_Component::includes() for a description.
	 */
	public function includes( $includes = array() ) {
		// Files to include.
		$includes = array(
			'functions',
		);

		if ( is_admin() ) {
			$includes[] = 'admin';
		}

		parent::includes( $includes );
	}

	/**
	 * Late includes method.
	 *
	 * Only load up certain code when on specific pages.
	 *
	 * @since 1.0.0
	 */
	public function late_includes() {
		// Bail if PHPUnit is running.
		if ( defined( 'BP_TESTS_DIR' ) ) {
			return;
		}

		/*
		 * Load attachments action and screen code if PHPUnit isn't running.
		 *
		 * For PHPUnit, we load these files in tests/phpunit/includes/install.php.
		 */
		if ( bp_is_current_component( 'attachments' ) ) {

			// Screens - User main nav.
			if ( bp_is_user() ) {
				require $this->path . 'screens/personal.php';
			}
		}
	}

	/**
	 * Set up component global variables.
	 *
	 * @see BP_Component::setup_globals() for a description of arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See BP_Component::setup_globals() for a description.
	 */
	public function setup_globals( $args = array() ) {
		$bp = buddypress();

		// Globals for BuddyPress components.
		$args = array(
			'slug'                  => $this->id,
			'has_directory'         => false,
			'notification_callback' => 'bp_attachements_format_notifications',
		);

		parent::setup_globals( $args );

		/**
		 * Globals specific to this component.
		 */

		// Current version.
		$this->version = '1.0.0-alpha';

		// Paths.
		$this->templates_dir = trailingslashit( plugin_dir_path( $this->path ) ) . 'templates';

		// URLs.
		$this->templates_url = plugins_url( 'templates/', $this->path );
		$this->js_url        = plugins_url( 'bp-attachments/js/dist/', $this->path );
		$this->assets_url    = plugins_url( 'bp-attachments/assets/', $this->path );
	}

	/**
	 * Set up component navigation.
	 *
	 * @since 1.0.0
	 *
	 * @see BP_Component::setup_nav() for a description of arguments.
	 *
	 * @param array $main_nav Optional. See BP_Component::setup_nav() for
	 *                        description.
	 * @param array $sub_nav  Optional. See BP_Component::setup_nav() for
	 *                        description.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
		// Main User navigation.
		$main_nav = array(
			'name'                => __( 'Media', 'bp-attachments' ),
			'slug'                => $this->slug,
			'position'            => 80,
			'screen_function'     => 'bp_attachments_personal_screen',
			'default_subnav_slug' => 'personal',
			'item_css_id'         => $this->id,
		);

		// Determine the user domain to use.
		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			return;
		}

		// User link.
		$this->attachments_link = trailingslashit( $user_domain . $this->slug );

		// Add the subnav items to the attachments nav item if we are using a theme that supports this.
		$sub_nav['default'] = wp_parse_args(
			array(
				'name'        => __( 'Personal', 'bp-attachments' ),
				'slug'        => 'personal',
				'parent_url'  => $this->attachments_link,
				'parent_slug' => $this->slug,
				'position'    => 10,
				'item_css_id' => 'personal-' . $this->id,
			),
			$main_nav
		);

		unset( $sub_nav['default']['default_subnav_slug'] );

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up the component entries in the WordPress Admin Bar.
	 *
	 * @since 1.0.0
	 *
	 * @see BP_Component::setup_admin_bar() for a description of arguments.
	 *
	 * @param array $wp_admin_nav See BP_Component::setup_admin_bar()
	 *                            for description.
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		$bp = buddypress();

		// Menus for logged in user.
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables.
			$user_domain      = bp_loggedin_user_domain();
			$attachments_link = trailingslashit( $user_domain . $this->slug );

			$default_admin_nav = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Media', 'bp-attachments' ),
				'href'   => $attachments_link,
			);

			// Add the "My Account" sub menus.
			$wp_admin_nav[] = $default_admin_nav;

			// My Attachments.
			$wp_admin_nav[] = wp_parse_args(
				array(
					'parent' => 'my-account-' . $this->id,
					'id'     => 'my-account-' . $this->id . '-personal',
					'title'  => __( 'Personal', 'bp-attachments' ),
				),
				$default_admin_nav
			);
		}

		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Set up the title for pages and <title>.
	 *
	 * @since 1.0.0
	 */
	public function setup_title() {

		// Set up the component options navigation for Site.
		if ( bp_is_current_component( 'attachments' ) ) {
			$bp = buddypress();

			if ( bp_is_my_profile() ) {
				$bp->bp_options_title = __( 'My Sites', 'buddypress' );

				/**
				 * If we are not viewing the logged in user, set up the current
				 * users avatar and name.
				 */
			} else {
				$bp->bp_options_avatar = bp_core_fetch_avatar(
					array(
						'item_id' => bp_displayed_user_id(),
						'type'    => 'thumb',
						'alt'     => sprintf(
							/* translators: %s is the Displayed User full name */
							__( 'Profile picture of %s', 'bp-attachments' ),
							bp_get_displayed_user_fullname()
						),
					)
				);
				$bp->bp_options_title  = bp_get_displayed_user_fullname();
			}
		}

		parent::setup_title();
	}

	/**
	 * Register the BP Attachments REST Controller.
	 *
	 * @since 1.0.0
	 *
	 * @param array $controllers Optional. See BP_Component::rest_api_init() for
	 *                           description.
	 */
	public function rest_api_init( $controllers = array() ) {
		parent::rest_api_init( array( 'BP_Attachments_REST_Controller' ) );
	}
}
