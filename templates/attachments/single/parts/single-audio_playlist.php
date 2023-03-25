<?php
/**
 * BP Attachments single view template for an audio playlist.
 *
 * @package \bp-attachments\templates\attachments\single\parts\single-audio_playlist
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wp-playlist wp-audio-playlist wp-playlist-light">
	<audio id="bp-medium-player" controls="controls" preload="none"></audio>
	<div class="wp-playlist-tracks"></div>
</div>
