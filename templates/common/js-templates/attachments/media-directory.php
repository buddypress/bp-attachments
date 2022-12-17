<?php
/**
 * BP Attachments Media Directory JavaScript Template.
 *
 * @package \templates\buddypress\common\js-templates\attachments\media-directory
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="html/template" id="tmpl-bp-media-item">
	<div class="bp-media-item">
		<# if ( !! data.content.rendered ) { #>
			<div class="bp-media-item-author">
				<# if ( data.owner.link ) { #>
					<a href="{{{data.owner.link}}}" title="{{data.owner.name}}">
						<img src="{{{ data.user_avatar.thumb }}}" alt="" class="avatar profile-photo">
					</a>
				<# } else { #>
					<img src="{{{ data.user_avatar.thumb }}}" alt="" class="avatar profile-photo">
				<# } #>
			</div>
		<# } #>
		{{{ data.content.rendered }}}
	</div>
</script>
