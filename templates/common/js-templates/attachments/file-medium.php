<?php
/**
 * BP Attachments file folder JavaScript Template.
 *
 * @package \templates\buddypress\common\js-templates\attachments\file-medium
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="html/template" id="tmpl-bp-media-folder">
	<a class="media-item" href="{{{ data.links.view }}}">
		<div class="item-preview">
			<div class="vignette">
				<div class="centered">
					<# if ( '' !== data.vignette ) { #>
						<img src="{{ data.vignette }}" class="{{data.orientation}}" alt="" />
					<# } else if ( 'inode/directory' === data.mime_type ) { #>
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
	</a>
</script>
