<?php
/**
 * BP Attachments single view template for an image.
 *
 * @package \bp-attachments\templates\attachments\single-image
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<figure class="bp-attachment-image">
	<a href="<?php bp_attachments_medium_download_url(); ?>">
		<?php bp_attachments_render_medium(); ?>
	</a>
</figure>
