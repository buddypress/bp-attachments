/**
 * WordPress dependencies
 */
const { apiFetch } = wp;
const { registerStore } = wp.data;

/**
 * External dependencies
 */
const { reject, uniqueId } = lodash;

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

export default store;
