import { __ } from '@wordpress/i18n';

const affilizzHTMLDecode = (input) => {
	const doc = new DOMParser().parseFromString(input, "text/html");
	return doc.documentElement.textContent;
}

// We need to override TomSelect's Drag and drop that assumes jQuery is in the "$" variable
function affilizzTomSelectDragDrop () {
	var self = this;
	if ( ! jQuery.fn.sortable ) throw new Error( 'The "affilizzTomSelectDragDrop" plugin requires jQuery UI "sortable".' );
    if ( self.settings.mode !== 'multi' ) return;

	var originalLock = self.lock;
	var originalUnlock = self.unlock;
	self.hook( 'instead', 'lock', () => {
		var sortable = jQuery( self.control ).data( 'sortable' );
		if (sortable) sortable.disable();
		return originalLock.call( self );
	} );
	self.hook( 'instead', 'unlock', () => {
		var sortable = jQuery( self.control ).data( 'sortable' );
		if ( sortable ) sortable.enable();
		return originalUnlock.call( self );
	} );

	self.on( 'initialize', () => {
		var control = jQuery( self.control ).sortable( {
			items: '[data-value]',
			forcePlaceholderSize: true,
			disabled: false,
			start: ( e, ui ) => {
				ui.placeholder.css( 'width', ui.helper.css( 'width' ) );
				control.css( {
					overflow: 'visible'
				} );
			},
			stop: () => {
				control.css( {
					overflow: 'hidden'
				} );
				var values = [];
				control.children( '[data-value]' ).each( function () {
					if ( this.dataset.value ) values.push( this.dataset.value );
				} );
				self.setValue( values );
			}
		} );
	} );
}

export default class AffilizzModal {
	constructor( attributes ) {
		// State management
		this.isLoadingPublications = false;
		this.isLoadingPublicationContents = false;
		this.hasLoadedFirstTime = false;

		// Initialization
		this.updateId( attributes.id ?? null );
		this.getUniqueId();
		this.containerSelector = document.querySelector( '#affilizz-modal-gutenberg' ) !== null ? '#affilizz-modal-gutenberg' : '#affilizz-modal';
		this.addEventListeners();

		if ( this.publicationIdSelector ) {
			this.publicationIdSelector.destroy();
			this.publicationIdSelector = null;
		}

		this.qualifiedAvailablePublicationContents = []
	}

	updateId( id ) {
		this.currentUniqueId = id ?? null;
		if ( this.currentUniqueId != null ) {
			document.querySelector( '[name="original-affilizz-publication-id"]' ).value = this.currentUniqueId;
		}
		this.mode = id ? 'edit' : 'create';
	}

	getUniqueId( selectedPublicationId = '' ) {
		// Generating a id to link the shortcode and its attribute in database
		if ( ! this.currentUniqueId || this.currentUniqueId.length == 0 || typeof this.currentUniqueId == 'undefined' || this.currentUniqueId == 'null' ) {
			this.currentUniqueId = Math.random().toString( 36 ).substr( 2, 9 );
			if ( selectedPublicationId != '' ) {
				this.currentUniqueId = selectedPublicationId + '-' + this.currentUniqueId;
			}
			this.mode = 'create';
		}


		// Avoid making two calls
		return this.currentUniqueId;
	}

	open( replace = true ) {
		document.activeElement.blur()
		document.getElementById( 'affilizz-modal-logo' ).click();
		if ( replace ) {
			document.querySelector( '#affilizz-modal' ).replaceChildren();
		}
		document.querySelector( '.affilizz-modal' ).classList.add( 'visible' );
		return this;
	}

	close() {
		document.querySelector( '.affilizz-modal' ).classList.remove( 'visible' );
		window.dispatchEvent( new CustomEvent( 'affilizz:close' ) );

		return this;
	}

	title( title, overtitle ) {
		document.querySelector( '.affilizz-modal__overtitle' ).textContent = ( typeof overtitle == 'undefined' ? '' : overtitle.substr( 0, 64 ).trim() + ( overtitle.length > 63 ? '&hellip;' : '' ) );
		document.querySelector( '.affilizz-modal__title' ).textContent = title;
		return this;
	}

