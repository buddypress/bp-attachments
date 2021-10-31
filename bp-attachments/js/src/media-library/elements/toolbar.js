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
import getDirectoryAncestors from '../utils/tree-functions';

/**
 * Toolbar element.
 */
const MediaLibraryToolbar = ( { gridDisplay, tree } ) => {
	const { switchDisplayMode, requestMedia } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const switchMode = ( e, isGrid ) => {
		e.preventDefault();
		switchDisplayMode( isGrid );
	};
	const { user, currentDirectory, flatTree } = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY )
		return {
			user: store.getLoggedInUser(),
			currentDirectory: store.getCurrentDirectory(),
			flatTree: store.getFlatTree(),
		}
	}, [] );

	const [ page, setPage ] = useState( currentDirectory );
	if ( currentDirectory !== page ) {
		setPage( currentDirectory );
	}

	const changeDirectory = ( directory ) => {
		setPage( directory );

		const directoryItem = find( flatTree, { id: directory } );
		let args = {};

		if ( directoryItem ) {
			args.directory = directoryItem.slug;
			args.parent = directoryItem.id;

			if ( directoryItem.parent && directoryItem.object ) {
				let chunks = reverse(
					getDirectoryAncestors(
						flatTree,
						directoryItem.parent
					).map( ( parent ) => parent.slug )
				);

				if ( 'members' === directoryItem.object ) {
					chunks.splice( 1, 0, directoryItem.object, user.id );
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
	}

	return (
		<div className="media-toolbar wp-filter">
			<div className="media-toolbar-secondary">
				<div className="view-switch media-grid-view-switch">
					<a href="#view-list" onClick={ ( e ) => switchMode( e, false ) } className={ gridDisplay ? 'view-list' : 'view-list current' }>
						<span className="screen-reader-text">{ __( 'Display list', 'bp-attachments' ) }</span>
					</a>
					<a href="#view-grid" onClick={ ( e ) => switchMode( e, true ) } className={ gridDisplay ? 'view-grid current' : 'view-grid' }>
						<span className="screen-reader-text">{ __( 'Display grid', 'bp-attachments' ) }</span>
					</a>
				</div>
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
