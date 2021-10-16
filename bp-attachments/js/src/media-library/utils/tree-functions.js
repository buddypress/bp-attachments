/**
 * External dependencies
 */
const { filter } = lodash;

const getDirectoryAncestors = ( tree, parentId ) => {
	let parents = filter( tree, { id: parentId } );

	parents.forEach( ( parent ) => {
		const grandParents = getDirectoryAncestors( tree, parent.parent );
		parents = [ ...parents, ...grandParents ];
	} );

	return parents;
}

export default getDirectoryAncestors;
