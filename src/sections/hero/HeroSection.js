import { RichText } from '@wordpress/block-editor';
import { __, sprintf } from '@wordpress/i18n';

const MarqueeGroup = ( { message, hidden = false } ) => (
	<div className="marquee-group" aria-hidden={ hidden || undefined }>
		{ [ 0, 1, 2, 3 ].map( ( item ) => (
			<span className="marquee-item" key={ item }>
				<span className="marquee-symbol" aria-hidden="true">
					✦
				</span>
				<span>{ message }</span>
			</span>
		) ) }
	</div>
);

export default function HeroSection( { attributes, setAttributes } ) {
	const {
		title,
		primaryCta,
		heroBrand,
		heroProductId,
		heroSecondaryProductId,
	} = attributes;

	return (
		<section className="hero-section hero-section--editor">
			<div className="hero-atmosphere" aria-hidden="true" />
			<div className="wrapper hero-layout">
				<div className="hero-copy">
					<span className="hero-eyebrow">
						{ __( 'Perfumería original · Colombia', 'perfumes' ) }
					</span>
					<RichText
						tagName="p"
						className="sub-title"
						value={ title }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
						placeholder={ __( 'Promesa principal', 'perfumes' ) }
					/>
					<h1>{ __( 'Orientica Amber Rouge', 'perfumes' ) }</h1>
					<p className="hero-editor-product-id">
						{ sprintf(
							/* translators: %d product ID. */
							__( 'Producto principal · ID %d', 'perfumes' ),
							heroProductId || 0
						) }
					</p>
					<RichText
						tagName="span"
						className="hero-primary-cta"
						value={ primaryCta }
						onChange={ ( value ) =>
							setAttributes( { primaryCta: value } )
						}
						placeholder={ __( 'Ver producto', 'perfumes' ) }
					/>
				</div>

				<div className="hero-product-primary" aria-hidden="true">
					<div className="hero-editor-bottle">ORIENTICA</div>
				</div>

				<aside className="content-cta">
					<div className="cta-marquee">
						<MarqueeGroup message={ heroBrand } />
						<MarqueeGroup message={ heroBrand } hidden />
					</div>
					<div className="img-wrapper hero-editor-secondary">
						AFNAN 9 PM
					</div>
					<p className="hero-editor-product-id">
						{ sprintf(
							/* translators: %d product ID. */
							__( 'Producto secundario · ID %d', 'perfumes' ),
							heroSecondaryProductId || 0
						) }
					</p>
				</aside>
			</div>
		</section>
	);
}
