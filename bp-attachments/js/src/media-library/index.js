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
import MediaLibraryToolbar from './elements/toolbar';
import MediaLibraryMain from './elements/main';

const MediaLibrary = ( { settings } ) => {
	const { isGrid, globalSettings, tree } = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY );
		return {
			isGrid: store.isGridDisplayMode(),
			globalSettings: store.getSettings(),
			tree: store.getTree(),
		};
	}, [] );

	if ( ! Object.keys( globalSettings ).length ) {
		const { setSettings } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
		setSettings( settings );
	}

	return (
		<Fragment>
			<MediaLibraryHeader />
			<MediaLibraryUploader settings={ globalSettings } />
			<MediaLibraryToolbar gridDisplay={ isGrid } tree={ tree } />
			<MediaLibraryMain gridDisplay={ isGrid } tree={ tree } />
		</Fragment>
	);
};

domReady( function() {
	const settings = window.bpAttachmentsMediaLibrarySettings || {};
	render( <MediaLibrary settings={ settings }/>, document.querySelector( '#bp-media-library' ) );
} );
