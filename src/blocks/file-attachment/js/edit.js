/**
 * WordPress dependencies.
 */
const {
	blockEditor: {
		BlockAlignmentControl,
		BlockControls,
		useBlockProps,
		RichText,
	},
	data: {
		useSelect,
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

const editFile = ( { attributes, setAttributes } ) => {
	const {
		bpAttachments: {
			mimeTypeImageBaseUrl,
		},
	} = useSelect( ( select ) => {
		return select( 'core/editor' ).getEditorSettings();
	}, [] );
	const { align, name, url, mediaType } = attributes;
	const blockProps = useBlockProps( {
		className: !! align && undefined !== align ? 'wp-block-bp-file-attachment align' + align : 'wp-block-bp-file-attachment',
	} );

	if ( ! url ) {
		return (
			<figure { ...blockProps }>
				<AttachmentPlaceholder
					type="any"
					icon="media-text"
					label={ __( 'Community File', 'bp-attachments' ) }
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
			<div { ...blockProps }>
				<div className="bp-attachment-file-icon">
					<img src={ mimeTypeImageBaseUrl + mediaType + '.png' } />
				</div>
				<div className="bp-attachment-file-content">
					<div className="bp-attachment-file-title">
						<RichText
							tagName="a"
							value={ name }
							placeholder={ __( 'Write file name…', 'bp-attachments' ) }
							withoutInteractiveFormatting
							onChange={ ( text ) =>
								setAttributes( { name: text } )
							}
							href={ url }
						/>
					</div>
					<div className="wp-element-button bp-attachments-button">{ __( 'Download', 'bp-attachments') }</div>
				</div>
			</div>
		</Fragment>
	)
};

export default editFile;
