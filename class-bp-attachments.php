<?php
/**
 * BP Attachments is a BuddyPress add-on to manage your community members media.
 *
 * @package   BP Attachments
 * @author    The BuddyPress community
 * @license   GPL-2.0+
 * @link      http://buddypress.org
 *
 * @buddypress-plugin
 * Plugin Name:       BP Attachments
 * Plugin URI:        https://github.com/buddypress/bp-attachments
 * Description:       BP Attachments is a BuddyPress add-on to manage your community members media.
 * Version:           1.2.0
 * Author:            The BuddyPress Community
 * Author URI:        http://buddypress.org/community/members/
 * Text Domain:       bp-attachments
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/buddypress/bp-attachments
 * Requires at least: 6.1
 * Requires PHP:      5.6
 * Requires Plugins:  buddypress
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BP Attachments Main Class
 *
 * @since 1.0.0
 */
class BP_Attachments {
	/**
	 * Plugin Main Instance.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	protected static $instance = null;

	/**
	 * Checks whether BuddyPress is active.
	 *
	 * @since 1.0.0
	 */
	public static function is_buddypress_active() {
		$bp_plugin_basename   = 'buddypress/bp-loader.php';
		$is_buddypress_active = false;
		$sitewide_plugins     = (array) get_site_option( 'active_sitewide_plugins', array() );

		if ( $sitewide_plugins ) {
			$is_buddypress_active = isset( $sitewide_plugins[ $bp_plugin_basename ] );
		}

		if ( ! $is_buddypress_active ) {
			$plugins              = (array) get_option( 'active_plugins', array() );
			$is_buddypress_active = in_array( $bp_plugin_basename, $plugins, true );
		}

		return $is_buddypress_active;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 */
	public static function start() {
		// This plugin is only usable with the genuine BuddyPress.
		if ( ! self::is_buddypress_active() && ! defined( 'BP_ATTACHMENTS_TESTS_DIR' ) ) {
			return false;
		}

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Include the plugin files.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Autoload Classes.
		spl_autoload_register( array( $this, 'autoload' ) );

		// Load the Component's loader.
		require plugin_dir_path( __FILE__ ) . 'bp-attachments/bp-attachments-loader.php';
	}

	/**
	 * Class Autoload function
	 *
	 * @since  1.0.0
	 *
	 * @param  string $class The class name.
	 */
	public function autoload( $class ) {
		$name = str_replace( '_', '-', strtolower( $class ) );

		if ( 0 !== strpos( $name, 'bp-attachments' ) && 'bp-medium' !== $name ) {
			return;
		}

		$path = plugin_dir_path( __FILE__ ) . "bp-attachments/classes/class-{$name}.php";

		// Sanity check.
		if ( ! file_exists( $path ) ) {
			return;
		}

		require $path;
	}

	/**
	 * Activate the BP Attachments Component.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		if ( ! function_exists( 'buddypress' ) ) {
			return;
		}

		$active_components = array_merge(
			bp_get_option( 'bp-active-components', array() ),
			// Use the Component's id.
			array( 'attachments' => 1 )
		);

		bp_update_option( 'bp-active-components', $active_components );
	}

	/**
	 * Deactivate the BP Attachments Component.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		if ( ! function_exists( 'buddypress' ) ) {
			return;
		}

		$active_components = array_diff_key(
			bp_get_option( 'bp-active-components', array() ),
			// Use the Component's id.
			array( 'attachments' => 1 )
		);

		bp_update_option( 'bp-active-components', $active_components );
	}

	/**
	 * Displays an admin notice to explain how to activate BP Attachments.
	 *
	 * @since 1.0.0
	 */
	public static function admin_notice() {
		if ( self::is_buddypress_active() ) {
			return false;
		}

		$bp_plugin_link = sprintf( '<a href="%s">BuddyPress</a>', esc_url( _x( 'https://wordpress.org/plugins/buddypress', 'BuddyPress WP plugin directory URL', 'bp-attachments' ) ) );

		printf(
			'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
			sprintf(
				/* translators: 1. is the link to the BuddyPress plugin on the WordPress.org plugin directory. */
				esc_html__( 'BP Attachments requires the %1$s plugin to be active. Please deactivate BP Attachments, activate %1$s and only then, reactivate BP Attachments.', 'bp-attachments' ),
				$bp_plugin_link // phpcs:ignore
			)
		);
	}
}

/**
 * Let's start !
 *
 * @since 1.0.0
 */
function bp_attachments() {
	return BP_Attachments::start();
}
add_action( 'bp_loaded', 'bp_attachments', 0 );

/**
 * Use Activation and Deactivation hooks to update the
 * BuddyPress active components.
 */
register_activation_hook( __FILE__, array( 'BP_Attachments', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'BP_Attachments', 'deactivate' ) );

// Displays a notice to inform BP Attachments needs to be activated after BuddyPress.
add_action( 'admin_notices', array( 'BP_Attachments', 'admin_notice' ) );
