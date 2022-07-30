/**
 * External dependencies
 */
import Cropper from 'react-easy-crop';

/**
 * WordPress dependencies
 */
const {
	components: {
		Button,
		RangeControl,
	},
	element: {
		createElement,
		Fragment,
		useCallback,
		useState,
	},
	i18n: {
		__,
	},
} = wp;

/**
 * Internal dependencies.
 */
import AvatarEditorPortal from './portal';

const AvatarCropper = ( { image, onCropEdit, onSaveEdits } ) => {
	const [ crop, setCrop ] = useState( { x: 0, y: 0 } );
	const [ zoom, setZoom ] = useState( 1 );

	const onMediaLoaded = useCallback( ( mediaSize ) => {
		onCropEdit( null, null, null, mediaSize );
	}, [] );

	const onCropComplete = useCallback( ( croppedArea, croppedAreaPixels ) => {
		onCropEdit( croppedArea, croppedAreaPixels, null, null );
	}, [] );

	const editZoom = useCallback( ( zoomValue ) => {
		onCropEdit( null, null, zoomValue, null );
		setZoom( zoomValue );
	}, [] );

	return (
		<Fragment>
			<Cropper
				image={ image }
				objectFit="vertical-cover"
				crop={ crop }
				zoom={ zoom }
				aspect={ 1 }
				onCropChange={ setCrop }
				onCropComplete={ onCropComplete }
				onZoomChange={ editZoom }
				onMediaLoaded={ onMediaLoaded }
			/>
			<AvatarEditorPortal>
				<RangeControl
					label={ __( 'Zoom', 'bp-attachments' ) }
					value={ zoom }
					onChange={ editZoom }
					min={ 1 }
					max={ 10 }
				/>
				<Button variant="primary" onClick={ () => onSaveEdits() }>
					{ __( 'Save profile photo', 'bp-attachments' ) }
				</Button>
			</AvatarEditorPortal>
		</Fragment>
	);
}

export default AvatarCropper;
