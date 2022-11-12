/**
 * WordPress dependencies
 */
const {
	domReady,
} = wp;

/**
 * Internal dependencies.
 */
import setTemplate from '../media-library/utils/set-template';
import bytesToSize from '../media-library/utils/functions';

class bpAttachmentsActivity {
	/**
	 * Setup the Activity Buttons.
	 *
	 * @since 1.0.0
	 */
	constructor( { path, root, nonce, allowedExtTypes } ) {
		const {
			bp: {
				Nouveau: {
					Activity: {
						postForm: {
							buttons,
						}
					}
				}
			}
		} = window;

		this.ActivityButtons = buttons;
		this.container = document.querySelector( '#whats-new-textarea' );
		this.endpoint = root + path;
		this.nonce = nonce;
		this.allowedTypes = allowedExtTypes;
		this.uploadedFiles = [];
	}

	/**
	 * Renders the Medium preview.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} props The medium properties.
	 * @returns {string} HTML output.
	 */
	 renderItemPreview( props ) {
		const Template = setTemplate( 'bp-media-preview' );
		return Template( props );
	}

	/**
	 * Open the file browser and process the upload.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} backboneEvent
	 */
	upload( backboneEvent ) {
		const {
			collection: {
				models,
			},
			model: {
				attributes: {
					user_id,
				}
			},
		} = backboneEvent;
		const fileInput = document.createElement( 'input' );
		fileInput.type = 'file';
		fileInput.id = 'bp-attachments-activity-medium';
		fileInput.name = '_bp_attachments_activity_medium';
		fileInput.style = 'display: none;';
		fileInput.accept = this.allowedTypes;

		// Add the File input to the DOM & simulate a click.
		if ( ! this.container.querySelector( '#bp-attachments-activity-medium' ) ) {
			this.container.append( fileInput );
			fileInput.click();
		}

		fileInput.addEventListener( 'change', ( event ) => {
			event.preventDefault();

			const medium = event.target.files[0];

			// Make sure to only upload once a file.
			if ( !! medium && -1 === this.uploadedFiles.indexOf( medium.name ) ) {
				this.uploadedFiles.push( medium.name );

				const formData = new FormData();
				formData.append( 'file', medium );
				formData.append( 'action', 'bp_attachments_media_upload' );
				formData.append( 'object', 'members' );
				formData.append( 'object_item', user_id );
				formData.append( 'visibility', 'public' );
				formData.append( 'total_bytes', medium.size );

				const previousErrors = backboneEvent.model.get( 'errors' );
				if ( previousErrors && previousErrors.origin && 'bpAttachments' === previousErrors.origin ) {
					backboneEvent.model.unset( 'errors' );
				}

				fetch( this.endpoint, {
					method: 'POST',
					body: formData,
					headers: {
						'X-WP-Nonce' : this.nonce,
					}
				} ).then(
					( response ) => response.json()
				).then(
					( data ) => {
						if ( data.code && data.message ) {
							backboneEvent.model.set( 'errors', { type: 'error', value: data.message, origin: 'bpAttachments' } );
							return;
						}

						if ( data.size ) {
							data.size = bytesToSize( data.size );
						}

						// Render the media preview.
						document.querySelector( '#bp-attachments-activity-medium-preview' ).innerHTML = this.renderItemPreview( data );
					}
				).finally( () => {
					this.container.querySelector( '#bp-attachments-activity-medium' ).remove();

					models.forEach( ( buttonModel ) => {
						if ( 'bpAttachments' === buttonModel.get( 'id' ) ) {
							buttonModel.set( 'active', false );
						}
					} );
				} );
			}
		} );
	}

	/**
	 * Remove all Medium preview content.
	 *
	 * @since 1.0.0
	 */
	reset() {
		document.querySelector( '#bp-attachments-activity-medium-preview' ).innerHTML = '';
	}

	/**
	 * Init the Activity Attachments Button.
	 *
	 * @since 1.0.0
	 */
	 start() {
		// Add the preview placeholder.
		const previewPlaceholder = document.createElement( 'div' );
		previewPlaceholder.id = 'bp-attachments-activity-medium-preview';

		// Add the placeholder to the DOM.
		this.container.append( previewPlaceholder );

		this.ActivityButtons.on(
			'display:bpAttachments',
			( backboneEvent ) => this.upload( backboneEvent )
		);

		this.ActivityButtons.on(
			'resetForm:bpAttachments',
			() => this.reset()
		);

		this.container.addEventListener(
			'click',
			( event ) => {
				if ( 'bp-attachments-activity-medium-exit' === event.target.getAttribute( 'id' ) ) {
					event.preventDefault();

					return this.reset();
				}
			}
		);
	 }
}

window.bp = window.bp || {};
window.bp.Attachments = window.bp.Attachments || {};

const settings = window.bpAttachmentsActivitySettings || {};
window.bp.Attachments.Activity = new bpAttachmentsActivity( settings );

domReady( () => window.bp.Attachments.Activity.start() );
