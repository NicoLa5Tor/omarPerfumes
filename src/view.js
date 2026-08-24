const storageKey = 'perfumes-showcase-cart';
const getCart = () => JSON.parse( window.localStorage.getItem( storageKey ) || '{}' );
const setCart = ( cart ) => window.localStorage.setItem( storageKey, JSON.stringify( cart ) );

function syncCart( root ) {
	const cart = getCart();
	const count = Object.values( cart ).reduce( ( total, quantity ) => total + quantity, 0 );
	root.querySelectorAll( '[data-perfumes-cart-count]' ).forEach( ( element ) => { element.textContent = count; } );
}

function mountStorefront( root ) {
	root.querySelectorAll( '[data-perfumes-add]' ).forEach( ( button ) => {
		button.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			const id = button.dataset.perfumesAdd;
			const cart = getCart();
			cart[ id ] = ( cart[ id ] || 0 ) + 1;
			setCart( cart );
			syncCart( root );
			button.textContent = 'Agregado al carrito';
			window.setTimeout( () => { button.textContent = 'Agregar al carrito'; }, 1200 );
		} );
	} );
	syncCart( root );
}

document.addEventListener( 'DOMContentLoaded', () => document.querySelectorAll( '.perfumes-landing' ).forEach( mountStorefront ) );
