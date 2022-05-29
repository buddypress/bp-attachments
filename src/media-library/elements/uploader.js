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
		sprintf,
	},
} = wp;

/**
 * Internal dependencies.
 */
import { BP_ATTACHMENTS_STORE_KEY } from '../store';
import { bytesToSize } from '../utils/functions';

/**
 * File Uploader element.
 */
const MediaLibraryUploader = ( { settings } ) => {
	const { updateFormState, createMedium } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const formState = useSelect( ( select ) => {
		return select( BP_ATTACHMENTS_STORE_KEY ).getFormState();
	}, [] );

	const resetFormState = () => {
		formState.action = '';
		return updateFormState( formState );
	}

	const closeForm = ( e ) => {
		e.preventDefault();
		resetFormState();
	};

	const uploadMedia = ( files ) => {
		let media;

		if ( files.currentTarget && files.currentTarget.files ) {
			media = [ ...files.currentTarget.files ];
		} else {
			media = files;
		}

		media.forEach( ( medium ) => {
			createMedium( medium );
		} );

		resetFormState();
	};

	if ( ! formState.action || 'upload' !== formState.action ) {
		return null;
	}

	return (
		<div className="uploader-container enabled">
			<DropZone
				label={ __( 'Drop your files here.', 'bp-attachments' ) }
				onFilesDrop={ ( files ) => uploadMedia( files ) }
				className="uploader-inline"
			/>
			<button className="close dashicons dashicons-no" onClick={ ( e ) => closeForm( e ) }>
				<span className="screen-reader-text">{ __( 'Close the upload panel', 'bp-attachments' ) }</span>
			</button>
			<div className="dropzone-label">
				<h2 className="upload-instructions drop-instructions">{ __( 'Drop files to upload', 'bp-attachments' ) }</h2>
				<p className="upload-instructions drop-instructions">{ __( 'or', 'bp-attachments' ) }</p>
				<FormFileUpload
					onChange={ ( files ) => uploadMedia( files ) }
					multiple={ true }
					className="browser button button-hero"
				>
					{ __( 'Select Files', 'bp-attachments' ) }
				</FormFileUpload>
			</div>
			<div className="upload-restrictions">
				<p>{ sprintf( __( 'Maximum upload file size: %s.', 'bp-attachments' ), bytesToSize( settings.maxUploadFileSize ) ) }</p>
			</div>
		</div>
	);
 };

 export default MediaLibraryUploader;
