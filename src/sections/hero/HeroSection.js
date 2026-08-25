import { RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import EditableImage from '../../components/EditableImage';

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
		heroWordmark,
		title,
		primaryCta,
		heroImageUrl,
		heroCtaImageUrl,
		heroBrand,
	} = attributes;

	return (
		<section className="hero-section hero-section--editor">
			<div className="wrapper">
				<EditableImage
					className="hero-img"
					imageUrl={ heroImageUrl }
					label={ __( 'Imagen principal del hero', 'perfumes' ) }
					onChange={ ( value ) =>
						setAttributes( { heroImageUrl: value } )
					}
				/>
				<div className="hero-content">
					<div className="content-main">
						<RichText
							tagName="p"
							className="sub-title"
							value={ title }
							onChange={ ( value ) =>
								setAttributes( { title: value } )
							}
							placeholder={ __(
								'Promesa principal',
								'perfumes'
							) }
						/>
						<RichText
							tagName="h1"
							value={ heroWordmark }
							onChange={ ( value ) =>
								setAttributes( { heroWordmark: value } )
							}
							placeholder={ __( 'OMAR®', 'perfumes' ) }
						/>
					</div>
					<div className="content-cta">
						<div className="cta-marquee">
							<MarqueeGroup message={ heroBrand } />
							<MarqueeGroup message={ heroBrand } hidden />
						</div>
						<EditableImage
							className="img-wrapper"
							imageUrl={ heroCtaImageUrl }
							label={ __( 'Imagen de la tarjeta', 'perfumes' ) }
							onChange={ ( value ) =>
								setAttributes( { heroCtaImageUrl: value } )
							}
						/>
						<RichText
							tagName="span"
							className="register-button"
							value={ primaryCta }
							onChange={ ( value ) =>
								setAttributes( { primaryCta: value } )
							}
							placeholder={ __( 'Explorar ahora', 'perfumes' ) }
						/>
					</div>
				</div>
			</div>
		</section>
	);
}
