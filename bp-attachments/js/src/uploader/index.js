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
import MediaToolbar from './elements/media-toolbar';
import MediaItem from './elements/media-item';

class BP_Media_Uploader extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			uploadEnabled: false,
			makedirEnabled: false,
		};

		this.handleAction = this.handleAction.bind( this );
	}

	handleAction( action ) {
		const isUpload = 'upload' === action;
		const isMakeDir = 'makedir' === action;

		dispatch( 'bp-attachments' ).reset();

		this.setState( { uploadEnabled: isUpload, makedirEnabled: isMakeDir } );
	}

	renderResult( file ) {
		const { files, errored } = this.props;
		const isError = find( errored, { name: file.name } );
		const isSuccess = find( files, { name: file.name } );

		if ( isSuccess ) {
			return (
				<span className="bp-info">
					<span className="bp-uploaded"></span>
					<span className="screen-reader-text">{ __( 'Uploaded!', 'bp-attachments' ) }</span>
				</span>
			);
		}

		if ( isError ) {
			return (
				<span className="bp-info">
					<span>{ isError.error }</span>
					<span className="bp-errored"></span>
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
		let mediaItems, dzClass = 'disabled', result = [];

		if ( uploadEnabled && ! isUploading && ! hasUploaded ) {
			dzClass = 'enabled';
		}

		result = result.concat( uploaded, errored );

		if ( files.length ) {
			mediaItems = files.map( ( file ) => {
				return (
					<MediaItem
						key={ 'media-item-' + file.id }
						name={ file.name }
						id={ file.id }
					/>
				);
			} );
		}

		return (
			<Fragment>
				<SplitButton onDoAction={ this.handleAction }/>
				<div className={ 'uploader-container ' + dzClass }>
					<button className="close dashicons dashicons-no" onClick={ this.handleAction }>
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
