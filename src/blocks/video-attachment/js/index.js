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
	title: __( 'Community Video', 'bp-attachments' ),
	description: __( 'Insert a movie from your personal media library.', 'bp-attachments' ),
	icon: {
		background: '#fff',
		foreground: '#d84800',
		src: 'video-alt3',
	},
	category: 'media',
	keywords: [
		'BuddyPress',
		__( 'community', 'bp-attachments' ),
		__( 'video', 'bp-attachments' ),
		__( 'movie', 'bp-attachments' ),
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
			<Placeholder
				icon="video-alt3"
				label={ __( 'Insert a video', 'bp-attachments' ) }
			/>
		);
	},
	save: function( { attributes} ) {
		const blockProps = useBlockProps.save();
		return null;
	}
} );
