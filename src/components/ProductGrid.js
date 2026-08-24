import { RichText } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import EditableImage from './EditableImage';

export default function ProductGrid( { products, onChange } ) {
	const updateProduct = ( index, patch ) => {
		const next = [ ...products ];
		next[ index ] = { ...next[ index ], ...patch };
		onChange( next );
	};

	return (
		<section className="perfumes-products">
			<div className="perfumes-section-heading">
				<p>Seleccionados</p>
				<h2>Top en ventas</h2>
			</div>
			<div className="perfumes-product-grid">
				{ products.map( ( product, index ) => (
					<article className="perfumes-product-card" key={ index }>
						<EditableImage
							className="perfumes-product-card__image"
							imageUrl={ product.imageUrl }
							label={ __( 'Imagen producto', 'perfumes' ) }
							onChange={ ( value ) =>
								updateProduct( index, { imageUrl: value } )
							}
						/>
						<RichText
							tagName="span"
							className="perfumes-badge"
							value={ product.badge }
							onChange={ ( value ) =>
								updateProduct( index, { badge: value } )
							}
						/>
						<RichText
							tagName="p"
							className="perfumes-product-card__brand"
							value={ product.brand }
							onChange={ ( value ) =>
								updateProduct( index, { brand: value } )
							}
						/>
						<RichText
							tagName="h3"
							value={ product.name }
							onChange={ ( value ) => updateProduct( index, { name: value } ) }
						/>
						<RichText
							tagName="strong"
							className="perfumes-product-card__price"
							value={ product.price }
							onChange={ ( value ) =>
								updateProduct( index, { price: value } )
							}
						/>
						<Button variant="primary" disabled>
							{ __( 'Agregar al carrito', 'perfumes' ) }
						</Button>
					</article>
				) ) }
			</div>
		</section>
	);
}
