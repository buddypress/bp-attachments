/**
 * External dependencies.
 */
const {
	find,
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

/**
 * Toolbar element.
 */
const MediaLibraryToolbar = ( { gridDisplay, tree } ) => {
	const { switchDisplayMode, requestMedia } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const switchMode = ( e, isGrid ) => {
		e.preventDefault();
		switchDisplayMode( isGrid );
	};
	const { currentDirectory, path, flatTree } = useSelect( ( select ) => {
		const store = select( BP_ATTACHMENTS_STORE_KEY )
		return {
			currentDirectory: store.getCurrentDirectory(),
			path: store.getRelativePath(), // @todo check we really need this.
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
			args.directory = directoryItem.name;
			args.parent = directoryItem.id;

			if ( directoryItem.parent ) {
				args.path = path.split( directoryItem.name )[0]
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