	body( content ) {
		document.querySelector( this.containerSelector ).innerHTML = content;
		return this;
	}

	addEventListeners() {
		this.removeEventListeners();

		// Do not bind window events multiple times
		this.eventCloseBind = this.eventClose.bind( this );
		this.eventEscapeBind = this.eventEscape.bind( this );
		this.eventSubmitBind = this.eventSubmit.bind( this );
		this.eventRefreshBind = this.eventRefresh.bind( this );
		this.eventWindowFocusBind = this.eventWindowFocus.bind( this );

		document.addEventListener( 'click', this.eventCloseBind, true );
		document.addEventListener( 'keydown', this.eventEscapeBind, true );
		document.addEventListener( 'click', this.eventSubmitBind, true );
		document.addEventListener( 'click', this.eventRefreshBind, true );
		document.addEventListener( 'visibilitychange', this.eventWindowFocusBind, true );
	}

	removeEventListeners() {
		// Remove previously bound event listeners
		document.removeEventListener( 'click', this.eventCloseBind, true );
		document.removeEventListener( 'keydown', this.eventEscapeBind, true );
		document.removeEventListener( 'click', this.eventSubmitBind, true );
		document.removeEventListener( 'click', this.eventRefreshBind, true );
		document.removeEventListener( 'visibilitychange', this.eventWindowFocusBind, true );
	}

	eventClose( event ) {
		// Bind the close button
		if ( event.target.classList.contains( 'affilizz-modal__close' ) ) {
			event.preventDefault();
			this.close();
			return false;
		}
	}

	eventEscape( event ) {
		if ( event.keyCode === 27 ) {
      		this.close();
    	}
	}

	eventSubmit( event ) {
		if ( event.target.closest( 'input[name="affilizz-modal-submit"]' ) ) {
			event.preventDefault();
			window.dispatchEvent( new CustomEvent( 'affilizz:save', {
				detail : {
					id   : this.getUniqueId(),
					mode : this.mode
				}
			} ) );

			this.save()
			this.close();
			return false;
		}
	}

	async eventWindowFocus( event ) {
		if ( ! document.hidden && this.hasLoadedFirstTime ) {
			this.refreshContent( event );
	    }
		return this;
	}

	hideMessage() {
		document.querySelector( '.affilizz-modal__content--default' ).style.display = 'block';
		document.querySelector( '.affilizz-modal__content--message' ).style.display = 'none';
		document.querySelector( '#affilizz-refresh-lists' ).style.display = 'block';
		document.querySelector( '.affilizz-modal__actions' ).style.display = 'block';

		document.querySelector( '.affilizz-modal' ).classList.remove( 'has-error' );
		['error', 'message', 'information', 'success', 'warning'].forEach(function(type){
			document.querySelector( '.affilizz-modal' ).classList.remove( 'message-' + type );
		});
	}

	displayMessage( type, title, content, modalOvertitle, modalTitle, linkText ) {
		document.querySelector( '.affilizz-modal' ).classList.add( 'has-error' );
		document.querySelector( '.affilizz-modal' ).classList.add( 'message-' + type );

		this.title( modalTitle, modalOvertitle );

		if ( typeof title == 'undefined' ) {
			document.querySelector( '.affilizz-modal-message__title' ).style.display = 'none';
			document.querySelector( '.affilizz-modal-message__title' ).textContent = '';
		} else {
			document.querySelector( '.affilizz-modal-message__title' ).style.display = 'block';
			document.querySelector( '.affilizz-modal-message__title' ).textContent = title;
		}

		if ( typeof title == 'undefined' ) {
			document.querySelector( '.affilizz-modal-message__content' ).style.display = 'none';
			document.querySelector( '.affilizz-modal-message__content' ).textContent = '';
		} else {
			document.querySelector( '.affilizz-modal-message__content' ).style.display = 'block';
			document.querySelector( '.affilizz-modal-message__content' ).textContent = content;
		}

		document.querySelector( '#affilizz-refresh-lists' ).style.display = 'none';
		document.querySelector( '.affilizz-modal__content--default' ).style.display = 'none';
		document.querySelector( '.affilizz-modal__actions' ).style.display = 'none';
		document.querySelector( '.affilizz-modal__content--message' ).style.display = 'block';
	}

