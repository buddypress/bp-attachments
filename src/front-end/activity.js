/**
 * WordPress dependencies
 */
const {
	domReady,
} = wp;

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
	}

	getFileInput() {
		let fileInput;

		if ( !! document.querySelector( '#bp-attachments-activity-file' ) ) {
			fileInput = document.querySelector( '#bp-attachments-activity-file' );
		} else {
			fileInput = document.createElement( 'input' );
			fileInput.type = 'file';
			fileInput.id = 'bp-attachments-activity-file';
			fileInput.name = '_bp_attachments_activity_file';
			fileInput.style = 'display: none;';
			fileInput.accept = this.allowedTypes;

			// Add it to the DOM.
			this.container.append( fileInput );
		}

		return fileInput;
	}

	/**
	 * Open the file browser and process the upload.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} backboneEvent
	 * @param {HTMLInputElement} fileInput
	 */
	upload( backboneEvent, fileInput ) {
		const {
			model: {
				attributes: {
					user_id,
				}
			}
		} = backboneEvent;

		fileInput.addEventListener( 'change', ( event ) => {
			const medium = event.target.files[0];

			if ( !! medium ) {
				const formData = new FormData();
				formData.append( 'file', medium );
				formData.append( 'action', 'bp_attachments_media_upload' );
				formData.append( 'object', 'members' );
				formData.append( 'object_item', user_id );
				formData.append( 'visibility', 'public' );
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
						console.log( data );
					}
				).catch( ( error ) => {
					console.log( error );
				} );
			}
		} );

		fileInput.click();
	}

	/**
	 * Init the Activity Attachments Button.
	 *
	 * @since 1.0.0
	 */
	 start() {
		const fileInput = this.getFileInput();

		this.ActivityButtons.on(
			'display:bpAttachments',
			( backboneEvent ) => this.upload( backboneEvent, fileInput )
		);
	 }
}

window.bp = window.bp || {};
window.bp.Attachments = window.bp.Attachments || {};

const settings = window.bpAttachmentsActivitySettings || {};
window.bp.Attachments.Activity = new bpAttachmentsActivity( settings );

domReady( () => window.bp.Attachments.Activity.start() );
