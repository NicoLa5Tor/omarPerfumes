import { useBlockProps } from '@wordpress/block-editor';
import HeroSection from './components/HeroSection';
import CategoryNav from './components/CategoryNav';
import ProductGrid from './components/ProductGrid';
import PromoSection from './components/PromoSection';
import BenefitsBar from './components/BenefitsBar';
import FooterPreview from './components/FooterPreview';
import {
	defaultBenefits,
	defaultCategories,
	defaultProducts,
} from './data/defaults';

export default function Edit( { attributes, setAttributes } ) {
	const categories = attributes.categories?.length
		? attributes.categories
		: defaultCategories;
	const products = attributes.products?.length
		? attributes.products
		: defaultProducts;
	const benefits = attributes.benefits?.length
		? attributes.benefits
		: defaultBenefits;

	return (
		<div { ...useBlockProps( { className: 'perfumes-landing' } ) }>
			<HeroSection
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
			<CategoryNav
				categories={ categories }
				onChange={ ( value ) => setAttributes( { categories: value } ) }
			/>
			<ProductGrid
				products={ products }
				onChange={ ( value ) => setAttributes( { products: value } ) }
			/>
			<PromoSection
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
			<BenefitsBar
				benefits={ benefits }
				onChange={ ( value ) => setAttributes( { benefits: value } ) }
			/>
			<FooterPreview />
		</div>
	);
}
