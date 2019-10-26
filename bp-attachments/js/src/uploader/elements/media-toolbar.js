/**
 * WordPress dependencies
 */
const { Component, createElement } = wp.element;
const { __ } = wp.i18n;

class MediaToolbar extends Component {
	render() {
		return (
			<div className="media-toolbar wp-filter">
				<div className="media-toolbar-secondary">
					<div className="view-switch media-grid-view-switch">
						<a href="#view-list" className="view-list">
							<span className="screen-reader-text">{ __( 'Display list', 'bp-attachments' ) }</span>
						</a>
						<a href="#view-grid" className="view-grid current">
							<span className="screen-reader-text">{ __( 'Display grid', 'bp-attachments' ) }</span>
						</a>
					</div>
				</div>
			</div>
		);
	}
}

export default MediaToolbar;
