/**
 * WordPress dependencies.
 */
const {
	components: {
		DropZone,
		FormFileUpload,
		Placeholder,
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
const AttachmentPlaceholder = ( { type, icon, label, onUploadedMedium, children } ) => {
	const {
		bpAttachments: {
			allowedExtByMediaList,
		},
	} = useSelect( ( select ) => {
		return select( 'core/editor' ).getEditorSettings();
	}, [] );
	const allowedTypes = allowedExtByMediaList[ type + '_playlist' ] ? allowedExtByMediaList[ type + '_playlist' ] : allowedExtByMediaList.album;

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
			{ children }
		</Placeholder>
	);
};

export default AttachmentPlaceholder;
