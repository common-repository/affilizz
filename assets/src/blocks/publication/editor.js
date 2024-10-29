// Import __ from i18n internationalization library
const { __ } = wp.i18n;

// Import registerBlockType() from block building libary
import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import Save from './save';
import metadata from './block.json';

import AffilizzIcon from './icon';

registerBlockType( metadata, {
    edit: Edit,
    save: Save,
    title: __( 'Affilizz affiliate content', 'affilizz' ),
    description: __( 'Insert affiliate content from your Affilizz account', 'affilizz' ),
    keywords: [
        'affiliate', __( 'affiliate', 'affilizz' ),
        'affiliated', __( 'affiliated', 'affilizz' ),
        'media', __( 'publication', 'affilizz' ), 'publication',
        'affilizz', 'afiliz', 'affiliz', 'afilizz'
    ],
    attributes: {
        id: {
            type: 'string',
            default: ''
        }
    },
    icon: {
        src: AffilizzIcon
    }
} );