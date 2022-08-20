/**
 * WordPress dependencies.
 */
const {
	apiFetch,
	blockEditor: {
		useBlockProps,
	},
	data: {
		useSelect,
	},
	components: {
		Notice,
	},
	element: {
		createElement,
		useState,
	},
	i18n: {
		__,
	},
} = wp;

/**
 * Internal dependencies.
 */
import AttachmentPlaceholder from '../../common/components/attachment-placeholder';

const editImage = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();
	const [ errorMessage, setErrorMessage ] = useState( '' );
	const { align, url, src } = attributes;
	const currentUser = useSelect( ( select ) => {
		return select( 'core' ).getCurrentUser();
	}, [] );

	const onUploadedImage = ( file ) => {
		const formData = new FormData();
		formData.append( 'file', file );
		formData.append( 'action', 'bp_attachments_media_upload' );
		formData.append( 'object', 'members' );
		formData.append( 'object_item', currentUser.id );
		formData.append( 'status', 'public' );

		setErrorMessage( '' );

		apiFetch( {
			path: 'buddypress/v1/attachments',
			method: 'POST',
			body: formData,
		} ).then( ( response ) => {
			if ( response.links && response.links.src ) {
				setAttributes( {
					url: response.links.view,
					src: response.links.src,
				} );
			}
		} ).catch( ( error ) => {
			if ( error.message ) {
				const errorMessage = (
					<Notice status="error" isDismissible={ false }>
						<p>{ error.message }</p>
					</Notice>
				);

				setErrorMessage( errorMessage );
			}
		} );
	}

	if ( ! src ) {
		return (
			<AttachmentPlaceholder
				type="image"
				icon="format-image"
				label={ __( 'Community Image', 'bp-attachments' ) }
				onUploadedMedium={ onUploadedImage }
			>
				{ errorMessage }
			</AttachmentPlaceholder>
		);
	}

	return (
		<figure { ...blockProps }>
			<img src={ src } />
		</figure>
	)
};

export default editImage;
