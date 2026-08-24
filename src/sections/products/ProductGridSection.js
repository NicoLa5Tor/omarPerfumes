import { RichText } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import EditableImage from '../../components/EditableImage';

export default function ProductGridSection( { products, onChange, onOpen, onAdd } ) {
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
					<article className="perfumes-product-card" key={ product.id || index }>
						<div className="perfumes-product-card__image" role="button" tabIndex={ 0 } onClick={ () => onOpen?.( index ) } onKeyDown={ () => onOpen?.( index ) }>
							<EditableImage imageUrl={ product.imageUrl } label={ __( 'Imagen producto', 'perfumes' ) } onChange={ ( value ) => updateProduct( index, { imageUrl: value } ) } />
						</div>
						<RichText
							tagName="span"
							className="perfumes-badge"
							value={ product.discount }
							onChange={ ( value ) =>
								updateProduct( index, { discount: value } )
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
							tagName="p"
							className="perfumes-product-card__size"
							value={ product.size }
							onChange={ ( value ) => updateProduct( index, { size: value } ) }
						/>
						<div className="perfumes-product-card__price-row">
							<RichText
								tagName="strong"
								className="perfumes-product-card__price"
								value={ product.price }
								onChange={ ( value ) =>
									updateProduct( index, { price: value } )
								}
							/>
							<RichText
								tagName="span"
								className="perfumes-product-card__old-price"
								value={ product.oldPrice }
								onChange={ ( value ) =>
									updateProduct( index, { oldPrice: value } )
								}
							/>
						</div>
						<Button className="perfumes-product-card__button" variant="primary" onClick={ () => onAdd?.( index ) }>
							{ __( 'Agregar al carrito', 'perfumes' ) }
						</Button>
					</article>
				) ) }
			</div>
		</section>
	);
}
