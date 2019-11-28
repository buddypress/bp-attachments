/**
 * WordPress dependencies
 */
const { Component, createElement, Fragment } = wp.element;
const { Dashicon } = wp.components;
const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;

/**
 * External dependencies
 */
const { last, trim, drop, dropRight } = lodash;

class BreadCrumb extends Component {
	constructor() {
		super( ...arguments );

		this.onCrumbClick = this.onCrumbClick.bind( this );
	}

	onCrumbClick( event ) {
		event.preventDefault();

		const { changeDirectory } = this.props;
		let crumbItem = event.currentTarget.getAttribute( 'data-path' );

		if ( 'root' === crumbItem ) {
			crumbItem = '';
		}

		return changeDirectory( crumbItem );
	}

	render() {
		const { path, user } = this.props;
		const crumbs = trim( path, '/' ).split( '/' );
		let objectPath = '/' + crumbs[0] + '/' + crumbs[1] + '/' + user.id + '/';

		if ( 'groups' === crumbs[1] ) {
			objectPath = '/' + crumbs[0] + '/' + crumbs[1] + '/' + crumbs[2] + '/';
		}

		if ( ! path ) {
			return null;
		}

		let currentCrumb = last( crumbs ), crumbsList = crumbs, crumbItems;

		// Set the current crumb and list of available ones.
		if ( path === objectPath ) {
			currentCrumb = crumbs[0];
			crumbsList = [];

			if ( 'groups' === crumbs[1] ) {
				currentCrumb = crumbs[2];
				crumbsList = [ crumbs[1] ];
			}
		} else {
			crumbsList = drop( crumbsList, 3 );
			crumbsList = dropRight( crumbsList, 1 );

			if ( 'groups' === crumbs[1] ) {
				crumbsList.unshift( crumbs[1] );
			} else {
				crumbsList.unshift( crumbs[0] );
			}
		}

		// Build the BreadCrumb items.
		if ( crumbsList.length ) {
			crumbItems = crumbsList.map( ( crumb ) => {
				let slug = crumb + '/';
				if ( crumb === crumbs[0] ) {
					slug = '';
				}

				if ( 'groups' === crumbs[1] && crumb === crumbs[1] ) {
					slug = '';
					objectPath = crumbs[1];
				}

				return (
					<Fragment key={ crumb }>
						<Dashicon icon="arrow-right"/>
						<a href={ '#' + crumb } data-path={ objectPath + slug } onClick={ ( e ) => this.onCrumbClick( e ) }>
							<span>{ crumb }</span>
						</a>
					</Fragment>
				);
			} );
		}

		return (
			<div className="bp-media-breadcrumb">
				<a href="#root" data-path="root" onClick={ ( e ) => this.onCrumbClick( e ) }>
					<Dashicon icon="admin-home" />
				</a>
				{ crumbItems }
				<Dashicon icon="arrow-right"/>
				<span>{ currentCrumb }</span>
			</div>
		);
	}
}

export default compose( [
	withSelect( ( select ) => {
		const bpAttachmentsStore = select( 'bp-attachments' );

		return {
			path: bpAttachmentsStore.getRelativePath(),
			user: bpAttachmentsStore.loggedInUser(),
		};
	} ),
	withDispatch( ( dispatch ) => ( {
		changeDirectory( crumbItem ) {
			let object = 'members';

			/**
			 * This needs to be improved.
			 *
			 * @todo
			 */
			if ( 'groups' === crumbItem ) {
				object = 'groups';
			}

			dispatch( 'bp-attachments' ).requestMedia( { directory: crumbItem, object: object } );
		},
	} ) ),
] )( BreadCrumb );
