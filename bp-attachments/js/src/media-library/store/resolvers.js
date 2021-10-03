/**
 * Internal dependencies.
 */
import {
	fetchFromAPI,
	getLoggedInUser as getLoggedInUserAction,
	getMedia as getMediaAction,
} from './actions';

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
	const path = '/buddypress/v1/attachments?context=edit';
	const files = yield fetchFromAPI( path, true );
	yield getMediaAction( files, '' );
}
