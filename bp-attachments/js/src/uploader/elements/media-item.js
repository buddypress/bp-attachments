/**
 * WordPress dependencies
 */
const { Component, createElement } = wp.element;

/**
 * Internal dependencies
 */
import setTemplate from '../utils/set-template';

class MediaItem extends Component {
	constructor() {
        super( ...arguments );
	}

    render() {
		const Template = setTemplate( 'bp-attachments-media-item' );

        return (
            <div className="media-item" dangerouslySetInnerHTML={ { __html: Template( this.props ) } } />
        );
    }
}

export default MediaItem;
