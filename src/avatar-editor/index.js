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
		Fragment,
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
import AvatarEditorMain from './elements/main';

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

	let output = null;

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

	const onOriginalImageLoaded = ( imageSize ) => {
		setCurrentImage( {
			...currentImage,
			originalSize: {
				naturalHeight: imageSize.naturalHeight,
				naturalWidth: imageSize.naturalWidth,
			},
		} );
	}

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
		const {
			area,
			originalSize,
			zoom,
		} = currentImage;
		const roundedX = ( Math.round( parseFloat( area.x ) * 10 ) / 10 );
		const roundedY = ( Math.round( parseFloat( area.y ) * 10 ) / 10 );
		const isPortrait = originalSize.naturalHeight > originalSize.naturalWidth;
		const profileImageData = new FormData();
		profileImageData.append( 'file', currentImage.file );
		profileImageData.append( 'action', 'bp_avatar_upload' );
		profileImageData.append( 'crop_x', currentImage.x );
		profileImageData.append( 'crop_y', currentImage.y );
		profileImageData.append( 'crop_w', currentImage.width );
		profileImageData.append( 'crop_h', currentImage.height );

		apiFetch( {
			path: 'buddypress/v1/members/' + settings.displayedUserId + '/avatar',
			method: 'POST',
			body: profileImageData
		} ).catch( ( error ) => {
			console.log( error );
		} );

		const imgStyle = {
			height: isPortrait ? 'auto' : '100%',
			width: isPortrait ? '100%' : 'auto',
			transform: 'translate(-' + parseInt( zoom ) * roundedX + '%,-'  + parseInt( zoom ) * roundedY + '%) scale(' + zoom + ')',
			transformOrigin: 'top left',
		};

		output = (
			<img src={ currentImage.src } style={ imgStyle } />
		);
	} else if ( !! currentImage.src && !! currentImage.originalSize.naturalHeight ) {
		output = (
			<AvatarCropper
				image={ currentImage.src }
				originalSize={ currentImage.originalSize }
				onCropEdit={ onCropEdit }
				onSaveEdits={ onSaveEdits }
			/>
		);
	} else if ( ! currentImage.src ) {
		output = (
			<AvatarEditorUploader
				settings={ settings }
				onSelectedImage={ onSelectedImage }
			/>
		);
	}

	return (
		<Fragment>
			{ output }
			<AvatarEditorMain
				settings={ settings }
				originalImageSrc={ currentImage.src }
				onOriginalImageLoaded={ onOriginalImageLoaded }
			/>
		</Fragment>
	)
};

domReady( function() {
	const settings = window.bpAttachmentsAvatarEditorSettings || {};
	render( <AvatarEditor settings={ settings } />, document.querySelector( '#bp-avatar-editor' ) );
} );
