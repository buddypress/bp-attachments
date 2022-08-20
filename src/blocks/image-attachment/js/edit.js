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
	element: {
		createElement,
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

		apiFetch( {
			path: 'buddypress/v1/attachments',
			method: 'POST',
			body: formData,
		} ).then( ( response ) => {
			console.log( response );
		} ).catch( ( error ) => {
			console.log( error );
		} ).finally( () => {
			console.log( 'The end' );
		} );
	}

	return (
		<AttachmentPlaceholder
			type="image"
			icon="format-image"
			label={ __( 'Community Image', 'bp-attachments' ) }
			onUploadedMedium={ onUploadedImage }
		/>
	);
};

export default editImage;
