/**
 * WordPress dependencies
 */
 const {
	element: {
		createElement,
		Fragment,
		useState,
	},
	components: {
		Modal,
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
import setTemplate from '../utils/set-template';
import EditMediaItem from './edit-item';

const MediaItem = ( props ) => {
	const {
		medium,
		selected,
		isGrid,
	} = props;
	const Template = !! isGrid ? setTemplate( 'bp-attachments-media-item' ) : setTemplate( 'bp-attachments-list-media-item' );
	const { toggleMediaSelection, requestMedia, setDisplayedUserId } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const [ isOpen, toggleModal ] = useState( false );
	const [ isSelected, selectMedia ] = useState( selected );
	const { getRelativePath, isSelectable } = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY );

		return {
			getRelativePath: store.getRelativePath(),
			isSelectable: store.isSelectable(),
		};
	}, [] );

	if ( ! isSelectable && ! selected && isSelected ) {
		selectMedia( false );
	}

	const classes = isSelected ? 'media-item selected' : 'media-item';
	const onMediaClick = () => {
		const { mimeType, name, isSelectable, id, object } = props;

		if ( isSelectable ) {
			selectMedia( ! isSelected );
			return toggleMediaSelection( [ id ], ! isSelected );
		}

		if ( 'inode/directory' === mimeType ) {
			const displayedUserId = 0 === id.indexOf( 'member-' ) ? parseInt( id.replace( 'member-', '' ), 10 ) : 0;

			if ( !! displayedUserId ) {
				setDisplayedUserId( displayedUserId );
			}

			return requestMedia( { directory: name, path: getRelativePath, object: object, parent: id } );
		}

		toggleModal( true );
	};

	return (
		<Fragment>
			<div
				className={ classes }
				dangerouslySetInnerHTML={ { __html: Template( props ) } }
				role="checkbox"
				onClick={ () => onMediaClick() }
			/>
			{ isOpen && (
				<Modal
					title={ __( 'Media details', 'bp-attachments' ) }
					onRequestClose={ () => toggleModal( false ) }
				>
					<EditMediaItem medium={ medium } errorCallback={ toggleModal }/>
				</Modal>
			) }
		</Fragment>
	);
};

export default MediaItem;
