const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType( 'buddypress/attachments', {
    title: __( 'BP Attachments', 'bp-attachments' ),

    description: __( 'BuddyPress Attachments Media.', 'bp-attachments' ),

    icon: 'images-alt',

    category: 'buddypress',

    attributes: {},

    edit: function( props ) {},

    save: function( props ) {}
} );
