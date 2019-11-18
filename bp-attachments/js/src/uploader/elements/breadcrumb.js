/**
 * WordPress dependencies
 */
const { Component, createElement } = wp.element;
const { Dashicon } = wp.components;
const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;

/**
 * External dependencies
 */
const { last } = lodash;

class BreadCrumb extends Component {
	constructor() {
		super( ...arguments );

		this.onCrumbClick = this.onCrumbClick.bind( this );
	}

	onCrumbClick( event ) {
		event.preventDefault;

		const { changeDirectory } = this.props;
		let crumbItem = event.currentTarget.getAttribute( 'data-path' );

		if ( 'root' === crumbItem ) {
			crumbItem = '';
		}

		return changeDirectory( crumbItem );
	}

	render() {
		const { path, user } = this.props;

		// HardCoded for development purpose.
		// @todo Make it completely variable!
		const root = '/private/members/' + user.id;
		const crumbs = path.replace( root + '/', '' ).split( '/' );
		const currentCrumb = last( crumbs );

		if ( ! path || root === path ) {
			return null;
		}

		/**
		 * @todo: remove the last crumb then loop on each
		 * to attach a link
		 */
		return (
			<div className="bp-media-breadcrumb">
				<a href="#root" data-path="root" onClick={ ( e ) => this.onCrumbClick( e ) }>
					<Dashicon icon="admin-home" />
				</a>
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
