import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder, Button, Flex, FlexItem, Notice, Animate, Dashicon, Modal } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

import AffilizzModal from '../../js/modal.js';

import AffilizzIcon from './icon';
import './editor.scss';

export default function Edit({ attributes, setAttributes, isSelected }) {
	const [isModalOpen, setModalOpen] = useState(false);
	const [ssrContent, setSSRContent] = useState(false);

	const [apiFailure, setApiFailure] = useState(false);

	async function loadPopinContent() {
		window.affilizzModal.updateId( attributes.id ?? 0 );
	    window.affilizzModal.open( false );
        window.affilizzModal.refreshContent();
	}

	async function loadSSRContent( forceId = false ) {
		let remoteSSRId = ( forceId !== false ? forceId : attributes.id );
		await fetch(ajaxurl + '?action=get_affilizz_publication&id=' + remoteSSRId + '&cache_key=' + Date.now() + '&with_indicator=1' )
			.then(response => {
				if(!response.ok) {
					throw Error(response.statusText);
				} else {
					setApiFailure(false);
				}

				return response;
			})
			.then(result => result.text())
			.then(data => setSSRContent( data ) )
			.catch(error => {
				setApiFailure(__('Unable to get the preview of the publication', 'affilizz'));
			});
	}

	const closeModal = () => {
		setModalOpen(false);
	}

	useEffect(() => {
		window.addEventListener( 'affilizz:saved', ( event ) => {
			if ( attributes.id == '' || typeof attributes.id == 'undefined' ) {
				setAttributes({ id: "" + event.detail.id }); // Convert data to string
				attributes.id = event.detail.id;
			}
			if ( attributes.id == '' || typeof attributes.id == 'undefined' || attributes.id == event.detail.id ) {
				setSSRContent( '' );
				loadSSRContent( event.detail.id );
			}
			wp.data.dispatch( 'core/block-editor' ).clearSelectedBlock();
		} );

		window.addEventListener( 'affilizz:close', ( event ) => {
			wp.data.dispatch( 'core/block-editor' ).clearSelectedBlock();
		} );

		loadSSRContent();
	}, []);

	return (
		<div {...useBlockProps()}>
			{(apiFailure &&
				<Animate type="appear">
					{({ className }) => (
						<Notice className={className} status="error" isDismissible={false}>
							<Dashicon icon="warning" /> {__('There is an error with Affilizz API: ', 'affilizz')} <code>{apiFailure}</code>
						</Notice>
					)}
				</Animate>
			)}

			{(!isSelected && attributes.id && ssrContent != "" &&
				<div dangerouslySetInnerHTML={{ __html: ssrContent }}></div>
			)}

			{(!isSelected && attributes.id && ssrContent == "" &&
				<div className="affilizz-loading">
					{AffilizzIcon}
					<p>{__('Rendering in progress…', 'affilizz')}</p>
				</div>
			)}

			{((isSelected || !attributes.id) &&
				<Flex align="row" className="affilizz-placeholder-wrapper">
					<FlexItem>
						<Placeholder
							className="affilizz-placeholder"
							icon={AffilizzIcon}
							instructions={__('To select or update the publication contents displayed here, please open the configuration pane below.', 'affilizz')}
							label={__('Affilizz affiliate content', 'affilizz')}
							isColumnLayout="false">
								<Button onClick={loadPopinContent} className="affilizz-button affilizz-button--primary">
									{(attributes.id && __('Edit content', 'affilizz'))}
									{(!attributes.id && __('Add content', 'affilizz'))}
								</Button>
						</Placeholder>
					</FlexItem>
				</Flex>
			)}
		</div>
	);
}