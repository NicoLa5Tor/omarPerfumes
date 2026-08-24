const storeApi = `${ window.location.origin }/wp-json/wc/store/v1`;

function syncCart( root, count ) {
	root.querySelectorAll( '[data-perfumes-cart-count]' ).forEach( ( element ) => {
		element.textContent = count;
	} );
}

async function addToCart( root, button ) {
	const productId = Number( button.dataset.perfumesAdd );
	if ( ! productId ) return;
	button.setAttribute( 'aria-busy', 'true' );
	button.textContent = 'Agregando...';
	try {
		const cartResponse = await fetch( `${ storeApi }/cart`, { credentials: 'same-origin' } );
		const nonce = cartResponse.headers.get( 'Nonce' );
		const response = await fetch( `${ storeApi }/cart/add-item`, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', Nonce: nonce }, body: JSON.stringify( { id: productId, quantity: 1 } ) } );
		if ( ! response.ok ) throw new Error( 'WooCommerce rejected the cart request.' );
		const cart = await response.json();
		syncCart( root, cart.items_count );
		button.textContent = 'Agregado al carrito';
		window.setTimeout( () => { button.textContent = 'Agregar al carrito'; }, 1400 );
	} catch ( error ) {
		button.textContent = 'No se pudo agregar';
		window.setTimeout( () => { button.textContent = 'Agregar al carrito'; }, 1800 );
	} finally {
		button.removeAttribute( 'aria-busy' );
	}
}

function mountStorefront( root ) {
	root.querySelectorAll( '[data-perfumes-add]' ).forEach( ( button ) => {
		button.addEventListener( 'click', ( event ) => { event.preventDefault(); addToCart( root, button ); } );
	} );
}

document.addEventListener( 'DOMContentLoaded', () => document.querySelectorAll( '.perfumes-landing' ).forEach( mountStorefront ) );
