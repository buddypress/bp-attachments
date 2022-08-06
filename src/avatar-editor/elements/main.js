/**
 * WordPress dependencies
 */
const {
	element: {
		createElement,
		Fragment,
		useRef,
	},
	i18n: {
		__,
	},
} = wp;

/**
 * Internal dependencies.
 */
import AvatarEditorPortal from './portal';

const AvatarEditorMain = ( { settings, currentImage, onOriginalImageLoaded, onCreateProfilePhoto } ) => {
	const { avatarFullWidth, avatarFullHeight } = settings;
	const {
		height,
		isUploading,
		src,
		width,
		x,
		y,
	} = currentImage;
	const canvasRef = useRef( null );
	const originalImageRef = useRef( null );

	const drawProfileImage = ( x = 0, y = 0, w = avatarFullWidth, h = avatarFullHeight ) => {
		const canvas = canvasRef.current;
		const profileImage = originalImageRef.current;
		const context = canvas.getContext( '2d' );

		context.drawImage( profileImage, x, y, w, h, 0, 0, avatarFullWidth, avatarFullHeight );

		onCreateProfilePhoto( canvas.toDataURL( 'image/png' ) );
	};

	const setOriginalImageSize = ( event ) => {
		onOriginalImageLoaded( {
			naturalHeight: event.target.naturalHeight,
			naturalWidth: event.target.naturalWidth,
		} );
	};

	if ( !! isUploading ) {
		drawProfileImage( x, y, width, height );
	}

	return (
		<AvatarEditorPortal selector="bp-attachments-avatar-editor">
			<Fragment>
				<canvas
					ref={ canvasRef }
					className="bp-hide"
					width={ avatarFullWidth }
					height={ avatarFullHeight }
				/>
				{ !! src && (
					<img
						ref={ originalImageRef }
						className="bp-hide"
						src={ src }
						onLoad={ ( e ) => setOriginalImageSize( e ) }
					/>
				) }
			</Fragment>
		</AvatarEditorPortal>
	);
}

export default AvatarEditorMain;
