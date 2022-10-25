/**
 * External dependencies.
 */
 const {
	uniqueId,
	hasIn,
	trim,
	trimEnd,
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
};

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
};

/**
 * Returns an action object used to get media from the API.
 *
 * @param {Promise} response the API respose.
 * @return {Object} Object for action.
 */
export function getFromAPI( response ) {
	return {
		type: types.GET_FROM_API,
		response,
	};
};

/**
 * Returns an action object used to create media via the API.
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
};

/**
 * Returns an action object used to update media via the API.
 *
 * @param {string} path Endpoint path.
 * @param {Object} data The data to be updated.
 * @return {Object} Object for action.
 */
 export function updateFromAPI( path, data ) {
	return {
		type: types.UPDATE_FROM_API,
		path,
		data,
	};
};

/**
 * Returns an action object used to delete a media via the API.
 *
 * @param {string} path Endpoint path.
 * @param {Object} data The data to be created.
 * @param {integer} totalBytes The total amount of bytes per delete batches.
 * @return {Object} Object for action.
 */
export function deleteFromAPI( path, relativePath, totalBytes ) {
	return {
		type: types.DELETE_FROM_API,
		path,
		relativePath,
		totalBytes,
	};
};

/**
 * Returns an action object used to switch between Grid & List mode.
 *
 * @param {Boolean} isGrid
 * @returns {Object} Object for action.
 */
export function switchDisplayMode( isGrid ) {
	return {
		type: types.SWITCH_DISPLAY_MODE,
		isGrid,
	};
};

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
};

/**
 * Returns an action object used to set the displayed user id.
 *
 * @param {integer} userId Displayed user ID.
 * @return {Object} Object for action.
 */
export function setDisplayedUserId( userId ) {
	return {
		type: types.SET_DISPLAYED_USER_ID,
		userId,
	};
};

/**
 * Returns an action object used to get media.
 *
 * @param {Array} files The list of files.
 * @param {String} relativePath The relative path.
 * @param {Object} currentDirectory The current directory.
 * @return {Object} Object for action.
 */
export function getMedia( files, relativePath, currentDirectory ) {
	return {
		type: types.GET_MEDIA,
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
};

/**
 * Prepare a directory to be added to the Tree.
 *
 * @param {Object} directory The medium object.
 * @param {string} parent The parent ID.
 * @returns {Object} The item Tree.
 */
const setItemTree = ( directory, parent ) => {
	const itemTree = {
		id: directory.id,
		slug: directory.name,
		name: directory.title,
		parent: parent,
		object: directory.object ? directory.object : 'members',
		readonly: directory.readonly ? directory.readonly : false,
		visibility: directory.visibility ? directory.visibility : 'public',
		type: directory.media_type ? directory.media_type : 'folder',
	}

	return itemTree;
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
			const itemTree = setItemTree( item, 0 );
			dispatch( STORE_KEY ).addItemTree( itemTree );
		} );
	}
};

/**
 * Returns an action object used to add a directory item to the Items tree.
 *
 * @param {Object} item A media item.
 * @return {Object} Object for action.
 */
export function addItemTree( item ) {
	return {
		type: types.FILL_TREE,
		item,
	};
};

/**
 * Returns an action object used to remove a directory item from the Items tree.
 *
 * @param {string} itemId A media item ID.
 * @return {Object} Object for action.
 */
 export function removeItemTree( itemId ) {
	return {
		type: types.PURGE_TREE,
		itemId,
	};
};

/**
 * Returns an action object used to switch between Selectable & Regular mode.
 *
 * @param {boolean} isSelectable True to switch to Selectable mode. False otherwise.
 * @returns {Object} Object for action.
 */
export function toggleSelectable( isSelectable ) {
	return {
		type: types.TOGGLE_SELECTABLE,
		isSelectable,
	};
};

/**
 * Returns an action object used to set the Member Media Libraries pagination.
 *
 * @param {Object} pagination Pagination data.
 * @return {Object} Object for action.
 */
export function setPagination( pagination ) {
	return {
		type: types.SET_MEMBER_MEDIA_LIBRARIES_PAGINATION,
		pagination,
	};
};

/**
 * Returns an action object used to switch between Selectable & Regular mode.
 *
 * @param {array} ids The list of media ID.
 * @param {boolean} isSelected True if the media is selected. False otherwise.
 * @returns {Object} Object for action.
 */
export function toggleMediaSelection( ids, isSelected ) {
	return {
		type: types.TOGGLE_MEDIA_SELECTION,
		ids,
		isSelected,
	};
};

