/**
 * WordPress dependencies.
 */
const {
	apiFetch,
	blockEditor: {
		BlockAlignmentControl,
		BlockControls,
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
		Fragment,
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
	const { align, src } = attributes;
	const blockProps = useBlockProps( {
		className: !! align && undefined !== align ? 'wp-block-bp-image-attachment align' + align : 'wp-block-bp-image-attachment',
	} );
	const [ errorMessage, setErrorMessage ] = useState( '' );
	const { userId, postId } = useSelect( ( select ) => {
		const currentUser = select( 'core' ).getCurrentUser();

		return {
			userId: currentUser.id,
			postId: select( 'core/editor' ).getCurrentPostId(),
		};
	}, [] );

	const onUploadedImage = ( file ) => {
		const formData = new FormData();
		formData.append( 'file', file );
		formData.append( 'action', 'bp_attachments_media_upload' );
		formData.append( 'object', 'members' );
		formData.append( 'object_item', userId );
		formData.append( 'status', 'public' );

		if ( !! postId ) {
			formData.append( 'attached_to_object_type', 'post' );
			formData.append( 'attached_to_object_id', postId );
		}

		// Reset error message.
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
		<Fragment>
			<BlockControls group="block">
				<BlockAlignmentControl
					controls={ [ 'none', 'left', 'center', 'right' ] }
					value={ align }
					onChange={ ( alignment ) => setAttributes( { align: alignment } ) }
				/>
			</BlockControls>
			<figure { ...blockProps }>
				<img src={ src } />
			</figure>
		</Fragment>
	)
};

export default editImage;
