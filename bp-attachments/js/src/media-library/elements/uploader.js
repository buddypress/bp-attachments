/**
 * WordPress dependencies
 */
 const {
	components: {
		DropZone,
		FormFileUpload,
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
 * Internal dependencies.
 */
import { BP_ATTACHMENTS_STORE_KEY } from '../store';

/**
 * File Uploader element.
 */
const MediaLibraryUploader = () => {
	const { updateFormState } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const formState = useSelect( ( select ) => {
		return select( BP_ATTACHMENTS_STORE_KEY ).getFormState();
	}, [] );

	const closeForm = ( e ) => {
		e.preventDefault();

		formState.action = '';

		return updateFormState( formState );
	};

	const uploadMedia = ( e ) => {
		e.preventDefault();
	};

	if ( ! formState.action || 'upload' !== formState.action ) {
		return null;
	}

	return (
		<div className="uploader-container enabled">
			<DropZone
				label={ __( 'Drop your files here.', 'bp-attachments' ) }
				onFilesDrop={ ( e ) => uploadMedia( e ) }
				className="uploader-inline"
			/>
			<button className="close dashicons dashicons-no" onClick={ ( e ) => closeForm( e ) }>
				<span className="screen-reader-text">{ __( 'Close the upload panel', 'bp-attachments' ) }</span>
			</button>
			<div className="dropzone-label">
				<h2 className="upload-instructions drop-instructions">{ __( 'Drop files to upload', 'bp-attachments' ) }</h2>
				<p className="upload-instructions drop-instructions">{ __( 'or', 'bp-attachments' ) }</p>
				<FormFileUpload
					onChange={ ( e ) => uploadMedia( e ) }
					multiple={ true }
					className="browser button button-hero"
				>
					{ __( 'Select Files', 'bp-attachments' ) }
				</FormFileUpload>
			</div>
		</div>
	);
 };

 export default MediaLibraryUploader;
