// Import context
import { __ } from '@wordpress/i18n';
import AffilizzModal from './modal.js';

// Default values for state management
let currentBookmark         = null;
let preventShortcodeRefresh = [];
let uncachedAffilizzPublicationDisplay = [];
let processShortcode        = null;

document.addEventListener('DOMContentLoaded', (event) => {
    if ( document.getElementById( 'affilizz-modal' ) != undefined ) {
        window.affilizzModal = new AffilizzModal( {} );
        window.affilizzModal.loadContent();
    }

    // Retrieve default media popins
    wp.mce = wp.mce || {};

    if ( wp.media ) {
        // Add the custom view to TinyMCE
        wp.mce['affilizz-publication'] = {
            shortcode_data : {},
            template: wp.media.template( 'affilizz-publication-block' ),
            getContent : function() {
                // If we intentionally skip the rendering in the editor, return the processed template
                let attributes = this.shortcode.attrs.named;
                if ( typeof attributes.id != 'undefined' && preventShortcodeRefresh.includes( attributes.id ) ) {
                    return this.template( attributes );
                }

                attributes.render = null;

                // Build the form data object to post to admin-ajax
                let cache = ( typeof attributes.id != 'undefined' && attributes.id != 0 && typeof uncachedAffilizzPublicationDisplay != 'undefined' && typeof uncachedAffilizzPublicationDisplay[attributes.id] != 'undefined' ) ? 0 : 1;
                if ( cache ) {
                    delete uncachedAffilizzPublicationDisplay[attributes.id];
                }
                let requestURL = ajaxurl + '?action=affilizz_render_shortcode&id=' + ( attributes.id ?? 0 ) + '&cache=' + cache;

                // Fetch the data from the API and instanciate the modal
                let request = new XMLHttpRequest();
                request.open( 'GET', requestURL, false );
                request.send( null );

                if ( request.status === 200 ) {
                    let response = JSON.parse( request.responseText );
                    attributes.render = response.render;
                }

                if ( typeof attributes.id != 'undefined' ) {
                    preventShortcodeRefresh.push( attributes.id );
                }
                return this.template( attributes );
            },
            edit: function( data ) {
                wp.mce['affilizz-publication'].modal( tinyMCE.activeEditor, wp.shortcode.next( 'affilizz-publication', data ).shortcode.attrs.named );
            },
            modal: function( editor, values, processShortcode ) {
                values = values || [];

                // Generating a id to link the shortcode and its attribute in database
                if ( ! values.id || values.id.length == 0 || typeof values.id == 'undefined' || values.id == 'null' ) {
                    values.id = '';
                }

                if ( typeof processShortcode !== 'function' ) {
                    processShortcode = function( serialized_form, values ) {
                        let shortcodeArguments = {
                            tag   : 'affilizz-publication',
                            type  : 'single',
                            attrs : { id : values.id }
                        };

                        tinymce.editors['content'].focus();
                        tinymce.activeEditor.selection.moveToBookmark( currentBookmark );
                        tinymce.activeEditor.selection.setContent( wp.shortcode.string( shortcodeArguments ) );
                    };
                }

                // Clone the values, in order to disable render without affecting the original object
                let options = JSON.parse( JSON.stringify( values ) );
                options['render'] = null;

                preventShortcodeRefresh.push( values.id );
                window.affilizzModal.updateId( values.id );
                window.affilizzModal.open( false );
                window.affilizzModal.refreshContent();

                // Using MCE bookmark to keep track of the position we were inside of the MCE editor.
                if ( ( typeof tinymce.activeEditor == 'undefined' || tinymce.activeEditor == null ) ) {
                    window.affilizzModal.displayMessage( 'error',
                        affilizz_admin_l10n.modal.messages.missingBookmark.title,
                        affilizz_admin_l10n.modal.messages.missingBookmark.content,
                        affilizz_admin_l10n.modal.messages.type.error,
                        affilizz_admin_l10n.modal.messages.missingBookmark.overtitle
					);
                } else {
                    window.affilizzModal.hideMessage();
                    currentBookmark = tinymce.activeEditor.selection.getBookmark();
                }
            }
        };

        wp.mce.views.register( 'affilizz-publication', wp.mce['affilizz-publication'] );

        window.addEventListener( 'affilizz:saved', function(event) {
            let shortcodeArguments = {
                tag   : 'affilizz-publication',
                type  : 'single',
                attrs : { id : event.detail.id }
            };

            preventShortcodeRefresh = preventShortcodeRefresh.filter(e => e !== event.detail.id );
            uncachedAffilizzPublicationDisplay[event.detail.id] = true;

            if ( typeof tinymce.editors['content'] == 'undefined' ) return;
            tinymce.editors['content'].focus();
            tinymce.activeEditor.selection.moveToBookmark( currentBookmark );
            tinymce.activeEditor.selection.setContent( wp.shortcode.string( shortcodeArguments ) );
        });

        // Insertion button behavior
        window.addEventListener( 'click', function(event) {
            // Bind the close button
            if ( event.target.classList.contains( 'insert-affilizz-publication' ) ) {
                event.preventDefault();
                wp.mce['affilizz-publication'].modal( window.wp.editor );
                return false;
            }
        });
    }
} );