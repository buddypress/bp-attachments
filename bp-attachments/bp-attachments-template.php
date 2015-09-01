<?php
/**
 * BP Attachments Template.
 *
 * Template functions inspired by the notifications component
 *
 * @package BP Attachments
 * @subpackage Template
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Main Loop *****************************************************************/

/**
 * The main Attachments template loop class.
 *
 * @since BP Attachments (1.0.0)
 */
class BP_Attachments_Template {

	/**
	 * The loop iterator.
	 *
	 * @access public
	 * @var int
	 */
	public $current_attachment = -1;

	/**
	 * The number of Attachments returned by the paged query.
	 *
	 * @access public
	 * @var int
	 */
	public $current_attachment_count;

	/**
	 * Total number of Attachments matching the query.
	 *
	 * @access public
	 * @var int
	 */
	public $total_attachment_count;

	/**
	 * Array of Attachments located by the query.
	 *
	 * @access public
	 * @var array
	 */
	public $attachments;

	/**
	 * The Attachment object currently being iterated on.
	 *
	 * @access public
	 * @var object
	 */
	public $attachment;

	/**
	 * A flag for whether the loop is currently being iterated.
	 *
	 * @access public
	 * @var bool
	 */
	public $in_the_loop;

	/**
	 * Array of item ids to filter on.
	 *
	 * @access public
	 * @var array
	 */
	public $item_ids;

	/**
	 * Component slug.
	 *
	 * @access public
	 * @var string
	 */
	public $component;

	/**
	 * Include private attachments ?
	 *
	 * @access public
	 * @var bool
	 */
	public $show_private;

	/**
	 * The ID of the user to whom the displayed Attachments belong.
	 *
	 * @access public
	 * @var int
	 */
	public $user_id;

	/**
	 * The page number being requested.
	 *
	 * @access public
	 * @var int
	 */
	public $pag_page;

	/**
	 * The number of items to display per page of results.
	 *
	 * @access public
	 * @var int
	 */
	public $pag_num;

	/**
	 * An HTML string containing pagination links.
	 *
	 * @access public
	 * @var string
	 */
	public $pag_links;

	/**
	 * A string to match against.
	 *
	 * @access public
	 * @var string
	 */
	public $search_terms;

	/**
	 * comma separated list of attachment ids or array.
	 *
	 * @access public
	 * @var array|string
	 */
	public $exclude;

	/**
	 * A database column to order the results by.
	 *
	 * @access public
	 * @var string
	 */
	public $order_by;

	/**
	 * The direction to sort the results (ASC or DESC)
	 *
	 * @access public
	 * @var string
	 */
	public $sort_order;

