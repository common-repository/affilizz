import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
	return (
		<div {...useBlockProps.save()}>
			[affilizz-publication id="{attributes.id}"]
		</div>
	);
}