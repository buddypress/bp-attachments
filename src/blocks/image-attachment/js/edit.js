/**
 * WordPress dependencies.
 */
const {
	blockEditor: {
		BlockAlignmentControl,
		BlockControls,
		useBlockProps,
	},
	element: {
		createElement,
		Fragment,
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
	const { align, src } = attributes;
	const blockProps = useBlockProps( {
		className: !! align && undefined !== align ? 'wp-block-bp-image-attachment align' + align : 'wp-block-bp-image-attachment',
	} );

	if ( ! src ) {
		return (
			<figure { ...blockProps }>
				<AttachmentPlaceholder
					type="image"
					icon="format-image"
					label={ __( 'Community Image', 'bp-attachments' ) }
					onSetAttributes={ setAttributes }
				/>
			</figure>
		);
	}

	return (
		<Fragment>
			<BlockControls group="block">
				<BlockAlignmentControl
					controls={ [ 'none', 'left', 'center', 'right' ] }
					value={ align }
					onChange={ ( alignment ) => setAttributes( { align: alignment } ) }
				/>
			</BlockControls>
			<figure { ...blockProps }>
				<img src={ src } />
			</figure>
		</Fragment>
	)
};

export default editImage;
