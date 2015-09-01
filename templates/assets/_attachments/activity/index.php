<?php
/**
 * BuddyPress Attachments activity template
 *
 * @since 1.1.0
 *
 * @package BP Attachments
 */
?>
<div id="bp-activity-attachments">

	<a href="#" title="<?php esc_attr_e( 'Add Attachment', 'bp-attachments' );?>" id="bp-attachments-activity-btn" class="button">
		<span class="bp-screen-reader-text"><?php echo esc_html_e( 'Attachments', 'bp-attachments' ); ?>
	</a>

	<?php bp_attachments_get_template_part( 'files/index' ); ?>

</div>

<?php do_action( 'bp_attachments_activity_template' ); ?>
