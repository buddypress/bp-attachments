/**
 * External dependencies.
 */
const {
	get,
	hasIn,
} = lodash;

/**
 * Internal dependencies.
 */
import {
	fetchFromAPI,
	getFromAPI,
	initTree,
	getLoggedInUser as getLoggedInUserAction,
	getMedia as getMediaAction,
	setPagination as setPaginationAction,
} from './actions';

/**
 * Returns the request context.
 *
 * @access private
 * @returns {string} The request context (view or edit).
 */
const _requestContext = () => {
	const { isAdminScreen } = window.bpAttachmentsMediaLibrarySettings || {};
	return isAdminScreen && true === isAdminScreen ? 'edit' : 'view';
};

/**
 * Resolver for retrieving current user.
 */
export function* getLoggedInUser() {
	const path = '/buddypress/v1/members/me?context=edit';
	const user = yield fetchFromAPI( path, true );
	yield getLoggedInUserAction( user );
};

/**
 * Resolver for retrieving the current user media library or all user media libraries.
 */
export function* getMedia() {
	const path = '/buddypress/v1/attachments?context=' + _requestContext();
	const response = yield fetchFromAPI( path, false );
	const files = yield getFromAPI( response );
	let pagination = {
		membersPage: 1,
	};

	if ( hasIn( response, [ 'headers', 'get' ] ) ) {
		pagination.membersDisplayedAmount = parseInt( response.headers.get( 'X-BP-Attachments-Media-Libraries-Total' ), 10 );
		pagination.totalMembersPage = parseInt( response.headers.get( 'X-BP-Attachments-Media-Libraries-TotalPages' ), 10 );
	} else {
		pagination.membersDisplayedAmount = parseInt( get( response, [ 'headers', 'X-BP-Attachments-Media-Libraries-Total' ], 0 ), 10 );
		pagination.totalMembersPage = parseInt( get( response, [ 'headers', 'X-BP-Attachments-Media-Libraries-TotalPages' ], 0 ), 10 );
	}

	initTree( files );

	yield getMediaAction( files, '', '', pagination );

	// @todo this should be set above.
	yield setPaginationAction( pagination );
};
