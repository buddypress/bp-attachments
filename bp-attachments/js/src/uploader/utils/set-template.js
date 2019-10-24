/**
 * External dependencies
 */
const { template } = lodash;

function setTemplate( tmpl ) {
	const options = {
		evaluate:    /<#([\s\S]+?)#>/g,
		interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
		escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
		variable:    'data'
	};

	return template( document.querySelector( '#tmpl-' + tmpl ).innerHTML, options );
}

export default setTemplate;
