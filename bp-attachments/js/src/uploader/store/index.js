/**
 * WordPress dependencies
 */
const { apiFetch } = wp;
const { registerStore } = wp.data;

/**
 * External dependencies
 */
const { reject, uniqueId } = lodash;

function * saveAttachment( file ) {
	let uploading = true, uploaded;
	yield { type: 'UPLOAD_START', uploading, file };

	const formData = new FormData();
	formData.append( 'file', file );
	formData.append( 'action', 'bp_attachments_media_upload' );

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

// This needs improvements too.
function * requestMedia( args ) {
	let path = '/buddypress/v1/attachments?context=edit';

	if ( args && args.directory ) {
		path += '&directory=' + args.directory;
	}

	const files = yield actions.fetchFromAPI( path );
	return actions.getFiles( files );
}

const DEFAULT_STATE = {
	user: {},
	files: [],
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
		}
	},

	controls: {
		CREATE_MEDIA( action ) {
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

export default store;
