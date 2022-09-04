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
<div class="<?php bp_attachments_medium_classes(); ?>">
	<?php bp_attachments_render_medium(); ?>
</div>

<div class="wp-embed-excerpt">
	<p><strong><?php bp_attachments_medium_title(); ?></strong></p>

	<?php if ( bp_attachments_medium_has_description() ) : ?>
		<p><?php bp_attachments_medium_description(); ?></p>
	<?php endif; ?>
</div>
