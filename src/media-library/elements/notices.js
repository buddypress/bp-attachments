/**
 * WordPress dependencies
 */
const {
	components: {
		Animate,
		Dashicon,
		Notice,
	},
	element: {
		createElement,
		Fragment,
	},
	i18n: {
		__,
		sprintf,
	},
	data: {
		useSelect,
		useDispatch,
	},
} = wp;

/**
 * Internal dependencies.
 */
import { BP_ATTACHMENTS_STORE_KEY } from '../store';

/**
 * Notices element.
 */
const MediaLibraryNotices = () => {
	const { uploads, errors } = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY );

		return {
			uploads: store.getUploads(),
			errors: store.getErrors(),
		};
	}, [] );
	const { removeMediumError } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const onRemoveError = ( errorID ) => {
		return removeMediumError( errorID );
	};

	let errorNotices = [];

	if ( errors && errors.length ) {
		errorNotices = errors.map( ( error ) => {
			return (
				<Notice
					key={ 'error-' + error.id }
					status="error"
					onRemove={ () => onRemoveError( error.id ) }
					isDismissible={ true }
				>
					<p>
						<Dashicon icon="warning" />
						{ sprintf(
							/* translators: 1: file name. 2: error message. */
							__( '« %1$s » wasn‘t added to the media library. %2$s', 'bp-attachments' ),
							error.name,
							error.error
						) }
					</p>
				</Notice>
			);
		} );
	}

	let loadingNotice = null;
	const numberUploads = uploads && uploads.length ? uploads.length : 0;

	if ( !! numberUploads ) {
		// Looks like WP CLI can't find _n() usage.
		let uploadingMedia = __( 'Uploading the media, please wait.', 'bp-attachments' );
		if ( numberUploads > 1 ) {
			/* translators: %d: number of media being uploaded. */
			uploadingMedia = sprintf( __( 'Uploading %d media, please wait.', 'bp-attachments' ), numberUploads );
		}

		loadingNotice = (
			<div className="chargement-de-documents">
				<Animate
					type="loading"
				>
					{ ( { className } ) => (
						<Notice
							status="warning"
							isDismissible={ false }
						>
							<p className={ className }>
								<Dashicon icon="update" />
								{ uploadingMedia }
							</p>
						</Notice>
					) }
				</Animate>
			</div>
		);
	}

	return (
		<div className="bp-attachments-notices">
			{ loadingNotice }
			{ errorNotices }
		</div>
	);
}

export default MediaLibraryNotices;
