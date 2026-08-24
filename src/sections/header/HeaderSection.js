import { RichText } from '@wordpress/block-editor';

export default function HeaderSection( { attributes, setAttributes } ) {
	const { siteTitle, tagline, searchPlaceholder, cartCount } = attributes;

	return (
		<header className="perfumes-header">
			<div className="perfumes-header__social" aria-hidden="true">
				<span>Ig</span>
				<span>Fb</span>
				<span>Tk</span>
			</div>
			<div className="perfumes-logo">
				<RichText
					tagName="div"
					className="perfumes-logo__title"
					value={ siteTitle }
					onChange={ ( value ) => setAttributes( { siteTitle: value } ) }
				/>
				<RichText
					tagName="div"
					className="perfumes-logo__tagline"
					value={ tagline }
					onChange={ ( value ) => setAttributes( { tagline: value } ) }
				/>
			</div>
			<div className="perfumes-header__right">
				<div className="perfumes-search">
					<span aria-hidden="true">Search</span>
					<RichText
						tagName="span"
						value={ searchPlaceholder }
						onChange={ ( value ) =>
							setAttributes( { searchPlaceholder: value } )
						}
					/>
				</div>
				<div className="perfumes-cart" aria-label="Carrito">
					<span aria-hidden="true">Bag</span>
					<RichText
						tagName="strong"
						value={ String( cartCount ) }
						onChange={ ( value ) =>
							setAttributes( { cartCount: value } )
						}
					/>
				</div>
			</div>
		</header>
	);
}
