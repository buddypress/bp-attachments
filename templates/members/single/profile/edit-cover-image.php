<?php
/**
 * BP Attachments' `change-cover-image.php` template override to manage member's cover image.
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
<h2 class="screen-heading change-cover-image-screen"><?php esc_html_e( 'Change Cover Image', 'bp-attachments' ); ?></h2>

<?php bp_attachments_member_before_edit_cover_image(); ?>

<p class="info bp-feedback">
	<span class="bp-icon" aria-hidden="true"></span>
	<span class="bp-help-text"><?php esc_html_e( 'Your Cover Image will be used to customize the header of your profile.', 'bp-attachments' ); ?></span>
</p>

<?php
bp_attachments_member_after_edit_cover_image();
