<?php
/**
 * BP Attachments single view template.
 *
 * @package \bp-attachments\templates\attachments\view
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="bp-attachments-medium">
	<?php bp_get_template_part( 'attachments/single/parts/single', bp_attachments_get_medium_type() ); ?>
</div>
