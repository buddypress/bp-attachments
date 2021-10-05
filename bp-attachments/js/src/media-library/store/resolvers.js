/**
 * External dependencies.
 */
const {
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
} = wp;

/**
 * Internal dependencies.
 */
import {
	fetchFromAPI,
	getLoggedInUser as getLoggedInUserAction,
	getMedia as getMediaAction,
} from './actions';
import { STORE_KEY } from './constants';

/**
 * Returns the requests context.
 *
 * @access private
 * @returns {string} The requests context (view or edit).
 */
const _requetsContext = () => {
	const { isAdminScreen } = window.bpAttachmentsMediaLibrarySettings || {};
	return isAdminScreen && true === isAdminScreen ? 'edit' : 'view';
}

/**
 * Resolver for retrieving current user.
 */
export function* getLoggedInUser() {
	const path = '/buddypress/v1/members/me?context=edit';
	const user = yield fetchFromAPI( path, true );
	yield getLoggedInUserAction( user );
};

/**
 * Resolver for retrieving the media.
 */
export function* getMedia() {
	const path = '/buddypress/v1/attachments?context=' + _requetsContext();
	const files = yield fetchFromAPI( path, true );

	const tree = select( STORE_KEY ).getTree();
	const directories = filter( files, { 'mime_type': 'inode/directory' } );
	if ( ! tree.length ) {
		directories.forEach( ( item ) => {
			dispatch( STORE_KEY ).addItemTree( {
				id: item.id,
				name: item.name,
				title: item.title,
				children: [],
			} );
		} );
	}

	yield getMediaAction( files, '' );
}
