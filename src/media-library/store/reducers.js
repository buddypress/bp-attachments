/**
 * External dependencies.
 */
 const {
	reject,
} = lodash;

/**
 * WordPress dependencies
 */
const {
	data: {
		select,
	},
	preferences: {
		store: preferenceStore,
	},
} = wp;

/**
 * Internal dependencies
 */
import { TYPES as types } from './action-types';

/**
 * Default state.
 */
const DEFAULT_STATE = {
	user: {},
	displayedUserId: 0,
	tree: [],
	currentDirectory: '',
	files: [],
	relativePath: '',
	uploads: [],
	errors: [],
	uploading: false,
	ended: false,
	isSelectable: false,
	isGrid: select( preferenceStore ).get( 'bp/attachments', 'isGridLayout' ),
	settings: {},
	formState: {},
	pagination: {
		membersPage: 1,
		membersDisplayedAmount: 0,
		totalMembersPage: 0,
	},
	querying: false,
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

		case types.SET_DISPLAYED_USER_ID:
			return {
				...state,
				displayedUserId: parseInt( action.userId, 10 ),
			};

		case types.GET_MEDIA:
			let media = [];
			if ( action.pagination.membersPage && 1 < action.pagination.membersPage ) {
				media = [ ...state.files, ...action.files ];
			} else {
				media = action.files;
			}

			return {
				...state,
				files: media,
				relativePath: action.relativePath,
				currentDirectory: action.currentDirectory,
				pagination: {
					...action.pagination
				},
			};

		case types.FILL_TREE:
			return {
				...state,
				tree: [
					...reject( state.tree, [ 'id', action.item.id ] ),
					action.item,
				],
			};

		case types.PURGE_TREE:
			return {
				...state,
				tree: reject( state.tree, [ 'id', action.itemId ] ),
			};

		case types.UPDATE_FORM_STATE:
			return {
				...state,
				formState: {
					...action.params
				},
			};

		case types.ADD_MEDIUM:
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
				uploads: [
					...state.uploads,
					action.file,
				],
			};

		case types.ADD_ERROR:
			return {
				...state,
				errors: [
					...state.errors,
					action.error,
				],
			};

		case types.REMOVE_ERROR:
			return {
				...state,
				errors: reject( state.errors, [ 'id', action.errorID ] ),
			};

		case types.UPLOAD_END:
			return {
				...state,
				uploading: action.uploading,
				uploads: reject( state.uploads, ( u ) => { return u.name === action.file.name; } ),
				ended: true,
			};

		case types.RESET_UPLOADS:
			return {
				...state,
				uploading: false,
				uploads: [],
				errors:[],
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
					if ( ( 'all' === action.ids[0] && ! action.isSelected ) || ( -1 !== action.ids.indexOf( file.id ) ) ) {
						file.selected = action.isSelected;
					}

					return file;
				} ),
			};

		case types.SWITCH_DISPLAY_MODE:
			return {
				...state,
				isGrid: action.isGrid,
			};

		case types.REMOVE_MEDIUM:
			return {
				...state,
				files: [
					...reject( state.files, [ 'id', action.id ] )
				],
			};

		case types.SET_QUERY_STATUS:
			return {
				...state,
				querying: action.querying,
			};
	}

	return state;
};

export default reducer;
