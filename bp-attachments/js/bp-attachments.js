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
			// Init some vars
			this.views    = new Backbone.Collection();
			this.warning = null;

			// Set up View
			this.uploaderView();

			// Attachments files are uploaded files
			this.attachments = bp.Uploader.filesUploaded;
		},

		uploaderView: function() {
			// Listen to the Queued uploads
			bp.Uploader.filesQueue.on( 'add', this.uploadProgress, this );

			// Create the BuddyPress Uploader
			var uploader = new bp.Views.Uploader();

			// Add it to views
			this.views.add( { id: 'upload', view: uploader } );

			// Display it
			uploader.inject( '.bp-attachments-uploader' );
		},

		uploadProgress: function() {
			// Create the Uploader status view
			var attachmentStatus = new bp.Views.AttachmentStatus( { collection: bp.Uploader.filesQueue } );

			if ( ! _.isUndefined( this.views.get( 'status' ) ) ) {
				this.views.set( { id: 'status', view: attachmentStatus } );
			} else {
				this.views.add( { id: 'status', view: attachmentStatus } );
			}

			// Display it
	 		attachmentStatus.inject( '.bp-attachments-uploader-status' );
		},
	}

	// Custom Uploader Files view
	bp.Views.AttachmentStatus = bp.Views.uploaderStatus.extend( {
		className: 'files',

		initialize: function() {
			bp.Views.uploaderStatus.prototype.initialize.apply( this, arguments );

			this.collection.on( 'change:url', this.updateEntry, this );
		},

		updateEntry: function( model ) {

			if ( ! _.isUndefined( model.get( 'icon' ) ) && ! $( '#' + model.get( 'id' ) + ' .filename img' ).length ) {
				$( '#' + model.get( 'id' ) + ' .filename' ).prepend( '<img src="' + model.get( 'icon' ) + '"> ' );
				$( '#' + model.get( 'id' ) + ' .bp-progress' ).addClass( 'bp-attachments-success' );
				$( '#' + model.get( 'id' ) + ' .bp-progress' ).html( '<a href="' + model.get( 'edit_url' ) + '" class="bp-attachments-edit button">Edit</a>' );
			}
		}
	} );

	bp.Attachments.start();

} )( bp, jQuery );
