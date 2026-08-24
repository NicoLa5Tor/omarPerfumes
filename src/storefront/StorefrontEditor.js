import { useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	BrandStripSection,
	FooterSection,
	HeaderSection,
	HeroSection,
	NavigationSection,
	PaymentMethodsSection,
	ProductGridSection,
	PromoSection,
	TrustBarSection,
} from '../sections';
import { getLandingData, updateArrayAttribute } from '../utils/blockData';
import BrandsPage from './brands/BrandsPage';
import CartPage from './cart/CartPage';
import CatalogPage from './catalog/CatalogPage';
import ProductPage from './product/ProductPage';

const views = [ [ 'home', 'Inicio' ], [ 'catalog', 'Catalogo' ], [ 'brands', 'Marcas' ], [ 'product', 'Producto' ], [ 'cart', 'Carrito' ] ];

export default function StorefrontEditor( { attributes, setAttributes } ) {
	const data = getLandingData( attributes );
	const [ view, setView ] = useState( 'home' );
	const [ productIndex, setProductIndex ] = useState( 0 );
	const [ cart, setCart ] = useState( {} );
	const [ activeFilter, setActiveFilter ] = useState( '' );
	const updateProducts = updateArrayAttribute( setAttributes, 'products' );
	const addToCart = ( index ) => setCart( { ...cart, [ index ]: ( cart[ index ] || 0 ) + 1 } );
	const openProduct = ( index ) => { setProductIndex( index ); setView( 'product' ); };
	const common = { products: data.products, onChange: updateProducts, onOpen: openProduct, onAdd: addToCart };
	const brandSelection = ( brand ) => {
		const match = data.products.findIndex( ( product ) => product.brand.toLowerCase() === brand.toLowerCase() );
		if ( match >= 0 ) openProduct( match ); else setView( 'catalog' );
	};

	return (
		<div className="perfumes-storefront-editor">
			<div className="perfumes-view-switcher" role="tablist">
				{ views.map( ( [ id, label ] ) => <Button key={ id } variant={ view === id ? 'primary' : 'secondary' } onClick={ () => setView( id ) }>{ __( label, 'perfumes' ) }</Button> ) }
				<Button variant="secondary" onClick={ () => setView( 'cart' ) }>Carrito ({ Object.values( cart ).reduce( ( total, quantity ) => total + quantity, 0 ) })</Button>
			</div>
			<HeaderSection attributes={ attributes } setAttributes={ setAttributes } />
			<NavigationSection categories={ data.categories } onChange={ updateArrayAttribute( setAttributes, 'categories' ) } />
			<main className="perfumes-storefront-content">
				{ view === 'home' && <><HeroSection attributes={ attributes } setAttributes={ setAttributes } /><BrandStripSection brands={ data.brands } onChange={ updateArrayAttribute( setAttributes, 'brands' ) } /><ProductGridSection { ...common } /><PromoSection attributes={ attributes } setAttributes={ setAttributes } /><TrustBarSection benefits={ data.benefits } onChange={ updateArrayAttribute( setAttributes, 'benefits' ) } /><PaymentMethodsSection methods={ data.paymentMethods } onChange={ updateArrayAttribute( setAttributes, 'paymentMethods' ) } /></> }
				{ view === 'catalog' && <CatalogPage { ...common } activeFilter={ activeFilter } setActiveFilter={ setActiveFilter } /> }
				{ view === 'brands' && <BrandsPage brands={ data.brandDirectory } onChange={ updateArrayAttribute( setAttributes, 'brandDirectory' ) } onSelect={ brandSelection } /> }
				{ view === 'product' && <ProductPage { ...common } index={ productIndex } product={ data.products[ productIndex ] } onBack={ () => setView( 'catalog' ) } /> }
				{ view === 'cart' && <CartPage products={ data.products } cart={ cart } setCart={ setCart } onBack={ () => setView( 'catalog' ) } /> }
			</main>
			<FooterSection />
		</div>
	);
}
