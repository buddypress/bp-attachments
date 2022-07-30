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

	const onCropEdit = ( croppedArea, croppedAreaPixels ) => {
		setCurrentImage( {
			...currentImage,
			x: croppedAreaPixels.x,
			y: croppedAreaPixels.y,
			width: croppedAreaPixels.width,
			height: croppedAreaPixels.height,
			area: croppedArea,
		} );
	};

	const onSaveEdits = () => {
		setCurrentImage( {
			...currentImage,
			isUploading: true,
		} );
	}

	if ( !! currentImage.isUploading ) {
		const roundedX = Math.round( parseInt( currentImage.area.x, 10 ) * 10 ) / 10;
		const roundedY = Math.round( parseInt( currentImage.area.y, 10 ) * 10 ) / 10;
		const roundedXw = ( Math.round( ( parseInt( currentImage.area.x, 10 ) + parseInt( currentImage.area.width, 10 ) ) * 10 ) / 10 ) + 1;
		const roundedYh = ( Math.round( ( parseInt( currentImage.area.y, 10 ) + parseInt( currentImage.area.height, 10 ) ) * 10 ) / 10 ) + 1;

		const imgStyle = {
			height: currentImage.height + 'px',
			width: 'auto',
			transform: 'translate(-' + roundedX + '%,-'  + roundedY + '%)',
			clipPath: 'polygon(' + roundedX + '% '  + roundedY + '%, ' + roundedXw + '% ' + roundedY + '%, ' + roundedXw + '% ' + roundedYh + '%, ' + roundedX + '% '  + roundedYh + '%)',
		};

		return (
			<img src={ currentImage.src } style={ imgStyle } />
		);
	}

	if ( !! currentImage.src ) {
		return (
			<AvatarCropper
				image={ currentImage.src }
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
