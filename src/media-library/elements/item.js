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
		sprintf,
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

const MediaItem = ( props ) => {
	const Template = setTemplate( 'bp-attachments-media-item' );
	const { title, vignette } = props;
	const [ isOpen, toggleModal ] = useState( false );
	const [ isSelected, selectMedia ] = useState( false );
	const getRelativePath = useSelect( ( select ) => {
		return select( BP_ATTACHMENTS_STORE_KEY ).getRelativePath();
	}, [] );
	const { toggleMediaSelection, requestMedia } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const classes = isSelected ? 'media-item selected' : 'media-item';

	const onMediaClick = () => {
		const { mimeType, name, isSelectable, id, object } = props;

		if ( isSelectable ) {
			selectMedia( ! isSelected );
			return toggleMediaSelection( id, ! isSelected );
		}

		if ( 'inode/directory' === mimeType ) {
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
					title={ sprintf( __( 'Details for: %s', 'bp-attachments' ), title ) }
					onRequestClose={ () => toggleModal( false ) }
				>

					{ vignette && (
						<img src={vignette} className="mediaDetails" />
					) }

					{ ! vignette && (
						<p>{ __( '@todo Fetch the Media properties.', 'bp-attachments' ) }</p>
					) }
				</Modal>
			) }
		</Fragment>
	);
};

export default MediaItem;