/**
 * Returns an action object used to add a new file to the Media list.
 *
 * @param {object} file The uploaded medium.
 * @returns {Object} Object for action.
 */
export function addMedium( file ) {
	return {
		type: types.ADD_MEDIUM,
		file,
	};
};

/**
 * Returns an action object used to add a new error.
 *
 * @param {object} error The uploaded file which errored.
 * @returns {Object} Object for action.
 */
export function addMediumError( error ) {
	return {
		type: types.ADD_ERROR,
		error,
	};
};

/**
 * Creates a Medium uploading a file.
 *
 * @param {Object} file The file object to upload.
 * @param {integer} totalBytes The total amount of bytes per upload batches.
 * @returns {Object} Object for action.
 */
export function * createMedium( file, totalBytes ) {
	let uploading = true, upload;
	const store = select( STORE_KEY );
	const { object, item, visibility } = store.getDestinationData();
	const relativePath = store.getRelativePath();

	yield { type: 'UPLOAD_START', uploading, file };

	const formData = new FormData();
	formData.append( 'file', file );
	formData.append( 'action', 'bp_attachments_media_upload' );
	formData.append( 'object', object );
	formData.append( 'object_item', item );

	if ( visibility ) {
		formData.append( 'visibility', visibility );
	}

	if ( totalBytes ) {
		formData.append( 'total_bytes', totalBytes );
	}

	if ( trim( relativePath, '/' ) !== visibility + '/' + object + '/' + item ) {
		let uploadRelativePath = relativePath;

		// Private uploads are stored out of the site's uploads dir.
		if ( 'private' === visibility ) {
			uploadRelativePath = relativePath.replace( '/private', '' );
		}

		formData.append( 'parent_dir', uploadRelativePath );
	}

	uploading = false;
	try {
		upload = yield createFromAPI( '/buddypress/v1/attachments', formData );
		yield { type: 'UPLOAD_END', uploading, file };

		return addMedium( upload );
	} catch ( error ) {
		upload = {
			id: uniqueId(),
			name: file.name,
			error: error.message,
			uploaded: false,
		};

		yield { type: 'UPLOAD_END', uploading, file };

		return addMediumError( upload );
	}
};

/**
 * Creates a new directory, photo album, audio or video playluist.
 *
 * @todo to avoid too much code duplication, createDirectory & createMedium should probably be
 *       gathered into one function.
 *
 * @param {Object} directory The data to use create the directory
 * @returns {Object} Object for action.
 */
export function * createDirectory( directory ) {
	let uploading = true, upload;

	// A directory is handled as a file having the inode/directory mime type.
	let file = {
		name: directory.directoryName,
		type: directory.directoryType,
	};

	const store = select( STORE_KEY );
	const { object, item, visibility } = store.getDestinationData();
	const relativePath = store.getRelativePath();

	yield { type: 'UPLOAD_START', uploading, file };

	const formData = new FormData();
	formData.append( 'directory_name', file.name );
	formData.append( 'directory_type', file.type );
	formData.append( 'action', 'bp_attachments_make_directory' );
	formData.append( 'object', object );
	formData.append( 'object_item', item );

	if ( visibility ) {
		formData.append( 'visibility', visibility );
	}

	if ( trim( relativePath, '/' ) !== visibility + '/' + object + '/' + item ) {
		let createDirRelativePath = relativePath;

		// Private uploads are stored out of the site's uploads dir.
		if ( 'private' === visibility ) {
			createDirRelativePath = relativePath.replace( '/private', '' );
		}

		formData.append( 'parent_dir', createDirRelativePath );
	}

	uploading = false;
	try {
		upload = yield createFromAPI( '/buddypress/v1/attachments', formData );
		yield { type: 'UPLOAD_END', uploading, file };
		upload.uploaded = true;

		const currentDir = store.getCurrentDirectoryObject();
		const itemTree = setItemTree( upload, currentDir.id );

		// Add the directory to the tree.
		yield addItemTree( itemTree );

		return addMedium( upload );
	} catch ( error ) {
		upload = {
			id: uniqueId(),
			name: file.name,
			error: error.message,
			uploaded: false,
		};

		yield { type: 'UPLOAD_END', uploading, file };

		return addMediumError( upload );
	}
};

/**
 * Updates a Medium.
 *
 * @param {Object} medium The medium object to update.
 * @returns {Object} Object for action.
 */
