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

const editAudio = ( { attributes, setAttributes } ) => {
	const { align, src } = attributes;
	const blockProps = useBlockProps( {
		className: !! align && undefined !== align ? 'wp-block-bp-audio-attachment align' + align : 'wp-block-bp-audio-attachment',
	} );

	if ( ! src ) {
		return (
			<figure { ...blockProps }>
				<AttachmentPlaceholder
					type="audio"
					icon="format-audio"
					label={ __( 'Community Audio', 'bp-attachments' ) }
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
				<audio controls="controls" preload="metadata" src={ src } />
				<figcaption class="wp-element-caption">
					{ __( 'If your browser does not take in charge this media format. Please download it to play it from your computer.', 'bp-attachments' ) }
				</figcaption>
			</figure>
		</Fragment>
	)
};

export default editAudio;