	async eventRefresh( event ) {
		if ( event.target.closest( '#affilizz-refresh-lists' ) ) {
			this.refreshContent( event, true );
			return this;
		}
	}

	async refreshContent( event, force = false, search = '' ) {
		let previouslySelectedPublication = document.querySelector( '[name="original-affilizz-publication-id"]' ).value;
		let _selector = this.publicationIdSelector;
		this.showPublicationLoader();
		this.showPublicationContentLoader();

		// Update the textual content in the modal
		if ( this.mode == 'edit' ) {
			document.querySelector( '[name="affilizz-modal-submit"]' ).value = affilizz_admin_l10n.modal.button.update;
			this.title( affilizz_admin_l10n.modal.title.update, affilizz_admin_l10n.modal.overtitle.update );
		} else {
			document.querySelector( '[name="affilizz-modal-submit"]' ).value = affilizz_admin_l10n.modal.button.insert;
			this.title( affilizz_admin_l10n.modal.title.insert, affilizz_admin_l10n.modal.overtitle.insert );
		}

		// Build the URL
		let requestURL = ajaxurl + '?action=affilizz_get_publications&current_id=' + previouslySelectedPublication + '&context_id=' + this.getUniqueId() + '&force=' + ( force ? 1 : 0 ) + '&search=' + search;
		let _this = this;

		// Fetch the data from the API and instanciate the modal
		await fetch( requestURL ).then( ( response ) => {
			if ( ! response.ok ) {
				throw new Error( 'Error thrown in modal while populating content IDs.' );
			}
			return response.json();
		} ).then( ( data ) => {
			if ( data.publications ) {
				// Set the custom selector in a known state
				_selector.clear();
				_selector.setValue( null, true );
				_selector.clearOptions();
				_selector.disable();

				document.querySelector( '[name="affilizz-publication-id"]' ).replaceChildren();

				Object.keys(data.publications).forEach(function(value){
					let refreshedOption = document.createElement( 'option' );

					refreshedOption.value = value;
					refreshedOption.text = data.publications[value].name;
					refreshedOption.dataset.recent = data.publications[value].recent ? '1' : '0';
					document.querySelector( '[name="affilizz-publication-id"]' ).add(refreshedOption);

					_selector.addOption( {
						value   : data.publications[value].value,
						text    : data.publications[value].name,
						dataset : {
							recent: ( data.publications[value].recent ? '1' : '0' )
						}
					} );
				});

				let selectorValue = ( data.currently_selected && data.currently_selected != '' ) ? data.currently_selected : '';
				if ( typeof selectorValue == 'undefined' || selectorValue == '' ) {
					selectorValue = _selector.options[ Object.keys(_selector.options) [ 0 ] ].value;
				}

				_selector.setValue( selectorValue );
				_this.populatePublicationContentIds( selectorValue, _this.getUniqueId() );
				_selector.refreshOptions();
			}
			
			this.hidePublicationLoader();
		} ).catch( ( error ) => {
			this.hidePublicationLoader();
		} );
	}

	async loadContent() {
		// Build the form data object to post to admin-ajax
		let requestURL = ajaxurl + '?action=edit_affilizz_publication_shortcode&id=' + this.getUniqueId();

		// Fetch the data from the API
		await fetch( requestURL ).then( ( response ) => {
			if ( ! response.ok ) {
				throw new Error( 'Error thrown in modal while creating the modal.' );
			}
			return response.json();
		} ).then( ( data ) => {
			this.body( data.render );

			this.enhance();

			// Update the textual content in the modal
			if ( this.mode == 'edit' ) {
				document.querySelector( '[name="affilizz-modal-submit"]' ).value = affilizz_admin_l10n.modal.button.update;
				this.title( affilizz_admin_l10n.modal.title.update, affilizz_admin_l10n.modal.overtitle.update );
			} else {
				document.querySelector( '[name="affilizz-modal-submit"]' ).value = affilizz_admin_l10n.modal.button.insert;
				this.title( affilizz_admin_l10n.modal.title.insert, affilizz_admin_l10n.modal.overtitle.insert );
			}
		} ).catch( ( error ) => {
			this.hideLoader();
		} );
	}

