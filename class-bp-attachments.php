<?php
/**
 * BP Attachments is a BuddyPress component to manage user media.
 *
 * @package   BP Attachments
 * @author    The BuddyPress community
 * @license   GPL-2.0+
 * @link      http://buddypress.org
 *
 * @buddypress-plugin
 * Plugin Name:       BP Attachments
 * Plugin URI:        https://github.com/buddypress/bp-attachments
 * Description:       BP Attachments is a BuddyPress component to manage user media.
 * Version:           1.0.0-alpha
 * Author:            The BuddyPress Community
 * Author URI:        http://buddypress.org/community/members/
 * Text Domain:       bp-attachments
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/buddypress/bp-attachments
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 */
	public static function start() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
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
		$this->inc();
	}

	/**
	 * Load needed files.
	 *
	 * @since 1.0.0
	 */
	private function inc() {
		// Classes.
		spl_autoload_register( array( $this, 'autoload' ) );

		// Functions.
		$inc_path = plugin_dir_path( __FILE__ ) . 'bp-attachments/';

		require $inc_path . 'globals.php';
		require $inc_path . 'bp-attachments-loader.php';
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

		if ( 0 !== strpos( $name, 'bp-attachments' ) ) {
			return;
		}

		$path = plugin_dir_path( __FILE__ ) . "bp-attachments/classes/class-{$name}.php";

		// Sanity check.
		if ( ! file_exists( $path ) ) {
			return;
		}

		require $path;
	}
}

// Let's start !
function bp_attachments() {
	return BP_Attachments::start();
}
add_action( 'bp_loaded', 'bp_attachments', 0 );
