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

class bpAttachmentsMessages {
	/**
	 * Setup the Messages Attachments Button.
	 *
	 * @since 1.0.0
	 */
	constructor( { path, root, nonce, allowedExtTypes, userId } ) {
		this.endpoint = root + path;
		this.nonce = nonce;
		this.allowedTypes = allowedExtTypes;
		this.userId = userId;
		this.uploadedFiles = [];
		this.container = null;
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
	 * Remove all Medium preview content.
	 *
	 * @since 1.0.0
	 */
	reset() {
		document.querySelector( '#bp-attachments-messages-medium-preview' ).innerHTML = '';
	}

	/**
	 * Open the file browser and process the upload.
	 *
	 * @since 1.0.0
	 */
	 upload() {
		const fileInput = document.createElement( 'input' );
		fileInput.type = 'file';
		fileInput.id = 'bp-attachments-messages-medium';
		fileInput.name = '_bp_attachments_messages_medium';
		fileInput.style = 'display: none;';
		fileInput.accept = this.allowedTypes;

		// Add the File input to the DOM & simulate a click.
		if ( ! this.container.querySelector( '#bp-attachments-messages-medium' ) ) {
			this.reset();
			this.container.append( fileInput );
			fileInput.click();
		}

		fileInput.addEventListener(
			'change',
			( event ) => {
				event.preventDefault();

				const medium = event.target.files[0];

				// Make sure to only upload once a file.
				if ( !! medium && -1 === this.uploadedFiles.indexOf( medium.name ) ) {
					this.uploadedFiles.push( medium.name );

					const formData = new FormData();
					formData.append( 'file', medium );
					formData.append( 'action', 'bp_attachments_media_upload' );
					formData.append( 'object', 'members' );
					formData.append( 'object_item', this.userId );
					formData.append( 'visibility', 'private' );
					formData.append( 'total_bytes', medium.size );

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
								console.log( data.message );
								return;
							}

							if ( data.size ) {
								data.size = bytesToSize( data.size );
							}

							// Render the media preview.
							this.container.querySelector( '#bp-attachments-messages-medium-preview' ).innerHTML = this.renderItemPreview( data );
						}
					).finally( () => {
						this.container.querySelector( '#bp-attachments-messages-medium' ).remove();
					} );
				}
			}
		);
	}

	/**
	 * Init the Messages Attachments Button.
	 *
	 * @since 1.0.0
	 */
	 start() {
		// Add the preview placeholder.
		const previewPlaceholder = document.createElement( 'div' );
		previewPlaceholder.id = 'bp-attachments-messages-medium-preview';

		document.addEventListener(
			'click',
			( event ) => {
				if ( event.target.classList.contains( 'bp-attachments-messages-file' ) ) {
					event.preventDefault();

					this.container = event.target.closest( '.bp-attachments-messages-button-container' );
					this.container.prepend( previewPlaceholder );
					event.target.style = 'visibility: hidden;';

					return this.upload();
				}

				if ( 'bp-attachments-medium-preview-exit' === event.target.getAttribute( 'id' ) ) {
					event.preventDefault();
					this.container.querySelector( '.bp-attachments-messages-file' ).style = "visibility: visible;";

					return this.reset();
				}
			}
		);
	}
}

window.bp = window.bp || {};
window.bp.Attachments = window.bp.Attachments || {};

const settings = window.bpAttachmentsMessagesSettings || {};
window.bp.Attachments.Messages = new bpAttachmentsMessages( settings );

domReady( () => window.bp.Attachments.Messages.start() );
