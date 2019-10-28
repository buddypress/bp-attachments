<?php
/**
 * BP Attachments Entry JavaScript Template.
 *
 * @package BP Attachments
 * @subpackage \templates\buddypress\common\js-templates\attachments\media-item
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>

<script type="html/template" id="tmpl-bp-attachments-media-item">
	<div class="item-preview">
		<div class="vignette">
			<div class="centered">
				<img src="{{ data.icon }}" class="icon" alt="" />
			</div>
			<div class="media-name" data-media_id="{{ data.id }}">
				<div>{{ data.title }}</div>
			</div>
		</div>
	</div>
</script>
