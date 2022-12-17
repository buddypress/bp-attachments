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

class bpAttachmentsDirectory {
	/**
	 * Setup the Directory.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} preloadedData The preloaded data.
	 */
	constructor( preloadedData ) {
		const { body } = preloadedData;
		this.items = body;
		this.template = 'bp-media-item';
		this.container = document.querySelector( '#bp-media-directory' );
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
		if ( props._embedded && props._embedded.owner ) {
			props.owner = props._embedded.owner.at( 0 );
		}

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

		document.querySelectorAll( '.bp-media-item' ).forEach( ( renderedItem ) => {
			// Only show existing media.
			if ( !! renderedItem.innerHTML.trim() ) {
				renderedItem.style.height = renderedItem.clientWidth + 'px';
			} else {
				renderedItem.style.display = 'none';
			}
		} );
	}

	/**
	 * Init the Directory.
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

const preloadedData = window.bpAttachmentsDirectoryItems || {};
window.bp.Attachments.Directory = new bpAttachmentsDirectory( preloadedData );

domReady( () => window.bp.Attachments.Directory.start() );
