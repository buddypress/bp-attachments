/**
 * WordPress dependencies
 */
const { apiFetch } = wp;
const { registerStore, dispatch } = wp.data;

/**
 * External dependencies
 */
const { get, hasIn, reject, uniqueId } = lodash;

function * saveAttachment( file ) {
	let uploading = true, uploaded;
	const parentDir = store.getState().relativePath;

	yield { type: 'UPLOAD_START', uploading, file };

	const formData = new FormData();
	formData.append( 'file', file );
	formData.append( 'action', 'bp_attachments_media_upload' );

	if ( parentDir ) {
		formData.append( 'parent_dir', parentDir );
	}

	uploading = false;
	try {
		uploaded = yield actions.createMedia( '/buddypress/v1/attachments', formData );
		yield { type: 'UPLOAD_END', uploading, uploaded };
		uploaded.uploaded = true;

		return actions.addMedia( uploaded );
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

function * createDirectory( directory ) {
	let uploading = true, uploaded, file = {
		name: directory.directoryName,
		type: directory.directoryType,
	};

	yield { type: 'UPLOAD_START', uploading, file };

	const formData = new FormData();
	formData.append( 'directory_name', file.name );
	formData.append( 'directory_type', file.type );
	formData.append( 'action', 'bp_attachments_make_directory' );

	uploading = false;
	try {
		uploaded = yield actions.createMedia( '/buddypress/v1/attachments', formData );
		yield { type: 'UPLOAD_END', uploading, uploaded };
		uploaded.uploaded = true;

		return actions.addMedia( uploaded );
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

async function getJsonResponse( response, relativePath ) {
	const files = await response.json().then( ( data ) => {
		return data;
	} );

	store.dispatch(
		actions.getFiles( files, relativePath )
	);
}

// This needs improvements too.
function * requestMedia( args ) {
	let path = '/buddypress/v1/attachments?context=edit';

	if ( args && args.directory ) {
		path += '&directory=';

		if ( args.path ) {
			path += args.path;
		}

		path += args.directory;
	}

	const response = yield actions.fetchFromAPI( path, false );
	let relativePathHeader = '';
	if ( hasIn( response, [ 'headers', 'get' ] ) ) {
		// If the request is fetched using the fetch api, the header can be
		// retrieved using the 'get' method.
		relativePathHeader = response.headers.get( 'X-BP-Attachments-Relative-Path' );
	} else {
		// If the request was preloaded server-side and is returned by the
		// preloading middleware, the header will be a simple property.
		relativePathHeader = get( response, [ 'headers', 'X-BP-Attachments-Relative-Path' ], '' );
	}

	return getJsonResponse( response, relativePathHeader );
}

const DEFAULT_STATE = {
	user: {},
	files: [],
	relativePath: '',
	uploaded: [],
	errored: [],
	uploading: false,
	ended: false,
};

const actions = {
	getLoggedInUser( user ) {
		return {
			type: 'GET_LOGGED_IN_USER',
			user,
		};
	},

	requestMedia,
	getFiles( files, relativePath ) {
		return {
			type: 'GET_FILES',
			files,
			relativePath,
		};
	},

	fetchFromAPI( path, parse ) {
		return {
			type: 'FETCH_FROM_API',
			path,
			parse,
		};
	},

	saveAttachment,
	createDirectory,
	createMedia( path, formData ) {
		return {
			type: 'CREATE_MEDIA',
			path,
			formData,
		};
	},

	reset() {
		return {
			type: 'RESET_UPLOADS',
		};
	},

	addMedia( file ) {
		return {
			type: 'ADD_MEDIA',
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

const store = registerStore( 'bp-attachments', {
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
					relativePath: action.relativePath,
				};

			case 'ADD_MEDIA':
				return {
					...state,
					files: [
						...reject( state.files, [ 'id', action.file.id ] ),
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
					uploaded: reject( state.uploaded, ( u ) => { return u.name === action.uploaded.name || u.name === action.uploaded.title; } ),
					ended: true,
				};

			case 'RESET_UPLOADS':
				return {
					...state,
					uploading: false,
					uploaded: [],
					errored:[],
					ended: false,
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
		hasUploaded( state ) {
			const { ended } = state;
			return ended;
		},
		getRelativePath( state ) {
			const { relativePath } = state;
			return relativePath;
		}
	},

	controls: {
		CREATE_MEDIA( action ) {
			return apiFetch( { path: action.path, method: 'POST', body: action.formData } );
		},

		FETCH_FROM_API( action ) {
			return apiFetch( { path: action.path, parse: action.parse } );
		},
	},

	resolvers: {
		* loggedInUser() {
			const path = '/buddypress/v1/members/me?context=edit';
			const user = yield actions.fetchFromAPI( path, true );
			yield actions.getLoggedInUser( user );
		},

		* getFiles() {
			const path = '/buddypress/v1/attachments?context=edit';
			const files = yield actions.fetchFromAPI( path, true );
			return actions.getFiles( files, '' );
		},
	},
} );

export default store;
