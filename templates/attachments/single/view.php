<?php
/**
 * BP Attachments single view template.
 *
 * @package \bp-attachments\templates\attachments\view
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="bp-attachments-medium">
	<?php bp_get_template_part( 'attachments/single/parts/single', bp_attachments_get_medium_type() ); ?>

	<?php if ( bp_attachments_medium_has_description() ) :?>
		<div class="bp-attachments-medium-description">
			<?php bp_attachments_medium_description(); ?>
		</div>
	<?php endif; ?>

	<div class="wp-block-post-author">
		<div class="wp-block-post-author__avatar">
			<?php bp_attachments_medium_owner_avatar(); ?>
		</div>
		<div class="wp-block-post-author__content">
			<p class="wp-block-post-author__name"><?php esc_html_e( 'Shared by: ', 'bp-attachments' ); ?>
				<a href="<?php bp_attachments_medium_owner_url(); ?>">
					<?php bp_attachments_medium_owner_displayname(); ?>
				</a>
			</p>

			<?php if ( bp_attachments_medium_owner_has_description() ) :?>
				<p class="wp-block-post-author__bio"><?php bp_attachments_medium_owner_description(); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>
