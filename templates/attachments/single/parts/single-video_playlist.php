<?php
/**
 * BP Attachments single view template for a video playlist.
 *
 * @package \bp-attachments\templates\attachments\single\parts\single-video_playlist
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wp-playlist wp-video-playlist wp-playlist-light">
	<video id="bp-medium-player" controls="controls" preload="none"></video>
	<div class="wp-playlist-tracks"></div>
</div>
