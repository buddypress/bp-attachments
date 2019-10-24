/**
 * WordPress dependencies
 */
const { Component, createElement } = wp.element;

class MediaItem extends Component {
	constructor() {
        super( ...arguments );
	}

    render() {
		const { id, name } = this.props;

        return (
            <div className="bp-attachments-media">
				<span>{ name }</span>
			</div>
        );
    }
}

export default MediaItem;
