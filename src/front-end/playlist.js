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

class bpAttachmentsPlaylist {
	/**
	 * Setup the Playlist.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} items The preloaded playlist items.
	 */
	constructor( items ) {
		const { body } = items;
		this.items = body;
		this.player = document.querySelector( '#bp-medium-player' );
	}

	/**
	 * Plays the medium according to the clicked link.
	 *
	 * @since 1.0.0
	 *
	 * @param {PointerEvent} event The event.
	 */
	playTrack( event ) {
		event.preventDefault();
		this.player.pause();

		const parent = event.target.closest( '.wp-playlist-tracks' );
		parent.querySelector( '.wp-playlist-playing' ).classList.remove( 'wp-playlist-playing' );
		event.target.classList.add( 'wp-playlist-playing' );

		const currentSrc = event.target.closest( '.wp-playlist-caption' ).getAttribute( 'href' );
		this.player.setAttribute( 'src', currentSrc );
		this.player.play();
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
		const Template = setTemplate( 'wp-playlist-item' );
		return Template( props )
	}

	/**
	 * Renders Media links.
	 *
	 * @since 1.0.0
	 */
	renderItems() {
		let itemIndex = 0;
		const container = document.querySelector( '.bp-attachments-medium .wp-playlist .wp-playlist-tracks' );

		this.items.forEach( ( item ) => {
			itemIndex += 1;

			const data = {
				index: itemIndex,
				src: item.links.src,
				title: item.title,
				meta: {},
			};

			container.innerHTML += this.renderItem( data );
		} );

		container.querySelectorAll( '.wp-playlist-item' ).forEach( ( element, elementIndex ) => {
			if ( 0 === elementIndex ) {
				element.classList.add( 'wp-playlist-playing' );
			}

			element.addEventListener( 'click', this.playTrack.bind( this ) );
		} );
	}

	/**
	 * Init the playlist.
	 *
	 * @since 1.0.0
	 */
	 start() {
		if ( ! this.items || ! this.items.length ) {
			return;
		}

		const initialSrc = this.items[0].links.src;
		this.player.setAttribute( 'src', initialSrc );

		this.renderItems();
	 }
}

window.bp = window.bp || {};
window.bp.Attachments = window.bp.Attachments || {};

const playlistItems = window.bpAttachmentsPlaylistItems || {};
window.bp.Attachments.Playlist = new bpAttachmentsPlaylist( playlistItems );

domReady( () => window.bp.Attachments.Playlist.start() );
