<?php
/**
 * BP Attachments Screens.
 *
 * User's screens
 *
 * @package BP Attachments
 * @subpackage Screens
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Attachments User Screens Class.
 *
 * @since BP Attachments (1.0.0)
 */
class BP_Attachments_User_Screens {

	public $title = 'user_title';
	public $content = 'user_content';

	/**
	 * Construct the screen.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function __construct() {
		bp_core_load_template( apply_filters( 'bp_attachments_user_screens', 'members/single/plugins' ) );
		$this->query_string();
		$this->setup_actions();
	}

	/**
	 * Deal with actions.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function query_string() {
		if ( ! empty( $_GET['action'] ) && 'delete' == $_GET['action'] && ! empty( $_GET['attachment'] ) ) {
			check_admin_referer( 'bp_attachments_delete' );

			$redirect = remove_query_arg( array( 'attachment', 'action' ), wp_get_referer() );

			$deleted = bp_attachments_delete_attachment( $_GET['attachment'] );

			if ( ! empty( $deleted ) ) {
				bp_core_add_message( sprintf( __( 'Attachment: %s successfully deleted', 'bp-attachments' ), $deleted ) );
			} else {
				bp_core_add_message( __( 'Attachment could not be deleted.', 'bp-attachments' ), 'error' );
			}

			bp_core_redirect( $redirect );
		}

		if ( ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] && ! empty( $_GET['attachment'] ) ) {
			$this->title = 'edit_title';
			$this->content = 'edit_content';

			$redirect = remove_query_arg( array( 'attachment', 'action' ), wp_get_referer() );
			$attachment_id = absint( $_GET['attachment'] );

			$attachment = bp_attachments_get_attachment( $attachment_id );

			if ( empty( $attachment ) || ! bp_attachments_current_user_can( 'edit_bp_attachment', $attachment_id ) ) {
				bp_core_add_message( sprintf( __( 'Attachment could not be found', 'bp-attachments' ), 'error' ) );
				bp_core_redirect( $redirect );
			}

			// Set up the attachment global
			buddypress()->attachments->attachment = $attachment;
		}

		if ( ! empty( $_POST['_bp_attachments_edit']['update'] ) ) {

			check_admin_referer( 'bp_attachment_update' );
			$redirect = wp_get_referer();

			$attachment_id = absint( $_POST['_bp_attachments_edit']['id'] );

			if ( empty( $attachment_id ) || ! bp_attachments_current_user_can( 'edit_bp_attachment', $attachment_id ) ) {
				bp_core_add_message( sprintf( __( 'Attachment could not be edited', 'bp-attachments' ), 'error' ) );
				bp_core_redirect( $redirect );
			}

			$updated = bp_attachments_update_attachment( $_POST['_bp_attachments_edit'] );

			if ( ! empty( $updated ) ) {
				$redirect = trailingslashit( bp_core_get_user_domain( bp_displayed_user_id() ) . buddypress()->attachments->slug );
				bp_core_add_message( sprintf( __( 'Attachment: %s successfully updated', 'bp-attachments' ), $updated ) );
			} else {
				bp_core_add_message( __( 'Attachment could not be edited.', 'bp-attachments' ), 'error' );
			}

			bp_core_redirect( $redirect );
		}
	}

	/**
	 * Register the screen class.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public static function register_screens() {

		$bp = buddypress();

		if( empty( $bp->attachments->user_screens ) ) {
			$bp->attachments->user_screens = new self;
		}

		return $bp->attachments->user_screens;
	}

	/**
	 * Customize the members/single/plugins template
	 *
	 * @since BP Attachments (1.0.0)
	 */
	private function setup_actions() {
		add_action( 'bp_template_title', array( $this, $this->title ) );
		add_action( 'bp_template_content', array( $this, $this->content ) );
	}

	/**
	 * Displays the button to launch the BP Media Editor
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function user_title() {
		bp_attachments_browser( 'bp-attachments-upload', array(
			'item_type'       => 'attachment',
			'btn_caption'     => __( 'Manage attachments', 'bp-attachments' ),
			'multi_selection' => true,
			'btn_class'       => 'attachments-editor',
			'callback'        => trailingslashit( bp_core_get_user_domain( bp_loggedin_user_id() ) . buddypress()->attachments->slug )
		) );
	}

	/**
	 * Displays the component loop
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function user_content() {
		do_action( 'bp_attachments_uploader_fallback' );

		bp_attachments_template_loop( buddypress()->attachments->current_component );
	}

	/**
	 * Displays the name of the file being edited
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function edit_title() {
		echo esc_html( sprintf( __( 'Editing: %s', 'bp-attachments' ), buddypress()->attachments->attachment->title ) );
	}

	/**
	 * Displays the form to edit the file
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function edit_content() {
		bp_attachments_template_single();
	}

}

/**
 * The User's screen function!
 *
 * @since BP Attachments (1.0.0)
 * @see BP_Attachments_Component::setup_nav()
 *
 * @uses BP_Attachments_User_Screens
 */
function bp_attachments_screen_my_attachments() {
	return BP_Attachments_User_Screens::register_screens();
}

/**
 * The User's new change avatar screen content
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_members_change_avatar_content() {
	if ( ! bp_is_user_change_avatar() )
		return;

	do_action( 'bp_before_profile_avatar_upload_content' );

	if ( bp_attachments_avatar_is_enabled() ) : ?>

		<p><?php _e( 'Your avatar will be used on your profile and throughout the site. If there is a <a href="http://gravatar.com">Gravatar</a> associated with your account email we will use that, or you can upload an image from your computer.', 'bp-attachments' ); ?></p>

		<div id="bp_xprofile_avatar">

			<div id="xprofile-avatar">
		        <?php
		        // Displays the current avatar and a link to delete it.
		        if ( bp_get_user_has_avatar() ) {
		            echo bp_core_fetch_avatar( array( 'item_id' => bp_displayed_user_id(), 'object' => 'user', 'type' => 'full' ) );
		            ?>
		            <p><a href="<?php bp_avatar_delete_link(); ?>" id="remove-xprofile-avatar"><?php esc_html_e( 'Remove Avatar', 'bp-attachments' ); ?></a></p>
		            <?php
		        }
		        ?>
	        </div>

        <?php
        // Displays the button to Change the avatar.
        bp_attachments_browser( 'bp-avatar-upload', array(
            'item_id'         => bp_displayed_user_id(),
            'component'       => 'xprofile',
            'item_type'       => 'avatar',
            'btn_caption'     => __( 'Edit Avatar', 'bp-attachments' ),
            'multi_selection' => false,
            'action'          => 'bp_attachments_upload_avatar',
            'btn_class'       => 'attachments-new-avatar'
        ) );

        do_action( 'bp_attachments_uploader_fallback' );
        ?>

        </div>

	<?php else: ?>

		<p><?php _e( 'Your avatar will be used on your profile and throughout the site. To change your avatar, please create an account with <a href="http://gravatar.com">Gravatar</a> using the same email address as you used to register with this site.', 'bp-attachments' ); ?></p>

	<?php endif;

	do_action( 'bp_after_profile_avatar_upload_content' );
}
add_action( 'bp_template_content', 'bp_attachments_members_change_avatar_content' );

/**
 * The User's new change avatar screen title
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_members_change_avatar_title() {
	if ( ! bp_is_user_change_avatar() )
		return;

	esc_html_e( 'Change Avatar', 'bp-attachments' );
}
add_action( 'bp_template_title', 'bp_attachments_members_change_avatar_title' );
