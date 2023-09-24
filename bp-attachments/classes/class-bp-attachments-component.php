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
#[AllowDynamicProperties]
class BP_Attachments_Component extends BP_Component {
	/**
	 * Whether private uploads are enabled or not.
	 *
	 * @var bool
	 */
	public $private_uploads = false;

	/**
	 * The list of supported attachment block names.
	 *
	 * @var array
	 */
	public $block_names = array();

	/**
	 * Construct the BP Attachments component.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->private_uploads = (bool) bp_get_option( '_bp_attachments_can_upload_privately', false );

		parent::start(
			'attachments',
			__( 'Attachments', 'bp-attachments' ),
			plugin_dir_path( dirname( __FILE__ ) ),
			array(
				'adminbar_myaccount_order' => 30,
				'features'                 => bp_attachments_get_features(),
			)
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

		add_action( 'bp_' . $this->id . '_parse_query', array( $this, 'very_late_includes' ), 10, 0 );

		add_filter( 'bp_classic_admin_display_directory_states', array( $this, 'admin_directory_states' ), 10, 2 );
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
			'compat',
			'cache',
			'users',
			'template-loader',
			'assets-loader',
			'blocks',
			'templates',
		);

		// Tacking attachments allowes public directory and more sharing options.
		if ( bp_is_active( $this->id, 'tracking' ) ) {
			$includes[] = 'tracking';
		}

		if ( bp_is_active( $this->id, 'profile-images' ) ) {
			$includes[] = 'profile-images';
		}

		if ( bp_is_active( 'activity' ) ) {
			$includes[] = 'activity';
		}

		if ( $this->private_uploads && bp_is_active( 'messages' ) ) {
			$includes[] = 'messages';
		}

		if ( is_admin() ) {
			$includes[] = 'admin';
			$includes[] = 'settings';
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
	 * Includes what `$this->late_includes()` cannot as it fires too early.
	 *
	 * @since 1.0.0
	 */
	public function very_late_includes() {
		if ( bp_is_current_component( 'attachments' ) & ! bp_is_user() ) {
			require $this->path . 'screens/directory.php';
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
		$bp            = buddypress();
		$root_slug     = 'bp-attachments';
		$has_directory = bp_is_active( $this->id, 'tracking' );

		if ( $has_directory && isset( $bp->pages->{ $this->id }->slug ) ) {
			$root_slug = $bp->pages->{ $this->id }->slug;
		}

		// Rewrite args.
		$rewrite_args = array(
			'root_slug'   => $root_slug,
			'rewrite_ids' => array(
				'directory'                    => 'bp_attachments',
				'directory_visibility'         => 'bp_attachments_visibility',
				'directory_object'             => 'bp_attachments_object',
				'single_item'                  => 'bp_attachments_object_item',
				'single_item_action'           => 'bp_attachments_item_action',
				'single_item_action_variables' => 'bp_attachments_item_action_variables',
			),
		);

		// Globals for BuddyPress components.
		$args = array(
			'slug'                  => $this->id,
			'has_directory'         => $has_directory,
			'notification_callback' => 'bp_attachments_format_notifications',
			'directory_title'       => __( 'Community Media', 'bp-attachments' ),
		);

		parent::setup_globals( $args + $rewrite_args );

		/**
		 * Globals specific to this component.
		 */

		// Current version.
		$this->version = '1.2.0';

		// Paths.
		$this->templates_dir = trailingslashit( plugin_dir_path( $this->path ) ) . 'templates';

		// URLs.
		$this->plugin_url    = plugins_url( '/', $this->path );
		$this->templates_url = plugins_url( 'templates/', $this->path );
		$this->js_url        = plugins_url( 'bp-attachments/js/', $this->path );
		$this->assets_url    = plugins_url( 'bp-attachments/assets/', $this->path );

		// Rewrites.
		if ( ! isset( $this->rewrite_ids ) ) {
			$this->rewrite_ids           = $rewrite_args['rewrite_ids'];
			$this->directory_permastruct = $this->root_slug . '/%' . $this->rewrite_ids['directory'] . '%';
		}

		// Use a global to store attachment's URL query vars.
		$this->query_vars = array();

		// Use our own queried object to avoid conflicts with WordPress one.
		$this->queried_object    = null;
		$this->queried_object_id = 0;

		$this->oembed = new BP_Attachments_OEmbed_Extension();
	}

	/**
	 * Register component navigation.
	 *
	 * @since 2.0.0
	 *
	 * @see `BP_Component::register_nav()` for a description of arguments.
	 *
	 * @param array $main_nav Optional. See `BP_Component::register_nav()` for
	 *                        description.
	 * @param array $sub_nav  Optional. See `BP_Component::register_nav()` for
	 *                        description.
	 */
	public function register_nav( $main_nav = array(), $sub_nav = array() ) {

		// User main navigation.
		$main_nav = array(
			'name'                     => __( 'Media', 'bp-attachments' ),
			'slug'                     => $this->slug,
			'position'                 => 30,
			'screen_function'          => 'bp_attachments_personal_screen',
			'default_subnav_slug'      => 'personal',
			'item_css_id'              => $this->id,
			'user_has_access_callback' => 'bp_is_my_profile',
		);

		// User sub navigation.
		$sub_nav[] = array(
			'name'                     => __( 'Personal', 'bp-attachments' ),
			'slug'                     => 'personal',
			'parent_slug'              => $this->slug,
			'position'                 => 10,
			'screen_function'          => 'bp_attachments_personal_screen',
			'item_css_id'              => 'personal-' . $this->id,
			'user_has_access'          => false,
			'user_has_access_callback' => 'bp_is_my_profile',
		);

		if ( method_exists( get_parent_class( $this ), 'register_nav' ) ) {
			parent::register_nav( $main_nav, $sub_nav );

		} else {
			return array(
				'main_nav' => $main_nav,
				'sub_nav'  => $sub_nav,
			);
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
		if ( ! method_exists( get_parent_class( $this ), 'register_nav' ) ) {
			$nav    = $this->register_nav();
			$access = bp_is_my_profile();

			unset( $nav['main_nav']['user_has_access_callback'] );

			// Set main nav access.
			$nav['main_nav']['show_for_displayed_user'] = $access;

			// Determine the user link to use.
			if ( bp_attachments_displayed_user_url() ) {
				$attachments_link = bp_attachments_displayed_user_url( array( $this->slug ) );
			} elseif ( bp_attachments_loggedin_user_url() ) {
				$attachments_link = bp_attachments_loggedin_user_url( array( $this->slug ) );
			}

			if ( isset( $attachments_link ) ) {
				foreach ( $nav['sub_nav'] as $sub_nav_key => $sub_nav_item ) {
					if ( isset( $sub_nav_item['user_has_access_callback'] ) ) {
						$nav['sub_nav'][ $sub_nav_key ]['user_has_access'] = call_user_func( $sub_nav_item['user_has_access_callback'] );
						unset( $nav['sub_nav'][ $sub_nav_key ]['user_has_access_callback'] );
					}

					$nav['sub_nav'][ $sub_nav_key ]['parent_url'] = $attachments_link;
				}

				$main_nav = $nav['main_nav'];
				$sub_nav  = $nav['sub_nav'];
			}
		}

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

			// Setup the logged in user attachments link.
			$attachments_link = bp_attachments_loggedin_user_url(
				array(
					'single_item_component' => $this->slug,
				)
			);

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
				$bp->bp_options_title = __( 'My Media', 'bp-attachments' );

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
							/* translators: %s is the displayed User full name */
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
			'directory-visibility'         => array(
				'id'    => '%' . $this->rewrite_ids['directory_visibility'] . '%',
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
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_visibility'] . '=$matches[1]&' . $this->rewrite_ids['directory_object'] . '=$matches[2]&' . $this->rewrite_ids['single_item'] . '=$matches[3]&' . $this->rewrite_ids['single_item_action'] . '=$matches[4]&' . $this->rewrite_ids['single_item_action_variables'] . '=$matches[5]',
			),
			'single-item-action'           => array(
				'regex' => $this->root_slug . '/([^/]+)\/([^/]+)\/([^/]+)\/([^/]+)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_visibility'] . '=$matches[1]&' . $this->rewrite_ids['directory_object'] . '=$matches[2]&' . $this->rewrite_ids['single_item'] . '=$matches[3]&' . $this->rewrite_ids['single_item_action'] . '=$matches[4]',
			),
			'single-item'                  => array(
				'regex' => $this->root_slug . '/([^/]+)\/([^/]+)\/([^/]+)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_visibility'] . '=$matches[1]&' . $this->rewrite_ids['directory_object'] . '=$matches[2]&' . $this->rewrite_ids['single_item'] . '=$matches[3]',
			),
			'directory_object'             => array(
				'regex' => $this->root_slug . '/([^/]+)\/([^/]+)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_visibility'] . '=$matches[1]&' . $this->rewrite_ids['directory_object'] . '=$matches[2]',
			),
			'directory_visibility'         => array(
				'regex' => $this->root_slug . '/([^/]+)/?$',
				'query' => 'index.php?' . $this->rewrite_ids['directory'] . '=1&' . $this->rewrite_ids['directory_visibility'] . '=$matches[1]',
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

		$this->query_vars['raw'] = $parse_array;
		$visibility              = array_search( $parse_array['bp_attachments_visibility'], bp_attachments_get_item_visibilities(), true );
		$object                  = array_search( $parse_array['bp_attachments_object'], bp_attachments_get_item_object_slugs(), true );
		$action                  = array_search( $parse_array['bp_attachments_item_action'], bp_attachments_get_item_actions(), true );
		$item_id                 = 0;

		/*
		 * Set the queried object if a view or download action is requested
		 * for an existing User Media.
		 */
		if ( $action ) {
			$bp->current_action = $action;

			if ( 'embed' === bp_attachments_get_item_action_key( $action ) ) {
				$query->set( 'embed', true );
				$query->is_embed = true;
			}

			if ( $parse_array['bp_attachments_object_item'] ) {
				if ( 'members' === $object ) {
					$item = get_user_by( 'slug', $parse_array['bp_attachments_object_item'] );

					if ( $item && isset( $item->ID ) ) {
						$item_id = (int) $item->ID;
					}
				} else {
					/**
					 * Filter here to set other components item ID.
					 *
					 * @since 1.0.0
					 *
					 * @param array $parse_array {
					 *     Associative array of arguments list used to get a medium.
					 *
					 *     @type string $bp_attachments                       "1" to inform the Attachments directory is displayed.
					 *     @type string $bp_attachments_visibility            The medium visibility (eg: private or public).
					 *     @type string $bp_attachments_object                The BuddyPress object the medium relates to. Defaults to `members`.
					 *     @type string $bp_attachments_object_item           The BuddyPress object item slug the medium relates to.
					 *     @type string $bp_attachments_item_action           Whether the `view` or `download` action is requested.
					 *     @type string $bp_attachments_item_action_variables An array containing relative path chunks to the JSON filename containing the medium data.
					 * }
					 */
					$item_id = apply_filters( 'bp_attachments_parse_querried_object_item', $parse_array );
				}

				// Set query vars’ item ID.
				if ( $item_id ) {
					$this->query_vars['data']['item_id'] = $item_id;
				} else {
					bp_do_404();
					return;
				}
			}

			if ( $action && $visibility && $object ) {
				// Set query vars’ visibility & $object.
				$this->query_vars['data']['visibility'] = $visibility;
				$this->query_vars['data']['object']     = $object;

				// Set item action variables.
				$item_action_variables = explode( '/', $parse_array['bp_attachments_item_action_variables'] );

				$relative_path = array_filter(
					array_merge(
						array( $object, $item_id ),
						$item_action_variables
					)
				);
				$id            = array_pop( $relative_path );
				$rel_path      = implode( '/', $relative_path );
				$absolute_path = trailingslashit( bp_attachments_get_media_uploads_dir( $visibility )['path'] ) . $rel_path;

				// Set query vars’ relative path and action variables.
				$this->query_vars['data']['relative_path']    = $rel_path;
				$this->query_vars['data']['medium_id']        = array_pop( $item_action_variables );
				$this->query_vars['data']['action_variables'] = $item_action_variables;

				// Try to get the medium.
				$medium = bp_attachments_get_medium( $id, $absolute_path );

				if ( $medium ) {
					// Eventually set the current directory.
					if ( 'inode/directory' === $medium->mime_type ) {
						$this->query_vars['data']['current_directory'] = $medium->name;
					}

					$this->queried_object    = $medium;
					$this->queried_object_id = $medium->id;
				} else {
					bp_do_404();
					return;
				}
			} else {
				bp_do_404();
				return;
			}
		} elseif ( ( $visibility || $object ) && ! $item_id ) {
			bp_do_404();
			return;
		}

		/*
		 * Set the BuddyPress queried object when BP is >= 12.0.
		 * This allows to benefit from the community visibility feature.
		 */
		if ( function_exists( 'bp_core_get_query_parser' ) && 'rewrites' === bp_core_get_query_parser() ) {
			// Directory.
			if ( ( isset( $bp->pages->attachments->id ) && $is_attachments_component && ! $action ) ) {
				$query->queried_object    = get_post( $bp->pages->attachments->id );
				$query->queried_object_id = $query->queried_object->ID;

				// In case of single item, use the object it is attached to.
			} elseif ( $this->queried_object_id && $object && isset( $bp->pages->{$object}->id ) ) {
				$query->queried_object    = get_post( $bp->pages->{$object}->id );
				$query->queried_object_id = $query->queried_object->ID;
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
		$controllers = array( 'BP_Attachments_REST_Controller' );

		if ( bp_is_active( $this->id, 'profile-images' ) ) {
			$controllers[] = 'BP_Attachments_Profile_Image_REST_Controller';
		}

		if ( bp_is_active( $this->id, 'tracking' ) ) {
			$controllers[] = 'BP_Attachments_Tracking_REST_Controller';
		}

		parent::rest_api_init( $controllers );
	}

	/**
	 * Register the BP Attachments Blocks.
	 *
	 * @since 1.0.0
	 *
	 * @param array $blocks Optional. See BP_Component::blocks_init() for
	 *                      description.
	 */
	public function blocks_init( $blocks = array() ) {
		$editor_script_deps = array(
			'wp-api-fetch',
			'wp-blocks',
			'wp-block-editor',
			'wp-components',
			'wp-data',
			'wp-element',
			'wp-i18n',
		);

		// Get the blocks metadata.
		$metadata_file   = $this->path . 'assets/blocks/blocks.json';
		$blocks_metadata = wp_json_file_decode( $metadata_file, array( 'associative' => true ) );
		$blocks_list     = array();
		$allowed_types   = bp_attachments_get_allowed_media_types();

		if ( ! is_null( $blocks_metadata ) ) {
			foreach ( $blocks_metadata as $block_metadata ) {
				$this->block_names[] = $block_metadata['name'];
				$block_suffix        = str_replace( 'bp/', '', $block_metadata['name'] );
				$block_type          = str_replace( '-attachment', '', $block_suffix );

				// Only register blocks if the corresponding attachment type is allowed.
				if ( 'file' !== $block_type && ( ! isset( $allowed_types[ $block_type ] ) || ! $allowed_types[ $block_type ] ) ) {
					continue;
				}

				$block_metadata['editor_script_deps']   = $editor_script_deps;
				$block_metadata['render_callback']      = 'bp_attachments_render_' . str_replace( '-', '_', $block_suffix );
				$block_metadata['plugin_url']           = $this->plugin_url;
				$block_metadata['buddypress_contexts']  = array( 'activity' );
				$blocks_list[ $block_metadata['name'] ] = $block_metadata;
			}
		}

		parent::blocks_init( $blocks_list );
	}

	/**
	 * Add the Attachments directory state.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $states Optional. See BP_Component::admin_directory_states() for description.
	 * @param WP_Post $post   Optional. See BP_Component::admin_directory_states() for description.
	 * @return array          See BP_Component::admin_directory_states() for description.
	 */
	public function admin_directory_states( $states = array(), $post = null ) {
		$bp = buddypress();

		if ( isset( $bp->pages->{ $this->id }->id ) && (int) $bp->pages->{ $this->id }->id === (int) $post->ID ) {
			$states['page_for_attachments_directory'] = _x( 'BP Attachments Page', 'page label', 'bp-attachments' );
		}

		if ( 'bp_classic_admin_display_directory_states' === current_filter() ) {
			return $states;
		}

		return parent::admin_directory_states( $states, $post );
	}
}
