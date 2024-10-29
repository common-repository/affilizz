var step = 'connect';

// Import context
import { __ } from '@wordpress/i18n';

// Default values for state management
let currentBookmark         = null;
let preventShortcodeRefresh = false;
let processShortcode        = null;

function trigger( element, eventType ) {
    if ( typeof eventType === 'string' && typeof element[ eventType ] === 'function' ) {
        element[ eventType ]();
    } else {
        let event = ( eventType === 'string' ? new Event( eventType, { bubbles: true } ) : eventType );
        element.dispatchEvent( event );
    }
}

document.addEventListener('DOMContentLoaded', (event) => {
    // Enable the navigation through the tabs
    document.addEventListener( 'click', function( event ) {
        // Bind the steps button
        if ( event.target.classList.contains( 'affilizz-wizard__step__trigger' ) ) {
            event.preventDefault();

            // Define the neighbouting context
            let li     = event.target.parentNode;
            let target = event.target.getAttribute( 'href' );

            // Deal with the classes
            if ( ! li.classList.contains( 'step--current' ) ) {
                [].forEach.call( document.getElementsByClassName( 'step--current' ), step => {
                    step.classList.remove( 'step--current' );
                } );
                li.classList.add( 'step--current' );

                [].forEach.call( document.getElementsByClassName( 'step--current__content' ), step => {
                    step.classList.remove( 'step--current__content' );
                } );
                document.getElementById( target.substring( 1 ) ).classList.add( 'step--current__content' );

                // Finally, emit / triggers an event
                window.dispatchEvent( new CustomEvent( 'affilizz:wizard:show', {
                    detail : {
                        target: target.substring( 1 )
                    }
                } ) );
            }
        }

        return false;
    } );

    // Section display management
    window.addEventListener( 'affilizz:wizard:show', async function( event ) {
        let panel = event.detail.target;

        let loadingOption = document.createElement( 'option' );
        loadingOption.value = '0';
        loadingOption.text = __( 'Loading...', 'affilizz' );

        // When populating the second tab, fill in the lists
        if ( panel == 'step-select' ) {
            document.getElementById( 'affilizz-organization' ).replaceChildren();

            // Empty the lists
            [].forEach.call( document.querySelectorAll( '#affilizz-organization, #affilizz-media' ), item => {
                item.replaceChildren();
                item.disabled = true;
            } );

            document.getElementById( 'affilizz-organization' ).add( loadingOption );
            document.getElementById( 'step-select__error' ).replaceChildren();

            // Fetch the data from the API
            await fetch( ajaxurl + '?action=affilizz_get_entities' ).then(response => {
                return response;
            } ).then( results => {
                return results.json();
            } ).then( data => {
                document.getElementById( 'affilizz-organization' ).replaceChildren();
                if ( ! data.status ) {
                    for ( var organization in data ) {
                        let newOrganization = document.createElement( 'option' );
                        newOrganization.value = organization;
                        newOrganization.text = data[ organization ];

                        document.getElementById( 'affilizz-organization' ).add( newOrganization );
                    }

                    document.getElementById( 'affilizz-organization' ).disabled = false;
                    trigger( document.getElementById( 'affilizz-organization' ), new Event( 'change' ) );
                } else {
                    document.querySelector( '#step-select__error' ).textContent = data.message;
                }
            } ).catch( error => {
                document.querySelector( '#step-select__error' ).textContent = error;
            } );
        }
    } );

    // Validate the API key when moving from first to second tab
    document.getElementById( 'step-connect-button' ).addEventListener( 'click', async function( event ) {
        const button = event.target;

        // Fetch the data from the API
        let formData = new FormData();
        let nonceElement = null;
        nonceElement = document.getElementById('_wpnonce_key');
        formData.append( 'action', 'affilizz_check_api_key' );
        formData.append( 'key', document.getElementById( 'affilizz-api-key' ).value );
        formData.append('_wpnonce_key', nonceElement.value);

        await fetch( ajaxurl, {
            method: 'POST',
            body: formData
        } ).then(response => {
            return response;
        } ).then( results => {
            return results.json();
        } ).then( data => {
            if ( data.status && data.status == 'success' ) {
                document.querySelector( 'a[href="#step-select"]' ).click();
            } else {
                document.querySelector( '#step-connect__error' ).textContent = data.message;
            }
        } ).catch( error => {
            document.querySelector( '#step-connect__error' ).textContent = error;
        } );
    } );

    // Populate linked lists when change is triggered
    document.getElementById( 'affilizz-organization' ).addEventListener( 'change', function( event ) {
        const organization = event.target.value;

        // Fetch the data from the API
        let request = new XMLHttpRequest();
        request.open( 'GET', ajaxurl + '?action=affilizz_get_media&organizationId=' + organization, true );
        request.onload = (e) => {
            if ( request.readyState === 4 && request.status === 200 ) {
                let response = JSON.parse( request.responseText );
                document.getElementById( 'affilizz-media' ).replaceChildren();
    
                for ( var media in response ) {
                    let newMedia = document.createElement( 'option' );
                    newMedia.value = media;
                    newMedia.text = response[ media ];
    
                    document.getElementById( 'affilizz-media' ).add( newMedia );
                }
    
                document.getElementById( 'affilizz-media' ).disabled = false;
                trigger( document.getElementById( 'affilizz-media' ), new Event( 'change' ) );
            } else {
                document.getElementById( 'step-select__error' ).html( data.message );
            };
        }
        request.send( null );
    } );
    document.getElementById( 'affilizz-media' ).addEventListener( 'change', function( event ) {
        let mediaId = event.target.value;

        // Fetch the data from the API
        let request = new XMLHttpRequest();
        request.open( 'GET', ajaxurl + '?action=affilizz_get_channels&mediaId=' + mediaId, true );
        request.onload = (e) => {
            if ( request.readyState === 4 && request.status === 200 ) { 
                let response = JSON.parse( request.responseText );
                document.getElementById( 'affilizz-channel' ).replaceChildren();

                for ( var channel in response ) {
                    let newMedia = document.createElement( 'option' );
                    newMedia.value = channel;
                    newMedia.text = response[ channel ];

                    document.getElementById( 'affilizz-channel' ).add( newMedia );
                }

                document.getElementById( 'affilizz-channel' ).disabled = false;
            } else {
                document.getElementById( 'step-select__error' ).html( data.message );
            };
        };
        request.send( null );
    } );

    document.getElementById( 'step-select-back' ).addEventListener( 'click', function( event ) {
        document.getElementById( 'affilizz-wizard-step-connect-trigger' ).click();
    } );

    document.getElementById( 'step-success-back' ).addEventListener( 'click', function( event ) {
        document.getElementById( 'affilizz-wizard-step-select-trigger' ).click();
    } );

    document.getElementById( 'step-select-button' ).addEventListener( 'click', async function( event ) {
        let button = event.target;

        // Fetch the data from the API
        let formData = new FormData();

        let organizationElement = document.getElementById( 'affilizz-organization' );
        let mediaElement = document.getElementById( 'affilizz-media' );
        let channelElement = document.getElementById( 'affilizz-channel' );
        let nonceElement = document.getElementById( '_wpnonce' );

        formData.append( 'action', 'affilizz_save_params' );
        formData.append( 'key', document.getElementById( 'affilizz-api-key' ).value );

        formData.append( 'organization', organizationElement.value );
        formData.append( 'media', mediaElement.value );
        formData.append( 'channel', channelElement.value );
        formData.append( '_wpnonce', nonceElement.value );

        formData.append( 'organization-label', organizationElement.options[ organizationElement.selectedIndex ].text );
        formData.append( 'media-label', mediaElement.options[ mediaElement.selectedIndex ].text );
        formData.append( 'channel-label', channelElement.options[ channelElement.selectedIndex ].text );

        document.getElementById( 'step-select-button' ).disabled = true;

        await fetch( ajaxurl, {
            method: 'POST',
            body: formData
        } ).then(response => {
            return response;
        } ).then( results => {
            return results.json();
        } ).then( data => {
            if ( data.status && data.status == 'success' ) {
                document.querySelector( 'a[href="#step-success"]' ).click();
            } else {
                document.querySelector( '#step-select__error' ).textContent = data.message;
            }
        } ).catch( error => {
            document.querySelector( '#step-select__error' ).textContent = error;
        } );

        document.getElementById( 'step-select-button' ).disabled = false;
    } );
} );