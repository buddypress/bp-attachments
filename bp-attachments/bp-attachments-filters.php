<?php
/**
 * BP Attachments Filters.
 *
 * @package \bp-attachments\bp-attachments-filters
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'bp_core_get_components', 'bp_attachments_get_component_info' );
