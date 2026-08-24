import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, MediaUpload } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { title, description, imageUrl } = attributes;

	return (
		<div { ...useBlockProps() }>
			<div className="perfume-card">
				{ imageUrl ? (
					<img
						src={ imageUrl }
						alt={ title }
						onClick={ () => setAttributes( { imageUrl: '' } ) }
					/>
				) : (
					<MediaUpload
						onSelect={ ( media ) =>
							setAttributes( { imageUrl: media.url } )
						}
						allowedTypes={ [ 'image' ] }
						render={ ( { open } ) => (
							<Button onClick={ open } variant="secondary">
								{ __( 'Subir imagen', 'perfumes' ) }
							</Button>
						) }
					/>
				) }
				<RichText
					tagName="h3"
					value={ title }
					onChange={ ( title ) => setAttributes( { title } ) }
					placeholder={ __( 'Nombre del perfume…', 'perfumes' ) }
				/>
				<RichText
					tagName="p"
					value={ description }
					onChange={ ( description ) =>
						setAttributes( { description } )
					}
					placeholder={ __(
						'Descripción del perfume…',
						'perfumes'
					) }
				/>
			</div>
		</div>
	);
}
