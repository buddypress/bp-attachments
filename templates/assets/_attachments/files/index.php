<?php
/**
 * BuddyPress Attachments main template
 *
 * @since 1.1.0
 *
 * @package BP Attachments
 */
?>
<div id="bp-attachments">
	<div class="bp-attachments-uploader"></div>
	<div class="bp-attachments-uploader-status"></div>
	<div class="bp-attachments-browser"></div>

	<?php bp_attachments_get_template_part( 'uploader' ); ?>

	<?php bp_attachments_get_template_part( 'files/browser' ); ?>
</div>

<?php do_action( 'bp_attachments_main_template' ); ?>
