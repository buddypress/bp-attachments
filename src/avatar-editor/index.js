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
import AvatarCropper from './elements/cropper';

const AvatarEditor = ( { settings } ) => {
	const [ currentImage, setCurrentImage ] = useState( {
		file: null,
		src: null,
		x: 0,
		y: 0,
		width: 0,
		height: 0,
		area: {},
		originalSize: {},
		zoom: 1,
		isUploading: false,
	} );

	const onSelectedImage = ( files ) => {
		let image;

		if ( files.currentTarget && files.currentTarget.files ) {
			image = [ ...files.currentTarget.files ];
		} else {
			image = files;
		}

		// Only use the first image.
		setCurrentImage( {
			...currentImage,
			file: image[0],
			src: createBlobURL( image[0] ),
		} );
	};

	const onCropEdit = ( croppedArea, croppedAreaPixels, zoom ) => {
		const newImage = currentImage;

		if ( null !== croppedArea ) {
			newImage.area = croppedArea;
		}

		if ( null !== croppedAreaPixels ) {
			newImage.x = croppedAreaPixels.x;
			newImage.y = croppedAreaPixels.y;
			newImage.width = croppedAreaPixels.width;
			newImage.height = croppedAreaPixels.height;
		}

		if ( null !== zoom ) {
			newImage.zoom = zoom;
		}

		setCurrentImage( newImage );
	};

	const onSaveEdits = () => {
		setCurrentImage( {
			...currentImage,
			isUploading: true,
		} );
	}

	if ( !! currentImage.isUploading ) {
		const roundedX = ( Math.round( parseFloat( currentImage.area.x ) * 10 ) / 10 );
		const roundedY = ( Math.round( parseFloat( currentImage.area.y ) * 10 ) / 10 );

		const imgStyle = {
			height: '100%',
			width: 'auto',
			transform: 'translate(-' + parseInt( currentImage.zoom ) * roundedX + '%,-'  + parseInt( currentImage.zoom ) * roundedY + '%) scale(' + currentImage.zoom + ')',
			transformOrigin: 'top left',
		};

		return (
			<img src={ currentImage.src } style={ imgStyle } />
		);
	}

	if ( !! currentImage.src ) {
		if ( ! currentImage.originalSize.naturalHeight ) {
			const getImageSize = ( event ) => {
				setCurrentImage( {
					...currentImage,
					originalSize: {
						naturalHeight: event.target.naturalHeight,
						naturalWidth: event.target.naturalWidth,
					},
				} );
			};

			return (
				<img src={ currentImage.src } onLoad={ ( e ) => getImageSize( e ) } />
			);
		}

		return (
			<AvatarCropper
				image={ currentImage.src }
				originalSize={ currentImage.originalSize }
				onCropEdit={ onCropEdit }
				onSaveEdits={ onSaveEdits }
			/>
		);
	}

	if ( ! currentImage.src ) {
		return (
			<AvatarEditorUploader
				settings={ settings }
				onSelectedImage={ onSelectedImage }
			/>
		);
	}
};

domReady( function() {
	const settings = window.bpAttachmentsAvatarEditorSettings || {};
	render( <AvatarEditor settings={ settings }/>, document.querySelector( '#bp-avatar-editor' ) );
} );
