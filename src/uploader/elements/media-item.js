/**
 * WordPress dependencies
 */
const { Component, createElement, Fragment } = wp.element;
const { Modal } = wp.components;
const { compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
const { sprintf, __ } = wp.i18n;

/**
 * Internal dependencies
 */
import setTemplate from '../utils/set-template';

class MediaItem extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			isOpen: false,
			isSelected: false,
		};

		this.onMediaClick = this.onMediaClick.bind( this );
		this.openModal = this.openModal.bind( this );
		this.closeModal = this.closeModal.bind( this );
	}

	openModal() {
		this.setState( { isOpen: true } );
	}

	closeModal() {
		this.setState( { isOpen: false } );
	}

	onMediaClick() {
		const { mediaOpen, toggleMediaSelection, mimeType, name, path, isSelectable, id, object } = this.props;
		const { isSelected } = this.state;

		if ( isSelectable ) {
			this.setState( { isSelected: ! isSelected } );
			return toggleMediaSelection( id, ! isSelected );
		}

		if ( 'inode/directory' === mimeType ) {
			return mediaOpen( { name: name, path: path, object: object } );
		}

		return this.openModal();
	}

	render() {
		const Template = setTemplate( 'bp-attachments-media-item' );
		const { isOpen, isSelected } = this.state;
		const { title, vignette } = this.props;
		const classes = isSelected ? 'media-item selected' : 'media-item';

		return (
			<Fragment>
				<div
					className={ classes }
					dangerouslySetInnerHTML={ { __html: Template( this.props ) } }
					role="checkbox"
					onClick={ this.onMediaClick }
				/>
				{ isOpen && (
					<Modal
						title={ sprintf( __( 'Details for: %s', 'bp-attachments' ), title ) }
						onRequestClose={ this.closeModal }>

						{ vignette && (
							<img src={vignette} className="mediaDetails" />
						) }

						{ ! vignette && (
							<p>{ __( '@todo Fetch the Media properties.', 'bp-attachments' ) }</p>
						) }
					</Modal>
				) }
			</Fragment>
		);
	}
}

export default compose( [
	withSelect( ( select ) => {
		const bpAttachmentsStore = select( 'bp-attachments' );

		return {
			path: bpAttachmentsStore.getRelativePath(),
			isSelectable: bpAttachmentsStore.isSelectable(),
		};
	} ),
	withDispatch( ( dispatch ) => ( {
		mediaOpen( media ) {
			dispatch( 'bp-attachments' ).requestMedia( { directory: media.name, path: media.path, object: media.object } );
		},
		toggleMediaSelection( id, isSelected ) {
			dispatch( 'bp-attachments' ).toggleMediaSelection( id, isSelected );
		},
	} ) ),
] )( MediaItem );
