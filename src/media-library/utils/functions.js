/**
 * WordPress dependencies
 */
const {
	i18n: {
		__,
	},
} = wp;

/**
 * External dependencies
 */
const { filter } = lodash;

export const getDirectoryAncestors = ( tree, parentId ) => {
	let parents = filter( tree, { id: parentId } );

	parents.forEach( ( parent ) => {
		const grandParents = getDirectoryAncestors( tree, parent.parent );
		parents = [ ...parents, ...grandParents ];
	} );

	return parents;
}

export const bytesToSize = ( bytes ) => {
	const sizes = [
		__( 'Bytes', 'bp-attachments' ),
		__( 'KB', 'bp-attachments' ),
		__( 'MB', 'bp-attachments' ),
		__( 'GB', 'bp-attachments' ),
		__( 'TB', 'bp-attachments' ),
	];

	if ( bytes === 0 ) {
		return '0 ' + sizes[0];
	}

	const i = parseInt( Math.floor( Math.log( bytes ) / Math.log( 1024 ) ), 10 );

	if ( i === 0 ) {
		return `${bytes} ${sizes[i]}`;
	}

	return `${ ( bytes / ( 1024 ** i ) ).toFixed( 1 ) } ${ sizes[ i ] }`;
}

export default bytesToSize;
