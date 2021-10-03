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
} = wp;

/**
 * Header element.
 */
const MediaLibraryHeader = () => {
	return (
		<Fragment>
			<h1 className="wp-heading-inline">
				{ __( 'Community Media Library', 'bp-attachments' ) }
			</h1>
			<hr className="wp-header-end"></hr>
		</Fragment>
	);
};

export default MediaLibraryHeader;
