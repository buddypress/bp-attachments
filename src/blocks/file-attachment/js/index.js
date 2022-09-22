/**
 * WordPress dependencies.
 */
const {
	blocks: {
		registerBlockType,
	},
	element: {
		createElement,
	},
} = wp;

/**
 * Internal dependencies.
 */
import metadata from '../block.json';
import editFile from './edit';

registerBlockType( metadata, {
	icon: {
		background: '#fff',
		foreground: '#d84800',
		src: 'media-text',
	},
	edit: editFile,
} );
