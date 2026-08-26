import { RichText } from '@wordpress/block-editor';

export default function PaymentMethodsSection( { methods, onChange } ) {
	return (
		<section className="perfumes-payments">
			<p>Medios de pago</p>
			<div>
				{ methods.map( ( method, index ) => (
					<RichText
						key={ index }
						tagName="span"
						className={
							'perfumes-payments__chip' +
							( /addi/i.test( method || '' ) ? ' is-addi' : '' )
						}
						value={ method }
						onChange={ ( value ) => {
							const next = [ ...methods ];
							next[ index ] = value;
							onChange( next );
						} }
					/>
				) ) }
			</div>
		</section>
	);
}
