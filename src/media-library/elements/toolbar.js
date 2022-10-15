/**
 * External dependencies.
 */
const {
	find,
	reverse,
	filter,
} = lodash;

/**
 * WordPress dependencies
 */
const {
	components: {
		Button,
		Modal,
		TreeSelect,
	},
	element: {
		createElement,
		useState,
	},
	data: {
		useDispatch,
		useSelect,
	},
	hooks: {
		applyFilters,
	},
	i18n: {
		__,
	},
} = wp;

/**
 * Internal dependencies.
 */
import { BP_ATTACHMENTS_STORE_KEY } from '../store';
import { getDirectoryAncestors } from '../utils/functions';
import EditMediaItem from './edit-item';

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
		setDisplayedUserId,
	} = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const {
		user,
		displayedUserId,
		currentDirectory,
		currentDirectoryObject,
		flatTree,
		isSelectable,
		selectedMedia,
		settings,
	} = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY )
		return {
			user: store.getLoggedInUser(),
			displayedUserId: store.getDisplayedUserId(),
			currentDirectory: store.getCurrentDirectory(),
			currentDirectoryObject: store.getCurrentDirectoryObject(),
			flatTree: store.getFlatTree(),
			isSelectable: store.isSelectable(),
			selectedMedia: store.selectedMedia(),
			settings: store.getSettings(),
		}
	}, [] );
	const [ page, setPage ] = useState( currentDirectory );
	const [ isOpen, toggleModal ] = useState( false );
	const canSelect = true !== currentDirectoryObject.readonly;
	const hasSelectedMedia = isSelectable && selectedMedia.length !== 0;
	const hasOneSelectedMedia = isSelectable && selectedMedia.length === 1;
	const canModerate = !! settings.isAdminScreen && !! user.capabilities && -1 !== user.capabilities.indexOf( 'bp_moderate' );

	if ( currentDirectory !== page ) {
		setPage( currentDirectory );
	}

	const switchMode = ( e, isGrid ) => {
		e.preventDefault();
		switchDisplayMode( isGrid );
	};

	const changeDirectory = ( directory ) => {
		setPage( directory );
		const updateDisplayedUserId = 0 === directory.indexOf( 'member-' ) ? parseInt( directory.replace( 'member-', '' ), 10 ) : 0;
		if ( !! updateDisplayedUserId ) {
			setDisplayedUserId( updateDisplayedUserId );
		}

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
					 * In a future release, when Groups will be supported. The root directories will be:
					 * - My Groups Media,
					 * - My Media.
					 *
					 * The "My Media" ID is 'member'. We need to remove this from chunks as files are stored in
					 * `/uploads/buddypress/public/members/{userID}` or `../buddypress-private/members/{userID}`.
					 */
					const memberIndex = chunks.indexOf( 'member' );
					if ( -1 !== memberIndex ) {
						chunks.splice( memberIndex, 1 );
					}

					if ( chunks.length ) {
						chunks.splice( 1, 0, directoryItem.object, user.id );
					}
				} else {
					// Use this filter to customize the pathArray for other components (eg: groups).
					chunks = applyFilters(
						'buddypress.Attachments.toolbarTreeSelect.pathArray',
						chunks,
						directoryItem,
						user.id
					);
				}

				args.path = '/' + chunks.join( '/' );
			}

			if ( directoryItem.object ) {
				args.object = directoryItem.object;
			}

			if ( !! updateDisplayedUserId || !! displayedUserId ) {
				args.user_id = updateDisplayedUserId !== displayedUserId ? updateDisplayedUserId : displayedUserId;
			}

			/*
			 * When changing the selected option to 'All members', reset the displayedUserId.
			 * so that the Admin can go back to the list of members.
			 */
		} else if ( !! displayedUserId ) {
			setDisplayedUserId( 0 );
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

		let bytes = 0;
		let numSelectedMedia = selectedMedia.length;

		selectedMedia.forEach( medium => {
			// Deleted folders do not have a size.
			if ( !! medium.size ) {
				bytes += parseInt( medium.size, 10 );
			}

			numSelectedMedia -= 1;

			const totalBytes = 0 === numSelectedMedia ? bytes : 0;
			deleteMedium( medium, totalBytes );
		} );

		return toggleSelectable( false );
	};

	const onEditSelected = ( event ) => {
		event.preventDefault();

		toggleModal( true );
	};

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
				{ canSelect && hasOneSelectedMedia && (
					<Button variant="primary" className="media-button edit-selected-button" onClick={ ( e ) => onEditSelected( e ) }>
						{ __( 'Edit', 'bp-attachments' ) }
					</Button>
				) }
				{ canSelect && hasSelectedMedia && (
					<Button variant="tertiary" className="media-button delete-selected-button" onClick={ ( e ) => onDeleteSelected( e ) }>
						{ __( 'Delete selection', 'bp-attachments' ) }
					</Button>
				) }
				{ isOpen && (
						<Modal
							title={ __( 'Media details', 'bp-attachments' ) }
							onRequestClose={ () => toggleModal( false ) }
						>
							<EditMediaItem medium={ selectedMedia[0] } errorCallback={ toggleModal }/>
						</Modal>
					) }
			</div>
			{ !! tree.length && (
				<div className="media-toolbar-primary">
					<TreeSelect
						noOptionLabel={ !! displayedUserId ? __( 'All members', 'bp-attachments' ) : __( 'Home', 'bp-attachments' ) }
						onChange={ ( directory ) => changeDirectory( directory ) }
						selectedId={ page }
						tree={ ! canModerate ? tree : filter( tree, { id: 'member-' + displayedUserId || user.id } ) }
					/>
				</div>
			) }
		</div>
	);
};

export default MediaLibraryToolbar;