	enhance() {
		if ( typeof document.querySelector( '#affilizz-publication-id' ) != 'undefined' ) {
			if ( typeof this.publicationIdSelector == 'undefined' ) {
				document.querySelector( '[name="affilizz-publication-id"]' ).addEventListener('change', (event) => {
					this.populatePublicationContentIds( document.querySelector( '[name="affilizz-publication-id"]' ).value, this.getUniqueId() );
				});
				let _this = this;
				this.publicationIdSelector = new TomSelect( '#affilizz-publication-id', {
					plugins: ['dropdown_input'],
					// Fetch remote data from the search endpoint
					shouldLoad: function( query ) {
						return query.length > 1;
					},
					load: function( query, callback ) {
						let previouslySelectedPublication = document.querySelector( '[name="original-affilizz-publication-id"]' ).value;
						let requestURL = ajaxurl + '?action=affilizz_get_publications&current_id=' + previouslySelectedPublication + '&context_id=' + _this.getUniqueId() + '&force=1&search=' + encodeURIComponent(query);
						let _selector = this;

						this.clearOptions( function( option, value ) { return true } );

						fetch( requestURL, { cache: "no-store" } )
							.then( response => response.json() )
							.then( data => {
								callback( data.publications );
							} ).catch( ( error ) => {} );
					},
					render: {
						option: function( data, escape ) {
							return '<div>'
								+ data.text
								+ ( data.recent == 1 ? '<span class="recent">' + affilizz_admin_l10n.modal.recent + '</span>' : '' )
							+ '</div>';
						},
						loading: function() {
							return '<div class="affilizz-inline-loader">' +
								'<img src="' + affilizz_admin_l10n.plugin.url + '/assets/dist/images/logo/logo-type-green.svg" class="affilizz-modal__logo"width="40" />' +
								'Loading in progress...' +
							'</div>'
						},
						item: function( data, escape ) {
							return '<div>'
								+ data.text
								+ ( data.recent == 1 ? '<span class="recent">' + affilizz_admin_l10n.modal.recent + '</span>' : '' )
							+ '</div>';
						}
					}
				} );
			}
		}

		if ( typeof document.querySelector( '#affilizz-publication-content-id' ) != 'undefined' ) {
			if ( typeof this.publicationContentIdSelector != 'undefined' ) {
				this.publicationContentIdSelector.clearOptions( function( option, value ) { return true } );
			} else {
				TomSelect.define('affilizzTomSelectDragDrop', affilizzTomSelectDragDrop);
				this.publicationContentIdSelector = new TomSelect( '#affilizz-publication-content-id', {
					searchField: 'name',
					plugins: {
						affilizzTomSelectDragDrop: {},
						remove_button : {
							title : affilizz_admin_l10n.modal.list.remove,
						}
					},
					render: {
						option: function( data, escape ) {
							let content_type = data.type ?? 'default' ;
							if ( content_type == 'loader' ) {
								return '<div>'
									+ data.text
								+ '</div>';
							}

							let name = affilizzHTMLDecode( escape( data.name ) );
							if ( name == '' ) {
								name = '<span class="affilizz-empty-publication-content-name">' + affilizz_admin_l10n.modal.list.emptyPublicationContentName + '</span>';
							}

							return '<div>' +
									'<div class="affilizz-select-option affilizz-select-option--content-type-' + content_type + '">' +
										'<img src="' + affilizz_admin_l10n.plugin.url + '/assets/dist/images/content-type/' + content_type + '.svg" width="12" />' +
										'<span class="affilizz-type-icon affilizz-type-icon--' + content_type + '">' + affilizz_admin_l10n.constants.types[ content_type ] + '</span>' +
									'</div>' +
									'<span class="affilizz-select-option--text text">' +
										name +
									'</span>' +
								'</div>';
						},
						item: function( data, escape ) {
							let content_type = data.type ?? 'default' ;
							if ( content_type == 'loader' ) {
								return '<div>'
									+ data.text
								+ '</div>';
							}

							let name = affilizzHTMLDecode( escape( data.name ) );
							if ( name == '' ) {
								name = '<span class="affilizz-empty-publication-content-name">' + affilizz_admin_l10n.modal.list.emptyPublicationContentName + '</span>';
							}

							return '<div class="affilizz-select-item affilizz-select-item--content-type-' + content_type + '">' +
									'<img src="' + affilizz_admin_l10n.plugin.url + '/assets/dist/images/content-type/' + content_type + '.svg" width="12" />&nbsp;&nbsp;' +
									'<span class="affilizz-select-option--text text">' + name + '</span>' +
								'</div>';
						}
					}
				} );
			}
		}

		this.hasLoadedFirstTime = true;
		return this;
	}

