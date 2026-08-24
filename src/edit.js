import { useBlockProps } from '@wordpress/block-editor';
import FloatingWhatsApp from './components/FloatingWhatsApp';
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
} from './sections';
import { getLandingData, updateArrayAttribute } from './utils/blockData';

export default function Edit( { attributes, setAttributes } ) {
	const { categories, brands, products, benefits, paymentMethods } =
		getLandingData( attributes );

	return (
		<div { ...useBlockProps( { className: 'perfumes-landing' } ) }>
			<HeaderSection
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
			<NavigationSection
				categories={ categories }
				onChange={ updateArrayAttribute( setAttributes, 'categories' ) }
			/>
			<HeroSection
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
			<BrandStripSection
				brands={ brands }
				onChange={ updateArrayAttribute( setAttributes, 'brands' ) }
			/>
			<ProductGridSection
				products={ products }
				onChange={ updateArrayAttribute( setAttributes, 'products' ) }
			/>
			<PromoSection
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
			<TrustBarSection
				benefits={ benefits }
				onChange={ updateArrayAttribute( setAttributes, 'benefits' ) }
			/>
			<PaymentMethodsSection
				methods={ paymentMethods }
				onChange={ updateArrayAttribute(
					setAttributes,
					'paymentMethods'
				) }
			/>
			<FooterSection />
			<FloatingWhatsApp />
		</div>
	);
}