export function * updateMedium( medium ) {
	let update;
	const store = select( STORE_KEY );
	const relativePath = store.getRelativePath();

	try {
		update = yield updateFromAPI(
			'/buddypress/v1/attachments/' + medium.id + '/',
			{
				'relative_path':  relativePath,
				title: medium.title,
				description: medium.description,
			}
		);

		if ( !! medium.selected  ) {
			update.selected = true;
		}

		return addMedium( update );
	} catch ( error ) {
		update = {
			id: uniqueId(),
			name: medium.name,
			error: error.message,
			updated: false,
		};

		return addMediumError( update );
	}
};

/**
 * Parses the request response and edit Media store.
 *
 * @param {Object} response The request response.
 * @param {String} relativePath The relative path of the medium.
 * @param {String} parent The parent directory.
 * @param {Object} pagination The request pagination.
 * @returns {void}
 */
export const parseResponseMedia = async ( response, relativePath, parent = '', pagination ) => {
	const items = await response.json().then( ( data ) => {
		data.forEach( ( item ) => {
			item.parent = parent;

			if ( 'inode/directory' === item.mime_type ) {
				const itemTree = setItemTree( item, parent );
				dispatch( STORE_KEY ).addItemTree( itemTree );
			}
		} );

		return data;
	} );

	// Init the Tree when needed.
	if ( ! relativePath && ! parent ) {
		initTree( items );
	}

	dispatch( STORE_KEY ).getMedia( items, relativePath, parent );
	dispatch( STORE_KEY ).setPagination( pagination );
};

/**
 * Requests media according to specific arguments.
 *
 * @param {Object} args The Media request arguments.
 * @returns {void}
 */
export function * requestMedia( args = {} ) {
	const path = '/buddypress/v1/attachments';
	const displayedUserId = select( STORE_KEY ).getDisplayedUserId();
	let relativePathHeader = '';
	let parent = '';
	let pagination = {};

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

	if ( !! displayedUserId ) {
		args.user_id = displayedUserId;
	}

	delete args.path;

	const response = yield fetchFromAPI( addQueryArgs( path, args ), false );

	if ( hasIn( response, [ 'headers', 'get' ] ) ) {
		// If the request is fetched using the fetch api, the header can be
		// retrieved using the 'get' method.
		relativePathHeader = response.headers.get( 'X-BP-Attachments-Relative-Path' );
		pagination = {
			membersDisplayedAmount: response.headers.get( 'X-BP-Attachments-Media-Libraries-Total' ),
			totalMembersPage: response.headers.get( 'X-BP-Attachments-Media-Libraries-TotalPages' ),
		};
	} else {
		// If the request was preloaded server-side and is returned by the
		// preloading middleware, the header will be a simple property.
		relativePathHeader = get( response, [ 'headers', 'X-BP-Attachments-Relative-Path' ], '' );
		pagination = {
			membersDisplayedAmount: get( response, [ 'headers', 'X-BP-Attachments-Media-Libraries-Total' ], 0 ),
			totalMembersPage: get( response, [ 'headers', 'X-BP-Attachments-Media-Libraries-TotalPages' ], 0 ),
		};
	}

	return parseResponseMedia( response, relativePathHeader, parent, pagination );
};

/**
 * Returns an action object used to remove a medium from the state.
 *
 * @param {integer} id The medium ID.
 * @returns {Object} Object for action.
 */
export function removeMedium( id ) {
	return {
		type: 'REMOVE_MEDIUM',
		id,
	}
};

/**
 * Deletes a Medium removing the file from the server's filesystem.
 *
 * @param {Object} file The file object to upload.
 * @param {integer} totalBytes The total amount of bytes per delete batches.
 * @returns {Object} Object for action.
 */
export function * deleteMedium( file, totalBytes ) {
	const store = select( STORE_KEY );
	const relativePath = store.getRelativePath();
	let deleted;

	try {
		deleted = yield deleteFromAPI( '/buddypress/v1/attachments/' + file.id + '/', relativePath, totalBytes );

		if ( 'inode/directory' === deleted.previous.mime_type ) {
			yield removeItemTree( deleted.previous.id );
		}

		return removeMedium( deleted.previous.id );
	} catch ( error ) {
		file.error = error.message;

		return addMediumError( file );
	}
};

/**
 * Returns an action object used to remove an error.
 *
 * @param {integer} errorID The error ID.
 * @returns {Object} Object for action.
 */
export function removeMediumError( errorID ) {
	return {
		type: types.REMOVE_ERROR,
		errorID,
	};
};
