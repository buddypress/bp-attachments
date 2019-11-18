/**
 * WordPress dependencies
 */
const { Component, createElement, Fragment } = wp.element;
const { Button, Modal } = wp.components;
const { compose } = wp.compose;
const { withDispatch } = wp.data;
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
		const { mediaOpen, mimeType, name, id } = this.props;

		if ( 'inode/directory' === mimeType ) {
			return mediaOpen( { name: name } );
		}

		return this.openModal();
	}

    render() {
		const Template = setTemplate( 'bp-attachments-media-item' );
		const { isOpen } = this.state;
		const { title, vignette } = this.props;

        return (
			<Fragment>
				<div
					className="media-item"
					dangerouslySetInnerHTML={ { __html: Template( this.props ) } }
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
	withDispatch( ( dispatch ) => ( {
		mediaOpen( media ) {
			dispatch( 'bp-attachments' ).requestMedia( { directory: media.name } );
		},
	} ) ),
] )( MediaItem );
