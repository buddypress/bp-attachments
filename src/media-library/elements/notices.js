/**
 * WordPress dependencies
 */
const {
	components: {
		Notice,
		Dashicon,
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
	const onRemoveError = ( errorID ) => {
		// @todo handle errors removal.
		console.log( errorID );
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

	return (
		<div className="bp-attachments-notices">
			{ errorNotices }
		</div>
	);
}

export default MediaLibraryNotices;
