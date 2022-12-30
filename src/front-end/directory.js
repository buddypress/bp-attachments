/**
 * WordPress dependencies
 */
const {
	domReady,
	url: {
		addQueryArgs,
		getPath,
		getQueryArgs,
	},
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
	 * @param {Object} directorySettings The Attachments directory settings.
	 * @param {string} directorySettings.path The BP REST API path.
	 * @param {string} directorySettings.root The REST API root path.
	 * @param {string} directorySettings.nonce The REST API nonce value.
	 * @param {string} directorySettings.placeholder The URL of the WordPress default mime type image.
	 * @param {Object} directorySettings.items The result of the preloaeded BP PREST API request.
	 */
	constructor( { path, root, nonce, placeholder, items } ) {
		const { body } = items;
		this.items = body;
		this.queryArgs = { ...getQueryArgs( path ), page: 1, per_page: 20 };
		this.endpoint = getPath( root.replace( '/wp-json', '' ) + path );
		this.root = root;
		this.nonce = nonce;
		this.scope = 'any';
		this.isFetching = false;
		this.totalItems = 0;
		this.totalPages = 0;
		this.template = 'bp-media-item';
		this.filePlaceholder = placeholder;
		this.container = document.querySelector( '#bp-media-directory' );

		if ( 'headers' in items ) {
			Object.keys( items.headers ).forEach( ( header ) => {
				if ( 'X-WP-Total' === header ) {
					this.totalItems = parseInt( items.headers[ header ], 10 );
				} else if ( 'X-WP-TotalPages' === header ) {
					this.totalPages = parseInt( items.headers[ header ], 10 );
				}
			} );
		}
	}

	/**
	 * Renders the HTML of a medium.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} props The medium properties.
	 * @param {string} template The template to use.
	 * @returns {string} HTML output.
	 */
	renderItem( props, template ) {
		if ( props._embedded && props._embedded.owner ) {
			props.owner = props._embedded.owner.at( 0 );
		}

		if ( ! template ) {
			template = this.template;
		}

		const Template = setTemplate( template );
		return Template( props );
	}

	/**
	 * Renders Media.
	 *
	 * @since 1.0.0
	 *
	 * @param {Array} items The list of Attachment objects to render.
	 */
	renderItems( items ) {
		items.forEach( ( item ) => {
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

		this.loadMore();
	}

	/**
	 * Renders a skeleton loader when changing scope and filters.
	 *
	 * @since 1.0.0
	 */
	doSkeletonLoading() {
		const skeleton = {
			id: 0,
			placeholder: this.filePlaceholder,
		};
		const template = 'bp-media-skeleton';
		let numSkeletons = parseInt( this.queryArgs.per_page, 10 );
		const perPage = numSkeletons;
		const currentPage = parseInt( this.queryArgs.page, 10 );

		// Reset the container, it it's the first page.
		if ( 1 === this.queryArgs.page ) {
			this.container.innerHTML = '';
		} else {
			const maxNumDisplayed = currentPage * perPage;
			if ( maxNumDisplayed > this.totalItems ) {
				numSkeletons = this.totalItems - this.items.length;
			}
		}

		for ( let i = 0; i < numSkeletons; i++ ) {
			this.container.innerHTML += this.renderItem( skeleton, template );
		}
	}

	loadMore() {
		// Observe the last entry to perform an infinite scroll.
		let observedElement = document.querySelector( '.bp-attachments-media-list > div:not(.bp-skeleton):last-child' );
		let observer = new IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => {
					if ( entry.isIntersecting && ! this.isFetching && parseInt( this.queryArgs.page, 10 ) < this.totalPages ) {
						this.queryArgs.page += 1;

						// Load next page.
						this.query();
					}
				} )
			}, {
				root: null,
				rootMargin: '0px',
				threshold: 1.0,
			}
		);

		observer.observe( observedElement );
	}

	/**
	 * Fetch community media according to query arguments.
	 *
	 * @since 1.0.0
	 */
	query() {
		this.doSkeletonLoading();

		// Prevent multiple fetching.
		this.isFetching = true;
		const currentPage = parseInt( this.queryArgs.page, 10 );
		const queryArgs = { ...this.queryArgs };

		if ( 1 === queryArgs.page ) {
			delete queryArgs.page;
		}

		setTimeout( () => {
			fetch( addQueryArgs( this.root + this.endpoint, queryArgs ), {
				method: 'GET',
				headers: {
					'X-WP-Nonce' : this.nonce,
				}
			} ).then(
				( response ) => {
					if ( 200 !== response.status ) {
						return [];
					}

					this.totalItems = parseInt( response.headers.get( 'X-WP-Total' ), 10 );
					this.totalPages = parseInt( response.headers.get( 'X-WP-TotalPages' ), 10 );

					// Release the JSON data.
					return response.json();
				}
			).then(
				( data ) => {
					if ( 1 === currentPage ) {
						this.items = data;
					} else {
						this.items = [ ...this.items, data ];
					}

					this.renderItems( data );

					// Make fetching available again.
					this.isFetching = false;
				}
			).catch(
				() => {
					// Update query results.
					this.items = [];
					this.totalItems = 0;
					this.totalPages = 1;
				}
			).finally(
				() => {
					this.container.querySelectorAll( '.bp-media-item.skeleton' ).forEach( ( skull ) => skull.remove() );
				}
			);
		}, 500 );
	}

	/**
	 * Add listeners to catch interface changes made by the user.
	 *
	 * @since 1.0.0
	 */
	setupListeners() {
		document.addEventListener( 'click', ( e ) => {
			const mainNavItem = e.target.closest( 'li[data-bp-scope]' );

			if ( null !== mainNavItem && this.scope !== mainNavItem.dataset.bpScope ) {
				e.preventDefault();

				this.scope = mainNavItem.dataset.bpScope;
				this.queryArgs.type = mainNavItem.dataset.bpScope;

				mainNavItem.closest( '.component-navigation' ).childNodes.forEach( ( child ) => {
					if ( !! child.classList ) {
						if ( mainNavItem.dataset.bpScope === child.dataset.bpScope ) {
							child.classList.add( 'selected' )
						} else {
							child.classList.remove( 'selected' );
						}
					}
				} );

				// Reset the page argument before querying.
				this.queryArgs.page = 1;
				this.query();
			}
		} );
	}

	/**
	 * Init the Directory.
	 *
	 * @since 1.0.0
	 */
	 start() {
		this.setupListeners();

		if ( ! this.items || ! this.items.length ) {
			return;
		}

		this.renderItems( this.items );
	 }
}

window.bp = window.bp || {};
window.bp.Attachments = window.bp.Attachments || {};

const directorySettings = window.bpAttachmentsDirectorySettings || {};
window.bp.Attachments.Directory = new bpAttachmentsDirectory( directorySettings );

domReady( () => window.bp.Attachments.Directory.start() );
