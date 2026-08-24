import { RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import EditableImage from './EditableImage';

export default function HeroSection( { attributes, setAttributes } ) {
	const { eyebrow, title, description, primaryCta, secondaryCta, heroImageUrl } =
		attributes;

	return (
		<section className="perfumes-hero">
			<div className="perfumes-hero__content">
				<RichText
					tagName="p"
					className="perfumes-eyebrow"
					value={ eyebrow }
					onChange={ ( value ) => setAttributes( { eyebrow: value } ) }
					placeholder={ __( 'Texto superior', 'perfumes' ) }
				/>
				<RichText
					tagName="h1"
					value={ title }
					onChange={ ( value ) => setAttributes( { title: value } ) }
					placeholder={ __( 'Titulo principal', 'perfumes' ) }
				/>
				<RichText
					tagName="p"
					className="perfumes-hero__description"
					value={ description }
					onChange={ ( value ) => setAttributes( { description: value } ) }
					placeholder={ __( 'Descripcion principal', 'perfumes' ) }
				/>
				<div className="perfumes-hero__actions">
					<RichText
						tagName="span"
						className="perfumes-button perfumes-button--primary"
						value={ primaryCta }
						onChange={ ( value ) => setAttributes( { primaryCta: value } ) }
						placeholder={ __( 'CTA principal', 'perfumes' ) }
					/>
					<RichText
						tagName="span"
						className="perfumes-button perfumes-button--ghost"
						value={ secondaryCta }
						onChange={ ( value ) => setAttributes( { secondaryCta: value } ) }
						placeholder={ __( 'CTA secundario', 'perfumes' ) }
					/>
				</div>
			</div>
			<EditableImage
				className="perfumes-hero__media"
				imageUrl={ heroImageUrl }
				label={ __( 'Imagen hero', 'perfumes' ) }
				onChange={ ( value ) => setAttributes( { heroImageUrl: value } ) }
			/>
		</section>
	);
}
