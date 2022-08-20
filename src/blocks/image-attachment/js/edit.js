/**
 * WordPress dependencies.
 */
const {
	blockEditor: {
		useBlockProps,
	},
	element: {
		createElement,
	},
	i18n: {
		__,
	},
} = wp;

/**
 * Internal dependencies.
 */
import AttachmentPlaceholder from '../../common/components/attachment-placeholder';

const editImage = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();

	return (
		<AttachmentPlaceholder
			type="image"
			icon="format-image"
			label={ __( 'Community Image', 'bp-attachments' ) }
		/>
	);
};

export default editImage;
