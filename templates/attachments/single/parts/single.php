<?php
/**
 * BP Attachments single view template for an image.
 *
 * @package \bp-attachments\templates\attachments\view-image
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="bp-attachment-file">
	<div class="bp-attachment-file-icon">
		<a href="<?php bp_attachments_medium_download_url(); ?>">
			<?php bp_attachments_render_medium(); ?>
		</a>
	</div>

	<dl class="bp-attachment-meta">
		<dt><?php esc_html_e( 'Lastly edited on:', 'bp-attachments' ); ?></dt>
		<dd><?php bp_attachments_medium_modified_date(); ?></dd>
		<dt><?php esc_html_e( 'File type:', 'bp-attachments' ); ?></dt>
		<dd><?php bp_attachments_medium_mime_type(); ?></dd>
		<dt><?php esc_html_e( 'File size:', 'bp-attachments' ); ?></dt>
		<dd><?php bp_attachments_medium_size(); ?></dd>
	</dl>
</div>
