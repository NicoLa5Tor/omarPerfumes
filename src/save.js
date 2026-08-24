import { useBlockProps } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { title, description, imageUrl } = attributes;
	const blockProps = useBlockProps.save();

	return (
		<div { ...blockProps }>
			<div className="perfume-card">
				{ imageUrl && <img src={ imageUrl } alt={ title } /> }
				<h3>{ title }</h3>
				<p>{ description }</p>
			</div>
		</div>
	);
}
