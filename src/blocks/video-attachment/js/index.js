/**
 * WordPress dependencies.
 */
const {
	blocks: {
		registerBlockType,
	},
	blockEditor: {
		useBlockProps,
	},
	components: {
		Placeholder,
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
import metadata from '../block.json';

registerBlockType( metadata, {
	icon: {
		background: '#fff',
		foreground: '#d84800',
		src: 'video-alt3',
	},
	edit: function( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();

		return (
			<figure { ...blockProps }>
				<Placeholder
					type="video"
					icon="video-alt3"
					label={ __( 'Community Video', 'bp-attachments' ) }
				/>
			</figure>
		);
	},
	save: function( { attributes} ) {
		const blockProps = useBlockProps.save();
		return null;
	}
} );
