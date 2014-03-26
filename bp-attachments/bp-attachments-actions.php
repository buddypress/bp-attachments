<?php
/**
 * BP Attachments Actions.
 *
 * Adds specific templates to media templates
 * Handles Attachments upload if Javascrip is disabled
 *
 * @package BP Attachments
 * @subpackage Actions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Add the specific templates needed by BP Media Editor
 * 
 * @since BP Attachments (1.0.0)
 */
function bp_attachment_load_backbone_tmpl() {
	?>
	<script type="text/html" id="tmpl-bp-attachment-details">
		<h3>
			<?php _e('Attachment Details'); ?>

			<span class="settings-save-status">
				<span class="spinner"></span>
				<span class="saved"><?php esc_html_e('Saved.'); ?></span>
			</span>
		</h3>
		<div class="attachment-info">
			<div class="thumbnail">
				<# if ( data.uploading ) { #>
					<div class="media-progress-bar"><div></div></div>
				<# } else if ( 'image' === data.type ) { #>
					<img src="{{ data.size.url }}" draggable="false" />
				<# } else { #>
					<img src="{{ data.icon }}" class="icon" draggable="false" />
				<# } #>
			</div>
			<div class="details">
				<div class="filename">{{ data.filename }}</div>
				<div class="uploaded">{{ data.dateFormatted }}</div>

				<# if ( 'image' === data.type && ! data.uploading ) { #>
					<# if ( data.width && data.height ) { #>
						<div class="dimensions">{{ data.width }} &times; {{ data.height }}</div>
					<# } #>

					<# if ( data.can.save && data.can.show ) { #>
						<a class="edit-bp-attachment" href="{{ data.editLink }}"><?php _e( 'Edit Options', 'bp-attachments' ); ?></a>
					<# } #>
				<# } #>

				<# if ( data.fileLength ) { #>
					<div class="file-length"><?php _e( 'Length:' ); ?> {{ data.fileLength }}</div>
				<# } #>

				<# if ( ! data.uploading && data.can.remove && data.can.show ) { #>
					<?php if ( MEDIA_TRASH ): ?>
						<a class="trash-attachment" href="#"><?php _e( 'Trash' ); ?></a>
					<?php else: ?>
						<a class="delete-attachment" href="#"><?php _e( 'Delete Permanently' ); ?></a>
					<?php endif; ?>
				<# } #>

				<div class="compat-meta">
					<# if ( data.compat && data.compat.meta ) { #>
						{{{ data.compat.meta }}}
					<# } #>
				</div>
			</div>
		</div>

		<# var maybeReadOnly = ( data.can.save && data.can.show ) || ( data.allowLocalEdits && data.can.show ) ? '' : 'readonly'; #>
			<label class="setting" data-setting="title">
				<span><?php _e('Title'); ?></span>
				<input type="text" value="{{ data.title }}" {{ maybeReadOnly }} />
			</label>
			<label class="setting" data-setting="description">
				<span><?php _e('Description'); ?></span>
				<textarea {{ maybeReadOnly }}>{{ data.description }}</textarea>
			</label>
	</script>

	<script type="text/html" id="tmpl-avatar">
		<div class="avatar-preview">
			<# if ( data.uploading ) { #>
				<div class="media-progress-bar"><div></div></div>
			<# } else { #>
			<div class="resized">
				<div class="centered">
					<img src="{{ data.size.url }}" draggable="false" id="avatar-to-crop" />
				</div>
			</div>
			<# } #>
		</div>
	</script>

	<script type="text/html" id="tmpl-bp-avatar-details">
		<h3>
			<?php _e('Avatar preview', 'bp-attachments'); ?>
		</h3>
		<div class="attachment-info">
			<div class="avatar-thumb" style="width:<?php echo bp_core_avatar_full_width();?>px;height:<?php echo bp_core_avatar_full_height();?>px;overflow:hidden">
				<# if ( data.uploading ) { #>
					<div class="media-progress-bar"><div></div></div>
				<# } else if ( 'image' === data.type ) { #>
					<img src="{{ data.size.url }}" draggable="false" id="avatar-crop-preview" />
				<# } else { #>
					<img src="{{ data.icon }}" class="icon" draggable="false" />
				<# } #>
			</div>
		</div>
	</script>

	<script type="text/html" id="tmpl-bp-preview">
		<# if ( data.id ) { #>
			<div class="centered">
				<img src="{{ data.img }}" style="max-width:100%"/>
			</div>
		<# } #>
	</script>
	<?php
}

add_action( 'print_media_templates', 'bp_attachment_load_backbone_tmpl' );


/**
 * Handle uploads if no-js
 * 
 * @since BP Attachments (1.0.0)
 */
function bp_attachments_catch_upload() {

	if ( ! empty( $_POST['bp_attachment_upload'] ) ) {

		check_admin_referer( 'bp_attachments_upload' );

		$redirect = bp_get_root_domain() . $_POST['_wp_http_referer'];

		$file_name = 'bp_attachment_file';

		if ( ! empty( $_POST['file_data'] ) )
			$file_name = $_POST['file_data'];
		
		if ( ! empty( $_FILES[ $file_name ] ) ) {

			$args = array();

			if ( ! empty( $_POST['item_id'] ) )
				$args['item_id'] = absint( $_POST['item_id'] );

			if ( ! empty( $_POST['item_type'] ) )
				$args['item_type'] = $_POST['item_type'];

			if ( ! empty( $_POST['component'] ) )
				$args['component'] = $_POST['component'];

			if ( ! empty( $_POST['action'] ) )
				$args['action'] = $_POST['action'];

			// We don't categorized members as a bp_component term
			if ( 'members' == $args['component'] ) {
				unset( $args['component'] );
			}

			$cap_args = false;

			if ( ! empty( $args['component'] ) ) {
				$cap_args = array( 'component' => $args['component'], 'item_id' => $args['item_id'] );
			}

			// capability check
			if ( ! bp_attachments_current_user_can( 'publish_bp_attachments', $cap_args ) ) {
				bp_core_add_message( __( 'Error: you are not allowed to create this attachment.', 'bp-attachments' ), 'error' );
				bp_core_redirect( $redirect );
			}

			$attachment_id = bp_attachments_handle_upload( $args );

			if ( is_wp_error( $attachment_id ) ) {
				bp_core_add_message( sprintf( __( 'Error: %s', 'bp-attachments' ), $attachment_id->get_error_message() ), 'error' );
			} else {
				bp_core_add_message( __( 'Attachment successfully uploaded', 'bp-attachments' ) );
			}

			bp_core_redirect( $redirect );
		}
	}
}
add_action( 'bp_actions', 'bp_attachments_catch_upload' );
