import { RichText } from '@wordpress/block-editor';

export default function BrandsPage( { brands, onChange, onSelect } ) {
	const updateBrand = ( index, value ) => onChange( brands.map( ( brand, brandIndex ) => brandIndex === index ? value : brand ) );
	const groups = brands.reduce( ( result, brand, index ) => {
		const letter = brand.charAt( 0 ).toUpperCase();
		result[ letter ] = [ ...( result[ letter ] || [] ), { brand, index } ];
		return result;
	}, {} );
	return <><section className="perfumes-page-band"><p>Inicio / Marcas</p><h1>Nuestras marcas</h1></section><section className="perfumes-brands"><nav className="perfumes-letter-index">{ Object.keys( groups ).map( ( letter ) => <a key={ letter } href={ `#brand-${ letter }` }>{ letter }</a> ) }</nav><div className="perfumes-brand-directory">{ Object.entries( groups ).map( ( [ letter, entries ] ) => <section key={ letter } id={ `brand-${ letter }` }><h2>{ letter }</h2>{ entries.map( ( { brand, index } ) => <RichText key={ index } tagName="button" value={ brand } onClick={ () => onSelect( brand ) } onChange={ ( value ) => updateBrand( index, value ) } /> ) }</section> ) }</div></section></>;
}
