/**
 * WordPress dependencies
 */
const { Component, render, createElement, Fragment } = wp.element;
const { DropZoneProvider, DropZone } = wp.components;
const { __ } = wp.i18n;
const { apiFetch } = wp;
const { registerStore, withDispatch, withSelect } = wp.data;
const { compose } = wp.compose;

/**
 * External dependencies
 */
const { find, forEach, reject, uniqueId } = lodash;

/**
 * Internal dependencies
 */
import MediaItem from './elements/media-item';
import SplitButton from './elements/split-button';

function *saveAttachment( file ) {
	let uploading = true, uploaded;
	yield { type: 'UPLOAD_START', uploading, file };

	const formData = new FormData();
	formData.append( 'file', file );
	formData.append( 'action', 'bp_attachments_media_upload' );

	uploading = false;
	try {
		uploaded = yield actions.uploadFile( '/buddypress/v1/attachments', formData );
		yield { type: 'UPLOAD_END', uploading, uploaded };

		return actions.addFile( uploaded );
	} catch ( error ) {
		uploaded = {
			id: uniqueId(),
			name: file.name,
			error: error.message,
		};

		yield { type: 'UPLOAD_END', uploading, uploaded };

		return actions.traceError( uploaded );
	}
}

const DEFAULT_STATE = {
	user: {},
	files: [],
	uploaded: [],
	errored: [],
	uploading: false,
};

const actions = {
	getLoggedInUser( user ) {
		return {
			type: 'GET_LOGGED_IN_USER',
			user,
		};
	},

	getFiles( files ) {
		return {
			type: 'GET_FILES',
			files,
		};
	},

	fetchFromAPI( path ) {
		return {
			type: 'FETCH_FROM_API',
			path,
		};
	},

	saveAttachment,
	uploadFile( path, formData ) {
		return {
			type: 'UPLOAD_FILE',
			path,
			formData,
		};
	},

	addFile( file ) {
		return {
			type: 'ADD_FILE',
			file,
		};
	},

	traceError( file ) {
		return {
			type: 'ADD_ERROR',
			file,
		};
	},
};

registerStore( 'bp-attachments', {
	reducer( state = DEFAULT_STATE, action ) {
		switch ( action.type ) {
			case 'GET_LOGGED_IN_USER':
				return {
					...state,
					user: action.user,
				};

			case 'GET_FILES':
				return {
					...state,
					files: action.files,
				};

			case 'ADD_FILE':
				return {
					...state,
					files: [
						...state.files,
						action.file,
					],
				};

			case 'UPLOAD_START':
				return {
					...state,
					uploading: action.uploading,
					uploaded: [
						...state.uploaded,
						action.file,
					],
				};

			case 'ADD_ERROR':
				return {
					...state,
					errored: [
						...state.errored,
						action.file,
					],
				};

			case 'UPLOAD_END':
				return {
					...state,
					uploading: action.uploading,
					uploaded: reject( state.uploaded, [ 'name', action.uploaded.name ] ),
				};
		}

		return state;
	},

	actions,

	selectors: {
		loggedInUser( state ) {
			const { user } = state;
			return user;
		},

		isUploading( state ) {
			const { uploading } = state;
			return uploading;
		},
		getUploadedFiles( state ) {
			const { uploaded } = state;
			return uploaded;
		},
		getErroredFiles( state ) {
			const { errored } = state;
			return errored;
		},
		getFiles( state ) {
			const { files } = state;
			return files;
		},
	},

	controls: {
		UPLOAD_FILE( action ) {
			return apiFetch( { path: action.path, method: 'POST', body: action.formData } );
		},

		FETCH_FROM_API( action ) {
			return apiFetch( { path: action.path } );
		},
	},

	resolvers: {
		* loggedInUser() {
			const path = '/buddypress/v1/members/me?context=edit';
			const user = yield actions.fetchFromAPI( path );
			yield actions.getLoggedInUser( user );
		},

		* getFiles() {
			const path = '/buddypress/v1/attachments?context=edit';
			const files = yield actions.fetchFromAPI( path );
			return actions.getFiles( files );
		},
	},
} );

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
