<?php
/**
 * BP Attachments' `change-avatar.php` template override to manage member's avatar.
 *
 * @package \bp-attachments\templates\members\single\profile
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 class="screen-heading change-avatar-screen"><?php esc_html_e( 'Change Profile Photo', 'bp-attachments' ); ?></h2>

<?php bp_attachments_before_edit_avatar_content(); ?>

<?php if ( bp_attachments_is_avatar_uploads_enabled() ) : ?>

	<p class="bp-feedback info">
		<span class="bp-icon" aria-hidden="true"></span>
		<span class="bp-help-text">
			<?php
			printf(
				/* Translators: %s is used to output the link to the Gravatar site */
				esc_html__( 'Your profile photo will be used on your profile and throughout the site. If there is a %s associated with your account email we will use that, or you can upload an image from your computer.', 'bp-attachments' ),
				/* Translators: Url to the Gravatar site, you can use the one for your country eg: https://fr.gravatar.com for French translation */
				'<a href="' . esc_url( __( 'https://gravatar.com', 'bp-attachments' ) ) . '">Gravatar</a>'
			);
			?>
		</span>
	</p>

	<?php if ( bp_get_user_has_avatar() ) : ?>
		<p><?php esc_html_e( 'If you’d like to delete your current profile photo but not upload a new one, please use the delete profile photo button.', 'bp-attachments' ); ?></p>
		<p><a class="button edit" href="<?php echo esc_url( bp_get_avatar_delete_link() ); ?>"><?php esc_html_e( 'Delete My Profile Photo', 'bp-attachments' ); ?></a></p>
	<?php endif; ?>

	<div id="bp-attachments-avatar-editor"></div>

<?php else : ?>

	<p class="bp-help-text">
		<?php
		printf(
			/* Translators: %s is used to output the link to the Gravatar site */
			esc_html__( 'Your profile photo will be used on your profile and throughout the site. To change your profile photo, create an account with %s using the same email address as you used to register with this site.', 'bp-attachments' ),
			/* Translators: Url to the Gravatar site, you can use the one for your country eg: https://fr.gravatar.com for French translation */
			'<a href="' . esc_url( __( 'https://gravatar.com', 'bp-attachments' ) ) . '">Gravatar</a>'
		);
		?>
	</p>

<?php endif; ?>

<?php
bp_attachments_after_edit_avatar_content();
