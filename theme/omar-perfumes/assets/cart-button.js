( function () {
	const ANIMATION_MS = 1500;
	const CART_PULSE_MS = 900;

	const prefersReducedMotion = () =>
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	const pulseCart = () => {
		const cartButton = document.querySelector(
			'.wc-block-mini-cart__button'
		);
		const badge = document.querySelector( '.wc-block-mini-cart__badge' );
		[ cartButton, badge ].forEach( ( node ) => {
			if ( ! node ) {
				return;
			}
			node.classList.remove( 'is-cart-pulse' );
			void node.offsetWidth;
			node.classList.add( 'is-cart-pulse' );
		} );
		window.setTimeout( () => {
			cartButton?.classList.remove( 'is-cart-pulse' );
			badge?.classList.remove( 'is-cart-pulse' );
		}, 700 );
	};

	const markAdded = ( button ) => {
		const reduceMotion = prefersReducedMotion();
		if ( button && button.classList.contains( 'add-to-cart-button' ) ) {
			button.classList.remove( 'is-added' );
			void button.offsetWidth;
			button.classList.add( 'is-added' );
			window.setTimeout( () => {
				button.classList.remove( 'is-added' );
			}, ANIMATION_MS );
		}
		window.setTimeout( pulseCart, reduceMotion ? 0 : CART_PULSE_MS );
	};

	if ( ! window.jQuery ) {
		return;
	}

	window.jQuery( document.body ).on( 'added_to_cart', ( ...args ) => {
		const $button = args[ 3 ];
		const button = $button && $button.get ? $button.get( 0 ) : $button;
		markAdded( button );
	} );
} )();
