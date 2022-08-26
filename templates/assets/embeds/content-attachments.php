<?php
/**
 * BP Attachments single embed content template.
 *
 * @package \bp-attachments\templates\attachments\assets\embeds\content-attachments
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="<?php bp_attachment_media_classes(); ?>">
	<?php bp_attachments_render_medium(); ?>
</div>

<div class="wp-embed-excerpt">
	<p><strong><?php bp_attachments_media_title(); ?></strong></p>

	<?php if ( bp_attachments_media_has_description() ) : ?>
		<p><?php bp_attachments_media_description(); ?></p>
	<?php endif; ?>
</div>