	showPublicationLoader() {
		if ( ! this.isLoadingPublications ) {
			this.isLoadingPublications = true;
			document.getElementById( 'affilizz-publication-id-loader' ).classList.add( 'visible' );
			document.getElementById( 'affilizz-publication-id-loader' ).classList.remove( 'hidden' );
			document.getElementById( 'affilizz-publication-id-wrapper' ).classList.add( 'hidden' );
			document.getElementById( 'affilizz-publication-id-wrapper' ).classList.remove( 'visible' );
		}
	}

	showPublicationContentLoader() {
		if ( ! this.isLoadingPublicationContents ) {
			document.getElementById( 'affilizz-publication-content-id-loader' ).classList.add( 'visible' );
			document.getElementById( 'affilizz-publication-content-id-loader' ).classList.remove( 'hidden' );
			document.getElementById( 'affilizz-publication-content-id-wrapper' ).classList.add( 'hidden' );
			document.getElementById( 'affilizz-publication-content-id-wrapper' ).classList.remove( 'visible' );
		}
	}

	showLoader() {
		if ( document.querySelector( '.affilizz-loader' ) !== null ) {
			document.querySelector( '.affilizz-loader' ).style.visibility = 'visible';
		}
	}

	hidePublicationLoader() {
		this.isLoadingPublications = false;

		if ( typeof this.publicationIdSelector != 'undefined' ) {
			this.publicationIdSelector.enable();
		}
		document.getElementById( 'affilizz-publication-id-loader' ).classList.remove( 'visible' );
		document.getElementById( 'affilizz-publication-id-loader' ).classList.add( 'hidden' );
		document.getElementById( 'affilizz-publication-id-wrapper' ).classList.remove( 'hidden' );
		document.getElementById( 'affilizz-publication-id-wrapper' ).classList.add( 'visible' );
	}

	hidePublicationContentLoader() {
		this.isLoadingPublicationContents = false;
		if ( typeof this.publicationContentIdSelector != 'undefined' ) {
			this.publicationContentIdSelector.enable();
		}

		document.getElementById( 'affilizz-publication-content-id-loader' ).classList.remove( 'visible' );
		document.getElementById( 'affilizz-publication-content-id-loader' ).classList.add( 'hidden' );
		document.getElementById( 'affilizz-publication-content-id-wrapper' ).classList.remove( 'hidden' );
		document.getElementById( 'affilizz-publication-content-id-wrapper' ).classList.add( 'visible' );
	}

	hideLoader() {
		if ( document.querySelector( '.affilizz-loader' ) !== null ) {
			document.querySelector( '.affilizz-loader' ).style.visibility = 'hidden';
		}
	}

