const { Component, render, createElement, Fragment } = wp.element;
const { DropZoneProvider, DropZone } = wp.components;
const { __ } = wp.i18n;
const { apiFetch } = wp;
const { registerStore, withDispatch, withSelect } = wp.data;
const { compose } = wp.compose;
const { find, forEach, reject, uniqueId } = lodash;

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
	files: [],
	uploaded: [],
	errored: [],
	uploading: false,
};

const actions = {
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
		}
	},

	controls: {
		UPLOAD_FILE( action ) {
			return apiFetch( { path: action.path, method: 'POST', body: action.formData } );
		},
	},

	resolvers: {},
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
		const { onFilesDropped, isUploading, uploaded, files, errored } = this.props;
		let dzClass = 'enabled', result = [];

		if ( !! isUploading ) {
			dzClass = 'disabled';
		}

		result = result.concat( uploaded, files, errored );

		return (
			<Fragment>
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
			</Fragment>
		);
	}
};

const BP_Media_UI = compose( [
	withSelect( ( select ) => {
		return {
			isUploading: select( 'bp-attachments' ).isUploading(),
			uploaded: select( 'bp-attachments' ).getUploadedFiles(),
			files: select( 'bp-attachments' ).getFiles(),
			errored: select( 'bp-attachments' ).getErroredFiles(),
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
