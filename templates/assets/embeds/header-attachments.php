<?php
/**
 * BP Attachments single embed header template.
 *
 * @package \bp-attachments\templates\attachments\assets\embeds\header-attachments
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="bp-embed-header">
	<div class="bp-embed-header-avatar">
		<a href="<?php bp_attachments_medium_owner_url(); ?>">
			<?php bp_attachments_medium_owner_avatar(); ?>
		</a>
	</div>

	<p class="bp-embed-header-action">
		<?php bp_attachments_medium_action(); ?>
	</p>

	<p class="bp-embed-header-meta">
		<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) : ?>
			<span class="bp-embed-mentionname">@<?php bp_attachments_medium_owner_mentionname(); ?> &middot; </span>
		<?php endif; ?>

		<span class="bp-embed-timestamp"><a href="<?php bp_attachments_medium_view_url(); ?>"><?php bp_attachments_medium_modified_date(); ?></a></span>
	</p>
</div>
