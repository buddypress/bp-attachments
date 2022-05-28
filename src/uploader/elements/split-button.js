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
	constructor() {
		super( ...arguments );

		this.state = {
			is_open: false,
		};

		this.toggleClass = this.toggleClass.bind( this );
		this.doAction = this.doAction.bind( this );
	}

	toggleClass() {
		this.setState( { is_open: ! this.state.is_open } );
	}

	doAction( action, event ) {
		event.preventDefault();

		if ( 'makedir' === action ) {
			this.setState( { is_open: false } );
		}

		this.props.onDoAction( action );
	}

	render() {
		const { is_open } = this.state;
		const toggleClass = true === is_open ? 'split-button is-open' : 'split-button';
		const dashiconClass = true === is_open ? 'dashicons dashicons-arrow-up-alt2' : 'dashicons dashicons-arrow-down-alt2';

		return (
			<SplitButtonPortal>
				<div className={ toggleClass }>
					<div className="split-button-head">
						<a href="#new-bp-media-upload" className="button split-button-primary" aria-live="polite" onClick={ ( e ) => this.doAction( 'upload', e ) }>
							{ __( 'Add new', 'bp-attachments' ) }
						</a>
						<button type="button" className="split-button-toggle" aria-haspopup="true" aria-expanded={ is_open } onClick={ this.toggleClass }>
							<i className={ dashiconClass }></i>
							<span className="screen-reader-text">{ __( 'More actions', 'bp-attachments' ) }</span>
						</button>
					</div>
					<ul className="split-button-body">
						<li>
							<a href="#new-bp-media-directory" className="button-link directory-button split-button-option" onClick={ ( e ) => this.doAction( 'makedir', e ) }>
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
