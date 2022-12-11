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
		{{{ data.content.rendered }}}
	</div>
</script>
