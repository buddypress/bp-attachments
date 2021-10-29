/**
 * External dependencies.
 */
 const {
	uniqueId,
	hasIn,
	trim,
	trimEnd,
	pick,
	filter,
} = lodash;

/**
 * WordPress dependencies.
 */
const {
	data: {
		dispatch,
		select,
	},
	url: {
		addQueryArgs,
	}
} = wp;

/**
 * Internal dependencies.
 */
import { TYPES as types } from './action-types';
import { STORE_KEY } from './constants';

/**
 * Returns an action to set the BP attachments Media Library settings.
 *
 * @param {Object} settings The settings to use.
 * @return {Object} Object for action.
 */
export function setSettings( settings ) {
	return {
		type: types.SET_SETTINGS,
		settings,
	};
}

/**
 * Returns an action object used to fetch media from the API.
 *
 * @param {string} path Endpoint path.
 * @param {boolean} parse Should we parse the request.
 * @return {Object} Object for action.
 */
export function fetchFromAPI( path, parse ) {
	return {
		type: types.FETCH_FROM_API,
		path,
		parse,
	};
}

/**
 * Returns an action object used to create a medium via the API.
 *
 * @param {string} path Endpoint path.
 * @param {Object} data The data to be created.
 * @return {Object} Object for action.
 */
export function createFromAPI( path, data ) {
	return {
		type: types.CREATE_FROM_API,
		path,
		data,
	};
}

/**
 * Returns an action object used to delete a medium via the API.
 *
 * @param {string} path Endpoint path.
 * @param {Object} data The data to be created.
 * @return {Object} Object for action.
 */
export function deleteFromAPI( path, relativePath ) {
	return {
		type: types.DELETE_FROM_API,
		path,
		relativePath,
	};
}

/**
 * Returns an action object used to switch between Grid & Liste mode.
 *
 * @param {Boolean} isGrid
 * @returns {Object} Object for action.
 */
export function switchDisplayMode( isGrid ) {
	return {
		type: types.SWITCH_DISPLAY_MODE,
		isGrid,
	};
}

/**
 * Returns an action object used to get the logged in user.
 *
 * @param {Object} user Logged In User object.
 * @return {Object} Object for action.
 */
export function getLoggedInUser( user ) {
	return {
		type: types.GET_LOGGED_IN_USER,
		user,
	};
}

/**
 * Returns an action object used to get media.
 *
 * @param {Array} files The list of files.
 * @param {String} relativePath The relative path.
 * @return {Object} Object for action.
 */
export function getMedia( files, relativePath, currentDirectory ) {
	return {
		type: 'GET_MEDIA',
		files,
		relativePath,
		currentDirectory,
	};
};

/**
 * Returns an action object used to update the Upload/Directory Form state.
 *
 * @param {Object} params
 * @returns {Object} Object for action.
 */
export function updateFormState( params ) {
	return {
		type: types.UPDATE_FORM_STATE,
		params,
	};
}

export function createMedium( path, formData ) {
	return {
		type: 'CREATE_FROM_API',
		path,
		formData,
	};
};

export function addMedia( file ) {
	return {
		type: 'ADD_MEDIA',
		file,
	};
};

/**
 * Init the directories Tree.
 *
 * @param {array} items The list of media.
 */
export function initTree( items ) {
	const tree = select( STORE_KEY ).getTree();
	const directories = filter( items, { 'mime_type': 'inode/directory' } );
	if ( ! tree.length ) {
		directories.forEach( ( item ) => {
			dispatch( STORE_KEY ).addItemTree( {
				id: item.id,
				name: item.name,
				title: item.title,
				parent: 0,
			} );
		} );
	}
}

/**
 * Returns an action object used to add a directory item to the Items tree.
 *
 * @param {Object} item A media item.
 * @return {Object} Object for action.
 */
export function addItemTree( item ) {
	return {
		type: 'FILL_TREE',
		item,
	};
};

export function deleteMedium( path, relativePath ) {
	return {
		type: 'DELETE_FROM_API',
		path,
		relativePath,
	};
};

export function addMediaError( file ) {
	return {
		type: 'ADD_ERROR',
		file,
	};
};

