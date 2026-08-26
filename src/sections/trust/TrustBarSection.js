import { RichText } from '@wordpress/block-editor';

const trustIcons = [
	( props ) => (
		<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" { ...props }>
			<path
				stroke="currentColor"
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="1.7"
				d="M12 3 4.8 6.2v5.4c0 4.6 3.1 8 7.2 9.4 4.1-1.4 7.2-4.8 7.2-9.4V6.2L12 3Z"
			/>
			<path
				stroke="currentColor"
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="1.7"
				d="m8.6 12.1 2.2 2.3 4.6-5"
			/>
		</svg>
	),
	( props ) => (
		<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" { ...props }>
			<path
				stroke="currentColor"
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="1.7"
				d="M8 4h8a2 2 0 0 1 2 2v14l-6-2.4L6 20V6a2 2 0 0 1 2-2Z"
			/>
			<path
				stroke="currentColor"
				strokeLinecap="round"
				strokeWidth="1.7"
				d="M9 9h6M9 13h4"
			/>
		</svg>
	),
	( props ) => (
		<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" { ...props }>
			<path
				stroke="currentColor"
				strokeLinecap="round"
				strokeLinejoin="round"
				strokeWidth="1.7"
				d="M3 7h11v10H3zM14 11h4.2L21 14.2V17h-7"
			/>
			<circle
				cx="7"
				cy="17"
				r="1.8"
				stroke="currentColor"
				strokeWidth="1.7"
			/>
			<circle
				cx="17.5"
				cy="17"
				r="1.8"
				stroke="currentColor"
				strokeWidth="1.7"
			/>
		</svg>
	),
	( props ) => (
		<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" { ...props }>
			<rect
				x="3"
				y="6"
				width="18"
				height="12"
				rx="2"
				stroke="currentColor"
				strokeWidth="1.7"
			/>
			<path stroke="currentColor" strokeWidth="1.7" d="M3 10h18M7 14h4" />
		</svg>
	),
];

export default function TrustBarSection( { benefits, onChange } ) {
	const updateBenefit = ( index, patch ) => {
		const next = [ ...benefits ];
		next[ index ] = { ...next[ index ], ...patch };
		onChange( next );
	};

	return (
		<section className="perfumes-benefits">
			{ benefits.map( ( benefit, index ) => {
				const Icon = trustIcons[ index % trustIcons.length ];
				return (
					<div className="perfumes-benefit" key={ index }>
						<span
							className="perfumes-benefit__mark"
							aria-hidden="true"
						>
							<Icon />
						</span>
						<div>
							<RichText
								tagName="h3"
								value={ benefit.title }
								onChange={ ( value ) =>
									updateBenefit( index, { title: value } )
								}
							/>
							<RichText
								tagName="p"
								value={ benefit.description }
								onChange={ ( value ) =>
									updateBenefit( index, {
										description: value,
									} )
								}
							/>
						</div>
					</div>
				);
			} ) }
		</section>
	);
}
