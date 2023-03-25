<?php
/**
 * BP Attachments single view template for a folder.
 *
 * @package \bp-attachments\templates\attachments\single\parts\single-folder
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="bp-media-list" class="bp-attachments-media-list"></div>

<?php bp_attachments_get_javascript_template( 'file-medium' );
