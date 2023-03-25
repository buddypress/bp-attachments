<?php
/**
 * BP Attachments single view template for a Photos/images album.
 *
 * @package \bp-attachments\templates\attachments\single\parts\single-album
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<figure id="bp-media-list" class="wp-block-gallery has-nested-images columns-2 is-cropped"></figure>

<?php bp_attachments_get_javascript_template( 'image-medium' );
