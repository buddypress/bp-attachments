/**
 * External dependencies.
 */
const {
	trim,
} = lodash;

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
export const getUploadedFiles = ( state ) => {
	const { uploaded } = state;
	return uploaded;
};

/**
 * Returns the list of errored file Objects.
 *
 * @param {Object} state The current state.
 * @return {array} The list of errored file Objects.
 */
export const getErroredFiles = ( state ) => {
	const { errored } = state;
	return errored;
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
 * Returns the destination data for a medium.
 *
 * @param {Object} state The current state.
 * @return {Object} The destination data for a medium.
 */
export const getMediumDestinationData = ( state ) => {
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
};
