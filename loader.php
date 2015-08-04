<?php
/**
 * BP Attachments is a BuddyPress component to help others deal with attachments.
 *
 *
 * @package   BP Attachments
 * @author    The BuddyPress community
 * @license   GPL-2.0+
 * @link      http://buddypress.org
 *
 * @buddypress-plugin
 * Plugin Name:       BP Attachments
 * Plugin URI:        https://buddypress.trac.wordpress.org/ticket/5429
 * Description:       BP Attachments is a BuddyPress component to help others deal with attachments.
 * Version:           1.1.0-alpha
 * Author:            The BuddyPress Community
 * Author URI:        http://buddypress.org/community/members/
 * Text Domain:       bp-attachments
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/buddypress/bp-attachments
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'BP_Attachments_Loader' ) ) :
/**
 * BP Attachments Loader Class
 *
 * @since BP Attachments (1.0.0)
 */
class BP_Attachments_Loader {
	/**
	 * Instance of this class.
	 *
	 * @package BP Attachments
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Some init vars
	 *
	 * @package BP Attachments
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	public static $bp_version_required = '2.3.0';

	/**
	 * Initialize the plugin
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->setup_globals();
		$this->setup_hooks();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function start() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Sets some globals for the plugin
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 */
	private function setup_globals() {
		/** BP Attachments globals ********************************************/
		$this->version                = '1.1.0-alpha';
		$this->domain                 = 'bp-attachments';
		$this->file                   = __FILE__;
		$this->basename               = plugin_basename( $this->file );
		$this->plugin_dir             = plugin_dir_path( $this->file );
		$this->plugin_url             = plugin_dir_url( $this->file );
		$this->lang_dir               = trailingslashit( $this->plugin_dir . 'languages' );
		$this->includes_dir           = trailingslashit( $this->plugin_dir . 'bp-attachments' );
		$this->includes_url           = trailingslashit( $this->plugin_url . 'bp-attachments' );
		$this->plugin_js              = trailingslashit( $this->includes_url . 'js' );
		$this->plugin_css             = trailingslashit( $this->includes_url . 'css' );

		/** Component specific globals ********************************************/
		$this->component_id           = 'attachments';
		$this->component_slug         = 'attachments';
		$this->component_name         = 'BP Attachments';

		/** BuddyPress & BP Attachments configs **********************************/
		$this->config = $this->network_check();

	}

	/**
	 * Checks BuddyPress version
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 */
	public function version_check() {
		// taking no risk
		if ( ! defined( 'BP_VERSION' ) )
			return false;

		return version_compare( BP_VERSION, self::$bp_version_required, '>=' );
	}

	/**
	 * Checks if current blog is the one where BuddyPress is activated
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 */
	public function root_blog_check() {

		if ( ! function_exists( 'bp_get_root_blog_id' ) )
			return false;

		if ( get_current_blog_id() != bp_get_root_blog_id() )
			return false;

		return true;
	}

	/**
	 * Checks if current blog is the one where BuddyPress is activated
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 */
	public function network_check() {
		/*
		 * network_active : BP Attachments is activated on the network
		 * network_status : BuddyPress & BP Attachments share the same network status
		 */
		$config = array( 'network_active' => false, 'network_status' => true );
		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );

		// No Network plugins
		if ( empty( $network_plugins ) )
			return $config;

		$check = array( buddypress()->basename, $this->basename );
		$network_active = array_diff( $check, array_keys( $network_plugins ) );

		if ( count( $network_active ) == 1 )
			$config['network_status'] = false;

		$config['network_active'] = isset( $network_plugins[ $this->basename ] );

		return $config;
	}

	/**
	 * Includes the needed file
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 */
	public function includes() {
		if ( bp_is_active( $this->component_id ) )
			require( $this->includes_dir . 'bp-attachments-loader.php' );
	}

	/**
	 * Sets the key hooks to add an action or a filter to
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 */
	private function setup_hooks() {
		// BP Attachments && BuddyPress share the same config & BuddyPress version is ok
		if ( $this->version_check() && $this->root_blog_check() && $this->config['network_status'] ) {
			// Filters
			add_filter( 'bp_optional_components',       array( $this, 'append_component' ), 10, 1 );
			add_filter( 'bp_core_admin_get_components', array( $this, 'component_desc' ),   10, 2 );
			add_action( 'bp_core_components_included',  array( $this, 'includes' ),         10    );

		} else {
			add_action( $this->config['network_active'] ? 'network_admin_notices' : 'admin_notices', array( $this, 'admin_warning' ) );
		}

		// loads the languages..
		add_action( 'bp_init', array( $this, 'load_textdomain' ), 6 );

	}

	/**
	 * Display a message to admin in case config is not as expected
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 */
	public function admin_warning() {
		$warnings = array();

		if( ! $this->version_check() ) {
			$warnings[] = sprintf( __( 'BP Attachments requires at least version %s of BuddyPress.', 'bp-attachments' ), self::$bp_version_required );
		}

		if ( ! bp_core_do_network_admin() && ! $this->root_blog_check() ) {
			$warnings[] = __( 'BP Attachments requires to be activated on the blog where BuddyPress is activated.', 'bp-attachments' );
		}

		if ( bp_core_do_network_admin() && ! is_plugin_active_for_network( $this->basename ) ) {
			$warnings[] = __( 'BP Attachments and BuddyPress need to share the same network configuration.', 'bp-attachments' );
		}

		if ( ! empty( $warnings ) ) :
		?>
		<div id="message" class="error">
			<?php foreach ( $warnings as $warning ) : ?>
				<p><?php echo esc_html( $warning ) ; ?>
			<?php endforeach ; ?>
		</div>
		<?php
		endif;
	}

	/**
	 * Append Attachments to optional components list
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 */
	public function append_component( $optional_components = array() ) {
		$optional_components[] = 'attachments';
		return $optional_components;
	}

	/**
	 * Include Attachments in components settings
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 */
	public function component_desc( $components, $type, $optional_components_desc = array() ) {
		// BP Attachments is optional
		if ( 'optional' != $type )
			return $components;

		return array_merge( $components, array(
			'attachments' => array(
				'title'       => __( 'Attachments', 'bp-attachments' ),
				'description' => __( 'Utility to manage BuddyPress media elements', 'bp-attachments' )
 			)
		) );
	}

	/**
	 * Loads the translation files
	 *
	 * @package BP Attachments
	 * @since 1.0.0
	 *
	 * @uses get_locale() to get the language of WordPress config
	 * @uses load_texdomain() to load the translation if any is available for the language
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/bp-attachments/' . $mofile;

		// Look in global /wp-content/languages/buddyplug folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/buddyplug/languages/ folder
		load_textdomain( $this->domain, $mofile_local );
	}

}

// Let's start !
function bp_attachments_loader() {
	return BP_Attachments_Loader::start();
}
add_action( 'bp_loaded', 'bp_attachments_loader', 0 );

endif;
