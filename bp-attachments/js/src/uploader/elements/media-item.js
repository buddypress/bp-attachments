/**
 * WordPress dependencies
 */
const { Component, createElement } = wp.element;
const { compose } = wp.compose;
const { withDispatch } = wp.data;

/**
 * Internal dependencies
 */
import setTemplate from '../utils/set-template';

class MediaItem extends Component {
	constructor() {
		super( ...arguments );
		this.onMediaClick = this.onMediaClick.bind( this );
	}

	onMediaClick() {
		const { mediaOpen, mimeType, name, id } = this.props;

		if ( 'inode/directory' === mimeType ) {
			return mediaOpen( { name: name } );
		}
	}

    render() {
		const Template = setTemplate( 'bp-attachments-media-item' );

        return (
			<div
				className="media-item"
				dangerouslySetInnerHTML={ { __html: Template( this.props ) } }
				onClick={ this.onMediaClick }
			/>
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
