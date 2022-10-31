/**
 * WordPress dependencies
 */
const {
	components: {
		Popover,
	},
	data: {
		useDispatch,
		useSelect,
	},
	element: {
		createElement,
		Fragment,
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
 * Header element.
 */
const MediaLibraryHeader = ( { settings } ) => {
	const { updateFormState } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const {
		currentDirectoryObject,
		user: {
			capabilities,
		},
		displayedUserId,
	} = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY );

		return {
			currentDirectoryObject: store.getCurrentDirectoryObject(),
			user: store.getLoggedInUser(),
			displayedUserId: store.getDisplayedUserId(),
		};
	}, [] );
	const [ isOpen, setOpen ] = useState( false );
	const toggleClass = true === isOpen ? 'split-button is-open' : 'split-button';
	const dashiconClass = true === isOpen ? 'dashicons dashicons-arrow-up-alt2' : 'dashicons dashicons-arrow-down-alt2';
	const canUpload = true !== currentDirectoryObject.readonly && ( ! displayedUserId || displayedUserId === user.id );
	const { allowedExtByMediaList, isAdminScreen } = settings;
	const hrClass = isAdminScreen ? 'wp-header-end' : 'screen-header-end';
	const pageTitle = !! capabilities && -1 !== capabilities.indexOf( 'bp_moderate' ) ? __( 'Community Media Libraries', 'bp-attachments' ) : __( 'Community Media Library', 'bp-attachments' );

	const showUploadForm = ( e ) => {
		e.preventDefault();

		return updateFormState(
			{
				parentDirectory: currentDirectoryObject.id,
				action: 'upload',
			}
		);
	};

	const showCreateDirForm = ( e, type ) => {
		e.preventDefault();

		return updateFormState(
			{
				parentDirectory: currentDirectoryObject.id,
				action: 'createDirectory',
				directoryType: type,
			}
		);
	};

	let dirOptions = [];
	const directoryTypes = [ 'album', 'audio_playlist', 'video_playlist' ];

	if ( ! currentDirectoryObject.type || -1 === directoryTypes.indexOf( currentDirectoryObject.type ) ) {
		dirOptions = [
			{
				id: 'folder',
				text: __( 'Add new directory', 'bp-attachments' ),
			}
		];

		if ( allowedExtByMediaList && 'private' !== currentDirectoryObject.visibility ) {
			Object.keys( allowedExtByMediaList ).forEach( ( directoryType ) => {
				if ( 'album' === directoryType ) {
					dirOptions.push(
						{
							id: 'album',
							text: __( 'Add new photo album', 'bp-attachments' ),
						}
					);
				} else if ( 'audio_playlist' === directoryType ) {
					dirOptions.push(
						{
							id: 'audio_playlist',
							text: __( 'Add new audio playlist', 'bp-attachments' ),
						}
					);
				} else if ( 'video_playlist' === directoryType ) {
					dirOptions.push(
						{
							id: 'video_playlist',
							text: __( 'Add new video playlist', 'bp-attachments' ),
						}
					);
				}
			} );
		}
	}

	const dirList = dirOptions.map( ( dirOption ) => {
		return (
			<li key={ 'type-' + dirOption.id }>
				<a href="#new-bp-media-directory" className="button-link directory-button split-button-option" onClick={ ( e ) => showCreateDirForm( e, dirOption.id ) }>
					{ dirOption.text }
				</a>
			</li>
		);
	} );

	return (
		<Fragment>
			{ !! isAdminScreen && (
				<h1 className="wp-heading-inline">
					{ pageTitle }
				</h1>
			) }

			{ ! isAdminScreen && (
				<h3 className="screen-heading">
					{ __( 'Library', 'bp-attachments' ) }
				</h3>
			) }

			{ !! canUpload && (
				<div className={ toggleClass }>
					<div className="split-button-head">
						<a href="#new-bp-media-upload" className="button split-button-primary" aria-live="polite" onClick={ ( e ) => showUploadForm( e ) }>
							{ __( 'Add new', 'bp-attachments' ) }
						</a>
						<button type="button" className="split-button-toggle" aria-haspopup="true" aria-expanded={ isOpen } onClick={ () => setOpen( ! isOpen ) }>
							<i className={ dashiconClass }></i>
							<span className="screen-reader-text">{ __( 'More actions', 'bp-attachments' ) }</span>
							{ isOpen && (
								<Popover noArrow={ false } onFocusOutside={ () => setOpen( ! isOpen ) }>
									<ul className="split-button-body">
										<li>
											<a href="#new-bp-media-upload" className="button-link media-button split-button-option" onClick={ ( e ) => showUploadForm( e ) }>
												{ __( 'Upload media', 'bp-attachments' ) }
											</a>
										</li>
										{ dirList }
									</ul>
								</Popover>
							) }
						</button>
					</div>
				</div>
			) }
			<hr className={ hrClass }></hr>
		</Fragment>
	);
};

export default MediaLibraryHeader;
