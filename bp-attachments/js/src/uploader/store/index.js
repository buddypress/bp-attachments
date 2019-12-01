/**
 * WordPress dependencies
 */
const { apiFetch } = wp;
const { registerStore, dispatch } = wp.data;

/**
 * External dependencies
 */
const { get, hasIn, reject, uniqueId, trimEnd, trim } = lodash;

function getMediumDestinationData( state ) {
	const { relativePath } = state;

	if ( ! relativePath ) {
		return {
			object: 'members',
		}
	}

	const destinationData = trim( relativePath, '/' ).split( '/' );

	return {
		status: destinationData[0] ? destinationData[0] : 'private',
		object: 'groups' === destinationData[1] ? 'groups' : 'members',
		item: destinationData[2] ? destinationData[2] : '',
	}
}

function * insertMedium( file ) {
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
		uploaded = yield actions.createMedium( '/buddypress/v1/attachments', formData );
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
	const currentState = store.getState();
	const { object, item, status } = getMediumDestinationData( currentState );
	const parentDir = currentState.relativePath;

	yield { type: 'UPLOAD_START', uploading, file };

	const formData = new FormData();
	formData.append( 'directory_name', file.name );
	formData.append( 'directory_type', file.type );
	formData.append( 'action', 'bp_attachments_make_directory' );

	if ( 'groups' === object ) {
		formData.append( 'object', object );
		formData.append( 'object_slug', item );
	} else {
		formData.append( 'object_id', item );
	}

	if ( status ) {
		formData.append( 'status', status );
	}

	if ( trim( parentDir, '/' ) !== status + '/' + object + '/' + item ) {
		formData.append( 'parent_dir', parentDir );
	}

	uploading = false;
	try {
		uploaded = yield actions.createMedium( '/buddypress/v1/attachments', formData );
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
		actions.getMedia( files, relativePath )
	);
}

// This needs improvements too.
function * requestMedia( args ) {
	let path = '/buddypress/v1/attachments?context=edit';

	if ( args && args.object ) {
		path += '&object=' + args.object;
	}

	if ( args && args.directory ) {
		path += '&directory=';

		if ( args.path ) {
			// Makes sure there is a trailing slash.
			path += trimEnd( args.path, '/' ) + '/';
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
	isSelectable: false,
};

const actions = {
	getLoggedInUser( user ) {
		return {
			type: 'GET_LOGGED_IN_USER',
			user,
		};
	},

	requestMedia,
	getMedia( files, relativePath ) {
		return {
			type: 'GET_MEDIA',
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

	insertMedium,
	createDirectory,
	createMedium( path, formData ) {
		return {
			type: 'CREATE_MEDIUM',
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

	toggleSelectable( isSelectable ) {
		return {
			type: 'TOGGLE_SELECTABLE',
			isSelectable,
		};
	},

	toggleMediaSelection( id, isSelected ) {
		return {
			type: 'TOGGLE_MEDIA_SELECTION',
			id,
			isSelected,
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

			case 'GET_MEDIA':
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

			case 'TOGGLE_SELECTABLE':
				return {
					...state,
					isSelectable: action.isSelectable,
				};

			case 'TOGGLE_MEDIA_SELECTION' :
				return {
					...state,
					files: state.files.map( file => {
						if ( action.id === file.id ) {
							file.selected = action.isSelected
						}

						return file;
					} ),
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

		getMedia( state ) {
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
		},

		isSelectable( state ) {
			const { isSelectable } = state;
			return isSelectable;
		},
	},

	controls: {
		CREATE_MEDIUM( action ) {
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

		* getMedia() {
			const path = '/buddypress/v1/attachments?context=edit';
			const files = yield actions.fetchFromAPI( path, true );
			return actions.getMedia( files, '' );
		},
	},
} );

export default store;
