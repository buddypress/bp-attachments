/**
 * WordPress dependencies
 */
const {
	element: {
		Component,
		createElement,
		createPortal,
	},
} = wp;

class AvatarEditorPortal extends Component {
	render() {
		return createPortal(
			this.props.children,
			document.querySelector( "#bp-avatar-editor-controls" )
		);
	}
}

export default AvatarEditorPortal;
