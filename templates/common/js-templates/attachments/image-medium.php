<?php
/**
 * BP Attachments image gallery item JavaScript Template.
 *
 * @package \templates\buddypress\common\js-templates\attachments\image-medium
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="html/template" id="tmpl-bp-media-album">
	<figure class="wp-block-image size-large">
		<a href="{{{ data.links.view }}}">
			<img loading="lazy" data-id="{{ data.id }}" src="{{{ data.links.src }}}" alt="">
		</a>

		<# if ( data.title ) { #>
			<figcaption>{{ data.title }}</figcaption>
		<# } #>
	</figure>
</script>
