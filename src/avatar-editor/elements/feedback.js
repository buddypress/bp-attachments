/**
 * WordPress dependencies
 */
 const {
	element: {
		createElement,
	},
} = wp;

/**
 * Internal dependencies.
 */
import AvatarEditorPortal from './portal';

const AvatarEditorFeedback = ( { type, children } ) => {
	return (
		<AvatarEditorPortal selector="bp-avatar-editor-controls">
			<div className={ 'bp-avatar-status ' + type }>
				{ children }
			</div>
		</AvatarEditorPortal>
	);
}

export default AvatarEditorFeedback