	/**
	 * Constructor method.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function __construct( $args = array() ) {

		$defaults = array(
			'item_ids'        => array(), // one or more item ids regarding the component (eg group_ids, message_ids )
			'component'	      => false,   // groups / messages / blogs / xprofile...
			'show_private'    => false,   // wether to include private attachment
			'user_id'	      => false,   // the author id of the attachment
			'per_page'	      => 20,
			'page'		      => 1,
			'search'          => false,
			'exclude'		  => false,   // comma separated list or array of attachment ids.
			'orderby' 		  => 'ID',
			'order'           => 'DESC',
			'page_arg'        => 'apage',
		);

		// Parse arguments
		$r = bp_parse_args( $args, $defaults, 'attachments_template_args' );

		// Set which pagination page
		if ( isset( $_GET[ $r['page_arg'] ] ) ) {
			$pag_page = intval( $_GET[ $r['page_arg'] ] );
		} else {
			$pag_page = $r['page'];
		}

		// Setup variables
		$this->pag_page     = $pag_page;
		$this->pag_num      = $r['per_page'];
		$this->item_ids     = $r['item_ids'];
		$this->component	= $r['component'];
		$this->show_private = $r['show_private'];
		$this->user_id      = $r['user_id'];
		$this->search_terms = $r['search'];
		$this->exclude      = $r['exclude'];
		$this->page_arg     = $r['page_arg'];
		$this->order_by     = $r['orderby'];
		$this->sort_order   = $r['order'];

		// Get the attachments
		$attachments      = bp_attachments_get_attachments( array(
			'item_ids'        => $this->item_ids,
			'component'	      => $this->component,
			'show_private'    => $this->show_private,
			'user_id'	      => $this->user_id,
			'per_page'	      => $this->pag_num,
			'page'		      => $this->pag_page,
			'search'          => $this->search_terms,
			'exclude'		  => $this->exclude,
			'orderby' 		  => $this->order_by,
			'order'           => $this->sort_order,
		) );

		// Setup the attachments to loop through
		$this->attachments            = $attachments['attachments'];
		$this->total_attachment_count = $attachments['total'];

		if ( empty( $this->attachments ) ) {
			$this->attachment_count       = 0;
			$this->total_attachment_count = 0;
		} else {
			$this->attachment_count = count( $this->attachments );
		}

		if ( (int) $this->total_attachment_count && (int) $this->pag_num ) {
			$this->pag_links = paginate_links( array(
				'base'      => add_query_arg( $this->page_arg, '%#%' ),
				'format'    => '',
				'total'     => ceil( (int) $this->total_attachment_count / (int) $this->pag_num ),
				'current'   => $this->pag_page,
				'prev_text' => _x( '&larr;', 'Attachments pagination previous text', 'bp-attachments' ),
				'next_text' => _x( '&rarr;', 'Attachments pagination next text',     'bp-attachments' ),
				'mid_size'  => 1,
			) );

			// Remove first page from pagination
			$this->pag_links = str_replace( '?'      . $r['page_arg'] . '=1', '', $this->pag_links );
			$this->pag_links = str_replace( '&#038;' . $r['page_arg'] . '=1', '', $this->pag_links );
		}
	}

	/**
	 * Whether there are Attachments available in the loop.
	 *
	 * @since BP Attachments (1.0.0)
	 *
	 * @see bp_attachments_has_attachments()
	 *
	 * @return bool True if there are items in the loop, otherwise false.
	 */
	public function has_attachments() {
		if ( $this->attachment_count ) {
			return true;
		}

		return false;
	}

	/**
	 * Set up the next Attachment and iterate index.
	 *
	 * @since BP Attachments (1.0.0)
	 *
	 * @return object The next Attachment to iterate over.
	 */
	public function next_attachment() {

		$this->current_attachment++;

		$this->attachment = $this->attachments[ $this->current_attachment ];

		return $this->attachment;
	}

