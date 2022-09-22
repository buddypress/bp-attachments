/**
 * WordPress dependencies.
 */
const {
	apiFetch,
	components: {
		DropZone,
		FormFileUpload,
		Notice,
		Placeholder,
	},
	data: {
		useSelect,
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
 * BP Attachments blocks placeholder to upload files.
 *
 * @since 1.0.0
 *
 * @param {string} type The allowed media type.
 * @param {string} icon The dashicon name.
 * @param {string} label The label to use.
 */
const AttachmentPlaceholder = ( { type, icon, label, onSetAttributes } ) => {
	const {
		bpAttachments: {
			allowedExtByMediaList,
			allowedExtTypes,
		},
	} = useSelect( ( select ) => {
		return select( 'core/editor' ).getEditorSettings();
	}, [] );
	const extList = allowedExtByMediaList[ type + '_playlist' ] ? allowedExtByMediaList[ type + '_playlist' ] : allowedExtByMediaList.album;
	const allowedTypes = 'any' === type ? allowedExtTypes : extList;
	const [ errorMessage, setErrorMessage ] = useState( '' );
	const { userId, postId } = useSelect( ( select ) => {
		const currentUser = select( 'core' ).getCurrentUser();

		return {
			userId: currentUser.id,
			postId: select( 'core/editor' ).getCurrentPostId(),
		};
	}, [] );

	const onUploadedMedium = ( file ) => {
		const formData = new FormData();
		formData.append( 'file', file );
		formData.append( 'action', 'bp_attachments_media_upload' );
		formData.append( 'object', 'members' );
		formData.append( 'object_item', userId );
		formData.append( 'visibility', 'public' );

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
				onSetAttributes( {
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

	const uploadMedia = ( files ) => {
		let media;

		if ( files.currentTarget && files.currentTarget.files ) {
			media = [ ...files.currentTarget.files ];
		} else {
			media = files;
		}

		// Using only one medium.
		const medium = media[0];
		onUploadedMedium( medium );
	}

	return (
		<Placeholder
			icon={ !! icon ? icon : 'admin-media' }
			label={ !! label ? label : __( 'Community Media', 'bp-attachments' ) }
			isColumnLayout={ true }
			className="bp-attachments-media-placeholder"
		>
			<DropZone
				onFilesDrop={ ( files ) => uploadMedia( files ) }
				className="uploader-inline"
			/>
			<FormFileUpload
				onChange={ ( files ) => uploadMedia( files ) }
				multiple={ false }
				accept={ allowedTypes }
				className="components-button block-editor-media-placeholder__button block-editor-media-placeholder__upload-button is-primary"
			>
				{ __( 'Select a file', 'bp-attachments' ) }
			</FormFileUpload>
			{ errorMessage }
		</Placeholder>
	);
};

export default AttachmentPlaceholder;
