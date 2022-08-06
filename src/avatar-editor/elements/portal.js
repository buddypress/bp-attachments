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
		const { selector } = this.props;

		return createPortal(
			this.props.children,
			document.querySelector( '#' + selector )
		);
	}
}

export default AvatarEditorPortal;
