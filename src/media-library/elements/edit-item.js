/**
 * WordPress dependencies
 */
 const {
	components: {
		Button,
		TextareaControl,
		TextControl,
	},
	element: {
		createElement,
		useState,
	},
	i18n: {
		__,
	},
} = wp;

const EditMediaItem = ( { medium } ) => {
	const {
		title,
		description,
		vignette,
		icon,
		links: {
			view,
			download,
		},
	} = medium;
	const [ editedMedium, editMedium ] = useState( {
		title: title,
		description: description,
	} );

	const isDisabled = title === editedMedium.title && description === editedMedium.description;

	const saveMediumProps = ( event ) => {
		event.preventDefault();

		console.log( editedMedium );
	}

	const resetMediumProps = ( event ) => {
		event.preventDefault();

		editMedium( {
			...editedMedium,
			title: title,
			description: description,
		} );
	}

	return (
		<div className="bp-attachment-edit-item">
			<div className="bp-attachment-edit-item__preview">
				<h3 className="bp-attachment-edit-item__preview_title">{ editedMedium.title }</h3>
				<div className="bp-attachment-edit-item__preview_vignette">
					<p>{ editedMedium.description }</p>
					{ vignette && (
						<img src={ vignette } className="bp-attachment-medium-vignette" />
					) }
					{ ! vignette && (
						<img src={ icon } className="bp-attachment-medium-icon" />
					) }
				</div>
			</div>
			<div className="bp-attachment-edit-item__form">
				<h3>{ __( 'Editable media properties', 'bp-attachments' ) }</h3>
				<p className="description">{ __( 'Use the below fields to edit media properties.', 'bp-attachments' ) }</p>
				<TextControl
					label={ __( 'Title', 'bp-attachments' ) }
					value={ editedMedium.title }
					onChange={ ( value ) => editMedium( { ...editedMedium, title: value } ) }
					help={ __( 'Change the title of your medium to something more descriptive then its file name.', 'bp-attachments' ) }
				/>
				<TextareaControl
					label={ __( 'Description', 'bp-attachments' ) }
					value={ editedMedium.description }
					onChange={ ( text ) => editMedium( { ...editedMedium, description: text } ) }
					help={ __( 'Add or edit the description of your medium to tell your story about it.', 'bp-attachments' ) }
				/>
				<div className="bp-attachment-edit-item__form-actions">
					<Button variant="primary" disabled={ isDisabled } onClick={ ( e ) => saveMediumProps( e ) }>
						{ __( 'Save your edits', 'bp-attachment' ) }
					</Button>
					<Button variant="tertiary" disabled={ isDisabled } onClick={ ( e ) => resetMediumProps( e ) }>
						{ __( 'Cancel', 'bp-attachment' ) }
					</Button>
				</div>
			</div>
		</div>
	);
};

export default EditMediaItem;