	async populatePublicationContentIds( publication_id, context_id ) {
		if ( typeof publication_id == 'undefined' || publication_id == '' ) return;
		let _this = this;

		// Set the custom selector in a known state
		let _selector = this.publicationContentIdSelector;
		_selector.clear();
		_selector.setValue( null, true );
		_selector.clearOptions();
		_selector.disable();

		this.showPublicationContentLoader();

		// Build the form data object to post to admin-ajax
		let requestURL = ajaxurl + '?action=affilizz_get_publication_contents&publication_id=' + publication_id + '&context_id=' + context_id;

		// Update the edit link
		let mediaId = affilizz_admin_l10n.modal.configuration.media;
		let publicationId = publication_id;

		if (
			typeof mediaId != 'undefined' && mediaId != false && mediaId.trim() != ''
			&& typeof publicationId != 'undefined' && publicationId.trim() != ''
			&& typeof document.querySelector( '#affilizz-publication-id' ) != 'undefined'
			&& typeof affilizz_admin_l10n.constants.urls.edit != 'undefined'
		) {
			let updatedEditURL = affilizz_admin_l10n.constants.urls.edit.replace( '##media##', mediaId ).replace( '##publication##', publicationId );
			document.getElementById( 'affilizz-edit-publication-link' ).href = updatedEditURL;
			document.getElementById( 'affilizz-edit-publication-call' ).classList.add( 'visible' );
			document.getElementById( 'affilizz-edit-publication-call' ).classList.remove( 'hidden' );
		} else {
			document.getElementById( 'affilizz-edit-publication-call' ).classList.add( 'visible' );
			document.getElementById( 'affilizz-edit-publication-call' ).classList.remove( 'hidden' );
		}


		// Fetch the data from the API and instanciate the modal
		await fetch( requestURL ).then( ( response ) => {
			if ( ! response.ok ) {
				throw new Error( 'Error thrown in modal while populating content IDs.' );
			}
			return response.json();
		} ).then( ( data ) => {
			if ( data ) {
				_this.availablePublicationContents = [];
				_this.selectedPublicationContents = [];

				// Set the available options
				for ( let publication_content_id in data ) {
					let available_publication_content = {
						value : publication_content_id,
						name  : data[ publication_content_id ].name,
						type  : data[ publication_content_id ].type
					};
					_this.availablePublicationContents.push( available_publication_content );
					_this.qualifiedAvailablePublicationContents[ publication_content_id ] = available_publication_content;

					if ( data[ publication_content_id ].selected ) {
						_this.selectedPublicationContents.push( publication_content_id );
					}
				}

				// Select the only publication ID if we only have one
				if ( data.length == 1 ) {
					_this.selectedPublicationContents.push( data.keys()[ 0 ] );
				}

				_selector.addOptions( _this.availablePublicationContents, true );
				_selector.setValue( _this.selectedPublicationContents, true );
				_selector.enable();

				this.hidePublicationContentLoader();
			}
		} ).catch( ( error ) => {
			_selector.clear();
			_selector.disable();
			this.hidePublicationContentLoader();
		} );

		return this;
	};

	async save() {
		this.selectedPublicationContents = [];
		let _this = this;

		this.publicationContentIdSelector.getValue().forEach( ( selectedPublicationContent, index ) => {
			this.selectedPublicationContents.push( {
				'id'   : selectedPublicationContent,
				'name' : _this.qualifiedAvailablePublicationContents[ selectedPublicationContent ].name,
				'type' : _this.qualifiedAvailablePublicationContents[ selectedPublicationContent ].type
			} )
		} ) ;

		// Build the form data object to post to admin-ajax
		let requestURL = ajaxurl + '?action=affilizz_save_shortcode';
		let currentPublicationId = document.querySelector( '[name="affilizz-publication-id"]' ).selectedOptions[ 0 ].value;
		let requestURLShards = {
			id: this.getUniqueId( currentPublicationId ),
			post: ( typeof affilizz_admin_l10n.variables.current_post != 'undefined' ? affilizz_admin_l10n.variables.current_post : 0 ),
			user: ( typeof affilizz_admin_l10n.variables.current_user != 'undefined' ? affilizz_admin_l10n.variables.current_user : 0 ),
			publication_name: encodeURIComponent( document.querySelector( '[name="affilizz-publication-id"]' ).selectedOptions[ 0 ].label ),
			publication_id: currentPublicationId,
			publication_contents: encodeURIComponent( JSON.stringify( this.selectedPublicationContents ) )
		}

		for (const [key, value] of Object.entries(requestURLShards)) {
		  requestURL = requestURL + '&' + key + '=' + value;
		}

		// Fetch the data from the API and instanciate the modal
		await fetch( requestURL ).then( ( response ) => {
			if ( ! response.ok ) {
				throw new Error( 'Error thrown in modal while saving data.' );
			}
			return response.json();
		} ).then( ( data ) => {
			window.dispatchEvent( new CustomEvent( 'affilizz:saved', {
				detail : data
			} ) );
			this.hideLoader();
		} );

		return this;
	}
}