<?php
/**
 * BP Attachments directory main template.
 *
 * @package \bp-attachments\templates\attachments\index
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

bp_attachments_tracking_output_directory_nav();
?>

<div id="bp-media-directory" class="bp-attachments-media-list"></div>

<?php bp_attachments_get_javascript_template( 'media-directory' );
