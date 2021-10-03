/**
 * WordPress dependencies
 */
 const {
	element: {
		createElement,
	},
	i18n: {
		__,
	},
	data: {
		useDispatch,
	},
} = wp;

/**
 * Internal dependencies.
 */
 import { BP_ATTACHMENTS_STORE_KEY } from '../store';

/**
 * Toolbar element.
 */
const MediaLibraryToolbar = ( { gridDisplay } ) => {
	const { switchDisplayMode } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const switchMode = ( e, isGrid ) => {
		e.preventDefault();
		switchDisplayMode( isGrid );
	};

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
		</div>
	);
};

export default MediaLibraryToolbar;
