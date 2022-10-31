/**
 * WordPress dependencies
 */
const {
	element: {
		createElement,
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
import { BP_ATTACHMENTS_STORE_KEY } from '../store';
import MediaItem from './item';
import MediaLibraryNotices from './notices';
import { bytesToSize } from '../utils/functions';

/**
 * Main element.
 */
const MediaLibraryMain = ( { gridDisplay } ) => {
	const { items, isSelectable } = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY );

		return {
			items: store.getMedia(),
			isSelectable: store.isSelectable(),
		};
	}, [] );
	const modeClass = true === gridDisplay ? ' grid' : ' list';

	let mediaItems = null;
	if ( items.length ) {
		mediaItems = items.map( ( item ) => {
			return (
				<MediaItem
					key={ 'media-item-' + item.id }
					id={ item.id }
					name={ item.name }
					title={ item.title }
					description={ item.description }
					size={ bytesToSize( item.size ) }
					mediaType={ item.media_type }
					mimeType={ item.mime_type }
					icon={ item.icon }
					vignette={ item.vignette }
					orientation={ item.orientation }
					isSelected={ item.selected || false }
					object={ item.object || 'members' }
					isSelectable={ isSelectable }
					medium={ item }
					isGrid={ gridDisplay }
				/>
			);
		} );
	}

	return (
		<main className="bp-user-media">
			<MediaLibraryNotices />
			<div className={ isSelectable ? 'media-items mode-select' + modeClass : 'media-items' + modeClass }>
				{ mediaItems }
				{ ! items.length && (
					<p className="no-media">{ __( 'No community media items found.', 'bp-attachments' ) }</p>
				) }
			</div>
		</main>
	);
};

export default MediaLibraryMain;
