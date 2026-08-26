import { InspectorControls } from '@wordpress/block-editor';
import { NumberControl, PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	BrandStripSection,
	HeroSection,
	PaymentMethodsSection,
	PromoSection,
	TrustBarSection,
} from '../sections';
import { getLandingData, updateArrayAttribute } from '../utils/blockData';

export default function StorefrontEditor( { attributes, setAttributes } ) {
	const data = getLandingData( attributes );

	return (
		<div className="perfumes-storefront-editor">
			<InspectorControls>
				<PanelBody title={ __( 'Productos del hero', 'perfumes' ) }>
					<NumberControl
						label={ __( 'ID del producto principal', 'perfumes' ) }
						help={ __(
							'Orientica Amber Rouge se usa cuando el campo queda en 0.',
							'perfumes'
						) }
						min={ 0 }
						value={ attributes.heroProductId || 0 }
						onChange={ ( value ) =>
							setAttributes( {
								heroProductId:
									Number.parseInt( value, 10 ) || 0,
							} )
						}
					/>
					<NumberControl
						label={ __( 'ID del producto secundario', 'perfumes' ) }
						help={ __(
							'Afnan 9 PM se usa cuando el campo queda en 0.',
							'perfumes'
						) }
						min={ 0 }
						value={ attributes.heroSecondaryProductId || 0 }
						onChange={ ( value ) =>
							setAttributes( {
								heroSecondaryProductId:
									Number.parseInt( value, 10 ) || 0,
							} )
						}
					/>
				</PanelBody>
				<PanelBody title={ __( 'Enlaces de la landing', 'perfumes' ) }>
					<TextControl
						label={ __( 'URL del CTA principal', 'perfumes' ) }
						value={ attributes.primaryCtaUrl || '' }
						onChange={ ( value ) =>
							setAttributes( { primaryCtaUrl: value } )
						}
					/>
					<TextControl
						label={ __( 'URL del CTA secundario', 'perfumes' ) }
						value={ attributes.secondaryCtaUrl || '' }
						onChange={ ( value ) =>
							setAttributes( { secondaryCtaUrl: value } )
						}
					/>
					<TextControl
						label={ __( 'URL del CTA promocional', 'perfumes' ) }
						value={ attributes.promoCtaUrl || '' }
						onChange={ ( value ) =>
							setAttributes( { promoCtaUrl: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<HeroSection
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
			<BrandStripSection
				brands={ data.brands }
				onChange={ updateArrayAttribute( setAttributes, 'brands' ) }
			/>
			<section className="perfumes-products perfumes-products--woo-preview">
				<div className="perfumes-section-heading">
					<h2>{ __( 'Top en ventas', 'perfumes' ) }</h2>
				</div>
				<p className="perfumes-woo-preview-notice">
					{ __(
						'Los productos, precios e inventario de esta sección se cargan automáticamente desde WooCommerce en el sitio público.',
						'perfumes'
					) }
				</p>
			</section>
			<PromoSection
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
			<div className="perfumes-assurance">
				<TrustBarSection
					benefits={ data.benefits }
					onChange={ updateArrayAttribute(
						setAttributes,
						'benefits'
					) }
				/>
				<PaymentMethodsSection
					methods={ data.paymentMethods }
					onChange={ updateArrayAttribute(
						setAttributes,
						'paymentMethods'
					) }
				/>
			</div>
		</div>
	);
}
