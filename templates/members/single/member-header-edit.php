<?php
/**
 * BP Attachments' `member-header.php` template override to manage member's avatar.
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
			bp_attachments_member_header_buttons( array(), 'legacy' );
			bp_attachments_member_after_meta();
			?>

		</div><!-- #item-meta -->
	<?php endif; ?>

	<?php bp_attachments_member_type_list(); ?>

	<?php
	bp_attachments_member_header_buttons(
		array(
			'container_classes' => array( 'member-header-actions' ),
		),
		'nouveau'
	);
	?>
</div><!-- #item-header-content -->

<div id="bp-avatar-editor-controls"></div>

<?php
bp_attachments_after_member_header();
