/**
 * WordPress dependencies
 */
const {
	domReady,
} = wp;

class bpAttachmentsPlaylist {
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
window.bp.Attachments.Playlist = new bpAttachmentsPlaylist();

domReady( () => window.bp.Attachments.Playlist.start() );
