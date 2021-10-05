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
	const { switchDisplayMode } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const switchMode = ( e, isGrid ) => {
		e.preventDefault();
		switchDisplayMode( isGrid );
	};

	/**
	 * @todo Add a state selector to get the current directory.
	 */
	const currentDirectory  = 'public-1';
	const [ page, setPage ] = useState( currentDirectory );

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
						onChange={ ( newPage ) => setPage( newPage ) }
						selectedId={ page }
						tree={ tree }
					/>
				</div>
			) }
		</div>
	);
};

export default MediaLibraryToolbar;
