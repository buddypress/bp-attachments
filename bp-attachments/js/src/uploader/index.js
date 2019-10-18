const { Component, render, Fragment } = wp.element;
const { DropZoneProvider, DropZone } = wp.components;
const { __ } = wp.i18n;
const { apiFetch } = wp;
const { registerStore, withDispatch, withSelect } = wp.data;
const { compose } = wp.compose;
const { find, forEach, reject } = lodash;

function *saveAttachment( file ) {
	let uploading = true;
	yield { type: 'UPLOAD_START', uploading, file };

	const formData = new FormData();
	formData.append( 'file', file );
	formData.append( 'action', 'bp_attachments_media_upload' );

	const uploaded = yield actions.uploadFile( '/buddypress/v1/attachments', formData );

	uploading = false
	yield { type: 'UPLOAD_END', uploading, uploaded };
	return actions.addFile( uploaded );
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

        this.state = {
			files: [],
			uploaded: [],
			errored: [],
			uploading: false,
		};

		this.onResetState = this.onResetState.bind( this );
	}

	onResetState( event ) {
		event.preventDefault();

		this.setState( {
			files: [],
			uploaded: [],
			errored: [],
			uploading: false,
		} );
	}

	renderResult( file ) {
		const { files, errored } = this.props;
		const isError = find( errored, { fileName: file.name } );
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
					<span className="bp-errored"></span>
					<span>{ isError.error.message }</span>
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
		const { onFilesDropped, getUploadedFiles, isUploading, files } = this.props;
		let dzClass = 'enabled';

		if ( !! isUploading ) {
			dzClass = 'disabled';
		}

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
				{ !! getUploadedFiles.length &&
					<ol className="bp-files-list">
						{ getUploadedFiles.map( file => {
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
			getUploadedFiles: select( 'bp-attachments' ).getUploadedFiles(),
			files: select( 'bp-attachments' ).getFiles(),
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
