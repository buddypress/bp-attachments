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
	<p><?php esc_html_e( 'You are not allowed to download this media', 'bp-attachments' ); ?></p>
	<p>This template will be used to eventually output a password input.</p>
</div>
