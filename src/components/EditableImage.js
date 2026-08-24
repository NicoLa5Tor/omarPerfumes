import { Button } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function EditableImage( {
	className = '',
	imageUrl,
	label,
	onChange,
} ) {
	if ( imageUrl ) {
		return (
			<div className={ className }>
				<img src={ imageUrl } alt="" />
				<Button
					variant="secondary"
					onClick={ () => onChange( '' ) }
					className="perfumes-image-action"
				>
					{ __( 'Quitar imagen', 'perfumes' ) }
				</Button>
			</div>
		);
	}

	return (
		<MediaUploadCheck>
			<MediaUpload
				onSelect={ ( media ) => onChange( media.url ) }
				allowedTypes={ [ 'image' ] }
				render={ ( { open } ) => (
					<Button
						onClick={ open }
						variant="secondary"
						className={ `${ className } perfumes-image-placeholder` }
					>
						{ label || __( 'Seleccionar imagen', 'perfumes' ) }
					</Button>
				) }
			/>
		</MediaUploadCheck>
	);
}
