import { RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import EditableImage from '../../components/EditableImage';

export default function PromoSection( { attributes, setAttributes } ) {
	const { promoTitle, promoDescription, promoCta, promoImageUrl } =
		attributes;

	return (
		<section className="perfumes-promo">
			<div>
				<p className="perfumes-eyebrow">Addi</p>
				<RichText
					tagName="h2"
					value={ promoTitle }
					onChange={ ( value ) =>
						setAttributes( { promoTitle: value } )
					}
					placeholder={ __( 'Titulo promocional', 'perfumes' ) }
				/>
				<RichText
					tagName="p"
					value={ promoDescription }
					onChange={ ( value ) =>
						setAttributes( { promoDescription: value } )
					}
					placeholder={ __( 'Descripcion promocional', 'perfumes' ) }
				/>
				<RichText
					tagName="span"
					className="perfumes-button perfumes-button--primary"
					value={ promoCta }
					onChange={ ( value ) =>
						setAttributes( { promoCta: value } )
					}
					placeholder={ __( 'CTA promocional', 'perfumes' ) }
				/>
			</div>
			<EditableImage
				className="perfumes-promo__media"
				imageUrl={ promoImageUrl }
				label={ __( 'Imagen promocional', 'perfumes' ) }
				onChange={ ( value ) =>
					setAttributes( { promoImageUrl: value } )
				}
			/>
		</section>
	);
}
