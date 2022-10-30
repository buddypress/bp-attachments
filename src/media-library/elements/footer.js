/**
 * WordPress dependencies
 */
const {
	components: {
		Button,
		Spinner,
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
	const { requestMedia } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const {
		user: {
			capabilities,
		},
		pagination,
		mediaCount,
		isQuerying,
	} = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY );

		return {
			user: store.getLoggedInUser(),
			pagination: store.getPagination(),
			mediaCount: store.countMedia(),
			isQuerying: store.isQuerying(),
		};
	}, [] );
	const canPaginate = !! settings.isAdminScreen && !! capabilities && -1 !== capabilities.indexOf( 'bp_moderate' );
	const totalUserLibraries = parseInt( pagination.membersDisplayedAmount, 10 );

	const onLoadMore = ( event ) => {
		event.preventDefault();
		const { membersPage } = pagination;

		return requestMedia( { page: membersPage + 1 } );
	}

	if ( ! canPaginate || ! pagination.membersDisplayedAmount ) {
		return null;
	}

	return (
		<div className="load-more-wrapper">
			{
				true === isQuerying && (
					<Spinner />
				)
			}
			<p className="load-more-count">
				{
					1 !== totalUserLibraries ? sprintf(
						__( 'Showing %1$s of %2$s media libraries', 'bp-attachments' ),
						mediaCount,
						totalUserLibraries
					) : __( 'Showing one media library', 'bp-attachments' )
				}
			</p>
			{
				mediaCount !== totalUserLibraries && ! isQuerying && (
					<Button variant="primary" className="load-more" onClick={ ( e ) => onLoadMore( e ) }>
						{ __( 'Load more', 'bp-attachments' ) }
					</Button>
				)
			}
		</div>
	);
};

export default MediaLibraryFooter;
