/**
 * WordPress dependencies
 */
const { Component, createElement } = wp.element;
const { Button } = wp.components;
const { compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
const { __ } = wp.i18n;

/**
 * External dependencies
 */
const { filter } = lodash;

class MediaToolbar extends Component {
	constructor() {
		super( ...arguments );
	}

	toggleSelectable( event ) {
		event.preventDefault();
		const { onToggleSelectable, selectable } = this.props;

		return onToggleSelectable( ! selectable );
	}

	deleteSelected( event ) {
		event.preventDefault();
		const { onRemoveMedium, onToggleSelectable, media } = this.props;
		const toDelete = filter( media, [ 'selected', true ] );

		onRemoveMedium( toDelete );
		return onToggleSelectable( false );
	}

	render() {
		const { selectable, media } = this.props;
		const selection = filter( media, [ 'selected', true ] );
		let isDisabled = true;

		if ( selectable && selection.length ) {
			isDisabled = false;
		}

		return (
			<div className="media-toolbar wp-filter">
				<div className="media-toolbar-secondary">
					{ ! selectable && (
						<div className="view-switch media-grid-view-switch">
							<a href="#view-list" className="view-list">
								<span className="screen-reader-text">{ __( 'Display list', 'bp-attachments' ) }</span>
							</a>
							<a href="#view-grid" className="view-grid current">
								<span className="screen-reader-text">{ __( 'Display grid', 'bp-attachments' ) }</span>
							</a>
						</div>
					) }
					{ selectable && (
						<Button disabled={ isDisabled } className="button button-primary button-large media-button delete-selected-button" onClick={ ( e ) => this.deleteSelected( e ) }>
							{ __( 'Delete selection', 'bp-attachments' ) }
						</Button>
					) }
					<Button className="button button-secondary media-button select-mode-toggle-button" onClick={ ( e ) => this.toggleSelectable( e ) }>
						{ ! selectable ? __( 'Bulk Select', 'bp-attachments' ) : __( 'Cancel', 'bp-attachments' ) }
					</Button>
				</div>
			</div>
		);
	}
}

export default compose( [
	withSelect( ( select ) => {
		const bpAttachmentsStore = select( 'bp-attachments' );

		return {
			selectable: bpAttachmentsStore.isSelectable(),
			media: bpAttachmentsStore.getMedia(),
		};
	} ),
	withDispatch( ( dispatch ) => ( {
		onToggleSelectable( selectable ) {
			dispatch( 'bp-attachments' ).toggleSelectable( selectable );
		},
		onRemoveMedium( media ) {
			media.forEach( medium => {
				dispatch( 'bp-attachments' ).removeMedium( medium );
			} );
		},
	} ) ),
] )( MediaToolbar );
