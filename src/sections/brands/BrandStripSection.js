import { RichText } from '@wordpress/block-editor';

export default function BrandStripSection( { brands, onChange } ) {
	return (
		<section className="perfumes-brand-strip">
			<p>Las marcas mas buscadas</p>
			<div>
				{ brands.map( ( brand, index ) => (
					<RichText
						key={ index }
						tagName="span"
						value={ brand }
						onChange={ ( value ) => {
							const next = [ ...brands ];
							next[ index ] = value;
							onChange( next );
						} }
					/>
				) ) }
			</div>
		</section>
	);
}
