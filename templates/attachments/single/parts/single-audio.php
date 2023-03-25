<?php
/**
 * BP Attachments single view template for an audio.
 *
 * @package \bp-attachments\templates\attachments\single\parts\single-audio
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<figure class="bp-attachment-audio">
	<?php bp_attachments_render_medium(); ?>
	<figcaption class="wp-element-caption"><?php bp_attachments_medium_fallback_text(); ?></figcaption>
</figure>
<dl class="bp-attachment-meta horizontal">
	<dt><?php esc_html_e( 'Lastly edited on:', 'bp-attachments' ); ?></dt>
	<dd><?php bp_attachments_medium_modified_date(); ?></dd>
	<dt><?php esc_html_e( 'Audio type:', 'bp-attachments' ); ?></dt>
	<dd><?php bp_attachments_medium_mime_type(); ?></dd>
	<dt><?php esc_html_e( 'Audio size:', 'bp-attachments' ); ?></dt>
	<dd><?php bp_attachments_medium_size(); ?></dd>
</dl>
