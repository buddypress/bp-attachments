var bp = bp || {};

/**
 * BP Media Editor !
 */
( function( $ ) {
	var media;

	bp.media = media = {};
	bp.l10n = wp.media.view.l10n;
	bp.media.settings = wp.media.view.settings.bp_attachments;

	bp.media.params = {
		item_id:        _wpPluploadSettings.defaults.multipart_params.item_id,
		item_component: _wpPluploadSettings.defaults.multipart_params.component,
		item_type:      _wpPluploadSettings.defaults.multipart_params.item_type,
		item_post_id:   _wpPluploadSettings.defaults.multipart_params.post_id,
		nonce:          _wpPluploadSettings.defaults.multipart_params._bpnonce,
		callback:       _wpPluploadSettings.defaults.multipart_params.callback,
		callback_id:    _wpPluploadSettings.defaults.multipart_params.callback_id,
	};

	_.extend( media, { model: {}, view: {}, controller: {}, frames: {} } );

	// Models
	wp.media.attachment = function( id ) {
		return bpAttachment.get( id );
	};

	/**
	 * bp.media.model.Attachment
	 *
	 * It's an adpated copy of WordPress Attachment model
	 *
	 * @constructor
	 * @augments Backbone.Model
	 */
	bpAttachment = media.model.Attachment = bp.media.model.Attachment = wp.media.model.Attachment.extend({

		/**
		 * I still have a problem to solve here!
		 *
		 * using isbp to check if the bpAttachment model
		 * is in use. The uploader is still using the WP
		 * Attachment model, causing the update WP Attachment
		 * ajax update function to be used instead of the BP
		 * Attachment one. Till i find the fix, once a file
		 * is uploaded, i put the fields in readonly mode.
		 */
		defaults : {
            id: 0,
			isbp: true,
        },

		/**
		 * Triggered when attachment details change
		 * Overrides Backbone.Model.sync
		 *
		 * @param {string} method
		 * @param {bp.media.model.Attachment} model
		 * @param {Object} [options={}]
		 *
		 * @returns {Promise}
		 */
		sync: function( method, model, options ) {
			// If the attachment does not yet have an `id`, return an instantly
			// rejected promise. Otherwise, all of our requests will fail.
			if ( _.isUndefined( this.id ) ) {
				return $.Deferred().rejectWith( this ).promise();
			}

			// Overload the `read` request so Attachment.fetch() functions correctly.
			if ( 'read' === method ) {
				options = options || {};
				options.context = this;
				options.data = _.extend( options.data || {}, {
					action: 'get_bp_attachment',
					id: this.id
				});
				return wp.media.ajax( options );

			// Overload the `update` request so properties can be saved.
			} else if ( 'update' === method ) {
				console.log( this.get('nonces'));
				// If we do not have the necessary nonce, fail immeditately.
				if ( ! this.get('nonces') || ! this.get('nonces').update ) {
					return $.Deferred().rejectWith( this ).promise();
				}

				options = options || {};
				options.context = this;

				// Set the action and ID.
				options.data = _.extend( options.data || {}, {
					action:  'update_bp_attachment',
					id:      this.id,
					nonce:   this.get('nonces').update,
					post_id: wp.media.model.settings.post.id
				});

				// Record the values of the changed attributes.
				if ( model.hasChanged() ) {
					options.data.changes = {};

					_.each( model.changed, function( value, key ) {
						options.data.changes[ key ] = this.get( key );
					}, this );
				}

				return wp.media.ajax( options );

			// Overload the `delete` request so attachments can be removed.
			// This will permanently delete an attachment.
			} else if ( 'delete' === method ) {
				options = options || {};

				if ( ! options.wait ) {
					this.destroyed = true;
				}

				options.context = this;
				options.data = _.extend( options.data || {}, {
					action:   'delete_bp_attachment',
					id:       this.id,
					_wpnonce: this.get('nonces')['delete']
				});

				return wp.media.ajax( options ).done( function() {
					this.destroyed = true;
				}).fail( function() {
					this.destroyed = false;
				});

			// Otherwise, fall back to `Backbone.sync()`.
			} else {
				/**
				 * Call `sync` directly on Backbone.Model
				 */
				return wp.media.model.Attachment.prototype.sync.apply( this, arguments );
			}
		},
		/**
		 * Convert date strings into Date objects.
		 *
		 * @param {Object} resp The raw response object, typically returned by fetch()
		 * @returns {Object} The modified response object, which is the attributes hash
		 *    to be set on the model.
		 */
		parse: function( resp ) {
			if ( ! resp ) {
				return resp;
			}

			resp.date = new Date( resp.date );
			resp.modified = new Date( resp.modified );
			return resp;
		},
	}, {
		/**
		 * Add a model to the end of the static 'all' collection and return it.
		 *
		 * @static
		 * @param {Object} attrs
		 * @returns {wp.media.model.Attachment}
		 */
		create: function( attrs ) {
			return bpAttachments.all.push( attrs );
		},
		/**
		 * Retrieve a model, or add it to the end of the static 'all' collection before returning it.
		 *
		 * @static
		 * @param {string} id A string used to identify a model.
		 * @param {Backbone.Model|undefined} attachment
		 * @returns {wp.media.model.Attachment}
		 */
		get: _.memoize( function( id, attachment ) {
			return bpAttachments.all.push( attachment || { id: id } );
		})
	});

	/**
	 * bp.media.model.bpAttachments
	 *
	 * it's an adapted copy of WordPress Attachments Collection
	 * 
	 * @constructor
	 * @augments Backbone.Collection
	 */
	bpAttachments = bp.media.model.bpAttachments = wp.media.model.Attachments.extend({
		/**
		 * @type {wp.media.model.Attachment}
		 */
		model: bpAttachment,
		/**
		 * @param {Array} [models=[]] Array of models used to populate the collection.
		 * @param {Object} [options={}]
		 */
		initialize: function( models, options ) {
			options = options || {};

			wp.media.model.Attachments.prototype.initialize.apply( this, arguments );
		},

		/**
		 * @access private
		 */
		_requery: function() {
			if ( this.props.get('query') ) {
				this.mirror( bp.media.model.Query.get( this.props.toJSON() ) );
			}
		},

		parse: function( resp, xhr ) {
			if ( ! _.isArray( resp ) ) {
				resp = [resp];
			}

			return _.map( resp, function( attrs ) {
				var id, attachment, newAttributes;

				if ( attrs instanceof Backbone.Model ) {
					id = attrs.get( 'id' );
					attrs = attrs.attributes;
				} else {
					id = attrs.id;
				}

				attachment = bpAttachment.get( id );
				newAttributes = attachment.parse( attrs, xhr );

				if ( ! _.isEqual( attachment.attributes, newAttributes ) ) {
					attachment.set( newAttributes );
				}

				return attachment;
			});
		},
		
	});

	/**
	 * @static
	 * @member {bp.media.model.bpAttachments}
	 */
	bpAttachments.all = new bpAttachments();

	/**
	 * bp.media.model.Avatar
	 *
	 * @constructor
	 * @augments Backbone.Model
	 */
	Avatar = bp.media.model.Avatar = Backbone.Model.extend( {
		defaults : {
            id: 0,
			title: '',
			component: '',
			filename: '',
			url:'',
			path:'',
        },
	} );

	/**
	 * bp.media.model.Query
	 *
	 * It's an adpated copy of WordPress Query
	 *
	 * @constructor
	 * @augments Backbone.Model
	 */
	Query = media.model.Query = bp.media.model.Query = bpAttachments.extend({

		initialize: function( models, options ) {
			var allowed;

			options = options || {};
			bpAttachments.prototype.initialize.apply( this, arguments );

			this.args     = options.args;
			this._hasMore = true;
			this.created  = new Date();

			this.filters.order = function( attachment ) {
				var orderby = this.props.get('orderby'),
					order = this.props.get('order');

				if ( ! this.comparator ) {
					return true;
				}

				// We want any items that can be placed before the last
				// item in the set. If we add any items after the last
				// item, then we can't guarantee the set is complete.
				if ( this.length ) {
					return 1 !== this.comparator( attachment, this.last(), { ties: true });

				// Handle the case where there are no items yet and
				// we're sorting for recent items. In that case, we want
				// changes that occurred after we created the query.
				} else if ( 'DESC' === order && ( 'date' === orderby || 'modified' === orderby ) ) {
					return attachment.get( orderby ) >= this.created;

				// If we're sorting by menu order and we have no items,
				// accept any items that have the default menu order (0).
				} else if ( 'ASC' === order && 'menuOrder' === orderby ) {
					return attachment.get( orderby ) === 0;
				}

				// Otherwise, we don't want any items yet.
				return false;
			};

			// Observe the central `wp.Uploader.queue` collection to watch for
			// new matches for the query.
			//
			// Only observe when a limited number of query args are set. There
			// are no filters for other properties, so observing will result in
			// false positives in those queries.
			allowed = [ 's', 'order', 'orderby', 'posts_per_page', 'post_mime_type', 'post_parent', 'component', 'item_type', 'item_id' ];
			if ( wp.Uploader && _( this.args ).chain().keys().difference( allowed ).isEmpty().value() ) {
				this.observe( wp.Uploader.queue );
			}
		},
		/**
		 * @returns {Boolean}
		 */
		hasMore: function() {
			return this._hasMore;
		},
		/**
		 * @param {Object} [options={}]
		 * @returns {Promise}
		 */
		more: function( options ) {
			var query = this;

			if ( this._more && 'pending' === this._more.state() ) {
				return this._more;
			}

			if ( ! this.hasMore() || 'avatar' == this.args.item_type ) {
				return $.Deferred().resolveWith( this ).promise();
			}

			options = options || {};
			options.remove = false;

			return this._more = this.fetch( options ).done( function( resp ) {
				if ( _.isEmpty( resp ) || -1 === this.args.posts_per_page || resp.length < this.args.posts_per_page ) {
					query._hasMore = false;
				}
			});
		},

		sync: function( method, model, options ) {
			var args, fallback;

			// Overload the read method so bpAttachment.fetch() functions correctly.
			if ( 'read' === method ) {
				options = options || {};
				options.context = this;

				if ( 'avatar' == this.args.item_type ) {
					return false;
				}

				options.data = _.extend( options.data || {}, {
					action:  'query_bp_attachments',
					post_id: wp.media.model.settings.post.id
				});

				// Clone the args so manipulation is non-destructive.
				args = _.clone( this.args );

				// Determine which page to query.
				if ( -1 !== args.posts_per_page ) {
					args.paged = Math.floor( this.length / args.posts_per_page ) + 1;
				}

				options.data.query = args;
				return wp.media.ajax( options );

			// Otherwise, fall back to Backbone.sync()
			} else {
				/**
				 * Call bp.media.model.bpAttachments.sync or Backbone.sync
				 */
				fallback = bpAttachments.prototype.sync ? bpAttachments.prototype : Backbone;
				return fallback.sync.apply( this, arguments );
			}
		}

	}, {
		/**
		 * @readonly
		 */
		defaultProps: {
			orderby: 'date',
			order:   'DESC',
			component: bp.media.params.item_component,
			item_type: bp.media.params.item_type,
			item_id: bp.media.params.item_id,
		},
		/**
		 * @readonly
		 */
		defaultArgs: {
			posts_per_page: 40
		},
		/**
		 * @readonly
		 */
		orderby: {
			allowed:  [ 'name', 'author', 'date', 'title', 'modified', 'uploadedTo', 'id', 'post__in', 'menuOrder' ],
			valuemap: {
				'id':         'ID',
				'uploadedTo': 'parent',
				'attachedTo': 'item_id',
				'menuOrder':  'menu_order ID'
			}
		},
		/**
		 * @readonly
		 */
		propmap: {
			'search':    's',
			'type':      'post_mime_type',
			'perPage':   'posts_per_page',
			'menuOrder': 'menu_order',
			'uploadedTo': 'post_parent',
			'attachedTo': 'item_id',
		},
		/**
		 * @static
		 * @method
		 *
		 * @returns {wp.media.model.Query} A new query.
		 */
		// Caches query objects so queries can be easily reused.
		get: (function(){
			/**
			 * @static
			 * @type Array
			 */
			var queries = [];

			/**
			 * @param {Object} props
			 * @param {Object} options
			 * @returns {Query}
			 */
			return function( props, options ) {
				var args     = {},
					orderby  = Query.orderby,
					defaults = Query.defaultProps,
					query;

				// Remove the `query` property. This isn't linked to a query,
				// this *is* the query.
				delete props.query;

				// Fill default args.
				_.defaults( props, defaults );

				// Normalize the order.
				props.order = props.order.toUpperCase();
				if ( 'DESC' !== props.order && 'ASC' !== props.order ) {
					props.order = defaults.order.toUpperCase();
				}

				// Ensure we have a valid orderby value.
				if ( ! _.contains( orderby.allowed, props.orderby ) ) {
					props.orderby = defaults.orderby;
				}

				// Generate the query `args` object.
				// Correct any differing property names.
				_.each( props, function( value, prop ) {
					if ( _.isNull( value ) ) {
						return;
					}

					args[ Query.propmap[ prop ] || prop ] = value;
				});

				// Fill any other default query args.
				_.defaults( args, Query.defaultArgs );

				// `props.orderby` does not always map directly to `args.orderby`.
				// Substitute exceptions specified in orderby.keymap.
				args.orderby = orderby.valuemap[ props.orderby ] || props.orderby;

				// Search the query cache for matches.
				query = _.find( queries, function( query ) {
					return _.isEqual( query.args, args );
				});

				// Otherwise, create a new query and add it to the cache.
				if ( ! query ) {
					query = new Query( [], _.extend( options || {}, {
						props: props,
						args:  args
					} ) );
					queries.push( query );
				}

				return query;
			};
		}())

	});

	bp.media.query = function( props ) {
		return new bpAttachments( null, {
			props: _.extend( _.defaults( props || {}, { orderby: 'date' } ), { query: true } )
		});
	};

	bp.media.controller.bpLibrary = wp.media.controller.Library.extend({
		defaults: {
			id:         'bp_library',
			multiple:   false,
			describe:   false,
			toolbar:    'bp_select',
			sidebar:    'bp_settings',
			content:    'bp_upload',
			router:     'bp_browse',
			menu:       'default',
			searchable: true,
			filterable: false,
			sortable:   true,
			title:      bp.l10n.bp_attachments.title,

			// Uses a user setting to override the content mode.
			contentUserSetting: false,

			// Sync the selection from the last state when 'multiple' matches.
			syncSelection: true
		},

		initialize: function() {
			type = '';

			if ( 'avatar' == bp.media.params.item_type ) {
				type = { type: 'image' };
			}

			if ( ! this.get('library') ) {
				this.set( 'library', bp.media.query( type ) );
			}

			wp.media.controller.Library.prototype.initialize.apply( this, arguments );
		},

		uploading: function( attachment ) {
			var content = this.frame.content,
				mode = 'bpbrowse';

			if ( 'avatar' == bp.media.params.item_type ) 
				mode = 'crop';

			if ( 'bp_upload' === content.mode() ) {
				this.frame.content.mode( mode );
			}

			this.get('selection').add( attachment );
		},

	});

	media.view.Avatar = bp.media.view.Avatar = wp.media.view.Attachment.extend( {
		tagName:   'li',
		className: 'avatar',
		template:  wp.media.template('avatar'),

		initialize: function() {
			var selection = this.options.selection;

			wp.media.view.Attachment.prototype.initialize.apply( this, arguments );
		},

		render: function() {
			var selection = this.options.selection;

			wp.media.view.Attachment.prototype.render.apply( this, arguments );

			if( ! _.isUndefined( selection.single() ) ) {
				var tocrop = this.$el.find( 'img' );
				var ratio = 1;

				if ( ! _.isUndefined( bp.media.settings.full_h ) && ! _.isUndefined( bp.media.settings.full_w ) && bp.media.settings.full_h != bp.media.settings.full_w ) {
					ratio = bp.media.settings.full_h / bp.media.settings.full_w;
				}

				
				crop_left   = Math.round( selection.single().get('width' ) / 4 );
				crop_top    = Math.round( selection.single().get('height' ) / 4 );
				crop_right  = selection.single().get('width' ) - crop_left;
				crop_bottom = selection.single().get('height' ) - crop_top;

				tocrop.Jcrop({
					onChange: this.showPreview,
					onSelect: this.showPreview,
					onSelect: this.updateCoords,
					aspectRatio: ratio,
	        		setSelect: [ crop_left, crop_top, crop_right, crop_bottom ]
				});
				
				this.updateCoords({x: crop_left, y: crop_top, w: crop_right, h: crop_bottom});

			}

			return this;
		},

		updateCoords: function( args ) {
			var selection = media.frame().state().get( 'selection' ).single();
			selection.set( {
				'x': args.x,
				'y': args.y,
				'w': args.w,
				'h': args.h
			} );
		},

		showPreview: function( coords ) {
			var selection = media.frame().state().get( 'selection' ).single();

			if ( parseInt(coords.w) > 0 ) {
				var fw = bp.media.settings.full_w;
				var fh = bp.media.settings.full_h;
				var rx = fw / coords.w;
				var ry = fh / coords.h;

				$( '#avatar-crop-preview' ).css({
					width: Math.round(rx *  selection.get('width') )+ 'px',
					height: Math.round(ry * selection.get('height') )+ 'px',
					marginLeft: '-' + Math.round(rx * coords.x) + 'px',
					marginTop: '-' + Math.round(ry * coords.y) + 'px'
				});
			}
		},
	} );

	bp.media.view.AvatarDetails = wp.media.view.Attachment.Details.extend({

		tagName:   'div',
		className: 'attachment-details',
		template:  wp.media.template('bp-avatar-details'),

		render: function() {
			var options = _.defaults( this.model.toJSON(), {
					orientation:   'landscape',
					uploading:     false,
					type:          '',
					subtype:       '',
					icon:          '',
					filename:      '',
					caption:       '',
					title:         '',
					dateFormatted: '',
					width:         '',
					height:        '',
					compat:        false,
					alt:           '',
					description:   ''
				});

			options.buttons  = this.buttons;
			options.describe = this.controller.state().get('describe');

			if ( 'image' === options.type ) {
				options.size = this.imageSize();
			}

			options.can = {};
			if ( options.nonces ) {
				options.can.remove = !! options.nonces['delete'];
				options.can.save = !! options.nonces.update;
			}

			if ( this.controller.state().get('allowLocalEdits') ) {
				options.allowLocalEdits = true;
			}

			this.views.detach();
			this.$el.html( this.template( options ) );

			this.$el.toggleClass( 'uploading', options.uploading );
			if ( options.uploading ) {
				this.$bar = this.$('.media-progress-bar div');
			} else {
				delete this.$bar;
			}

			// Check if the model is selected.
			this.updateSelect();

			// Update the save status.
			this.updateSave();

			this.views.render();

			return this;
		},
	});

	bp.media.view.AttachmentsDetails = wp.media.view.Attachment.Details.extend({

		tagName:   'div',
		className: 'attachment-details',
		template:  wp.media.template('bp-attachment-details'),

		render: function() {
			var options = _.defaults( this.model.toJSON(), {
					orientation:   'landscape',
					uploading:     false,
					type:          '',
					subtype:       '',
					icon:          '',
					filename:      '',
					caption:       '',
					title:         '',
					dateFormatted: '',
					width:         '',
					height:        '',
					compat:        false,
					alt:           '',
					description:   ''
				});

			options.buttons  = this.buttons;
			options.describe = this.controller.state().get('describe');

			if ( 'image' === options.type ) {
				options.size = this.imageSize();
			}

			options.can = {};
			if ( options.nonces ) {
				options.can.remove = !! options.nonces['delete'];
				options.can.save = !! options.nonces.update;
			}

			if ( ! _.isUndefined( options.isbp )  )
				options.can.show = true;
			else
				options.can.show = false;

			if ( this.controller.state().get('allowLocalEdits') ) {
				options.allowLocalEdits = true;
			}

			this.views.detach();
			this.$el.html( this.template( options ) );

			this.$el.toggleClass( 'uploading', options.uploading );
			if ( options.uploading ) {
				this.$bar = this.$('.media-progress-bar div');
			} else {
				delete this.$bar;
			}

			// Check if the model is selected.
			this.updateSelect();

			// Update the save status.
			this.updateSave();

			this.views.render();

			return this;
		},

	});

	media.view.AttachmentsBrowser = bp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
		
		createSingle: function() {
			var sidebar = this.sidebar,
				single = this.options.selection.single();

			sidebar.set( 'details', new bp.media.view.AttachmentsDetails({
				controller: this.controller,
				model:      single,
				priority:   80
			}) );

			if ( this.options.display ) {
				sidebar.set( 'display', new wp.media.view.Settings.AttachmentDisplay({
					controller:   this.controller,
					model:        this.model.display( single ),
					attachment:   single,
					priority:     160,
					userSettings: this.model.get('displayUserSettings')
				}) );
			}
		},
	});

	media.view.AvatarCroper = bp.media.view.AvatarCroper = wp.media.view.AttachmentsBrowser.extend( {
		tagName:   'div',
		className: 'attachments-browser',

		initialize: function() {
			_.defaults( this.options, {
				filters: false,
				search:  false,
				display: false,

				AttachmentView: bp.media.view.Avatar
			});

			this.createToolbar();
			this.updateContent();
			this.createSidebar();

			this.collection.on( 'add remove reset', this.updateContent, this );
			this.on( 'ready', this.customizeUploader, this );
		},

		customizeUploader:function(){
			$( '.upload-ui h3.upload-instructions' ).html( 'Drop your avatar anywhere to upload' );
			$( '.upload-ui a.button-hero' ).html( 'Select an image' );
		},

		updateContent: function() {
			var view = this;

			if( ! this.attachments ) {
				this.createAttachments();
			}

			if ( ! this.collection.length ) {
				this.collection.more().done( function() {
					if ( ! view.collection.length ) {
						view.createUploader();
					}
				});
			}
		},

		createAttachments: function() {
			this.removeContent();

			this.attachments = new wp.media.view.Attachments({
				controller: this.controller,
				collection: this.collection,
				selection:  this.options.selection,
				model:      this.model,
				sortable:   this.options.sortable,

				// The single `Attachment` view to be used in the `Attachments` view.
				AttachmentView: this.options.AttachmentView
			});

			this.views.add( this.attachments );
		},

		removeContent: function() {
			_.each(['attachments','uploader'], function( key ) {
				if ( this[ key ] ) {
					this[ key ].remove();
					delete this[ key ];
				}
			}, this );
		},

		createSidebar: function() {
			var options = this.options,
				selection = options.selection,
				sidebar = this.sidebar = new wp.media.view.Sidebar({
					controller: this.controller
				});

			this.views.add( sidebar );

			if ( this.controller.uploader ) {
				sidebar.set( 'uploads', new wp.media.view.UploaderStatus({
					controller: this.controller,
					priority:   40
				}) );
			}

			selection.on( 'selection:single', this.createSingle, this );
			selection.on( 'selection:unsingle', this.disposeSingle, this );

			if ( selection.single() ) {
				this.createSingle();
			}
		},

		createSingle: function() {
			var sidebar = this.sidebar,
				single = this.options.selection.single();

			sidebar.set( 'details', new bp.media.view.AvatarDetails({
				controller: this.controller,
				model:      single,
				priority:   80
			}) );
		},
	} );

	bp.media.UploaderInline = wp.media.view.UploaderInline.extend( {
		initialize:function() {
			wp.media.view.UploaderInline.prototype.initialize.apply( this, arguments );

			if ( 'avatar' == bp.media.params.item_type ) {
				this.on( 'ready', this.customizeUploader, this );
			}
		},

		customizeUploader:function(){
			$( '.upload-ui h3.upload-instructions' ).html( 'Drop your avatar anywhere to upload' );
			$( '.upload-ui a.button-hero' ).html( 'Select an image' );
		}
	} );

	bp.media.view.ToolbarSelect = wp.media.view.Toolbar.Select.extend({
		initialize: function() {
			var options = this.options;
			/**
			 * call 'initialize' directly on the parent class
			 */
			wp.media.view.Toolbar.Select.prototype.initialize.apply( this, arguments );
		},

		refresh: function() {
			var library = media.bpAttachmentsBrowser._frame.state().get( 'library' ),
			    selection = media.bpAttachmentsBrowser._frame.state().get('selection');

			if ( -1 == bp.media.params.callback.indexOf( 'http://' ) ) {
				if ( selection.length > 0 ) {
					this.get( 'select' ).model.set( 'disabled', false );
				} else {
					this.get( 'select' ).model.set( 'disabled', true );
				}
			} else {
				this.get( 'select' ).model.set( 'text', 'Ok' );
				if ( library.length > 0 ) {
					this.get( 'select' ).model.set( 'disabled', false );
				} else {
					this.get( 'select' ).model.set( 'disabled', true );
				}
			}
		},
	});

	media.bpAttachmentsBrowser = _.extend( media, {
		frame: function() {
			if ( this._frame )
				return this._frame;

			var states = [new bp.media.controller.bpLibrary()];

			this._frame = wp.media( {
				className: 'media-frame no-sidebar',
				states: states,
				state: 'bp_library'
			} );

			this._frame.on( 'open', this.open );
			this._frame.on( 'close', this.close );
			this._frame.on( 'router:create:bp_browse', this.createRouter, this  );
			this._frame.on( 'router:render:bp_browse', this.bpBrowse, this );
			this._frame.on( 'content:create:bpbrowse', this.bpBrowseContent, this );
			this._frame.on( 'content:create:crop', this.cropAvatar, this );
			this._frame.on( 'content:render:bp_upload', this.uploadContent, this );
			this._frame.on( 'toolbar:create:bp_select', this.createSelectToolbar, this );

			this._frame.state( 'bp_library' ).on( 'select', this.select );

			// Check if one file at a time
			this._frame.listenToOnce( this._frame.states.get('bp_library').frame.uploader, 'ready', this.oneAtatime, this );

			return this._frame;
		},

		oneAtatime:function() {
			// plupload customs 1 at a time if set so !
			this.uploader.uploader.uploader.bind( 'FilesAdded', function( up, files ) {
				// one file at a time !
				if( _wpPluploadSettings.defaults.multi_selection == false && files.length > 1 ) {
					var default_error = pluploadL10n.default_error;
					pluploadL10n.default_error = bp.l10n.bp_attachments.files_error;

					for ( i in files ) {
						this.trigger( 'Error', {
	      					code : 'bp_failed',
						    file : files[i]
	    				});
						up.removeFile(files[i]);
					}
					pluploadL10n.default_error = default_error;
				}
			} );
		},

		createRouter:function( router ) {
			router.view = new wp.media.view.Router({
				controller: this._frame
			});
		},

		bpBrowse:function( view ) {

			if ( 'avatar' == bp.media.params.item_type ) {
				view.set({
					bp_upload: {
						text:     bp.l10n.bp_attachments.uploadtab,
						priority: 20
					},
					crop: {
						text:     bp.l10n.bp_attachments.croptab,
						priority: 40
					}
				});
			} else {
				view.set({
					bp_upload: {
						text:     bp.l10n.bp_attachments.uploadtab,
						priority: 20
					},
					bpbrowse: {
						text:     bp.l10n.bp_attachments.managetab,
						priority: 40
					}
				});
			}
			
		},

		bpBrowseContent:function( content ) {
			var state = this._frame.state();

			this._frame.$el.removeClass('hide-toolbar');

			// Browse our library of attachments.
			content.view = new media.view.AttachmentsBrowser({
				controller: this._frame,
				collection: state.get('library'),
				selection:  state.get('selection'),
				model:      state,
				sortable:   state.get('sortable'),
				search:     state.get('searchable'),
				filters:    state.get('filterable'),
				display:    state.get('displaySettings'),
				dragInfo:   state.get('dragInfo'),

				AttachmentView: state.get('AttachmentView')
			});
		},

		cropAvatar: function( content ) {
			var state = this._frame.state();

			this._frame.$el.removeClass('hide-toolbar');

			// Browse our library of attachments.
			content.view = new media.view.AvatarCroper({
				controller: this._frame,
				collection: state.get('library'),
				selection:  state.get('selection'),
				model:      state,
				display:    state.get('displaySettings'),
				dragInfo:   state.get('dragInfo')
			});
		},

		createSelectToolbar: function( toolbar, options ) {
			options = options || this._frame.options.button || {};
			options.controller = this._frame;

			if ( 'avatar' == bp.media.params.item_type ) {
				toolbar.view = new wp.media.view.Toolbar.Select( options );
			}

			if ( ! _.isUndefined( bp.media.params.callback ) && bp.media.params.callback ) {
				toolbar.view = new bp.media.view.ToolbarSelect( options );
			}
		},

		uploadContent: function() {

			if ( 'avatar' == bp.media.params.item_type ) {
				this._frame.reset();
				this._frame.state().get('library').reset();
			}

			this._frame.$el.removeClass('hide-toolbar');
			this._frame.content.set( new bp.media.UploaderInline({
				controller: this._frame
			}) );
		},

		open: function() {
			$( '.media-modal' ).css({
				"top":    "10%",
				"right":  "15%",
				"bottom": "10%",
				"left":   "15%"
		    });
		},

		close: function() {
			$( '.media-modal' ).removeAttr( 'style');
		},

		select: function() {
			var settings = wp.media.view.settings,
				selection = this.get( 'selection' ).single();

			$( '.added' ).remove();
			media.set( selection );
		},

		set: function( attachment ) {
			if ( 'avatar' == bp.media.params.item_type ) {
				wp.media.post( 'bp_attachments_set_avatar', {
					json:          true,
					object:        bp.media.settings.object,
					component:     bp.media.params.item_component,
					avatar_dir:    bp.media.settings.avatar_dir,
					item_id:       bp.media.settings.item_id,
					original_file: attachment.get( 'src' ),
					crop_w:        attachment.get( 'w' ),
					crop_h:        attachment.get( 'h' ),
					crop_x:        attachment.get( 'x' ),
					crop_y:        attachment.get( 'y' ), 
					nonce:         bp.media.params.nonce
				}).done( function( html ) {
					$( '#' + bp.media.params.item_component + '-avatar', '#bp_' + bp.media.params.item_component + '_avatar' ).html( html );
					$( bp.media.settings.button_id ).hide();
				});
				// reseting the frame
				this._frame.reset();
				this._frame.state().get('library').reset();

			} else {

				if ( -1 == bp.media.params.callback.indexOf( 'http://' ) ) {
					wp.media.post( bp.media.params.callback, {
						json:          true,
						id:            attachment.get('id'),
						object:        bp.media.settings.object,
						component:     bp.media.params.item_component,
						item_id:       bp.media.settings.item_id,
						nonce:         bp.media.params.nonce
					}).done( function( html ) {
						$( bp.media.params.callback_id ).html( html );
					});
				} else {
					window.location.href = bp.media.params.callback;
				}
			}
		},

		deleteAvatar: function() {
			wp.media.post( 'bp_attachments_delete_avatar', {
				json:          true,
				object:        bp.media.settings.object,
				avatar_dir:    bp.media.settings.avatar_dir,
				component:     bp.media.params.item_component,
				item_id:       bp.media.settings.item_id,
				nonce:         bp.media.params.nonce
			}).done( function( result ) {

				if ( 1 == result ) {
					$( '#' + bp.media.params.item_component + '-avatar img' ).remove();
					$( '#' + bp.media.params.item_component + '-avatar p' ).remove();
					$( bp.media.settings.button_id ).show();
				}

			});
		},

		init: function() {

			if ( 'avatar' == bp.media.params.item_type ) {
				// Avatar
				if ( ! $( '#remove-' + bp.media.params.item_component + '-avatar' ).length ) {
					$( bp.media.settings.button_id ).show();
				}
				$( '#' + bp.media.params.item_component + '-avatar' ).on( 'click', 'a', function(e) {
					e.preventDefault();

					media.bpAttachmentsBrowser.deleteAvatar();
				});
			} else {
				if ( $( '#attachment-upload-form' ).length ) {
					$( '#attachment-upload-form' ).remove();
					$( bp.media.settings.button_id ).show();
				}
			}

			$( bp.media.settings.button_id ).on( 'click', 'a', function( e ) {
				e.preventDefault();

				media.bpAttachmentsBrowser.frame().open();
			});
		}
	} );
	
	// For BP Attachments & Avatars
	$( media.bpAttachmentsBrowser.init );


	bp.media.view.ToolbarPreview = wp.media.view.Toolbar.extend( {
		initialize: function() {
			_this = this;

			_.defaults( this.options, {
			    event : 'pagination',
			    close : false,
				items : {
				    // See wp.media.view.Button
				    next : {
				        id       : 'bp-next',
				        style    : 'primary',
				        text     : bp.l10n.bp_attachments.nextCaption,
				        priority : 80,
				        click    : function() {
				        	this.controller.state().nextPage();
						}
				    },
				    prev : {
				        id       : 'bp-prev',
				        style    : 'primary',
				        text     : bp.l10n.bp_attachments.prevCaption,
				        priority : 60,
				        click    : function() {
				        	this.controller.state().prevPage();
						}
				    }
				}
			});

			wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );

			this.controller.state().selected.on( 'change', this.refresh, this );

		},

		refresh: function() {
			var hasmore = hasprev = false,
				total = this.controller.state().props.length,
				current = this.controller.state().selected.get( 'photo' ),
				ids =this.controller.state().props.pluck( 'id' );

			position = _.indexOf( ids, current );

			if( total > 0 ) {
				hasmore = ( Number( total ) - Number( position + 1 ) ) > 0 ? true : false ;
             	hasprev = position > 0 ? true : false ;
			}

			this.get( 'next' ).model.set( 'disabled', ! hasmore );
			this.get( 'prev' ).model.set( 'disabled', ! hasprev );

		},
	} );
	
	bp.media.view.PreviewImage = wp.media.View.extend( {
		className: 'bp-preview',
		template:  wp.media.template( 'bp-preview' ),

		render:function() {

			if( ! this.options.selected.get( 'photo' ) )
				return;

			var options = _.defaults( this.collection.get( this.options.selected.get( 'photo' ) ).toJSON(), {
					id:0,
					title:'',
					img: '',
				});

			this.views.detach();
			this.$el.html( this.template( options ) );

			this.views.render();

			return this;
		},
	} );


	media.controller.PreviewImage = wp.media.controller.State.extend( {
		defaults: {
			id:       'preview',
			menu:     'default',
			content:  'preview',
			toolbar:  'bp_preview',
		},

		initialize: function() {
			_this = this;
			this.props = new Backbone.Collection();
			this.selected = new Backbone.Model( {
				id:'_sel',
				photo:'',
			} )

			$( 'li.image .item-avatar a.attachment-link' ).each( function() {
				_this.props.add( new Backbone.Model({
					id   : Number( $(this).data( 'attachment') ),
					img  : $(this).prop( 'href' ),
					title: $(this).prop( 'title' ),
				}) );
			});

			this.selected.on( 'change', this.activate, this );
		},

		activate: function() {
			var view = new bp.media.view.PreviewImage( {
					controller: this.frame,
					model:      this.frame.state(),
					collection: this.props,
					selected: this.selected
			} );

			this.frame.content.set( view );
		},

		nextPage:function() {
			var current = this.selected.get('photo');
			var ids = this.props.pluck( 'id' );

			next = _.indexOf( ids, current );
			this.selected.set( 'photo', ids[ next + 1 ] );
		},

		prevPage:function() {
			var current = this.selected.get('photo');
			var ids = this.props.pluck( 'id' );

			prev = _.indexOf( ids, current );
			this.selected.set( 'photo', ids[ prev - 1 ] );
		},
	});


	media.attachmentsPreview = {
		
		defaults : {
            img: '',
        },

		frame: function() {
			if ( this._frame )
				return this._frame;

			states = [
					new media.controller.PreviewImage( {
						title: bp.l10n.bp_attachments.diapoTitle,
						id:    'preview',
					} ),
			];

			this._frame = wp.media( {
				className: 'media-frame no-sidebar',
				states: states,
				state: 'preview',
				selected:this.defaults.img,
			} );

			this._frame.on( 'open', this.open );

			this._frame.on( 'toolbar:create:bp_preview', this.createSelectToolbar, this );

			return this._frame;
		},

		createSelectToolbar: function( toolbar, options ) {
			options = options || this._frame.options.button || {};
			options.controller = this._frame;

			toolbar.view = new bp.media.view.ToolbarPreview( options );
		},

		open: function() {
			media.attachmentsPreview.frame().states.get('preview').selected.set( {id:'_sel', photo: media.attachmentsPreview.defaults.img });
		},

		init: function() {

			$( '#attachments-list li.image').on( 'click', '.attachment-link', function( e ) {
				e.preventDefault();

				image = $( e.target ).data( 'attachment' );

				if ( _.isUndefined( image ) )
					image = $( e.target ).parent().data( 'attachment' );

				media.attachmentsPreview.defaults.img = image;

				media.attachmentsPreview.frame().open();
			} );
		}
	};

	$( media.attachmentsPreview.init );

} )( jQuery );