	/**
	 * Rewind the Attachments and reset Attachment index.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function rewind_attachments() {

		$this->current_attachment = -1;

		if ( $this->attachment_count > 0 ) {
			$this->attachment = $this->attachments[0];
		}
	}

	/**
	 * Whether there are Attachments left in the loop to iterate over.
	 *
	 * @since BP Attachments (1.0.0)
	 *
	 * @return bool True if there are more Attachments to show,
	 *         otherwise false.
	 */
	public function attachments() {

		if ( $this->current_attachment + 1 < $this->attachment_count ) {
			return true;

		} elseif ( $this->current_attachment + 1 == $this->attachment_count ) {
			do_action( 'attachments_loop_end');

			$this->rewind_attachments();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Set up the current Attachment inside the loop.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	public function the_attachment() {
		$this->in_the_loop  = true;
		$this->attachment = $this->next_attachment();

		// loop has just started
		if ( 0 === $this->current_attachment ) {
			do_action( 'attachments_loop_start' );
		}
	}
}

/** The Loop ******************************************************************/

/**
 * Initialize the Attachments loop.
 *
 * Based on the $args passed, bp_attachments_has_attachments() populates
 * buddypress()->attachments->query_loop global, enabling the use of BP
 * templates and template functions to display a list of attachments.
 *
 * @since BP Attachments (1.0.0)
 *
 * @param array $args {
 *     Arguments for limiting the contents of the attachments loop. To be
 *     passed as an associative array.
 *     @type array $item_ids single component item ids such as group ids
 *     @type string $component Whether to limit query to a given component
 *     @type bool $show_private whether to load the private attachments (when available)
 *     @type $user_id wether to filter on a specific user
 *     @type int $per_page Number of items to display on a page. Default: 20.
 *     @type int $page The page of notifications being fetched. Default: 1.
 *     @type string $search_terms Optional. Term to match against
 *           attachment title or description.
 *     @type string $exclude comma separated list of attachment ids to exclude
 *     @type string $page_arg URL argument to use for pagination.
 *           Default: 'apage'.
 * }
 */
function bp_attachments_has_attachments( $args = array() ) {
	$bp = buddypress();

	// init vars
	$user_id = false;
	$item_ids = array();
	$show_private = false;
	$component = false;

	// Get the user ID
	if ( bp_is_user() ) {
		$user_id = bp_displayed_user_id();
		$show_private = bp_is_my_profile();
	}

	// Group filtering
	if ( ! empty( $bp->groups->current_group ) ) {
		$item_ids = array( $bp->groups->current_group->id );
		$component = $bp->groups->id;
	}

	// Parse the args
	$r = bp_parse_args( $args, array(
		'item_ids'     => $item_ids,
		'component'    => $component,
		'show_private' => $show_private,
		'user_id'      => $user_id,
		'per_page'     => 20,
		'page'         => 1,
		'search_terms' => isset( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : '',
		'exclude'      => false,
		'orderby'      => 'modified',
		'order'        => 'DESC',
		'page_arg'     => 'apage',
	), 'attachments_has_args' );

	// Get the attachments
	$query_loop = new BP_Attachments_Template( $r );

	// Setup the global query loop
	buddypress()->attachments->query_loop = $query_loop;

	return apply_filters( 'bp_attachments_has_attachments', $query_loop->has_attachments(), $query_loop );
}

/**
 * Get the attachments returned by the template loop.
 *
 * @since BP Attachments (1.0.0)
 *
 * @return array List of attachments.
 */
function bp_attachments_the_attachments() {
	return buddypress()->attachments->query_loop->attachments();
}

/**
 * Get the current attachment object in the loop.
 *
 * @since BP Attachments (1.0.0)
 *
 * @return object The current attachment within the loop.
 */
function bp_attachments_the_attachment() {
	return buddypress()->attachments->query_loop->the_attachment();
}

/** Loop Output ***************************************************************/

/**
 * Output the pagination count for the current attachment loop.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_pagination_count() {
	echo bp_attachments_get_pagination_count();
}
	/**
	 * Return the pagination count for the current attachment loop.
	 *
	 * @since BP Attachments (1.0.0)
	 *
	 * @return string HTML for the pagination count.
	 */
	function bp_attachments_get_pagination_count() {
		$query_loop = buddypress()->attachments->query_loop;
		$start_num  = intval( ( $query_loop->pag_page - 1 ) * $query_loop->pag_num ) + 1;
		$from_num   = bp_core_number_format( $start_num );
		$to_num     = bp_core_number_format( ( $start_num + ( $query_loop->pag_num - 1 ) > $query_loop->total_attachment_count ) ? $query_loop->total_attachment_count : $start_num + ( $query_loop->pag_num - 1 ) );
		$total      = bp_core_number_format( $query_loop->total_attachment_count );
		$pag        = sprintf( _n( 'Viewing %1$s to %2$s (of %3$s attachment)', 'Viewing %1$s to %2$s (of %3$s attachments)', $total, 'bp-attachments' ), $from_num, $to_num, $total );

		return apply_filters( 'bp_attachments_get_pagination_count', $pag );
	}

/**
 * Output the pagination links for the current attachment loop.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_pagination_links() {
	echo bp_attachments_get_pagination_links();
}
	/**
	 * Return the pagination links for the current attachment loop.
	 *
	 * @since BP Attachments (1.0.0)
	 *
	 * @return string HTML for the pagination links.
	 */
	function bp_attachments_get_pagination_links() {
		return apply_filters( 'bp_get_notifications_pagination_links', buddypress()->attachments->query_loop->pag_links );
	}

/**
 * Output the ID of the attachment currently being iterated on.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_the_attachment_id() {
	echo bp_attachments_get_the_attachment_id();
}
	/**
	 * Return the ID of the attachment currently being iterated on.
	 *
	 * @since BP Attachments (1.0.0)
	 *
	 * @return int ID of the current attachment.
	 */
	function bp_attachments_get_the_attachment_id() {
		return apply_filters( 'bp_attachments_get_the_attachment_id', buddypress()->attachments->query_loop->attachment->ID );
	}

/**
 * Output the class of the attachment row.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_class() {
	echo bp_attachments_get_class();
}

	/**
	 * Return the class of the attachment row.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	function bp_attachments_get_class() {
		$attachment = buddypress()->attachments->query_loop->attachment;
		$classes      = array();

		// Attachment status - inherit, private.
		$classes[] = esc_attr( $attachment->post_status );
		$classes[] = str_replace( '/', ' ', $attachment->post_mime_type );

		$classes = apply_filters( 'bp_attachments_get_class', $classes );
		$classes = array_merge( $classes, array() );
		$retval = 'class="' . join( ' ', $classes ) . '"';

		return $retval;
	}

/**
 * Output the thumbnail of the attachment.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_thumbnail() {
	echo bp_attachments_get_thumbnail();
}
	/**
	 * Return the thumbnail of the attachment.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	function bp_attachments_get_thumbnail() {
		add_filter( 'image_downsize', 'bp_attachments_image_downsize', 10, 3 );
		$thumbnail = wp_get_attachment_image( bp_attachments_get_the_attachment_id(), array( 50, 50 ), true, array( 'class' => 'avatar' ) );
		remove_filter( 'image_downsize', 'bp_attachments_image_downsize', 10, 3 );

		return apply_filters( 'bp_attachments_get_thumbnail', $thumbnail );
	}

/**
 * Output the title of the attachment.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_the_title() {
	echo bp_attachments_get_the_title();
}

	/**
	 * Return the title of the attachment.
	 *
	 * @since BP Attachments (1.0.0)
 	 */
	function bp_attachments_get_the_title() {
		return apply_filters( 'bp_attachments_get_the_title', buddypress()->attachments->query_loop->attachment->post_title );
	}

/**
 * Output the link of the attachment.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_the_link() {
	echo bp_attachments_get_the_link();
}

	/**
	 * Return the link of the attachment.
	 *
	 * @since BP Attachments (1.0.0)
 	 */
	function bp_attachments_get_the_link() {
		return apply_filters( 'bp_attachments_get_the_link', bp_attachments_get_attachment_url( buddypress()->attachments->query_loop->attachment->ID ) );
	}

/**
 * Output the date of the attachment.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_last_modified() {
	echo bp_attachments_get_last_modified();
}

	/**
	 * Return the date of the attachment.
	 *
	 * @since BP Attachments (1.0.0)
 	 */
	function bp_attachments_get_last_modified() {
		$last_modified = bp_core_time_since( buddypress()->attachments->query_loop->attachment->post_modified_gmt );
		return apply_filters( 'bp_attachments_get_last_modified', sprintf( __( 'Modified %s', 'bp-attachments' ), $last_modified ) );
	}

/**
 * Check whether the attachment has a description.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_has_description() {
	return ! empty( buddypress()->attachments->query_loop->attachment->post_content );
}

/**
 * Output the description of the attachment.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_the_excerpt() {
	echo bp_attachments_get_the_excerpt();
}

	/**
	 * Return the description of the attachment.
	 *
	 * @since BP Attachments (1.0.0)
 	 */
	function bp_attachments_get_the_excerpt() {
		$excerpt = bp_create_excerpt( buddypress()->attachments->query_loop->attachment->post_content );

		return apply_filters( 'bp_attachments_get_the_excerpt', $excerpt );
	}

/**
 * Output the status (inherit/private) of the attachment.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_the_status() {
	echo bp_attachments_get_the_status();
}

	/**
	 * Return the status (inherit/private) of the attachment.
	 *
	 * @since BP Attachments (1.0.0)
 	 */
	function bp_attachments_get_the_status() {
		$status = __( 'Public', 'bp-attachments' );

		if ( 'private' == buddypress()->attachments->query_loop->attachment->post_status )
			$status = __( 'Private', 'bp-attachments' );

		return apply_filters( 'bp_attachments_get_the_status', $status );
	}

/**
 * Output the mime type of the attachment.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_the_mime_type() {
	echo bp_attachments_get_the_mime_type();
}

	/**
	 * Return the mime type of the attachment.
	 *
	 * @since BP Attachments (1.0.0)
 	 */
	function bp_attachments_get_the_mime_type() {
		return apply_filters( 'bp_attachments_get_the_mime_type', strtoupper( preg_replace( '/(\w+)\//i', '', buddypress()->attachments->query_loop->attachment->post_mime_type ) ) );
	}

/**
 * Output the user's action for the attachment.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_the_user_actions() {
	echo bp_attachments_get_the_user_actions();
}
add_action( 'bp_attachments_members_actions', 'bp_attachments_the_user_actions' );

	/**
	 * Return the user's action for the attachment.
	 *
	 * @since BP Attachments (1.0.0)
 	 */
	function bp_attachments_get_the_user_actions() {
		$attachment_id = buddypress()->attachments->query_loop->attachment->ID;
		$user_id = buddypress()->attachments->query_loop->attachment->post_author;

		$edit = $delete = false;

		if ( bp_attachments_loggedin_user_can( 'edit_bp_attachment', $attachment_id ) ) {
			$edit_link = bp_attachments_get_edit_link( $attachment_id, $user_id );
			$edit = '<a href="'. esc_url( $edit_link ) .'" class="button edit-attachment bp-primary-action" id="edit-attachment-'. $attachment_id .' ">' . _x( 'Edit', 'attachments edit link', 'bp-attachments' ) . '</a>';
		}

		if ( bp_attachments_loggedin_user_can( 'delete_bp_attachment', $attachment_id ) ) {
			$delete_link = bp_attachments_get_delete_link( $attachment_id, $user_id );
			$delete_link = wp_nonce_url( $delete_link, 'bp_attachments_delete' );
			$delete = '<a href="'. esc_url( $delete_link ) .'" class="button delete-attachment bp-primary-action" id="delete-attachment-'. $attachment_id .' ">' . _x( 'Delete', 'attachments delete link', 'bp-attachments' ) . '</a>';
		}

		// Filter and return the HTML button
		return apply_filters( 'bp_attachments_get_the_user_actions', $edit . $delete, $edit, $delete );
	}


/** Edit Output ***************************************************************/

/**
 * Output the edit form action for the attachment.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_single_the_form_action() {
	return trailingslashit( bp_core_get_user_domain( buddypress()->attachments->attachment->user_id ) . buddypress()->attachments->slug );
}

/**
 * Output the ID of the attachment being edited.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_single_the_id() {
	echo bp_attachments_single_get_the_id();
}

	/**
	 * Return the ID of the attachment being edited.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	function bp_attachments_single_get_the_id(){
		return apply_filters( 'bp_attachments_single_get_the_id', buddypress()->attachments->attachment->id );
	}

/**
 * Output the title of the attachment being edited.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_single_the_title() {
	echo bp_attachments_single_get_the_title();
}

	/**
	 * Return the title of the attachment being edited.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	function bp_attachments_single_get_the_title() {
		return apply_filters( 'bp_attachments_single_get_the_title', buddypress()->attachments->attachment->title );
	}

/**
 * Output the description of the attachment being edited.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_single_the_description() {
	echo bp_attachments_single_get_the_description();
}

	/**
	 * Return the description of the attachment being edited.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	function bp_attachments_single_get_the_description() {
		return apply_filters( 'bp_attachments_single_get_the_description', buddypress()->attachments->attachment->description );
	}

/**
 * Output the list of components single items the attachment is linked to.
 *
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_single_attached_to() {
	echo bp_attachments_single_get_attached_to();
}

	/**
	 * Return the list of components single items the attachment is linked to.
	 *
	 * @since BP Attachments (1.0.0)
	 */
	function bp_attachments_single_get_attached_to() {
		$output = '';
		$components = get_terms( 'bp_component', array( 'hide_empty' => 0, 'fields' => 'all' ) );

		if ( empty( $components ) )
			return '<div id="message" class="info"><p>' . __( 'No components are available', 'bp-attachments' ) . '</p></div>';

		// builds an id lists of terms
		$terms = wp_list_pluck( $components, 'slug' );

		foreach( $components as $component ) {

			$output .= '<h5>' . $component->name .'</h5>';

			// BP Component can filter this to add their items.
			$output .= apply_filters( "bp_attachments_edit_attached_to_{$component->slug}", '', buddypress()->attachments->attachment );

		}

		$output .= '<input type="hidden" name="_bp_attachments_edit[terms]" value="' . implode( ',', $terms ) .'">';

		return apply_filters( 'bp_attachments_single_get_attached_to', $output, buddypress()->attachments->attachment );
	}

/** Activity Attachments ******************************************************/

// Make sure there won't be any fatals if ticket #6569 makes it in trunk
if ( ! function_exists( 'bp_activity_whats_new_placeholder' ) ) :
function bp_activity_whats_new_placeholder() {
	echo bp_get_activity_whats_new_placeholder();
}
endif;

	// Make sure there won't be any fatals if ticket #6569 makes it in trunk
	if ( ! function_exists( 'bp_get_activity_whats_new_placeholder' ) ) :
	function bp_get_activity_whats_new_placeholder() {
		$placeholder = sprintf( __( "What's new, %s?", 'bp-attachments' ), bp_get_user_firstname( bp_get_loggedin_user_fullname() ) );

		if ( bp_is_group() ) {
			$placeholder = sprintf( __( "What's new in %s, %s?", 'bp-attachments' ), bp_get_group_name(), bp_get_user_firstname( bp_get_loggedin_user_fullname() ) );
		}

		return apply_filters( 'bp_get_activity_whats_new_placeholder', $placeholder );
	}
	endif;

function bp_attachments_activity_post_form_attachments() {
	// Enqueue needed scripts
	bp_attachments_enqueue_scripts( 'BP_Attachments_Attachment' );

	bp_attachments_get_template_part( 'activity/index' );
}
add_action( 'bp_activity_post_form_before_options', 'bp_attachments_activity_post_form_attachments' );

/**
 * @todo cache the attachments !!!!!!!!!!!!!!!!
 */
function bp_attachments_activity_append_attachments() {
	global $activities_template;

	if ( ! isset( $activities_template->activity->id ) ) {
		return;
	}

	// Get the attachment ids
	$attachments = (array) bp_activity_get_meta( $activities_template->activity->id, '_bp_attachments_attachment_ids', false );

	if ( empty( $attachments ) ) {
		return;
	}

	// Init the output and the more link
	$output     = '';
	$more_link  = '';

	add_filter( 'image_downsize', 'bp_attachments_image_downsize', 10, 3 );

	if ( 1 === count( $attachments ) ) {
		$attachment_id = reset( $attachments );
		$output = wp_get_attachment_image( $attachment_id, 'full', false, array( 'class' => 'bp-attachments-full' ) );
	} else {
		$size = 'bp_attachments_avatar';
		$more = __( 'View full size images', 'bp-attachments' );

		if ( bp_is_single_activity() ) {
			$size = 'full';
		} else {
			// More than 3, pick 3 random ones
			if ( 3 < count( $attachments ) ) {
				$attachments = array_intersect_key( $attachments, array_flip( array_rand( $attachments, 3 ) ) );
				$more        = __( 'View all images', 'bp-attachments' );
			}

			$more_link = sprintf( '<p><a href="%1$s" title="%2$s">%3$s</a></p>',
				esc_url( bp_activity_get_permalink( $activities_template->activity->id, $activities_template->activity ) ),
				esc_attr( $more ),
				esc_html( $more )
			);
		}

		foreach( $attachments as $attachment_id ) {
			$output .= wp_get_attachment_image( $attachment_id, $size, false, array( 'class' => 'bp-attachments-' . $size ) );
		}
	}

	remove_filter( 'image_downsize', 'bp_attachments_image_downsize', 10, 3 );

	echo apply_filters( 'bp_attachments_activity_append_attachments', $output . $more_link, $activities_template->activity, $attachments );
}
add_action( 'bp_activity_entry_content', 'bp_attachments_activity_append_attachments' );
