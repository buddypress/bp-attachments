/**
 * WordPress dependencies
 */
const { Component, createElement } = wp.element;
const { SelectControl, TextControl, Button } = wp.components;
const { compose } = wp.compose;
const { withSelect, withDispatch } = wp.data;
const { __ } = wp.i18n;

class DirectoryForm extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			directoryType: 'folder',
			directoryName: '',
		};
	}

	handleChange( params ) {
		const state = this.state;

		if ( params.directoryName ) {
			state.directoryName = params.directoryName;
		}

		if ( params.directoryType ) {
			state.directoryType = params.directoryType;
		}

		this.setState( state );
	}

	submitForm( event ) {
		event.preventDefault();

		this.props.onDirectoryCreate( this.state );

		this.setState( {
			directoryType: 'folder',
			directoryName: '',
		} );
	}

	render() {
		const { directoryType, directoryName } = this.state;
		const { files, className, children } = this.props;

		return (
			<form id="bp-media-directory-form" className={ className }>
				{ children }
				<h2>{ __( 'Create a new directory', 'bp-attachments' ) }</h2>
				<TextControl
					label={ __( 'Type a name for your directory', 'bp-attachments' ) }
					value={ directoryName }
					onChange={ ( directoryName ) => this.handleChange( { directoryName: directoryName } ) }
				/>
				<SelectControl
					label={ __( 'Select a type for your directory', 'bp-attachments' ) }
					value={ directoryType }
					options={ [
						{ label: __( 'File directory', 'bp-attachments' ), value: 'folder' },
						{ label: __( 'Photo Album', 'bp-attachments' ), value: 'album' },
						{ label: __( 'Audio Playlist', 'bp-attachments' ), value: 'audio_playlist' },
						{ label: __( 'Video Playlist', 'bp-attachments' ), value: 'video_playlist' },
					] }
					onChange={ ( directoryType ) => this.handleChange( { directoryType: directoryType } ) }
				/>
				<Button isPrimary={ true } isLarge={ true } onClick={ ( e ) => this.submitForm( e ) }>
					{ __( 'Save directory', 'bp-attachments' ) }
				</Button>
			</form>
		);
	}
}

export default compose( [
	withSelect( ( select ) => ( {
		files: select( 'bp-attachments' ).getMedia(),
	} ) ),
	withDispatch( ( dispatch ) => ( {
		onDirectoryCreate( directory ) {
			dispatch( 'bp-attachments' ).createDirectory( directory );
		},
	} ) ),
] )( DirectoryForm );
