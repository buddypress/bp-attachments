/**
 * Internal dependencies.
 */
import {
	fetchFromAPI,
	getLoggedInUser as getLoggedInUserAction,
	getMedia as getMediaAction,
} from './actions';

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
	yield getMediaAction( files, '' );
}
