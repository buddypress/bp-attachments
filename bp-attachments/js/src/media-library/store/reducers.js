/**
 * External dependencies.
 */
 const {
	reject,
} = lodash;

/**
 * Internal dependencies
 */
import { TYPES as types } from './action-types';

/**
 * Default state.
 */
const DEFAULT_STATE = {
	user: {},
	tree: [],
	files: [],
	relativePath: '',
	uploaded: [],
	errored: [],
	uploading: false,
	ended: false,
	isSelectable: false,
	isGrid: true,
	settings: {},
};

/**
 * Reducer for the BP Attachments Library.
 *
 * @param   {Object}  state   The current state in the store.
 * @param   {Object}  action  Action object.
 *
 * @return  {Object}          New or existing state.
 */
 const reducer = ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.SET_SETTINGS:
			return {
				...state,
				settings: action.settings,
			};

		case types.GET_LOGGED_IN_USER:
			return {
				...state,
				user: action.user,
			};

		case types.GET_MEDIA:
			return {
				...state,
				files: action.files,
				relativePath: action.relativePath,
			};

		case types.FILL_TREE:
			return {
				...state,
				tree: [
					...reject( state.tree, [ 'id', action.item.id ] ),
					action.item,
				],
			};

		case types.ADD_MEDIA:
			return {
				...state,
				files: [
					...reject( state.files, [ 'id', action.file.id ] ),
					action.file,
				],
			};

		case types.UPLOAD_START:
			return {
				...state,
				uploading: action.uploading,
				uploaded: [
					...state.uploaded,
					action.file,
				],
			};

		case types.ADD_ERROR:
			return {
				...state,
				errored: [
					...state.errored,
					action.file,
				],
			};

		case types.UPLOAD_END:
			return {
				...state,
				uploading: action.uploading,
				uploaded: reject( state.uploaded, ( u ) => { return u.name === action.uploaded.name || u.name === action.uploaded.title; } ),
				ended: true,
			};

		case types.RESET_UPLOADS:
			return {
				...state,
				uploading: false,
				uploaded: [],
				errored:[],
				ended: false,
			};

		case types.TOGGLE_SELECTABLE:
			return {
				...state,
				isSelectable: action.isSelectable,
			};

		case types.TOGGLE_MEDIA_SELECTION:
			return {
				...state,
				files: state.files.map( file => {
					if ( action.id === file.id ) {
						file.selected = action.isSelected
					}

					return file;
				} ),
			};

		case types.SWITCH_DISPLAY_MODE:
			return {
				...state,
				isGrid: action.isGrid,
			};

		case types.DESTROY_MEDIUM:
			return {
				...state,
				files: [
					...reject( state.files, [ 'id', action.id ] )
				],
			};
	}

	return state;
};

export default reducer;
