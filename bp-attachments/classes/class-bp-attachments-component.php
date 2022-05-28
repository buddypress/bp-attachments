<?php
/**
 * BP Attachments Component.
 *
 * @package \bp-attachments\classes\class-bp-attachments-component
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
			'templates',
		);

		if ( is_admin() ) {
			$includes[] = 'admin';
			$includes[] = 'settings';
		}

		$includes[] = 'filters';

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

		// Rewrite args.
		$rewrite_args = array(
			'root_slug'   => 'bp-attachments',
			'rewrite_ids' => array(
				'directory'                    => 'bp_attachments',
				'directory_status'             => 'bp_attachments_status',
				'directory_object'             => 'bp_attachments_object',
				'single_item'                  => 'bp_attachments_object_item',
				'single_item_action'           => 'bp_attachments_item_action',
				'single_item_action_variables' => 'bp_attachments_item_action_variables',
			),
		);

		// Globals for BuddyPress components.
		$args = array(
			'slug'                  => $this->id,
			'has_directory'         => false,
			'notification_callback' => 'bp_attachements_format_notifications',
			'directory_title'       => __( 'User Media', 'bp-attachments' ),
		);

		parent::setup_globals( $args + $rewrite_args );

		/**
		 * Globals specific to this component.
		 */

		// Current version.
		$this->version = '1.0.0-alpha';

		// Paths.
		$this->templates_dir = trailingslashit( plugin_dir_path( $this->path ) ) . 'templates';

		// URLs.
		$this->templates_url = plugins_url( 'templates/', $this->path );
		$this->js_url        = plugins_url( 'bp-attachments/js/', $this->path );
		$this->assets_url    = plugins_url( 'bp-attachments/assets/', $this->path );

		// Rewrites.
		if ( ! isset( $this->rewrite_ids ) ) {
			$this->rewrite_ids           = $rewrite_args['rewrite_ids'];
			$this->directory_permastruct = $this->root_slug . '/%' . $this->rewrite_ids['directory'] . '%';
		}
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
	 * Setup cache groups
	 *
	 * @since 1.0.0
	 */
	public function setup_cache_groups() {
		// Global groups.
		wp_cache_add_global_groups(
			array(
				'bp_attachments',
			)
		);

		parent::setup_cache_groups();
	}

	/**
	 * Add additional rewrite tags.
	 *
	 * @since 1.0.0
	 *
	 * @param array $rewrite_tags {
	 *      Associative array of arguments list used to register WordPress permastructs.
	 *      The main array keys describe the rules type and allow individual edits if needed.
	 *
	 *      @type string $id    The name of the new rewrite tag. Required.
	 *      @type string $regex The regular expression to substitute the tag for in rewrite rules.
	 *                          Required.
	 * }
	 */
	public function add_rewrite_tags( $rewrite_tags = array() ) {
		$rewrite_tags = array(
			'directory'                    => array(
				'id'    => '%' . $this->rewrite_ids['directory'] . '%',
				'regex' => '([1]{1,})',
			),
			'directory-status'             => array(
				'id'    => '%' . $this->rewrite_ids['directory_status'] . '%',
				'regex' => '([^/]+)',
			),
			'directory-object'             => array(
				'id'    => '%' . $this->rewrite_ids['directory_object'] . '%',
				'regex' => '([^/]+)',
			),
			'single-item'                  => array(
				'id'    => '%' . $this->rewrite_ids['single_item'] . '%',
				'regex' => '([^/]+)',
			),
			'single-item-action'           => array(
				'id'    => '%' . $this->rewrite_ids['single_item_action'] . '%',
				'regex' => '([^/]+)',
			),
			'single-item-action-variables' => array(
				'id'    => '%' . $this->rewrite_ids['single_item_action_variables'] . '%',
				'regex' => '([^/]+)',
			),
		);

		foreach ( $rewrite_tags as $rewrite_tag ) {
			if ( ! isset( $rewrite_tag['id'] ) || ! isset( $rewrite_tag['regex'] ) ) {
				continue;
			}

			add_rewrite_tag( $rewrite_tag['id'], $rewrite_tag['regex'] );
		}

		parent::add_rewrite_tags();
	}

	/**
	 * Add additional rewrite rules.
	 *
	 * @since 1.0.0
	 *
	 * @param array $rewrite_rules {
	 *      Associative array of arguments list used to register WordPress permastructs.
	 *      The main array keys describe the rules type and allow individual edits if needed.
	 *
	 *      @type string $regex    Regular expression to match request against. Required.
	 *      @type string $query    The corresponding query vars for this rewrite rule. Required.
	 *      @type string $priority The Priority of the new rule. Accepts 'top' or 'bottom'. Optional.
	 *                             Default 'top'.
	 * }
	 */
	public function add_rewrite_rules( $rewrite_rules = array() ) {
		$rewrite_rules = array(
			'paged-directory'              => array(
				'regex' => $this->root_slug . '/page/?([0-9]{1,})/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&paged=$matches[1]',
			),
			'single-item-action-variables' => array(
				'regex' => $this->root_slug . '/([^/]+)\/([^/]+)\/([^/]+)\/([^/]+)\/(.+?)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_status'] . '=$matches[1]&' . $this->rewrite_ids['directory_object'] . '=$matches[2]&' . $this->rewrite_ids['single_item'] . '=$matches[3]&' . $this->rewrite_ids['single_item_action'] . '=$matches[4]&' . $this->rewrite_ids['single_item_action_variables'] . '=$matches[5]',
			),
			'single-item-action'           => array(
				'regex' => $this->root_slug . '/([^/]+)\/([^/]+)\/([^/]+)\/([^/]+)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_status'] . '=$matches[1]&' . $this->rewrite_ids['directory_object'] . '=$matches[2]&' . $this->rewrite_ids['single_item'] . '=$matches[3]&' . $this->rewrite_ids['single_item_action'] . '=$matches[4]',
			),
			'single-item'                  => array(
				'regex' => $this->root_slug . '/([^/]+)\/([^/]+)\/([^/]+)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_status'] . '=$matches[1]&' . $this->rewrite_ids['directory_object'] . '=$matches[2]&' . $this->rewrite_ids['single_item'] . '=$matches[3]',
			),
			'directory_object'             => array(
				'regex' => $this->root_slug . '/([^/]+)\/([^/]+)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_status'] . '=$matches[1]&' . $this->rewrite_ids['directory_object'] . '=$matches[2]',
			),
			'directory_status'             => array(
				'regex' => $this->root_slug . '/([^/]+)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_status'] . '=$matches[1]',
			),
			'directory'                    => array(
				'regex' => $this->root_slug,
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1',
			),
		);

		$priority = 'top';

		foreach ( $rewrite_rules as $rewrite_rule ) {
			if ( ! isset( $rewrite_rule['regex'] ) || ! isset( $rewrite_rule['query'] ) ) {
				continue;
			}
			if ( ! isset( $rewrite_rule['priority'] ) || ! ! $rewrite_rule['priority'] ) {
				$rewrite_rule['priority'] = $priority;
			}

			add_rewrite_rule( $rewrite_rule['regex'], $rewrite_rule['query'], $rewrite_rule['priority'] );
		}

		parent::add_rewrite_rules();
	}

	/**
	 * Add permalink structures.
	 *
	 * @since 1.0.0
	 *
	 * @param array $structs {
	 *      Associative array of arguments list used to register WordPress permastructs.
	 *      The main array keys hold the name argument of the `add_permastruct()` function.
	 *
	 *      @type string $struct The permalink structure. Required.
	 *      @type array  $args   The permalink structure arguments. Optional
	 * }
	 */
	public function add_permastructs( $structs = array() ) {
		$structs = array(
			// Directory permastruct.
			$this->rewrite_ids['directory'] => array(
				'struct' => $this->directory_permastruct,
				'args'   => array(),
			),
		);

		foreach ( $structs as $name => $params ) {
			if ( ! $name || ! isset( $params['struct'] ) || ! $params['struct'] ) {
				continue;
			}

			if ( ! $params['args'] ) {
				$params['args'] = array();
			}

			$args = wp_parse_args(
				$params['args'],
				array(
					'with_front'  => false,
					'ep_mask'     => EP_NONE,
					'paged'       => true,
					'feed'        => false,
					'forcomments' => false,
					'walk_dirs'   => true,
					'endpoints'   => false,
				)
			);

			// Add the permastruct.
			add_permastruct( $name, $params['struct'], $args );
		}

		parent::add_permastructs();
	}

	/**
	 * Allow the component to parse the main query.
	 *
	 * @since 1.0.0
	 *
	 * @param object $query The main WP_Query.
	 */
	public function parse_query( $query ) {
		$is_attachments_component = 1 === (int) $query->get( $this->rewrite_ids['directory'] );
		$bp                       = buddypress();

		if ( $is_attachments_component ) {
			$bp->current_component = $this->id;
		}

		$parse_array = array_fill_keys( $this->rewrite_ids, false );
		foreach ( $this->rewrite_ids as $rewrite_arg ) {
			$parse_array[ $rewrite_arg ] = $query->get( $rewrite_arg );
		}

		/**
		 * Set the queried object if a view or download action is requested
		 * for an existing User Media.
		 */
		if ( $parse_array['bp_attachments_item_action'] ) {
			$status = array_search( $parse_array['bp_attachments_status'], bp_attachments_get_item_stati(), true );
			$object = array_search( $parse_array['bp_attachments_object'], bp_attachments_get_item_objects(), true );
			$action = array_search( $parse_array['bp_attachments_item_action'], bp_attachments_get_item_actions(), true );

			if ( $action && $status && $object ) {
				$relative_path = array_filter(
					array(
						$status,
						$object,
						$parse_array['bp_attachments_object_item'],
						$parse_array['bp_attachments_item_action_variables'],
					)
				);

				$id    = array_pop( $relative_path );
				$media = BP_Attachments_Media::get_instance( $id, implode( '/', $relative_path ) );

				if ( $media ) {
					$query->queried_object    = $media;
					$query->queried_object_id = $media->id;
				}
			}
		}

		parent::parse_query( $query );
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
