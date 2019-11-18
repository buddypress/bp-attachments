/**
 * WordPress dependencies
 */
const { Component, render, createElement, Fragment } = wp.element;
const { DropZoneProvider, DropZone } = wp.components;
const { __ } = wp.i18n;
const { withDispatch, withSelect, dispatch } = wp.data;
const { compose } = wp.compose;

/**
 * External dependencies
 */
const { find, forEach } = lodash;

/**
 * Internal dependencies
 */
import store from './store';
import SplitButton from './elements/split-button';
import DirectoryForm from './elements/directory-form';
import MediaToolbar from './elements/media-toolbar';
import MediaItem from './elements/media-item';
import BreadCrumb from './elements/breadcrumb';

class BP_Media_Uploader extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			uploadEnabled: false,
			makedirEnabled: false,
		};

		this.handleAction = this.handleAction.bind( this );
		this.closeForm = this.closeForm.bind( this );
	}

	handleAction( action ) {
		const isUpload = 'upload' === action;
		const isMakeDir = 'makedir' === action;

		dispatch( 'bp-attachments' ).reset();

		this.setState( { uploadEnabled: isUpload, makedirEnabled: isMakeDir } );
	}

	closeForm( event ) {
		event.preventDefault();

		this.handleAction( '' );
	}

	renderResult( file ) {
		const { files, errored } = this.props;
		const { uploadEnabled, makedirEnabled } = this.state;

		const isError = find( errored, { name: file.name } );
		const isSuccess = true === makedirEnabled ? find( files, { title: file.name } ) : find( files, { name: file.name } );

		if ( isError ) {
			return (
				<span className="bp-info">
					<span>{ isError.error }</span>
					<span className="bp-errored"></span>
				</span>
			);
		}

		if ( isSuccess ) {
			return (
				<span className="bp-info">
					<span className="bp-uploaded"></span>
					<span className="screen-reader-text">{ __( 'Uploaded!', 'bp-attachments' ) }</span>
				</span>
			);
		}

		return (
			<span className="bp-info">
				<span className="bp-uploading"></span>
				<span className="screen-reader-text">{ __( 'Uploading...', 'bp-attachments' ) }</span>
			</span>
		);
	}

	render() {
		const { onFilesDropped, isUploading, hasUploaded, uploaded, files, errored, user } = this.props;
		const { uploadEnabled, makedirEnabled } = this.state;
		let mediaItems, dzClass = 'disabled', dfClass = 'disabled', result = [];

		if ( uploadEnabled && ! isUploading && ! hasUploaded ) {
			dzClass = 'enabled';
		}

		if ( makedirEnabled && ! isUploading && ! hasUploaded ) {
			dfClass = 'enabled';
		}

		result = result.concat( uploaded, errored );

		if ( files.length ) {
			mediaItems = files.map( ( file ) => {
				return (
					<MediaItem
						key={ 'media-item-' + file.id }
						id={ file.id }
						name={ file.name }
						title={ file.title }
						mediaType={ file.media_type }
						mimeType={ file.mime_type }
						icon={ file.icon }
						vignette={ file.vignette }
						orientation={ file.orientation }
					/>
				);
			} );
		}

		return (
			<Fragment>
				<SplitButton onDoAction={ this.handleAction }/>
				<div className={ 'uploader-container ' + dzClass }>
					<button className="close dashicons dashicons-no" onClick={ ( e ) => this.closeForm( e ) }>
						<span className="screen-reader-text">{ __( 'Close the upload panel', 'bp-attachments' ) }</span>
					</button>
					<DropZoneProvider>
						<DropZone
							label={ __( 'Drop your files here.', 'bp-attachments' ) }
							onFilesDrop={ onFilesDropped }
							className="uploader-inline"
						/>
					</DropZoneProvider>
				</div>
				<DirectoryForm className={ dfClass }>
					<button className="close dashicons dashicons-no" onClick={ ( e ) => this.closeForm( e ) }>
						<span className="screen-reader-text">{ __( 'Close the Create directory form', 'bp-attachments' ) }</span>
					</button>
				</DirectoryForm>
				<MediaToolbar />
				{ !! result.length &&
					<ol className="bp-files-list">
						{ result.map( file => {
							return (
								<li key={ file.name } className="row">
									<span className="filename">{ file.name }</span>
									{ this.renderResult( file ) }
								</li>
							);
						} ) }
					</ol>
				}
				<BreadCrumb />
				<div className="media-items">
                    { mediaItems }
                </div>
			</Fragment>
		);
	}
};

const BP_Media_UI = compose( [
	withSelect( ( select ) => {
		const bpAttachmentsStore = select( 'bp-attachments' );

		return {
			user: bpAttachmentsStore.loggedInUser(),
			isUploading: bpAttachmentsStore.isUploading(),
			hasUploaded: bpAttachmentsStore.hasUploaded(),
			uploaded: bpAttachmentsStore.getUploadedFiles(),
			files: bpAttachmentsStore.getFiles(),
			errored: bpAttachmentsStore.getErroredFiles(),
		};
	} ),
	withDispatch( ( dispatch ) => ( {
		onFilesDropped( files ) {
			files.forEach( file => {
				dispatch( 'bp-attachments' ).saveAttachment( file );
			} );
		},
	} ) ),
] )( BP_Media_Uploader );

render( <BP_Media_UI />, document.querySelector( '#bp-media-uploader' ) );
