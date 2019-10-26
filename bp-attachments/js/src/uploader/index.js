/**
 * WordPress dependencies
 */
const { Component, render, createElement, Fragment } = wp.element;
const { DropZoneProvider, DropZone } = wp.components;
const { __ } = wp.i18n;
const { withDispatch, withSelect } = wp.data;
const { compose } = wp.compose;

/**
 * External dependencies
 */
const { find, forEach } = lodash;

/**
 * Internal dependencies
 */
import store from './store';
import MediaItem from './elements/media-item';
import SplitButton from './elements/split-button';

class BP_Media_Uploader extends Component {
	constructor() {
		super( ...arguments );
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
		const { onFilesDropped, isUploading, uploaded, files, errored, user } = this.props;
		let mediaItems, dzClass = 'enabled', result = [];

		if ( !! isUploading ) {
			dzClass = 'disabled';
		}

		/**
		 * This needs to be improved.
		 *
		 * Errors should only be displayed and uploading/uploaded files should
		 * be merged with the list of files of the displayed directory.
		 */
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
				<SplitButton/>
				<DropZoneProvider>
					<div>
						<h2>{ __( 'Drop your files in the box below.', 'bp-attachments' ) }</h2>
						<DropZone
							label={ __( 'Drop your files here.', 'bp-attachments' ) }
							onFilesDrop={ onFilesDropped }
							className={ dzClass }
						/>
					</div>
				</DropZoneProvider>
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
