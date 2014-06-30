<?php
/**
 * BuddyPress Attachments Groups.
 *
 * @package BP Attachments
 * @subpackage Groups
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( class_exists( 'BP_Group_Extension' ) ) :
/**
 * The BP Attachments group class
 *
 * @package BP Attachments
 * @since 1.0.0
 */
class BP_Attachments_Group extends BP_Group_Extension {        
    /**
     * construct method to add some settings and hooks
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     *
     * @uses bp_attachments_loader() to get the plugin slug
     */
    public function __construct() {

        $this->avatar_enabled = bp_attachments_avatar_is_enabled();
    	$this->setup_actions();

        $attachments_count = array( 'total' => 0 );

        if ( bp_is_group() && $this->use_attachments() ) {
            $attachments_count = BP_Attachments::count( array( 
                'item_id'   => bp_get_current_group_id(),
                'term_slug' => buddypress()->groups->id
            ) );
        }
        $class    = ( 0 === absint( $attachments_count['total'] ) ) ? 'no-count' : 'count';

        $args = array(
            'slug'              => bp_attachments_loader()->component_slug,
            'name'              => __( 'Attachments', 'bp-attachments' ),
            'nav_item_name'     => sprintf( __( 'Attachments <span class="%s">%s</span>', 'bp-attachments' ), esc_attr( $class ), number_format_i18n( $attachments_count['total'] ) ),
            'visibility'        => 'private',
            'nav_item_position' => 61,
            'enable_nav_item'   => $this->enable_nav_item(),
            'screens'           => array(
                'create' => array(
                    'position' => 20,
                    'enabled'  => $this->avatar_enabled,
                    'name'     => __( 'Avatar', 'bp-attachments' ),
                ),
                 'admin' => array(
                    'metabox_context'  => 'side',
                    'metabox_priority' => 'core'
                ),
                'edit' => array(
                    'enabled' => false,
                )
            )
        );

        parent::init( $args );
            
    }

