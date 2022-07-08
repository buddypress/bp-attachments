/**
 * External dependencies.
 */
const {
	trim,
	groupBy,
	filter,
	indexOf,
	find,
	defaultTo,
} = lodash;

/**
 * Internal dependencies.
 */
import { getDirectoryAncestors } from '../utils/functions';

/**
 * Returns the Community Media Library settings.
 *
 * @param {Object} state
 * @returns {Object} The Community Media Library settings.
 */
export const getSettings = ( state ) => {
	const { settings } = state;
	return settings;
}

/**
 * Returns the requests context.
 *
 * @param {Object} state
 * @returns {string} The requests context (`edit` or `view`).
 */
export const getRequestsContext = ( state ) => {
	const {
		settings: {
			isAdminScreen,
		}
	} = state;

	return true === isAdminScreen ? 'edit' : 'view';
}

/**
 * Returns whether the display mode is Grid or not.
 *
 * @param {Object} state The current state.
 * @return {boolean} True if the display mode is Grid. False otherwise.
 */
export const isGridDisplayMode = ( state ) => {
	const { isGrid } = state;
	return isGrid;
}

/**
 * Returns the logged in user Object.
 *
 * @param {Object} state The current state.
 * @return {Object} The logged in user Object.
 */
export const getLoggedInUser = ( state ) => {
	const { user } = state;
	return user;
};

/**
 * Returns whether the display mode is Grid or not.
 *
 * @param {Object} state The current state.
 * @return {boolean} True if the display mode is Grid. False otherwise.
 */
export const getFormState = ( state ) => {
	const { formState } = state;
	return formState;
}

/**
 * Returns whether an upload is being processed.
 *
 * @param {Object} state The current state.
 * @return {boolean} True if an uploads is being processed. False otherwise.
 */
export const isUploading = ( state ) => {
	const { uploading } = state;
	return uploading;
};

/**
 * Returns whether an upload has been processed.
 *
 * @param {Object} state The current state.
 * @return {boolean} True if an uploads has been processed. False otherwise.
 */
 export const uploadEnded = ( state ) => {
	const { ended } = state;
	return ended;
};

/**
 * Returns the list of uploaded file Objects.
 *
 * @param {Object} state The current state.
 * @return {array} The list of uploaded file Objects.
 */
export const getUploads = ( state ) => {
	const { uploads } = state;
	return uploads;
};

/**
 * Returns the list of errors.
 *
 * @param {Object} state The current state.
 * @return {array} The list of errors.
 */
export const getErrors = ( state ) => {
	const { errors } = state;
	return errors;
};

/**
 * Returns the list community media objects.
 *
 * @param {Object} state The current state.
 * @return {array} The list of community media objects.
 */
export const getMedia = ( state ) => {
	const { files } = state;
	return files;
};

/**
 * Returns the current directory.
 *
 * @param {Object} state The current state.
 * @return {string} The current directory.
 */
export const getCurrentDirectory = ( state ) => {
	const { currentDirectory } = state;
	return currentDirectory || '';
};

/**
 * Returns the current directory object.
 *
 * @param {Object} state The current state.
 * @return {Object} The current directory object.
 */
 export const getCurrentDirectoryObject = ( state ) => {
	const { currentDirectory, tree } = state;
	const defaultValue = { readonly: true };

	if ( '' !== currentDirectory ) {
		return defaultTo(
			find( tree, { id: currentDirectory } ),
			defaultValue
		);
	}

	return defaultValue;
};

/**
 * Returns the directories Tree.
 *
 * @param {Object} state The current state.
 * @return {array} The directories Tree.
 */
 export const getTree = ( state ) => {
	const { tree, currentDirectory } = state;
	const groupedTree = groupBy( tree, 'parent' );
	const currentChildrenIds = filter( tree, { 'parent': currentDirectory || 0 } ).map( ( child ) => child.id );

	// Makes sure to only list current directory children.
	if ( currentChildrenIds && currentChildrenIds.length ) {
		currentChildrenIds.forEach( ( childId ) => {
			if ( groupedTree[ childId ] ) {
				delete groupedTree[ childId ];
			}
		} );
	}

	// Makes sure to avoid listing children of directories that are not an ancestor of the currentDirectory one.
	if ( currentDirectory ) {
		const currentAncestors = getDirectoryAncestors(
			tree,
			currentDirectory
		).map( ( ancestor ) => ancestor.id );

		Object.keys( groupedTree ).forEach( ( treeIndex ) => {
			if ( 0 !== parseInt( treeIndex, 10 ) && -1 === indexOf( currentAncestors, treeIndex ) ) {
				delete groupedTree[ treeIndex ];
			}
		} );
	}

	const fillWithChildren = ( items ) => {
		return items.map( ( item ) => {
			const children = groupedTree[ item.id ];
			return {
				...item,
				children: children && children.length ?
					fillWithChildren( children ) :
					[],
			};
		} );
	};

	return fillWithChildren( groupedTree[0] || [] );
};

/**
 * Returns the directory flat list.
 *
 * @param {Object} state The current state.
 * @return {array} The directory flat list.
 */
 export const getFlatTree = ( state ) => {
	const { tree } = state;
	return tree || [];
};

/**
 * Returns whether a media/directory item is selectable.
 *
 * @param {Object} state The current state.
 * @return {boolean} True if a media/directory item is selectable. False otherwise.
 */
export const isSelectable = ( state ) => {
	const { isSelectable } = state;
	return isSelectable;
};

/**
 * Returns the selected media.
 *
 * @param {Object} state The current state.
 * @return {array} The list of selected media.
 */
export const selectedMedia = ( state ) => {
	const { files } = state;

	return filter( files, [ 'selected', true ] );
}

/**
 * Returns the current relative path.
 *
 * @param {Object} state The current state.
 * @return {string} The current relative path.
 */
export const getRelativePath = ( state ) => {
	const { relativePath } = state;
	return relativePath;
};

/**
 * Returns the destination data for media.
 *
 * @param {Object} state The current state.
 * @return {Object} The destination data for media.
 */
export const getDestinationData = ( state ) => {
	const { relativePath } = state;

	if ( ! relativePath ) {
		return {
			object: 'members',
		}
	}

	const destinationData = trim( relativePath, '/' ).split( '/' );

	return {
		status: destinationData[0] ? destinationData[0] : 'public',
		object: destinationData[1] ? destinationData[1] : 'members',
		item: destinationData[2] ? destinationData[2] : '',
	}
};
