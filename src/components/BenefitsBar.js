import { RichText } from '@wordpress/block-editor';

export default function BenefitsBar( { benefits, onChange } ) {
	const updateBenefit = ( index, patch ) => {
		const next = [ ...benefits ];
		next[ index ] = { ...next[ index ], ...patch };
		onChange( next );
	};

	return (
		<section className="perfumes-benefits">
			{ benefits.map( ( benefit, index ) => (
				<div className="perfumes-benefit" key={ index }>
					<span aria-hidden="true">+</span>
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
								updateBenefit( index, { description: value } )
							}
						/>
					</div>
				</div>
			) ) }
		</section>
	);
}