    /**
     * Add an avatar to the new group.
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function create_screen( $group_id = null ) {
        $this->edit_group_avatar();
    }

    /**
     * @todo disable javascript and see how to deal with
     * it.
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function create_screen_save( $group_id = null ) {}

    /**
     * Unused Methods
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function edit_screen( $group_id = null ) {}
    public function edit_screen_save( $group_id = null ) {}
	public function widget_display() {}

    /**
     * Set up key actions
     * 
     * Some may not be in the Group Extension class..
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
	private function setup_actions() {
        $bp = buddypress();

        // Add BP Attachments option to Group settings on front end
		add_action( 'bp_after_group_settings_admin',                array( $this, 'group_settings' )             );
        add_action( 'bp_after_group_settings_creation_step',        array( $this, 'group_settings')              );
		add_action( 'groups_group_settings_edited',                 array( $this, 'group_settings_save' ), 10, 1 );
        add_action( 'groups_create_group_step_save_group-settings', array( $this, 'group_settings_save' )        );

        if ( $this->avatar_enabled )  {
            // Allow an avatar to be edited from Group Admin Screen
            if ( is_admin() ) {
                add_action( 'bp_groups_admin_meta_boxes', array( $this, 'group_avatar' ) );
            }

            // Override the change avatar settings for group in front end
            add_filter( 'bp_get_template_part', array( $this, 'filter_change_avatar_template'), 10, 2 );
            add_action( 'bp_template_content',  array( $this, 'edit_group_avatar'          )        );

            // override the group creation avatar step
            add_action( 'bp_actions', array( $this, 'remove_avatar_step' ), 1 );
        }

        // Add group specific item to attach the attach to
        add_filter( "bp_attachments_edit_attached_to_{$bp->groups->id}", array( $this, 'attach_group' ), 10, 2 );

        // Add remove from group action link if in a group
        add_action( "bp_attachments_{$bp->groups->id}_actions", array( $this, 'group_actions' ) );
        // Handle the remove action
        if ( bp_is_group() )
            add_action( 'bp_actions', array( $this, 'remove_from_group') );

	}

    /**
     * Displays the attchments setting in Group Admin & settings screens
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
	public function group_settings( $group_id = null ) {
        if ( empty( $group_id ) )
            $group_id = bp_get_new_group_id() ? bp_get_new_group_id() : bp_get_current_group_id();

		$use = $this->use_attachments( $group_id );

        if ( ! is_admin() ) :
		?>
		<h4><?php _e( 'Group attachments', 'bp-attachments' ); ?></h4>

        <?php endif ; ?>

		<div class="checkbox">
			<label><input type="checkbox" name="group-use-attachments" id="group-use-attachments" value="1" <?php checked( $use );?>/> <?php _e( 'Enable Attachments', 'bp-attachments' ); ?></label>
		</div>

        <?php if ( is_admin() ) :
            wp_nonce_field( 'group_attachments_admin_save_' . $group_id, 'bp_attachments_group_admin' );
        else : ?>
            <hr />
		<?php
        endif;
	}

    /**
     * Save the setting
     * 
     * Eventually remove linked attachments
     * in case the attachment feature has been disabled.
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
	public function group_settings_save( $group_id = null ) {
		if ( empty( $group_id ) )
			$group_id = bp_get_new_group_id() ? bp_get_new_group_id() : bp_get_current_group_id();

        if ( is_admin() ) {
            check_admin_referer( 'group_attachments_admin_save_' . $group_id, 'bp_attachments_group_admin' );
        }

		if ( isset( $_POST['group-use-attachments'] ) ) {
			groups_update_groupmeta( $group_id, 'group-use-attachments', 1 );
		} else {
			groups_delete_groupmeta( $group_id, 'group-use-attachments' );

            // Need to delete attached object
            $bp = buddypress();
            delete_metadata( 'post', null, "_bp_{$bp->groups->id}_id", $group_id, true );
		}
	}

    /**
     * Add a metabox to edit the Group Avatar
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function group_avatar() {
        add_meta_box( 
            'bp_groups_avatar', 
            _x( 'Avatar', 'group admin edit screen', 'bp-attachments' ), 
            array( &$this, 'group_avatar_metabox' ), 
            get_current_screen()->id, 
            'side', 
            'low' 
        );
    }

    /**
     * Displays the metabox to edit the Group Avatar
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function group_avatar_metabox( $item = null ) {
        if ( empty( $item ) )
            return;
        ?>
        <div id="groups-avatar">
        <?php
        // Displays the current avatar and a link to delete it.
        if ( bp_get_group_has_avatar( $item->id ) ) {
            $fallback_link = is_admin() ? '#' : bp_get_group_avatar_delete_link();
            echo bp_core_fetch_avatar( array( 'item_id' => $item->id, 'object' => 'group', 'type' => 'full' ) );

            if ( ! bp_is_group_create() ) : ?>
            <p><a href="<?php echo esc_url( $fallback_link );?>" id="remove-groups-avatar"><?php esc_html_e( 'Remove Avatar', 'bp-attachments' ); ?></a></p>
            <?php endif;
        }
        ?>
        </div>
        <?php
        // Displays the button to Change the avatar.
        bp_attachments_browser( 'bp-avatar-upload', array( 
            'item_id'         => $item->id,
            'component'       => 'groups',
            'item_type'       => 'avatar',
            'btn_caption'     => __( 'Edit Avatar', 'bp-attachments' ),
            'multi_selection' => false,
            'action'          => 'bp_attachments_upload_avatar',
            'btn_class'       => 'attachments-new-avatar'
        ) );

        // Provide a js fallback on edit/create screens
        if ( ! is_admin() ) {
            do_action( 'bp_attachments_uploader_fallback' );
        }
    }

    /**
     * Replace the group avatar template to use group/single/plugins one
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function filter_change_avatar_template( $templates = array(), $slug = '' ) {
        if( ! bp_is_group_admin_page() || ! bp_is_group_admin_screen( 'group-avatar' ) )
            return $templates;

        if( 'groups/single/admin' == $slug )
            $templates = array_merge( array( 'groups/single/plugins.php' ), $templates );

        return $templates;
    }

    /**
     * Are we on a screen to add/edit group avatar ?
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function is_group_avatar_screen() {
        $retval = false;

        if ( bp_is_group_admin_page() && bp_is_group_admin_screen( 'group-avatar' ) ) {
            $retval = 'edit_screen';
        }

        if ( bp_is_group_create() ) {
            $retval = 'create_screen';
        }

        return $retval;
    }

    /**
     * Displays the content to change avatar in group/single/plugins
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function edit_group_avatar() {
        $group_avatar_screen = $this->is_group_avatar_screen();

        if( empty( $group_avatar_screen ) ) {
            return;
        }

        $group = groups_get_current_group(); ?>
        
        <?php if ( 'edit_screen' ==  $group_avatar_screen ) :?>
            <div class="item-list-tabs no-ajax" id="subnav" role="navigation">
                <ul>
                    <?php bp_group_admin_tabs(); ?>
                </ul>
            </div><!-- .item-list-tabs -->

            <?php do_action( 'bp_before_group_admin_content' );

        endif ;?>

        <p><?php _e("Upload an image to use as an avatar for this group. The image will be shown on the main group page, and in search results.", 'bp-attachments' ); ?></p>

        <div id="bp_groups_avatar">
        
            <?php $this->group_avatar_metabox( $group ); ?>

        </div>

        <?php
    }

    /**
     * Add a metabox to Group Admin Screen
     * 
     * As it's not possible to hook into the settings meta box..
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function admin_screen( $group_id = null ) {
        $this->group_settings( $group_id );
    }

    /**
     * Save the setting in Group Admin screen
     * 
     * As it's not possible to hook into the settings meta box..
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function admin_screen_save( $group_id = null ) {
        $this->group_settings_save( $group_id );
    }

    /**
     * Displays the Attachments content of the group
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     *
     * @return string html output
     */
    public function display() {

        ?>
        <h3><?php 
        	bp_attachments_browser( 'bp-attachments-upload', array( 
				'item_id'         => bp_get_current_group_id(),
				'component'       => 'groups',
				'item_type'       => 'attachment',
				'btn_caption'     => __( 'Manage attachments', 'bp-attachments' ),
				'multi_selection' => true,
				'btn_class'       => 'attachments-editor',
                'callback'        => trailingslashit( bp_get_group_permalink( groups_get_current_group() ) . buddypress()->attachments->slug )
			) );?></h3>

        <?php
        do_action( 'bp_attachments_uploader_fallback' );
        bp_attachments_template_loop( 'groups' );
    }

