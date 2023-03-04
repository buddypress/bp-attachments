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

<script type="html/template" id="tmpl-bp-media-skeleton">
	<div class="bp-media-item skeleton">
		<div class="bp-media-item-author">
			<div class="avatar profile-photo"></div>
		</div>
		<figure class="aligncenter wp-block-bp-file-attachment">
			<img src="{{{data.placeholder}}}" alt="">
		</figure>
		<div class="bp-media-item-title">
			<span class="title-placeholder"></span>
		</div>
	</div>
</script>

<script type="html/template" id="tmpl-bp-media-empty-results">
	<aside id="bp-attachments-no-results" class="bp-feedback info">
		<span class="bp-icon" aria-hidden="true"></span>
		<p class="description">
			<?php esc_html_e( 'No media of the selected type were shared by the community so far.', 'bp-attachments' ); ?>
		</p>
	</aside>
</script>
