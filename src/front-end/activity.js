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
	constructor() {
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
	}

	upload( backboneEvent ) {
		const {
			model: {
				attributes: {
					user_id,
					item_id,
					object,
				}
			}
		} = backboneEvent;

		console.log( user_id );
	}

	/**
	 * Init the Activity Attachments Button.
	 *
	 * @since 1.0.0
	 */
	 start() {
		this.ActivityButtons.on( 'display:bpAttachments', ( backboneEvent ) => this.upload( backboneEvent ) );
	 }
}

window.bp = window.bp || {};
window.bp.Attachments = window.bp.Attachments || {};
window.bp.Attachments.Activity = new bpAttachmentsActivity();

domReady( () => window.bp.Attachments.Activity.start() );
