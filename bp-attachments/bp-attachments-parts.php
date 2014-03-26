<?php
/**
 * BP Attachments Parts.
 *
 * Used instead of template parts
 *
 * @package BP Attachments
 * @subpackage Parts
 */

/**
 * The attachments loop
 * 
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_template_loop( $component = '' ) {
	$current_component = $component;

	if ( empty( $component ) )
		$component = 'members';
	
	?>
	<div class="attachments <?php echo $component;?>-attachments">

		<?php do_action( "bp_attachments_before_{$component}_loop" ); ?>

		<?php if ( bp_attachments_has_attachments( array( 'component' => $current_component ) ) ) : ?>

			<div id="pag-top" class="pagination no-ajax">

				<div class="pag-count" id="attachment-<?php echo $component;?>-count-top">

					<?php bp_attachments_pagination_count(); ?>

				</div>

				<div class="pagination-links" id="attachment-<?php echo $component;?>-pag-top">

					<?php bp_attachments_pagination_links(); ?>

				</div>

			</div>

			<?php do_action( 'bp_attachments_before_{$component}_attachments_list' ); ?>

			<ul id="attachments-list" class="item-list" role="main">

			<?php while ( bp_attachments_the_attachments() ) : bp_attachments_the_attachment(); ?>

				<li <?php bp_attachments_class(); ?>>
					<div class="item-avatar">
						<a href="<?php bp_attachments_the_link(); ?>" class="attachment-link" data-attachment="<?php bp_attachments_the_attachment_id();?>" title="<?php echo esc_attr( bp_attachments_get_the_title() );?>"><?php bp_attachments_thumbnail(); ?></a>
					</div>

					<div class="item">
						<div class="item-title"><a href="<?php bp_attachments_the_link(); ?>" class="attachment-link" data-attachment="<?php bp_attachments_the_attachment_id();?>" title="<?php echo esc_attr( bp_attachments_get_the_title() );?>"><?php bp_attachments_the_title(); ?></a></div>
						<div class="item-meta"><span class="activity"><?php bp_attachments_last_modified(); ?></span></div>

						<?php if ( bp_attachments_has_description() ) : ?>
							<div class="item-desc"><?php bp_attachments_the_excerpt(); ?></div>
						<?php endif ; ?>

						<?php do_action( "bp_attachments_{$component}_item" ); ?>

					</div>

					<div class="action">

						<?php do_action( "bp_attachments_{$component}_actions" ); ?>

						<div class="meta">

							<?php bp_attachments_the_status(); ?> / <?php bp_attachments_the_mime_type(); ?>

						</div>

					</div>

					<div class="clear"></div>
				</li>

			<?php endwhile; ?>

			</ul>

			<?php do_action( 'bp_after_directory_groups_list' ); ?>

			<div id="pag-bottom" class="pagination no-ajax">

				<div class="pag-count" id="attachment-<?php echo $component;?>-count-bottom">

					<?php bp_attachments_pagination_count(); ?>

				</div>

				<div class="pagination-links" id="attachment-<?php echo $component;?>-pag-bottom">

					<?php bp_attachments_pagination_links(); ?>

				</div>

			</div>

		<?php else: ?>

			<div id="message" class="info">
				<p><?php _e( 'There were no attachments found.', 'bp-attachments' ); ?></p>
			</div>

		<?php endif; ?>

		<?php do_action( "bp_attachments_after_{$component}_loop" ); ?>

	</div>
	<?php
}

/**
 * Single attachment edit screen
 * 
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_template_single() {
	?>
	<form action="<?php echo esc_url( bp_attachments_single_the_form_action() );?>" method="post" id="attachment-edit-form" class="standard-form">  	
		<p>
			<label for="bp-attachments-edit-title"><?php esc_html_e( 'Name', 'bp-attachments' ); ?></label>
			<input type="text" name="_bp_attachments_edit[title]" id="bp-attachments-edit-title" value="<?php bp_attachments_single_the_title() ;?>"/>
		</p>
		<p>
			<label for="bp-attachments-edit-description"><?php esc_html_e( 'Description', 'bp-attachments' ); ?></label>
			<textarea name="_bp_attachments_edit[description]" id="bp-attachments-edit-description"><?php bp_attachments_single_the_description() ;?></textarea>
		</p>
		<p>
			<label for="bp-attachments-edit-status"><?php esc_html_e( 'Set as private', 'bp-attachments' ); ?>
				<input type="checkbox" name="_bp_attachments_edit[privacy]" id="bp-attachments-edit-privacy" disabled="true">
			</label>
		</p>

		<hr/>

		<h4><?php esc_html_e( 'Attached to', 'bp-attachments' ); ?></h4>

		<div class="bp-attachments-attached-to">
			<?php bp_attachments_single_attached_to();?>
		</div>

		<input type="hidden" value="<?php bp_attachments_single_the_id();?>" name="_bp_attachments_edit[id]"/>
		<?php wp_nonce_field( 'bp_attachment_update' ); ?>

		<input type="submit" name="_bp_attachments_edit[update]" id="bp-attachments-edit-submit" value="<?php esc_attr_e( 'Edit Attachment', 'bp-attachments' ); ?>" />
	</form>
	<?php
}
