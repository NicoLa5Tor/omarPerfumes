import fs from 'node:fs';

function readLocalEnv() {
	const file = 'base/woocomerse/.env';
	if ( ! fs.existsSync( file ) ) return {};
	return Object.fromEntries(
		fs.readFileSync( file, 'utf8' )
			.split( /\r?\n/ )
			.filter( ( line ) => /^\s*[^#=]+=/.test( line ) )
			.map( ( line ) => {
				const separator = line.indexOf( '=' );
				return [ line.slice( 0, separator ).trim(), line.slice( separator + 1 ).trim().replace( /^['"]|['"]$/g, '' ) ];
			} )
	);
}

const config = readLocalEnv();
const baseUrl = ( process.env.STORE_URL || config.STORE_URL || '' ).replace( /\/$/, '' );
if ( ! baseUrl ) throw new Error( 'STORE_URL is required.' );

const checks = [];
const expect = ( condition, message ) => {
	checks.push( { condition, message } );
	if ( ! condition ) throw new Error( message );
};

const productsResponse = await fetch( `${ baseUrl }/wp-json/wc/store/v1/products?per_page=100` );
expect( productsResponse.ok, 'Woo Store API must respond.' );
const products = await productsResponse.json();
expect( products.length >= 10, 'At least 10 products must be published.' );

for ( const path of [ '/', '/tienda/', '/carrito/', '/mi-cuenta/' ] ) {
	const response = await fetch( `${ baseUrl }${ path }`, { redirect: 'follow' } );
	const html = await response.text();
	expect( response.ok, `${ path } must respond with HTTP 200.` );
	expect( ( html.match( /perfumes-global-header/g ) || [] ).length === 1, `${ path } must render one global header.` );
	expect( ( html.match( /perfumes-footer-preview/g ) || [] ).length === 1, `${ path } must render one global footer.` );
}

const home = await ( await fetch( `${ baseUrl }/` ) ).text();
expect( ! /perfumes-whatsapp[^>]+href=["']#/.test( home ), 'WhatsApp must never point to #.' );
expect( ( home.match( /data-product_id=/g ) || [] ).length >= 10, 'Landing must expose WooCommerce add-to-cart buttons.' );

console.log( `Storefront smoke checks passed: ${ checks.length }` );
