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

class bpAttachmentsList {
	/**
	 * Setup the List.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} preloadedData The preloaded data.
	 */
	constructor( preloadedData ) {
		const { body, template } = preloadedData;
		this.items = body;
		this.template = template;
		this.container = document.querySelector( '#bp-media-list' );
	}

	/**
	 * Renders the HTML of a medium.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} props The medium properties.
	 * @returns {string} HTML output.
	 */
	renderItem( props ) {
		const Template = setTemplate( this.template );
		return Template( props );
	}

	/**
	 * Renders Media.
	 *
	 * @since 1.0.0
	 */
	renderItems() {
		this.items.forEach( ( item ) => {
			this.container.innerHTML += this.renderItem( item );
		} );
	}

	/**
	 * Init the list.
	 *
	 * @since 1.0.0
	 */
	 start() {
		if ( ! this.items || ! this.items.length ) {
			return;
		}

		this.renderItems();
	 }
}

window.bp = window.bp || {};
window.bp.Attachments = window.bp.Attachments || {};

const preloadedData = window.bpAttachmentsItems || {};
window.bp.Attachments.List = new bpAttachmentsList( preloadedData );

domReady( () => window.bp.Attachments.List.start() );
