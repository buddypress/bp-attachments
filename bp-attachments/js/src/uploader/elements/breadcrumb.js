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
		const objectPath = '/' + crumbs[0] + '/' + crumbs[1] + '/' + user.id + '/';

		if ( ! path ) {
			return null;
		}

		let currentCrumb = last( crumbs ), crumbsList = crumbs, crumbItems;

		// HardCoded for development purpose.
		// @todo Make it completely variable!
		if ( path === objectPath ) {
			currentCrumb = crumbs[0];
			crumbsList = [];
		} else {
			crumbsList = drop( crumbsList, 3 );
			crumbsList = dropRight( crumbsList, 1 );
			crumbsList.unshift( crumbs[0] );
		}

		/**
		 * @todo: remove the last crumb then loop on each
		 * to attach a link
		 */
		if ( crumbsList.length ) {
			crumbItems = crumbsList.map( ( crumb ) => {
				let slug = crumb + '/';
				if ( crumb === crumbs[0] ) {
					slug = '';
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
			dispatch( 'bp-attachments' ).requestMedia( { directory: crumbItem } );
		},
	} ) ),
] )( BreadCrumb );
