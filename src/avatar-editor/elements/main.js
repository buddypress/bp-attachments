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

const AvatarEditorMain = ( { settings, originalImageSrc, onOriginalImageLoaded } ) => {
	const { avatarFullWidth, avatarFullHeight } = settings;
	const canvasRef = useRef( null );
	const originalImageRef = useRef( null );

	const setOriginalImageSize = ( event ) => {
		onOriginalImageLoaded( {
			naturalHeight: event.target.naturalHeight,
			naturalWidth: event.target.naturalWidth,
		} );
	};

	return (
		<AvatarEditorPortal selector="bp-attachments-avatar-editor">
			<Fragment>
				<canvas ref={canvasRef} width={avatarFullWidth} height={avatarFullHeight} />
				{ !! originalImageSrc && (
					<img ref={ originalImageRef } className="bp-hide" src={ originalImageSrc } onLoad={ ( e ) => setOriginalImageSize( e ) } />
				) }
			</Fragment>
		</AvatarEditorPortal>
	);
}

export default AvatarEditorMain;
