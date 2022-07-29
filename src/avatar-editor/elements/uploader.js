/**
 * WordPress dependencies
 */
const {
	components: {
		DropZone,
		FormFileUpload,
	},
	element: {
		createElement,
	},
	i18n: {
		__,
	},
} = wp;

/**
 * File Uploader element.
 */
const AvatarEditorUploader = ( { settings, onSelectedImage } ) => {
	const { allowedExtTypes } = settings;

	return (
		<div className="uploader-container enabled">
			<DropZone
				label={ __( 'Drop your image here.', 'bp-attachments' ) }
				onFilesDrop={ ( files ) => onSelectedImage( files ) }
				className="uploader-inline"
			/>
			<div className="dropzone-label">
				<h2 className="upload-instructions drop-instructions">{ __( 'Drop an image here', 'bp-attachments' ) }</h2>
				<p className="upload-instructions drop-instructions">{ __( 'or', 'bp-attachments' ) }</p>
				<FormFileUpload
					onChange={ ( files ) => onSelectedImage( files ) }
					multiple={ false }
					accept={ '.' + allowedExtTypes.join( ', .' ) }
					className="browser button button-hero"
				>
					{ __( 'Select an image', 'bp-attachments' ) }
				</FormFileUpload>
			</div>
		</div>
	);
};

export default AvatarEditorUploader;
