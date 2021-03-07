<?php
/**
 * BP Attachments Entry JavaScript Template.
 *
 * @package \templates\buddypress\common\js-templates\attachments\media-item
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<script type="html/template" id="tmpl-bp-attachments-media-item">
	<div class="item-preview">
		<div class="vignette">
			<div class="centered">
				<# if ( 'image' === data.mediaType && '' !== data.vignette ) { #>
					<img src="{{ data.vignette }}" class="{{data.orientation}}" alt="" />
				<# } else if ( 'inode/directory' === data.mimeType && 'avatar' !== data.mediaType ) { #>
					<img src="{{ data.icon }}" class="icon" alt="" width="100%" height="100%" />
				<# } else { #>
					<img src="{{ data.icon }}" class="icon" alt="" />
				<# } #>
			</div>
			<div class="media-name" data-media_id="{{ data.id }}">
				<div>{{ data.title }}</div>
			</div>
		</div>
	</div>
	<# if ( data.isSelected ) { #>
		<button type="button" class="check" tabindex="-1"><span class="media-modal-icon"></span><span class="screen-reader-text"><?php esc_html_e( 'Deselect', 'bp-attachments' ); ?></span></button>
	<# } #>
</script>
