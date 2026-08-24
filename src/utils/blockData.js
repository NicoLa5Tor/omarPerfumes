import {
	defaultBenefits,
	defaultBrandDirectory,
	defaultBrands,
	defaultCategories,
	defaultPaymentMethods,
	defaultProducts,
} from '../data/defaults';

const withDefaultArray = ( value, fallback ) =>
	Array.isArray( value ) && value.length ? value : fallback;

export function getLandingData( attributes ) {
	return {
		categories: withDefaultArray(
			attributes.categories,
			defaultCategories
		),
		brands: withDefaultArray( attributes.brands, defaultBrands ),
		brandDirectory: withDefaultArray( attributes.brandDirectory, defaultBrandDirectory ),
		products: withDefaultArray( attributes.products, defaultProducts ),
		benefits: withDefaultArray( attributes.benefits, defaultBenefits ),
		paymentMethods: withDefaultArray(
			attributes.paymentMethods,
			defaultPaymentMethods
		),
	};
}

export function updateArrayAttribute( setAttributes, key ) {
	return ( value ) => setAttributes( { [ key ]: value } );
}
