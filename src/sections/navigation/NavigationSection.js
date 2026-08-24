import { RichText } from '@wordpress/block-editor';

export default function NavigationSection( { categories, onChange } ) {
	return (
		<nav className="perfumes-category-nav" aria-label="Categorias destacadas">
			{ categories.map( ( category, index ) => (
				<RichText
					key={ index }
					tagName="span"
					value={ category }
					onChange={ ( value ) => {
						const next = [ ...categories ];
						next[ index ] = value;
						onChange( next );
					} }
				/>
			) ) }
		</nav>
	);
}
