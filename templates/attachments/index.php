<?php
/**
 * BP Attachments directory main template.
 *
 * @package \bp-attachments\templates\attachments\index
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

bp_attachments_tracking_output_directory_nav();
?>

<div id="bp-media-directory" class="bp-attachments-media-list">
	<aside id="bp-attachments-no-media" class="bp-feedback info">
		<span class="bp-icon" aria-hidden="true"></span>
		<p class="description">
			<?php
			esc_html_e( 'No public media were shared by the community so far.', 'bp-attachments' );

			if ( is_user_logged_in() ) {
				echo '&nbsp;';
				printf(
					/* Translators: %s is the link to the current user's Media Library */
					esc_html__( 'Start adding yours from your %s.', 'bp-attachments' ),
					sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( trailingslashit( bp_loggedin_user_domain() ). 'attachments/' ),
						esc_html__( 'personal public Media Library', 'bp-attachments' )
					)
				);
			}
			?>
		</p>
	</aside>
</div>

<?php bp_attachments_get_javascript_template( 'media-directory' );
