<?php
/**
 * Attachment Browser class.
 *
 * @package BP Attachments
 * @subpackage Classes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Attachments Browser Class.
 *
 * This class is used to create the
 * "BP Media Editor" for attachments
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
			'item_id'         => 0,                                            // what is the item id ?
			'component'       => 'members',                                    // what is the component groups/xprofile/members/messages ?
			'item_type'       => 'attachment',                                 // attachment / image custom..
			'post_id'         => 0,
			'btn_caption'     => __( 'Manage attachments', 'bp-attachments' ), // the button caption
			'btn_class'       => 'attachments-editor',                         // the button class
			'file_data_name'  => 'bp-attachments-attachment-upload',           // the name of the $_FILE to upload
			'multi_selection' => false,                                        // allow multiple file upload ?
			'action'          => 'bp_attachments_upload',                      // the ajax action to deal with the file
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

		if ( bp_attachments_loggedin_user_can( 'publish_bp_attachments', $args ) ) {
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
