<?php
/**
 * BP Attachments single view template for an image.
 *
 * @package \bp-attachments\templates\attachments\single\parts\single-image
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
<dl class="bp-attachment-meta horizontal">
	<dt><?php esc_html_e( 'Lastly edited on:', 'bp-attachments' ); ?></dt>
	<dd><?php bp_attachments_medium_modified_date(); ?></dd>
	<dt><?php esc_html_e( 'Image type:', 'bp-attachments' ); ?></dt>
	<dd><?php bp_attachments_medium_mime_type(); ?></dd>
	<dt><?php esc_html_e( 'Image size:', 'bp-attachments' ); ?></dt>
	<dd><?php bp_attachments_medium_size(); ?></dd>
</dl>
