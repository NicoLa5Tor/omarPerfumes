import { useBlockProps } from '@wordpress/block-editor';
import StorefrontEditor from './storefront/StorefrontEditor';

export default function Edit( { attributes, setAttributes } ) {
	return (
		<div { ...useBlockProps( { className: 'perfumes-landing' } ) }>
			<StorefrontEditor
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
		</div>
	);
}
