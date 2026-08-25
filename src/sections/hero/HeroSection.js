import { RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import EditableImage from '../../components/EditableImage';

export default function HeroSection( { attributes, setAttributes } ) {
	const {
		siteTitle,
		eyebrow,
		title,
		description,
		primaryCta,
		secondaryCta,
		heroImageUrl,
		heroBrand,
		heroPrice,
	} = attributes;

	return (
		<section className="perfumes-hero perfumes-hero--editor">
			<EditableImage
				className="perfumes-hero__media"
				imageUrl={ heroImageUrl }
				label={ __( 'Imagen de fondo del hero', 'perfumes' ) }
				onChange={ ( value ) =>
					setAttributes( { heroImageUrl: value } )
				}
			/>
			<div className="perfumes-hero__shade" aria-hidden="true" />
			<div className="perfumes-hero__content">
				<div className="perfumes-hero__main">
					<RichText
						tagName="p"
						className="perfumes-eyebrow"
						value={ eyebrow }
						onChange={ ( value ) =>
							setAttributes( { eyebrow: value } )
						}
						placeholder={ __( 'Texto superior', 'perfumes' ) }
					/>
					<RichText
						tagName="p"
						className="perfumes-hero__subtitle"
						value={ title }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
						placeholder={ __( 'Promesa principal', 'perfumes' ) }
					/>
					<RichText
						tagName="h1"
						value={ siteTitle }
						onChange={ ( value ) =>
							setAttributes( { siteTitle: value } )
						}
						placeholder={ __( 'OMAR PERFUMES', 'perfumes' ) }
					/>
				</div>
				<div className="perfumes-hero-card">
					<div className="perfumes-hero-card__marquee">
						<div className="perfumes-hero-card__marquee-group">
							<span aria-hidden="true">✦</span>
							<RichText
								tagName="strong"
								value={ heroBrand }
								onChange={ ( value ) =>
									setAttributes( { heroBrand: value } )
								}
								placeholder={ __(
									'Mensaje de la banda',
									'perfumes'
								) }
							/>
							<span aria-hidden="true">✦ { heroBrand }</span>
						</div>
					</div>
					{ heroImageUrl && <img src={ heroImageUrl } alt="" /> }
					<RichText
						tagName="p"
						className="perfumes-hero__description"
						value={ description }
						onChange={ ( value ) =>
							setAttributes( { description: value } )
						}
						placeholder={ __(
							'Descripcion principal',
							'perfumes'
						) }
					/>
					<RichText
						tagName="strong"
						className="perfumes-hero__price"
						value={ heroPrice }
						onChange={ ( value ) =>
							setAttributes( { heroPrice: value } )
						}
						placeholder={ __( 'Precio hero', 'perfumes' ) }
					/>
					<div className="perfumes-hero__actions">
						<RichText
							tagName="span"
							className="perfumes-button perfumes-button--primary"
							value={ primaryCta }
							onChange={ ( value ) =>
								setAttributes( { primaryCta: value } )
							}
							placeholder={ __( 'CTA principal', 'perfumes' ) }
						/>
						<RichText
							tagName="span"
							className="perfumes-button perfumes-button--ghost"
							value={ secondaryCta }
							onChange={ ( value ) =>
								setAttributes( { secondaryCta: value } )
							}
							placeholder={ __( 'CTA secundario', 'perfumes' ) }
						/>
					</div>
				</div>
			</div>
		</section>
	);
}
