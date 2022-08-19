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
import AttachmentPlaceholder from '../../common/components/attachment-placeholder';

registerBlockType( metadata, {
	title: __( 'Community Image', 'bp-attachments' ),
	description: __( 'Insert an image from your personal media library.', 'bp-attachments' ),
	icon: {
		background: '#fff',
		foreground: '#d84800',
		src: 'format-image',
	},
	category: 'media',
	keywords: [
		'BuddyPress',
		__( 'community', 'bp-attachments' ),
		__( 'image', 'bp-attachments' ),
		__( 'img', 'bp-attachments' ),
		__( 'picture', 'bp-attachments' ),
		__( 'photo', 'bp-attachments' ),
		__( 'media', 'bp-attachments' ),
	],
	attributes: {
		link: {
			type: 'string',
		}
	},
	supports: {
		'align': false,
		'alignWide': false,
		'anchor': false,
		'className': false,
		'html': false,
	},
	edit: function( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();

		return (
			<AttachmentPlaceholder
				type="image"
				icon="format-image"
				label={ __( 'Community Image', 'bp-attachments' ) }
			/>
		);
	},
	save: function( { attributes } ) {
		const blockProps = useBlockProps.save();
		return null;
	}
} );
