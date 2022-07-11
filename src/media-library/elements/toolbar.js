/**
 * External dependencies.
 */
const {
	find,
	reverse,
} = lodash;

/**
 * WordPress dependencies
 */
const {
	element: {
		createElement,
		useState,
	},
	components: {
		Button,
		TreeSelect,
	},
	i18n: {
		__,
	},
	data: {
		useDispatch,
		useSelect,
	},
} = wp;

/**
 * Internal dependencies.
 */
import { BP_ATTACHMENTS_STORE_KEY } from '../store';
import { getDirectoryAncestors } from '../utils/functions';

/**
 * Toolbar element.
 */
const MediaLibraryToolbar = ( { gridDisplay, tree } ) => {
	const {
		switchDisplayMode,
		requestMedia,
		toggleSelectable,
		toggleMediaSelection,
		deleteMedium,
	} = useDispatch( BP_ATTACHMENTS_STORE_KEY );

	const {
		user,
		currentDirectory,
		currentDirectoryObject,
		flatTree,
		isSelectable,
		selectedMedia,
	} = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY )
		return {
			user: store.getLoggedInUser(),
			currentDirectory: store.getCurrentDirectory(),
			currentDirectoryObject: store.getCurrentDirectoryObject(),
			flatTree: store.getFlatTree(),
			isSelectable: store.isSelectable(),
			selectedMedia: store.selectedMedia(),
		}
	}, [] );
	const [ page, setPage ] = useState( currentDirectory );
	const canSelect = true !== currentDirectoryObject.readonly;
	const hasSelectedMedia = isSelectable && selectedMedia.length !== 0;

	if ( currentDirectory !== page ) {
		setPage( currentDirectory );
	}

	const switchMode = ( e, isGrid ) => {
		e.preventDefault();
		switchDisplayMode( isGrid );
	};

	const changeDirectory = ( directory ) => {
		setPage( directory );

		const directoryItem = find( flatTree, { id: directory } );
		let args = {};

		if ( directoryItem ) {
			args.directory = directoryItem.slug;
			args.parent = directoryItem.id;

			if ( directoryItem.parent && directoryItem.object ) {
				let chunks = reverse( getDirectoryAncestors(
					flatTree,
					directoryItem.parent
				).map( ( parent ) => parent.slug ) );

				if ( 'members' === directoryItem.object ) {
					/**
					 * Why do we have this member chunk?
					 *
					 * @todo find why!
					 */
					const memberIndex = chunks.indexOf( 'member' );
					if ( -1 !== memberIndex ) {
						chunks.splice( memberIndex, 1 );
					}

					if ( chunks.length ) {
						chunks.splice( 1, 0, directoryItem.object, user.id );
					}
				}

				/**
				 * @todo handle Groups object.
				 */

				args.path = '/' + chunks.join( '/' );
			}

			if ( directoryItem.object ) {
				args.object = directoryItem.object;
			}
		}

		return requestMedia( args );
	};

	const onToggleSectable = ( event ) => {
		event.preventDefault();
		const toggle = ! isSelectable;

		if ( ! toggle ) {
			toggleMediaSelection( ['all'], toggle );
		}

		return toggleSelectable( toggle );
	};

	const onDeleteSelected = ( event ) => {
		event.preventDefault();

		selectedMedia.forEach( medium => {
			deleteMedium( medium );
		} );

		return toggleSelectable( false );
	}

	return (
		<div className="media-toolbar wp-filter">
			<div className="media-toolbar-secondary">
				{ ! isSelectable && (
					<div className="view-switch media-grid-view-switch">
						<a href="#view-list" onClick={ ( e ) => switchMode( e, false ) } className={ gridDisplay ? 'view-list' : 'view-list current' }>
							<span className="screen-reader-text">{ __( 'Display list', 'bp-attachments' ) }</span>
						</a>
						<a href="#view-grid" onClick={ ( e ) => switchMode( e, true ) } className={ gridDisplay ? 'view-grid current' : 'view-grid' }>
							<span className="screen-reader-text">{ __( 'Display grid', 'bp-attachments' ) }</span>
						</a>
					</div>
				) }
				{ canSelect && (
					<Button variant="secondary" className="media-button select-mode-toggle-button" onClick={ ( e ) => onToggleSectable( e ) }>
						{ ! isSelectable ? __( 'Select', 'bp-attachments' ) : __( 'Cancel Selection', 'bp-attachments' ) }
					</Button>
				) }
				{ canSelect && hasSelectedMedia && (
					<Button variant="primary" className="media-button delete-selected-button" onClick={ ( e ) => onDeleteSelected( e ) }>
						{ __( 'Delete selection', 'bp-attachments' ) }
					</Button>
				) }
			</div>
			{ !! tree.length && (
				<div className="media-toolbar-primary">
					<TreeSelect
						noOptionLabel={ __( 'Home', 'bp-attachments' ) }
						onChange={ ( directory ) => changeDirectory( directory ) }
						selectedId={ page }
						tree={ tree }
					/>
				</div>
			) }
		</div>
	);
};

export default MediaLibraryToolbar;
