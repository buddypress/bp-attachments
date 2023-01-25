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
	const { maxUploadFileSize, allowedExtTypes, allowedExtByMediaList } = settings;
	const { updateFormState, createMedium } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const { formState, currentDirectoryObject } = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY )
		return {
			formState: store.getFormState(),
			currentDirectoryObject: store.getCurrentDirectoryObject(),
		}
	}, [] );

	const resetFormState = () => {
		formState.action = '';
		updateFormState( formState );
	}

	const closeForm = ( e ) => {
		e.preventDefault();
		resetFormState();
	};

	const uploadMedia = ( files ) => {
		let media;
		let bytes = 0;

		if ( files.currentTarget && files.currentTarget.files ) {
			media = [ ...files.currentTarget.files ];
		} else {
			media = files;
		}

		let numMedia = media.length;

		media.forEach( ( medium ) => {
			bytes += parseInt( medium.size, 10 );
			numMedia -= 1;

			const totalBytes = 0 === numMedia ? bytes : 0;
			createMedium( medium, totalBytes );
		} );

		resetFormState();
	};

	if ( ! formState.action || 'upload' !== formState.action ) {
		return null;
	}

	let allowedExts = allowedExtTypes;
	const directoryTypes = [ 'album', 'audio_playlist', 'video_playlist' ];

	if ( currentDirectoryObject.type && -1 !== directoryTypes.indexOf( currentDirectoryObject.type ) ) {
		allowedExts = allowedExtByMediaList[ currentDirectoryObject.type ];
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
					accept={ allowedExts }
					className="browser button button-hero"
				>
					{ __( 'Select Files', 'bp-attachments' ) }
				</FormFileUpload>
			</div>
			<div className="upload-restrictions">
				<p>
				{
					/* translators: %s is the max size allowed for a media file */
					sprintf( __( 'Maximum upload file size: %s.', 'bp-attachments' ), bytesToSize( maxUploadFileSize ) )
				}
				</p>
			</div>
		</div>
	);
};

export default MediaLibraryUploader;
