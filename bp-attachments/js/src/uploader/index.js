const { Component, render, Fragment } = wp.element;
const { DropZoneProvider, DropZone } = wp.components;
const { __ } = wp.i18n;
const { apiFetch } = wp;
const { find, forEach } = lodash;

class BP_Media_Uploader extends Component {
    constructor() {
		super( ...arguments );

        this.state = {
			files: [],
			uploaded: [],
			errored: [],
			uploading: false,
        };

		this.onFilesDropped = this.onFilesDropped.bind( this );
		this.onResetState = this.onResetState.bind( this );
	}

	onFilesDropped( files ) {
		if ( !! this.state.uploading ) {
			return;
		}

		this.setState( prevState => ( {
			files: prevState.files.concat( files ),
			uploaded: [],
			errored: [],
			uploading: true
		} ) );

		files.forEach( file => {
			const formData = new FormData();
			formData.append( 'file', file );
			formData.append( 'action', 'bp_attachments_media_upload' );

			apiFetch( { path: '/buddypress/v1/attachments', method: 'POST', body: formData } ).then( result => {
				const uploaded = { fileName: file.name, data: result };
				this.setState( prevState => ( {
					uploaded: prevState.uploaded.concat( uploaded ),
				} ) );
			}, error => {
				const errored = { fileName: file.name, error: error };
				this.setState( prevState => ( {
					errored: prevState.errored.concat( errored ),
				} ) );
			} );
		} );
	}

	onResetState( event ) {
		event.preventDefault();

		this.setState( {
			files: [],
			uploaded: [],
			errored: [],
			uploading: false,
		} );
	}

	renderResult( file ) {
		const { uploaded, errored } = this.state;
		const isError = find( errored, { fileName: file.name } );
		const isSuccess = find( uploaded, { fileName: file.name } );

		if ( isSuccess ) {
			return (
				<span className="bp-info">
					<span className="bp-uploaded"></span>
					<span className="screen-reader-text">{ __( 'Uploaded!', 'bp-attachments' ) }</span>
				</span>
			);
		}

		if ( isError ) {
			return (
				<span className="bp-info">
					<span className="bp-errored"></span>
					<span>{ isError.error.message }</span>
				</span>
			);
		}

		return (
			<span className="bp-info">
				<span className="bp-uploading"></span>
				<span className="screen-reader-text">{ __( 'Uploading...', 'bp-attachments' ) }</span>
			</span>
		);
	}

	render() {
		const { files, uploaded, errored, uploading } = this.state;
		const { customBlocks } = this.props;
		let fileItems, dzClass = 'enabled';

		if ( !! uploading ) {
			dzClass = 'disabled';
		}

		return (
			<Fragment>
				<DropZoneProvider>
					<div>
						<h2>{ __( 'Drop your files in the box below.', 'bp-attachments' ) }</h2>
						<DropZone
							label={ __( 'Drop your files here.', 'bp-attachments' ) }
							onFilesDrop={ this.onFilesDropped }
							className={ dzClass }
						/>
					</div>
				</DropZoneProvider>
				{ !! files.length &&
					<ol className="bp-files-list">
						{ files.map( file => {
							return (
								<li key={ file.name } className="row">
									<span className="filename">{ file.name }</span>
									{ this.renderResult( file ) }
								</li>
							);
						} ) }
					</ol>
				}
				{ !! files.length && files.length === errored.length + uploaded.length &&
					<div className="bp-reset">
						<a onClick={ this.onResetState } href="#new-uploads" className="button button-primary large">
							{ __( 'Start a new upload', 'bp-attachments' ) }
						</a>
					</div>
				}
			</Fragment>
		);
	}
};

render( <BP_Media_Uploader />, document.querySelector( '#bp-media-uploader' ) );
