/**
 * WordPress dependencies
 */
const {
	components: {
		Button,
		TextControl,
	},
	data: {
		useDispatch,
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
 * Internal dependencies.
 */
import { BP_ATTACHMENTS_STORE_KEY } from '../store';

/**
 * Directory Creator element.
 */
const MediaLibraryDirectoryCreator = ( { settings } ) => {
	const { allowedMediaTypes } = settings;
	const [ directoryName, setDirectoryName ] = useState( '' );
	const { updateFormState, createDirectory } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const formState = useSelect( ( select ) => {
		return select( BP_ATTACHMENTS_STORE_KEY ).getFormState();
	}, [] );

	const resetFormState = () => {
		formState.action = '';
		formState.directoryType = '';
		return updateFormState( formState );
	}

	const closeForm = ( e ) => {
		e.preventDefault();
		resetFormState();
	};

	const makeDirectory = ( e ) => {
		e.preventDefault();

		const directory = {
			directoryName: directoryName,
			directoryType: formState.directoryType,
		};

		createDirectory( directory );
		setDirectoryName( '' );
		resetFormState();
	};

	if ( ! formState.action || 'createDirectory' !== formState.action ) {
		return null;
	}

	// @todo allowedMediaTypes should be checked to make all directory types can be created.

	let title = __( 'Create a new directory', 'bp-attachments' );
	let nameLabel = __( 'Type a name for your directory', 'bp-attachments'  );
	let buttonLabel = __( 'Save directory', 'bp-attachments' );

	if ( 'album' === formState.directoryType ) {
		title = __( 'Create a new photo album', 'bp-attachments' );
		nameLabel = __( 'Type a name for your photo album', 'bp-attachments'  );
		buttonLabel = __( 'Save photo album', 'bp-attachments' );
	} else if ( 'audio_playlist' === formState.directoryType ) {
		title = __( 'Create a new audio playlist', 'bp-attachments' );
		nameLabel = __( 'Type a name for your audio playlist', 'bp-attachments'  );
		buttonLabel = __( 'Save audio playlist', 'bp-attachments' );
	} else if ( 'video_playlist' === formState.directoryType ) {
		title = __( 'Create a new video playlist', 'bp-attachments' );
		nameLabel = __( 'Type a name for your video playlist', 'bp-attachments'  );
		buttonLabel = __( 'Save video playlist', 'bp-attachments' );
	}

	return (
		<form id="bp-media-directory-form" className="directory-creator-container enabled">
			<button className="close dashicons dashicons-no" onClick={ ( e ) => closeForm( e ) }>
				<span className="screen-reader-text">{ __( 'Close the Create directory form', 'bp-attachments' ) }</span>
			</button>
			<h2>{ title }</h2>
			<TextControl
				label={ nameLabel }
				value={ directoryName }
				onChange={ ( directoryName ) => setDirectoryName( directoryName ) }
			/>
			<Button variant="primary" onClick={ ( e ) => makeDirectory( e ) }>
				{ buttonLabel }
			</Button>
		</form>
	);
}

export default MediaLibraryDirectoryCreator;
