/**
 * WordPress dependencies
 */
const {
	domReady,
} = wp;

class bpAdminAttachments {
	/**
	 * Add listeners to catch interface changes made by the user.
	 *
	 * @since 1.0.0
	 */
	setupListeners() {
		const accordions = document.querySelectorAll( '.health-check-accordion' );

		accordions.forEach( function( accordion ) {
			accordion.addEventListener( 'click', function( e ) {
				if ( e.target && e.target.matches( 'button.health-check-accordion-trigger' ) ) {
					e.preventDefault();

					const isExpanded = ( 'true' === e.target.getAttribute( 'aria-expanded' ) );
					const panel = document.querySelector( '#' + e.target.getAttribute( 'aria-controls' ) );

					if ( isExpanded ) {
						e.target.setAttribute( 'aria-expanded', 'false' );
						panel.setAttribute( 'hidden', true );
					} else {
						e.target.setAttribute( 'aria-expanded', 'true' );
						panel.removeAttribute( 'hidden' );
					}
				}
			} );
		} );
	}

	/**
	 * Init the Attachments settings admin.
	 *
	 * @since 1.0.0
	 */
	 start() {
		this.setupListeners();
	 }
}

window.bp = window.bp || {};
window.bp.Admin = window.bp.Admin || {};
window.bp.Admin.Attachments = new bpAdminAttachments();

domReady( () => window.bp.Admin.Attachments.start() );

