/**
 * WordPress dependencies
 */
const {
	apiFetch,
	blob: {
		createBlobURL,
		revokeBlobURL,
	},
	components: {
		Spinner,
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
import AvatarEditorFeedback from './elements/feedback';

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
		feedback: {},
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
			feedback: {},
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

	const onCreateProfilePhoto = ( base64Image ) => {
		const currentState = currentImage;
		const profileImageData = new FormData();
		profileImageData.append( 'file',  base64Image );

		apiFetch( {
			path: 'buddypress/v1/attachments-profile-image',
			method: 'POST',
			data: {
				user_id: settings.displayedUserId,
				image: base64Image,
				component: 'members',
			}
		} ).then( ( response ) => {
			if ( response.full ) {
				document.querySelector( '#item-header-avatar' ).style.backgroundImage = 'url( ' + response.full + ' )';
			}

			currentState.feedback = {
				type: 'updated',
				message: __( 'Profile image successfully saved.', 'bp-attachments' ),
			};

			setCurrentImage( currentState );

		} ).catch( ( error ) => {
			const errorMessage = !! error.message ? error.message : __( 'Unknow error. Please try again.', 'bp-attachments' );

			currentState.feedback = {
				type: 'error',
				message: errorMessage,
			};

			setCurrentImage( currentState );
		} ).finally( () => {
			revokeBlobURL( currentImage.src );
			const currentFeedback = currentImage.feedback;

			// Reset the state.
			setCurrentImage( {
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
				feedback: currentFeedback,
			} );
		} );
	};

	if ( !! currentImage.isUploading ) {
		const {
			area,
			originalSize,
			zoom,
		} = currentImage;
		const roundedX = ( Math.round( parseFloat( area.x ) * 10 ) / 10 );
		const roundedY = ( Math.round( parseFloat( area.y ) * 10 ) / 10 );
		const isPortrait = originalSize.naturalHeight > originalSize.naturalWidth;

		const imgStyle = {
			height: isPortrait ? 'auto' : '100%',
			width: isPortrait ? '100%' : 'auto',
			transform: 'translate(-' + parseInt( zoom ) * roundedX + '%,-'  + parseInt( zoom ) * roundedY + '%) scale(' + zoom + ')',
			transformOrigin: 'top left',
		};

		output = (
			<Fragment>
				<img src={ currentImage.src } style={ imgStyle } className="is-applying bp-profile-image-preview" />
				<Spinner />
			</Fragment>
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
				currentImage={ currentImage }
				onOriginalImageLoaded={ onOriginalImageLoaded }
				onCreateProfilePhoto={ onCreateProfilePhoto }
			/>
			{ !! currentImage.feedback.type && (
				<AvatarEditorFeedback type={ currentImage.feedback.type }>
					<p>
						{ currentImage.feedback.message }
					</p>
				</AvatarEditorFeedback>
			) }
		</Fragment>
	)
};

domReady( function() {
	const settings = window.bpAttachmentsAvatarEditorSettings || {};
	render( <AvatarEditor settings={ settings } />, document.querySelector( '#bp-avatar-editor' ) );
} );
