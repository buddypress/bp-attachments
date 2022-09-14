/**
 * WordPress dependencies
 */
 const {
	components: {
		Button,
		ExternalLink,
		TextareaControl,
		TextControl,
	},
	data: {
		useDispatch,
	},
	element: {
		createElement,
		useState,
	},
	i18n: {
		__,
		sprintf,
	},
} = wp;

/**
 * Internal dependencies.
 */
import { BP_ATTACHMENTS_STORE_KEY } from '../store';

const EditMediaItem = ( { medium, errorCallback } ) => {
	const {
		id,
		name,
		title,
		description,
		icon,
		media_type,
		mime_type,
		selected,
		links: {
			view,
			download,
			src
		},
	} = medium;
	const [ editedMedium, editMedium ] = useState( {
		title: title,
		description: description,
	} );
	const { updateMedium } = useDispatch( BP_ATTACHMENTS_STORE_KEY );
	const isDisabled = title === editedMedium.title && description === editedMedium.description;
	const hasNoPreview = -1 === [ 'image', 'video', 'audio' ].indexOf( media_type );
	let illustrationClasses = [ 'bp-attachment-edit-item__preview_illustration' ];
	if ( ! hasNoPreview ) {
		illustrationClasses.push( 'has-rich-preview' );
	}
	const isDirectory = 'inode/directory' === mime_type;

	const saveMediumProps = ( event ) => {
		event.preventDefault();

		updateMedium( {
			id: id,
			name: name,
			title: editedMedium.title,
			description: editedMedium.description,
			selected: selected,
		} ).then( ( response ) => {
			if ( response.error ) {
				errorCallback( false );
			} else if ( response.file ) {
				editMedium( {
					...editedMedium,
					title: response.file.title,
					description: response.file.description,
				} );
			}
		} );
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
				<div className="bp-attachment-edit-item__preview_content">
					<ul className="bp-attachment-edit-item__preview_links">
						<li><ExternalLink href={ view }>{ isDirectory ? __( 'Open directory page', 'bp-attachments' ) : __( 'Open media page', 'bp-attachments' ) }</ExternalLink></li>
						{ ! isDirectory && (
							<li><a href={ download }>{ __( 'Download media', 'bp-attachments' ) }</a></li>
						) }
					</ul>
					<div className={ illustrationClasses.join( ' ' ) }>
						{ hasNoPreview && (
							<img src={ icon } className="bp-attachment-medium-icon" />
						) }
						{ 'image' === media_type && src && (
							<img src={ src } className="bp-attachment-medium-vignette" />
						) }
						{ 'audio' === media_type && src && (
							<audio controls="controls" preload="metadata" className="bp-attachment-medium-player">
								<source src={ src } type={ mime_type } />
							</audio>
						) }
						{ 'video' === media_type && src && (
							<video controls="controls" muted={ true } preload="metadata" className="bp-attachment-medium-player">
								<source src={ src } type={ mime_type } />
							</video>
						) }
					</div>
					<div className="bp-attachment-edit-item__preview_description">
						<p>{ editedMedium.description }</p>
					</div>
				</div>
			</div>
			<div className="bp-attachment-edit-item__form">
				<h3>{ sprintf( __( 'Edit %s', 'bp-attachments' ), name ) }</h3>
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
