import { RichText } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import EditableImage from '../../components/EditableImage';

export default function ProductCard( { product, index, onChange, onOpen, onAdd } ) {
	const update = ( patch ) => onChange( index, patch );

	return (
		<article className="perfumes-product-card">
			<div className="perfumes-product-card__image" role="button" tabIndex={ 0 } onClick={ () => onOpen( index ) } onKeyDown={ () => onOpen( index ) }>
				<EditableImage imageUrl={ product.imageUrl } label="Imagen producto" onChange={ ( imageUrl ) => update( { imageUrl } ) } />
			</div>
			<RichText tagName="span" className="perfumes-badge" value={ product.discount } onChange={ ( discount ) => update( { discount } ) } />
			<div className="perfumes-product-card__body">
				<RichText tagName="p" className="perfumes-product-card__brand" value={ product.brand } onChange={ ( brand ) => update( { brand } ) } />
				<RichText tagName="h3" value={ product.name } onChange={ ( name ) => update( { name } ) } />
				<RichText tagName="p" className="perfumes-product-card__size" value={ product.size } onChange={ ( size ) => update( { size } ) } />
				<div className="perfumes-product-card__price-row"><RichText tagName="strong" className="perfumes-product-card__price" value={ product.price } onChange={ ( price ) => update( { price } ) } /><RichText tagName="span" className="perfumes-product-card__old-price" value={ product.oldPrice } onChange={ ( oldPrice ) => update( { oldPrice } ) } /></div>
				<Button className="perfumes-product-card__button" variant="primary" onClick={ () => onAdd( index ) }>Agregar al carrito</Button>
			</div>
		</article>
	);
}
