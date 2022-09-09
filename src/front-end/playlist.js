/**
 * WordPress dependencies
 */
const {
	domReady,
} = wp;

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
	}

	/**
	 * Init the playlist.
	 *
	 * @since 1.0.0
	 */
	 start() {
		console.log( this );
	 }
}

window.bp = window.bp || {};
window.bp.Attachments = window.bp.Attachments || {};

const playlistItems = window.bpAttachmentsPlaylistItems || {};
window.bp.Attachments.Playlist = new bpAttachmentsPlaylist( playlistItems );

domReady( () => window.bp.Attachments.Playlist.start() );
