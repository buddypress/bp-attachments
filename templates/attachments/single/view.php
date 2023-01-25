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
	<?php if ( bp_attachments_medium_can_view() ) : ?>
		<?php bp_get_template_part( 'attachments/single/parts/single', bp_attachments_get_medium_part() ); ?>

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
	<?php elseif ( ! is_user_logged_in() ) : ?>
		<p class="info bp-feedback">
			<span class="bp-icon" aria-hidden="true"></span>
			<span class="bp-help-text">
				<?php
				printf(
					/* translators: %s is the WP login link */
					esc_html__( 'You need to %s to be able to view this media.', 'bp-attachments' ),
					sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( bp_attachments_medium_get_login_url() ),
						esc_html__( 'log in', 'bp-attachments' )
					)
				);
				?>
			</span>
		</p>
	<?php else : ?>
		<p class="info bp-feedback">
			<span class="bp-icon" aria-hidden="true"></span>
			<span class="bp-help-text"><?php esc_html_e( 'This media is private and its owner has not shared it with you.', 'bp-attachments' ); ?></span>
		</p>
	<?php endif; ?>
</div>
