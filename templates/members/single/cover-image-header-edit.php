<?php
/**
 * BP Attachments' `cover-image-header.php` template override to manage member's avatar and cover image.
 *
 * @package \bp-attachments\templates\members\single
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

bp_attachments_before_member_header();
?>
<div id="cover-image-container">
	<div id="header-cover-image"></div>

	<div id="item-header-cover-image">
		<div id="item-header-avatar">
			<?php if ( bp_is_user_change_avatar() ) : ?>
				<div id="bp-avatar-editor"></div>
			<?php else : ?>
				<a href="<?php bp_displayed_user_link(); ?>">

					<?php bp_displayed_user_avatar( 'type=full' ); ?>

				</a>
			<?php endif; ?>
		</div><!-- #item-header-avatar -->

		<div id="item-header-content">

			<?php bp_attachments_member_header_actions(); ?>

			<?php if ( bp_attachments_member_has_meta() ) : ?>
				<div class="item-meta">

					<?php
					bp_attachments_member_meta();
					bp_attachments_member_after_meta();
					?>

				</div><!-- #item-meta -->
			<?php endif; ?>

			<?php bp_attachments_member_type_list(); ?>

		</div><!-- #item-header-content -->

	</div><!-- #item-header-cover-image -->
</div><!-- #cover-image-container -->

<div id="bp-avatar-editor-controls"></div>

<?php
bp_attachments_after_member_header();