export function toggleSelectable( isSelectable ) {
	return {
		type: 'TOGGLE_SELECTABLE',
		isSelectable,
	};
};

export function toggleMediaSelection( id, isSelected ) {
	return {
		type: 'TOGGLE_MEDIA_SELECTION',
		id,
		isSelected,
	};
};

export function * insertMedium( file ) {
	let uploading = true, uploaded;
	const currentState = select( STORE_KEY ).getState();
	const { object, item, status } = getMediumDestinationData( currentState );
	const parentDir = currentState.relativePath;

	yield { type: 'UPLOAD_START', uploading, file };

	const formData = new FormData();
	formData.append( 'file', file );
	formData.append( 'action', 'bp_attachments_media_upload' );

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
		uploaded = yield createMedium( '/buddypress/v1/attachments', formData );
		yield { type: 'UPLOAD_END', uploading, uploaded };
		uploaded.uploaded = true;

		return addMedia( uploaded );
	} catch ( error ) {
		uploaded = {
			id: uniqueId(),
			name: file.name,
			error: error.message,
		};

		yield { type: 'UPLOAD_END', uploading, uploaded };

		return addMediaError( uploaded );
	}
}

export function * createDirectory( directory ) {
	let uploading = true, uploaded, file = {
		name: directory.directoryName,
		type: directory.directoryType,
	};
	const currentState = select( STORE_KEY ).getState();
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
		uploaded = yield createMedium( '/buddypress/v1/attachments', formData );
		yield { type: 'UPLOAD_END', uploading, uploaded };
		uploaded.uploaded = true;

		return addMedia( uploaded );
	} catch ( error ) {
		uploaded = {
			id: uniqueId(),
			name: file.name,
			error: error.message,
		};

		yield { type: 'UPLOAD_END', uploading, uploaded };

		return addMediaError( uploaded );
	}
}

export const parseResponseMedia = async ( response, relativePath, parent = '' ) => {
	const items = await response.json().then( ( data ) => {
		data.forEach( ( item ) => {
			item.parent = parent;

			if ( 'inode/directory' === item.mime_type ) {
				dispatch( STORE_KEY ).addItemTree(
					pick(
						item,
						['id', 'name', 'title', 'parent', 'object']
					)
				);
			}
		} );

		return data;
	} );

	// Init the Tree when needed.
	if ( ! relativePath && ! parent ) {
		initTree( items );
	}

	dispatch( STORE_KEY ).getMedia( items, relativePath, parent );
}

export function * requestMedia( args = {} ) {
	const path = '/buddypress/v1/attachments';
	let relativePathHeader = '';
	let parent = '';

	if ( ! args.context ) {
		args.context = select( STORE_KEY ).getRequestsContext();
	}

	if ( args.directory && args.path ) {
		args.directory = trimEnd( args.path, '/' ) + '/' + args.directory;
	}

	if ( args.parent ) {
		parent = args.parent;
		delete args.parent;
	}

	delete args.path;

	const response = yield fetchFromAPI( addQueryArgs( path, args ), false );

	if ( hasIn( response, [ 'headers', 'get' ] ) ) {
		// If the request is fetched using the fetch api, the header can be
		// retrieved using the 'get' method.
		relativePathHeader = response.headers.get( 'X-BP-Attachments-Relative-Path' );
	} else {
		// If the request was preloaded server-side and is returned by the
		// preloading middleware, the header will be a simple property.
		relativePathHeader = get( response, [ 'headers', 'X-BP-Attachments-Relative-Path' ], '' );
	}

	return parseResponseMedia( response, relativePathHeader, parent );
}

export function destroyMedium( id ) {
	return {
		type: 'DESTROY_MEDIUM',
		id,
	}
};

export function * removeMedium( medium ) {
	const currentState = select( STORE_KEY ).getState();
	const { relativePath } = currentState;
	let deleted;

	try {
		deleted = yield deleteMedium( '/buddypress/v1/attachments/' + medium.id + '/', relativePath );

		return destroyMedium( deleted.previous.id );
	} catch ( error ) {
		medium.error = error.message;

		return addMediaError( medium );
	}
}
