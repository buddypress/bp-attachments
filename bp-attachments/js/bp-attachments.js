/* globals bp, BP_Uploader, _, Backbone */

window.bp = window.bp || {};

( function( exports, $ ) {

	// Bail if not set
	if ( typeof BP_Uploader === 'undefined' ) {
		return;
	}

	bp.Models      = bp.Models || {};
	bp.Collections = bp.Collections || {};
	bp.Views       = bp.Views || {};

	bp.Attachments = {
		start: function() {
			// Init some collections
			this.views       = new Backbone.Collection();
			this.feedback    = new bp.Collections.attachmentsFeedback();
			this.files       = new bp.Collections.bpAttachments();

			// Set up Uploader View
			if ( true === BP_Uploader.settings.defaults.multipart_params.bp_params.can_upload ) {
				this.uploaderView();
			}

			// Set up other Views
			this.browserView();
			this.statusView();
		},

		uploaderView: function() {
			// Create the BuddyPress Uploader
			var uploader = new bp.Views.Uploader();

			// Add it to views
			this.views.add( { id: 'upload', view: uploader } );

			// Display it
			uploader.inject( '.bp-attachments-uploader' );
		},

		browserView: function() {
			// Create the browser
			var browser = new bp.Views.AttachmentFiles( { collection:  this.files } );

			// Add it to views
			this.views.add( { id: 'browse', view: browser } );

			// Display it
			browser.inject( '.bp-attachments-browser' );
		},

		statusView: function() {
			// Create the Uploader status view
			var status = new bp.Views.AttachmentStatus( { collection: this.feedback } );

			// Add it to views
			this.views.add( { id: 'status', view: status } );

			// Display it
			status.inject( '.bp-attachments-uploader-status' );
		},

		removeWarnings: function() {
			$( 'p.warning' ).each( function() {
				this.remove();
			} );
		}
	}

	// BP Attachment model
	bp.Models.bpAttachment = Backbone.Model.extend( {
		attachment: {},

		deleteAttachment: function() {
			var self = this;

			if ( true !== this.get( 'deleting' ) ) {
				return;
			}

			// Reset feedbacks
			bp.Attachments.feedback.reset();

			return wp.ajax.post( 'bp_attachments_delete_file', {
				attachment_id: self.get( 'id' ),
				/*item_object: BP_Uploader.settings.defaults.multipart_params.bp_params.object,*/
				nonce:       self.get( 'nonces' ).delete
			} ).done( function( resp ) {
				bp.Attachments.files.remove( self );

				// Display a success message
				bp.Attachments.feedback.add( { type: 'delete_success', 'message': self.get( 'name' ) + ': ' + resp.feedback } );

			} ).fail( function( resp ) {
				self.unset( 'deleting', 0 );

				// Display an error
				bp.Attachments.feedback.add( { type: 'delete_error', 'message': self.get( 'name' ) + ': ' + resp.feedback } );
			} );
		},

		removeAttachment: function() {
			var self = this;

			if ( true !== this.get( 'removing' ) ) {
				return;
			}

			// Reset feedbacks
			bp.Attachments.feedback.reset();

			return wp.ajax.post( 'bp_attachments_remove_file', {
				attachment_id: self.get( 'id' ),
				item_id:       BP_Uploader.settings.defaults.multipart_params.bp_params.item_id,
				item_object:   BP_Uploader.settings.defaults.multipart_params.bp_params.object,
				nonce:         self.get( 'nonces' ).remove
			} ).done( function( resp ) {
				bp.Attachments.files.remove( self );

				// Display a success message
				bp.Attachments.feedback.add( { type: 'remove_success', 'message': self.get( 'name' ) + ': ' + resp.feedback } );

			} ).fail( function( resp ) {
				self.unset( 'removing', 0 );

				// Display an error
				bp.Attachments.feedback.add( { type: 'remove_error', 'message': self.get( 'name' ) + ': ' + resp.feedback } );
			} );
		}
	} );

	// Feedback collection
	bp.Collections.attachmentsFeedback = Backbone.Collection.extend( {
		initialize: function() {
			bp.Uploader.filesError.on( 'add', this.addItem, this );
			bp.Uploader.filesQueue.on( 'reset', this.cleanItems, this );
		},

		addItem: function( model ) {
			var message = BP_Uploader.strings.default_error;

			if ( ! model.get( 'file' ).id ) {
				return;
			}

			if ( model.get( 'file' ).name ) {
				message = model.get( 'file' ).name + ': ' + model.get( 'message' );
			}

			this.add( { type: 'error', message: message } );
		},

		cleanItems: function() {
			this.reset();
		}
	} );

	// BP Attachements collection
	bp.Collections.bpAttachments = Backbone.Collection.extend( {
		model: bp.Models.bpAttachment,

		initialize: function() {
			this.current_page = 1;
			this.hasMore      = true;
			bp.Uploader.filesQueue.on( 'add', this.uploadProgress, this );
		},

		uploadProgress: function( item ) {
			this.add( item );
		},

		sync: function( method, model, options ) {

			if ( 'read' === method ) {
				options = options || {};
				options.context = this;
				options.data = _.extend( options.data || {}, {
					action:        'bp_attachments_get_files',
					user_id:        BP_Uploader.settings.defaults.multipart_params.bp_params.user_id,
					item_id:        BP_Uploader.settings.defaults.multipart_params.bp_params.item_id,
					item_object:    BP_Uploader.settings.defaults.multipart_params.bp_params.object,
					item_component: BP_Uploader.settings.defaults.multipart_params.bp_params.component,
					nonce:          BP_Uploader.settings.defaults.multipart_params.bp_params.nonce
				} );

				return wp.ajax.send( options );
			}
		},

		parse: function( resp ) {
			if ( ! _.isArray( resp ) ) {
				resp = [resp];
			}

			return resp;
		},

		loadMore: function() {
			this.current_page += 1;

			this.fetch( {
				data: { page: this.current_page },
				remove: false,
				success : this._hasMore,
				error : this._hasMore
			} );
		},

		_hasMore: function( items, response ) {
			if ( response.length >= 1 ) {
				bp.Attachments.files.hasMore = true;
			}
		}
	} );

	// Attachment status view
	bp.Views.AttachmentStatus = bp.View.extend( {
		tagName: 'ul',

		initialize: function() {
			this.cleanupContent = false;

			// Catch events on the collection
			this.collection.on( 'add', this.addItemView, this );
			this.collection.on( 'reset', this.resetItemViews, this );
		},

		resetItemViews: function() {
			this.cleanupContent = true;

			// Immediately remove all warnings
			_.each( this.views._views[""], function( view ) {
				if ( 'error' !== view.model.get( 'type' ) ) {
					view.remove();
				}
			} );
		},

		addItemView: function( feedback ) {
			// Clean up previous feedbacks
			if ( true === this.cleanupContent ) {
				_.each( this.views._views[""], function( view ) {
					view.remove();
				} );

				// Remove any Uploader warning
				bp.Attachments.removeWarnings();

				this.cleanupContent = false;
			}

			this.views.add( new bp.Views.AttachmentStatusEntry( { model: feedback } ) );
		}
	} );

	// Attachment status entry view
	bp.Views.AttachmentStatusEntry = bp.View.extend( {
		tagName: 'li',
		template: bp.template( 'bp-attachments-feedback' ),

		initialize: function() {
			if ( this.model.get( 'type' ) ) {
				this.el.className = this.model.get( 'type' );
			}
		}
	} );

	// Files list view
	bp.Views.AttachmentFiles = bp.View.extend( {
		tagName: 'ul',
		id: 'bp-attachments-items',

		initialize: function() {
			// Fetch the files
			this.collection.fetch( { success : this.success, error : this.fail } );

			// Catch events on the collection
			this.collection.on( 'add', this.addItemView, this );
			this.collection.on( 'remove', this.removeItemView, this );

			this.on( 'ready', this.adjustWidth );

			// Load oldest attachments..
			$( document ).on( 'scroll', { el: this.el }, this.scroll );
		},

		addItemView: function( file ) {
			var options = {}, attachment_id = file.get( 'id' ), position;

			// If the file is uploading, prepend it.
			if ( file.get( 'uploading' ) || file.get( 'uploaded') ) {
				options = { at: 0 };
			}

			// Try to find the best place to inject the uploaded file
			if ( file.get( 'file_id' ) ) {
				position = _.indexOf( _.pluck( bp.Uploader.filesQueue.models, 'id' ), file.get( 'file_id' ) );
				if ( -1 !== position ) {
					options = { at: position };
				}
			}

			// Remove any Uploader warning
			bp.Attachments.removeWarnings();

			this.views.add( new bp.Views.AttachmentFile( { model: file } ), options );
		},

		removeItemView:function( file ) {
			_.each( this.views._views[""], function( view ) {
				if ( view.model.get( 'id' ) === file.get( 'id' ) ) {
					view.remove();
				}
			} );
		},

		adjustWidth: function() {
			var ulw   = this.el.clientWidth;
			var limax = parseInt( ulw / 170 );

			$( this.el ).css( {
				'maxWidth' : Number( limax * 170 ) + 'px'
			} );
		},

		scroll: function( event ) {
			var el = event.data.el, body = event.target,
				offset, bottom;

			if ( document === body ) {
				body = document.body;
			}

			offset = $( el ).offset();
			bottom = offset.top;

			if ( body.scrollHeight > body.scrollTop + bottom ) {
				return;
			}

			if ( true === bp.Attachments.files.hasMore ) {
				bp.Attachments.files.hasMore = false;
				bp.Attachments.files.loadMore();
			}
		},

		success: function( collection ) {
			if ( 0 === collection.length ) {
				// Display a warning message
				bp.Attachments.feedback.add( { type: 'warning', 'message': 'No items found' } );
				/* replace by BP_Uploader.strings.fetchingnoitems               ^^            */
			}
		},

		fail: function() {
			// Display a warning message
			bp.Attachments.feedback.add( { type: 'warning', 'message': 'Error while fetching the items' } );
			/* replace by BP_Uploader.strings.fetchingerror               ^^                              */
		}
	} );

	// Single file view
	bp.Views.AttachmentFile = bp.View.extend( {
		tagName:   'li',
		className: 'bp-attachments-item',
		template: bp.template( 'bp-attachments-file' ),

		events: {
			'click .delete': 'deleteAttachment',
			'click .remove': 'removeAttachment'
		},

		initialize: function() {
			this.model.on( 'change:percent', this.showProgress, this );
			this.model.on( 'change:id', this.deleteEntry, this );
			this.model.on( 'change:url', this.updateEntry, this );
		},

		deleteEntry: function( model ) {
			if ( _.isUndefined( model.get( 'id' ) ) ) {
				this.views.view.remove();

				// Remove silently the model from the collection
				bp.Attachments.files.remove( model, { silent: true } );
			}
		},

		showProgress: function( model ) {
			if ( ! _.isUndefined( model.get( 'percent' ) ) ) {
				$( '#' + model.get( 'id' ) + ' .bp-progress .bp-bar' ).css( 'width', model.get( 'percent' ) + '%' );
			}
		},

		updateEntry: function( model ) {
			var attachment = _.extend(
				{ id: model.get( 'attachment_id' ), file_id: model.get( 'id' ) },
				_.omit( model.attributes, ['id', 'date', 'filename', 'uploading', 'attachment_id' ] )
			);

			// Remove the uploading model from the main collection
			bp.Attachments.files.remove( model );

			// Add the uploaded model to the main collection
			bp.Attachments.files.add( attachment );
		},

		deleteAttachment: function( event ) {
			event.preventDefault();

			if ( this.model.get( 'deleting' ) ) {
				return;
			}

			this.model.set( 'deleting', true );
			this.model.deleteAttachment();
		},

		removeAttachment: function( event ) {
			event.preventDefault();

			if ( this.model.get( 'removing' ) ) {
				return;
			}

			this.model.set( 'removing', true );
			this.model.removeAttachment();
		}
	} );

	bp.Attachments.start();

} )( bp, jQuery );
