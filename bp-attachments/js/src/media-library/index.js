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
	},
} = wp;

/**
 * Internal dependencies.
 */
import { BP_ATTACHMENTS_STORE_KEY } from './store';
import MediaLibraryHeader from './elements/header';
import MediaLibraryToolbar from './elements/toolbar';
import MediaLibraryMain from './elements/main';

const MediaLibrary = () => {
	const isGrid = useSelect( ( select ) => {
		return select( BP_ATTACHMENTS_STORE_KEY ).isGridDisplayMode();
	}, [] );

	return (
		<Fragment>
			<MediaLibraryHeader/>
			<MediaLibraryToolbar gridDisplay={ isGrid } />
			<MediaLibraryMain gridDisplay={ isGrid } />
		</Fragment>
	);
};

domReady( function() {
	render( <MediaLibrary/>, document.querySelector( '#bp-media-library' ) );
} );
