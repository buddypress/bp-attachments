<?php
/**
 * BP Attachments single download template.
 *
 * @package \bp-attachments\templates\attachments\download
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="bp-attachments-medium">
	<p><?php esc_html_e( 'You need to log in to be able to download media.', 'bp-attachments' ); ?></p>
</div>
