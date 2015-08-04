<?php
/**
 * Attachment capacity class.
 *
 * @package BP Attachments
 * @subpackage Classes
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Attachments Capability Class.
 *
 * This class is used to create the
 * arguments to check in the capability
 * mapping filter
 *
 * @since BP Attachments (1.0.0)
 */
class BP_Attachments_Can {
	public function __construct( $args = array() ) {
		if ( empty( $args ) )
			return false;

		$r = bp_parse_args( $args, array(
			'component'     => '',
			'item_id'       => '',
			'attachment_id' => ''
		), 'attachments_can_args' );

		foreach( $r as $key => $value ) {
			$this->{$key} = $value;
		}
	}
}
