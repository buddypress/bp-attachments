/**
 * WordPress dependencies
 */
const {
	apiFetch,
	blob: {
		createBlobURL,
	},
	domReady,
	element: {
		createElement,
		render,
		useState,
	},
	i18n: {
		__,
	},
} = wp;

/**
 * Internal dependencies.
 */
import AvatarEditorUploader from './elements/uploader';

const AvatarEditor = ( { settings } ) => {
	const [ currentStep, setCurrentStep ] = useState( 'upload' );
	const [ currentBlob, setCurrentBlob ] = useState( '' );

	const uploadImage = ( files ) => {
		let image;

		if ( files.currentTarget && files.currentTarget.files ) {
			image = [ ...files.currentTarget.files ];
		} else {
			image = files;
		}

		const imgSrc = createBlobURL( image[0] );
		setCurrentBlob( imgSrc );
	};

	if ( currentBlob ) {
		return (
			<img src={ currentBlob } />
		);
	}

	if ( 'upload' === currentStep ) {
		return (
			<AvatarEditorUploader settings={ settings } onSelectedImage={ uploadImage }/>
		);
	}
};

domReady( function() {
	const settings = window.bpAttachmentsAvatarEditorSettings || {};
	render( <AvatarEditor settings={ settings }/>, document.querySelector( '#bp-avatar-placeholder' ) );
} );
