/**
 * WordPress dependencies
 */
const {
	components: {
		Button,
	},
	data: {
		useDispatch,
		useSelect,
	},
	element: {
		createElement,
	},
	i18n: {
		__,
		sprintf,
	},
} = wp;

/**
 * Internal dependencies.
 */
import { BP_ATTACHMENTS_STORE_KEY } from '../store';

/**
 * Footer element.
 */
const MediaLibraryFooter = ( { settings } ) => {
	const { setPagination } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const { user, displayedUserId, pagination, mediaCount } = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY );

		return {
			user: store.getLoggedInUser(),
			displayedUserId: store.getDisplayedUserId(),
			pagination: store.getPagination(),
			mediaCount: store.countMedia(),
		};
	}, [] );
	const { isAdminScreen } = settings;

	const onLoadMore = ( event ) => {
		event.preventDefault();
	}

	if ( ! pagination.membersDisplayedAmount ) {
		return null;
	}

	return (
		<div className="load-more-wrapper">
			<span className="spinner"></span>
			<p className="load-more-count">
				{
					sprintf(
						__( 'Showing %1$s of %2$s media libraries', 'bp-attachments' ),
						mediaCount,
						1 + parseInt( pagination.membersDisplayedAmount, 10 )
					)
				}
			</p>
			<Button variant="primary" className="load-more" onClick={ ( e ) => onLoadMore( e ) }>
				{ __( 'Load more', 'bp-attachments' ) }
			</Button>
		</div>
	);
};

export default MediaLibraryFooter;
