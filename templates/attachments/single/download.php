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
	<?php if ( ! is_user_logged_in() ) : ?>
		<p class="info bp-feedback">
			<span class="bp-icon" aria-hidden="true"></span>
			<span class="bp-help-text">
				<?php
					printf(
						/* translators: %s is the WP login link */
						esc_html__( 'You need to %s to be able to download this media.', 'bp-attachments' ),
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