    /**
     * Does this Group is using attachments ?
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function use_attachments( $group_id = 0 ) {
        if ( empty( $group_id ) )
            $group_id = bp_get_current_group_id();

        return (bool) groups_get_groupmeta( $group_id, 'group-use-attachments' );
    }

    /**
     * Let user change the groups linked in user's attachement edit screen
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function attach_group( $output = '', $attachment = null ) {
        $bp = buddypress();

        if ( empty( $attachment ) )
            return $output;

        $user_groups = groups_get_groups( array( 'user_id' => $attachment->user_id, 'show_hidden' => true, 'per_page' => false ) );

        if ( empty( $user_groups['groups'] ) ) {
            $output .= '<div id="message" class="info"><p>' . __( 'You must be a member of a group to link your attachment to it.', 'bp-attachments' ) . '</p></div>';
        } else {
            $item_ids = ! empty( $attachment->item_ids->{$bp->groups->id} ) ? $attachment->item_ids->{$bp->groups->id} : array();

            $output .= '<ul>';
            
            foreach ( $user_groups['groups'] as $group ) {
                if ( ! $this->use_attachments( $group->id ) )
                    continue;

                $output .= '<li><a href="' . esc_url( bp_get_group_permalink( $group ) ) .'" title="'. esc_attr( $group->name ) .'">' . bp_core_fetch_avatar( array( 'item_id' => $group->id, 'object' => 'group', 'type' => 'full', 'width' => 124, 'height' => 124, 'class' => 'edit-attachment'  ) ). '</a>';
                $output .= '<label for="bp-attachments-edit-component-'. $bp->groups->id .'-'.$group->id.'">';
                $output .= ' <input type="checkbox" value="' . $group->id . '" name="_bp_attachments_edit[component]['. $bp->groups->id .'][]" id="bp-attachments-edit-component-'. $bp->groups->id .'-'.$group->id.'" '. checked( true, in_array( $group->id, $item_ids ), false ).'/>';
                $output .=  esc_html( $group->name ) . '</label></li>';
            }

            $output .= '</ul>';
        }

        return $output;
    }

    /**
     * Let group admins remove an attachment from their group
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function group_actions() {
        if ( empty( buddypress()->attachments->query_loop->attachment ) )
            return;

        if ( bp_is_user() ) {
            echo bp_attachments_get_the_user_actions();
        }

        if ( bp_is_group() ) {
            // set some vars
            $attachment_id = buddypress()->attachments->query_loop->attachment->ID;
            $remove = false;
            $group = groups_get_current_group();

            $url = trailingslashit( bp_get_group_permalink( $group ) . buddypress()->attachments->slug );
            if ( bp_attachments_current_user_can( 'edit_bp_attachments', array( 'component' => buddypress()->groups->id, 'item_id' => $group->id,'attachment_id' => $attachment_id ) ) ) {
                $remove_link = add_query_arg( array( 'attachment' => $attachment_id, 'action' => 'remove' ), $url );
                $remove_link = wp_nonce_url( $remove_link, 'bp_attachments_remove' );
                $remove = '<a href="'. esc_url( $remove_link ) .'" class="button remove-attachment bp-primary-action" id="remove-attachment-'. $attachment_id .' ">' . _x( 'Remove', 'attachments remove link', 'bp-attachments' ) . '</a>';
            }

            // Filter and return the HTML button
            echo apply_filters( 'bp_attachments_get_the_group_actions', $remove );
        }
        
    }

    /**
     * Remove an attachment from a group
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function remove_from_group() {
        $bp = buddypress();

        if ( ! bp_is_current_action( $this->slug ) )
            return;

        if ( ! empty( $_GET['action'] ) && 'remove' == $_GET['action'] && is_numeric( $_GET['attachment'] ) ) {

            check_admin_referer( 'bp_attachments_remove' );

            $group = groups_get_current_group();
            $redirect = trailingslashit( bp_get_group_permalink( $group ) . $bp->attachments->slug );

            $attachment_id = absint( $_GET['attachment'] );

            if ( empty( $attachment_id ) )
                bp_core_redirect( $redirect );

            $cap_args = array( 
                'component'     => $bp->groups->id, 
                'item_id'       => $group->id
            );

            // capability check
            if ( ! bp_attachments_current_user_can( 'edit_bp_attachments', $cap_args ) ) {
                bp_core_add_message( __( 'Error: you are not allowed to remove this attachment.', 'bp-attachments' ), 'error' );
                bp_core_redirect( $redirect );
            } else {
                delete_post_meta( $attachment_id, "_bp_{$bp->groups->id}_id", $group->id );
                bp_core_add_message( __( 'Attachment successfully removed from group.', 'bp-attachments' ) );
                bp_core_redirect( $redirect );
            }
        }
    }

    /**
     * Remove the group avatar creation step
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    public function remove_avatar_step() {
        $bp = buddypress();

        // BP Attachments will handle this !
        unset( $bp->groups->group_creation_steps['group-avatar'] );
    }
    

    /**
     * Loads the BP Attachments navigation if group admin activated it
     * 
     * @package BP Attachments
     * @subpackage Groups
     * @since 1.0.0
     */
    function enable_nav_item() {
        $group_id = bp_get_current_group_id();
        
        if( empty( $group_id ) )
            return false;
        
        if ( $this->use_attachments( $group_id ) )
            return true;
        else
            return false;
    }
}

/**
 * Waits for bp_init hook before loading the group extension
 *
 * Let's make sure the group id is defined before loading our stuff
 * 
 * @package BP Attachments
 * @subpackage Groups
 * @since 1.0.0
 * 
 * @uses bp_register_group_extension() to register the group extension
 */
function bp_attachments_register_group_extension() {
        bp_register_group_extension( 'BP_Attachments_Group' );
}

add_action( 'bp_init', 'bp_attachments_register_group_extension' );

endif ;
