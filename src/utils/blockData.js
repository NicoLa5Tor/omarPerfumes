import {
	defaultBenefits,
	defaultBrands,
	defaultPaymentMethods,
} from '../data/defaults';

const withDefaultArray = ( value, fallback ) =>
	Array.isArray( value ) && value.length ? value : fallback;

export function getLandingData( attributes ) {
	return {
		brands: withDefaultArray( attributes.brands, defaultBrands ),
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
