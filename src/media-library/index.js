/**
 * WordPress dependencies
 */
const {
	domReady,
	element: {
		createElement,
		render,
		Fragment,
	},
	i18n: {
		__,
	},
	data: {
		useSelect,
		useDispatch,
	},
} = wp;

/**
 * Internal dependencies.
 */
import { BP_ATTACHMENTS_STORE_KEY } from './store';
import MediaLibraryHeader from './elements/header';
import MediaLibraryUploader from './elements/uploader';
import MediaLibraryDirectoryCreator from './elements/directory-creator';
import MediaLibraryToolbar from './elements/toolbar';
import MediaLibraryMain from './elements/main';
import MediaLibraryFooter from './elements/footer';

const MediaLibrary = ( { settings } ) => {
	const { isGrid, globalSettings } = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY );
		return {
			isGrid: store.isGridDisplayMode(),
			globalSettings: store.getSettings(),
		};
	}, [] );

	if ( ! Object.keys( globalSettings ).length ) {
		const { setSettings } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
		setSettings( settings );
	}

	return (
		<Fragment>
			<MediaLibraryHeader settings={ globalSettings } />
			<MediaLibraryUploader settings={ globalSettings } />
			<MediaLibraryDirectoryCreator />
			<MediaLibraryToolbar gridDisplay={ isGrid } />
			<MediaLibraryMain gridDisplay={ isGrid } />
			<MediaLibraryFooter settings={ globalSettings } />
		</Fragment>
	);
};

domReady( function() {
	const settings = window.bpAttachmentsMediaLibrarySettings || {};

	render( <MediaLibrary settings={ settings }/>, document.querySelector( '#bp-media-library' ) );
} );
