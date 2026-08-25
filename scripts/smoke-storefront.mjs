import fs from 'node:fs';

function readLocalEnv() {
	const file = 'base/woocomerse/.env';
	if ( ! fs.existsSync( file ) ) return {};
	return Object.fromEntries(
		fs
			.readFileSync( file, 'utf8' )
			.split( /\r?\n/ )
			.filter( ( line ) => /^\s*[^#=]+=/.test( line ) )
			.map( ( line ) => {
				const separator = line.indexOf( '=' );
				return [
					line.slice( 0, separator ).trim(),
					line
						.slice( separator + 1 )
						.trim()
						.replace( /^['"]|['"]$/g, '' ),
				];
			} )
	);
}

const config = readLocalEnv();
const baseUrl = ( process.env.STORE_URL || config.STORE_URL || '' ).replace(
	/\/$/,
	''
);
if ( ! baseUrl ) throw new Error( 'STORE_URL is required.' );

const checks = [];
const expect = ( condition, message ) => {
	checks.push( { condition, message } );
	if ( ! condition ) throw new Error( message );
};

const fetchStorefrontPage = ( path ) => {
	const url = new URL( path, `${ baseUrl }/` );
	url.searchParams.set( 'codex_smoke', `${ Date.now() }-${ Math.random() }` );
	return fetch( url, {
		cache: 'no-store',
		headers: {
			'cache-control': 'no-cache',
			'user-agent': 'Mozilla/5.0 CodexStorefrontSmoke/1.0',
		},
		redirect: 'follow',
	} );
};

const productsResponse = await fetch(
	`${ baseUrl }/wp-json/wc/store/v1/products?per_page=100`
);
expect( productsResponse.ok, 'Woo Store API must respond.' );
const products = await productsResponse.json();
expect( products.length >= 10, 'At least 10 products must be published.' );

for ( const path of [ '/', '/tienda/', '/carrito/', '/mi-cuenta/' ] ) {
	const response = await fetchStorefrontPage( path );
	const html = await response.text();
	expect( response.ok, `${ path } must respond with HTTP 200.` );
	expect(
		( html.match( /<header[^>]*perfumes-global-header/gi ) || [] )
			.length === 1,
		`${ path } must render one global header.`
	);
	expect(
		( html.match( /<footer[^>]*perfumes-footer-preview/gi ) || [] )
			.length === 1,
		`${ path } must render one global footer.`
	);
}

const home = await ( await fetchStorefrontPage( '/' ) ).text();
expect(
	/class="content-cta"/.test( home ),
	'Landing must render the Omar CTA card.'
);
expect(
	/class="preloader-mask"/.test( home ),
	'Landing must render the intro mask.'
);
expect(
	/class="logo-image"[^>]+omar-logo-light-v1\.png/.test( home ),
	'Preloader must render the official optimized Omar logo.'
);
expect(
	/class="hero-product-primary__image"/.test( home ) &&
		/class="secondary-product-copy"/.test( home ),
	'Hero must render two WooCommerce product presentations.'
);
expect(
	! /OMAR®|IronStride/.test( home ),
	'Landing must not render reconstructed or reference-brand copy.'
);
expect(
	! /perfumes-whatsapp[^>]+href=["']#/.test( home ),
	'WhatsApp must never point to #.'
);
expect(
	( home.match( /data-product-card/g ) || [] ).length === 8,
	'Landing must render exactly 8 best-selling product cards.'
);
expect(
	( home.match( /data-product_id=/g ) || [] ).length === 8,
	'Landing must expose one WooCommerce add-to-cart button per product card.'
);

console.log( `Storefront smoke checks passed: ${ checks.length }` );
