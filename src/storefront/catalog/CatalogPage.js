import { RichText } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import ProductCard from '../shared/ProductCard';

const filters = [ 'Aromatico', 'Floral', 'Oriental', 'Amaderado', 'EDP', 'EDT', 'Parfum' ];

export default function CatalogPage( { products, onChange, onOpen, onAdd, activeFilter, setActiveFilter } ) {
	const shown = activeFilter ? products.filter( ( product ) => product.family === activeFilter || product.concentration === activeFilter ) : products;
	const updateProduct = ( index, patch ) => onChange( products.map( ( product, productIndex ) => productIndex === index ? { ...product, ...patch } : product ) );
	return <>
		<section className="perfumes-page-band"><p>Inicio / Catalogo</p><h1>Catalogo de fragancias</h1></section>
		<section className="perfumes-catalog"><aside className="perfumes-filters"><strong>FILTROS</strong><div className="perfumes-filters__rule" />{ filters.map( ( filter ) => <Button key={ filter } isPressed={ activeFilter === filter } variant="tertiary" onClick={ () => setActiveFilter( activeFilter === filter ? '' : filter ) }>{ filter }</Button> ) }<Button variant="link" onClick={ () => setActiveFilter( '' ) }>Limpiar todos los filtros</Button></aside>
			<div className="perfumes-catalog__results"><div className="perfumes-catalog__head"><strong>Mostrando { shown.length } de { products.length } productos</strong><span>{ activeFilter || 'Todos los productos' }</span></div><div className="perfumes-product-grid perfumes-product-grid--catalog">{ shown.map( ( product ) => { const index = products.indexOf( product ); return <ProductCard key={ product.id || index } product={ product } index={ index } onChange={ updateProduct } onOpen={ onOpen } onAdd={ onAdd } />; } ) }</div><div className="perfumes-pagination"><Button variant="secondary">1</Button><Button variant="secondary">2</Button><Button variant="secondary">3</Button></div></div>
		</section>
	</>;
}
