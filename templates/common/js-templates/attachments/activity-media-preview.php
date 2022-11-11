<?php
/**
 * BP Attachments Activity Media preview JavaScript Template.
 *
 * @package \templates\buddypress\common\js-templates\attachments\activity-media-preview
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="html/template" id="tmpl-bp-media-preview">
	<# if ( !! data.links && !! data.links.view ) { #>
		<input type="hidden" id="bp-attachments-activity-medium-url" name="_bp_attachments_activity_medium_url" value="{{{ data.links.view }}}" />
	<# } #>
	<div class="bp-attachment-file-icon">
		<# if ( '' !== data.vignette ) { #>
			<div class="icon" style="background-image: url({{ data.vignette }})">
			</div>
		<# } else { #>
			<div class="icon" style="background-image: url({{ data.icon }})">
			</div>
		<# } #>
	</div>
	<dl class="bp-attachment-meta">
		<dt><?php esc_html_e( 'File name:', 'bp-attachments' ); ?></dt>
		<dd>{{ data.name }}</dd>
		<dt><?php esc_html_e( 'File type:', 'bp-attachments' ); ?></dt>
		<dd>{{ data.media_type }}</dd>
		<dt><?php esc_html_e( 'File size:', 'bp-attachments' ); ?></dt>
		<dd>{{ data.size }}</dd>
	</dl>
</script>
