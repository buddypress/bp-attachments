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
import editImage from './edit';

registerBlockType( metadata, {
	title: __( 'Community Image', 'bp-attachments' ),
	description: __( 'Insert an image into your personal media library and use it for this content.', 'bp-attachments' ),
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
	edit: editImage,
	save: function( { attributes } ) {
		const blockProps = useBlockProps.save();
		return null;
	}
} );
