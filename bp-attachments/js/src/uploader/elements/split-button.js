/**
 * WordPress dependencies
 */
const { Component, createPortal, createElement } = wp.element;
const { __ } = wp.i18n;

class SplitButtonPortal extends Component {
	render() {
		return createPortal( this.props.children, document.querySelector( '#bp-media-admin-page-title-actions' ) );
	}
}

class SplitButton extends Component {
	render() {
		return (
			<SplitButtonPortal>
				<div className="split-button">
					<div className="split-button-head">
						<a href="#new-bp-media-upload" className="button split-button-primary" aria-live="polite">
							{ __( 'Upload new', 'bp-attachments' ) }
						</a>
						<button type="button" className="split-button-toggle" aria-haspopup="true" aria-expanded="false">
							<i className="dashicons dashicons-arrow-down-alt2"></i>
							<span className="screen-reader-text">{ __( 'More actions', 'bp-attachments' ) }</span>
						</button>
					</div>
					<ul className="split-button-body">
						<li>
							<a href="#new-bp-media-directory" className="button-link directory-button split-button-option">
								{ __( 'Add new directory', 'bp-attachments' ) }
							</a>
						</li>
					</ul>
				</div>
			</SplitButtonPortal>
		);
	}
}

export default SplitButton;
