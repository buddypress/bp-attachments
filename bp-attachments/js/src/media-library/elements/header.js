/**
 * WordPress dependencies
 */
const {
	components: {
		Popover,
	},
	data: {
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
const MediaLibraryHeader = () => {
	const currentDirectory = useSelect( ( select ) => {
		return select( BP_ATTACHMENTS_STORE_KEY ).getCurrentDirectory();
	}, [] );
	const [ isOpen, setOpen ] = useState( false );
	const toggleClass = true === isOpen ? 'split-button is-open' : 'split-button';
	const dashiconClass = true === isOpen ? 'dashicons dashicons-arrow-up-alt2' : 'dashicons dashicons-arrow-down-alt2';

	const showUploadForm = ( e ) => {
		e.preventDefault();

		console.log( 'showUploadForm' );
	};

	const showCreateDirForm = ( e ) => {
		e.preventDefault();

		console.log( 'showCreateDirForm' );
	};

	return (
		<Fragment>
			<h1 className="wp-heading-inline">
				{ __( 'Community Media Library', 'bp-attachments' ) }
			</h1>
			{ !! currentDirectory && (
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
										<li>
											<a href="#new-bp-media-directory" className="button-link directory-button split-button-option" onClick={ ( e ) => showCreateDirForm( e ) }>
												{ __( 'Add new directory', 'bp-attachments' ) }
											</a>
										</li>
									</ul>
								</Popover>
							) }
						</button>
					</div>
				</div>
			) }
			<hr className="wp-header-end"></hr>
		</Fragment>
	);
};

export default MediaLibraryHeader;
