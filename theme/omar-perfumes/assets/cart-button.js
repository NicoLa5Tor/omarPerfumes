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

	const stripViewCartLinks = ( button ) => {
		const root = button?.parentElement || document;
		root.querySelectorAll( 'a.added_to_cart' ).forEach( ( node ) => {
			node.remove();
		} );
	};

	const toViewCart = ( button ) => {
		const cartUrl = window.omarPerfumesCart?.cartUrl || '/carrito/';
		const label = window.omarPerfumesCart?.viewCart || 'Ver carrito';
		button.classList.add( 'is-in-cart' );
		button.classList.remove( 'ajax_add_to_cart', 'add_to_cart_button' );
		button.setAttribute( 'aria-label', label );
		if ( button.tagName === 'A' ) {
			button.setAttribute( 'href', cartUrl );
			return;
		}
		button.type = 'button';
		button.removeAttribute( 'name' );
		button.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			window.location.href = cartUrl;
		} );
	};

	const markAdded = ( button ) => {
		const reduceMotion = prefersReducedMotion();
		stripViewCartLinks( button );
		if ( button && button.classList.contains( 'add-to-cart-button' ) ) {
			if (
				! button.classList.contains( 'is-in-cart' ) &&
				! button.classList.contains( 'is-added' )
			) {
				button.classList.remove( 'is-added' );
				void button.offsetWidth;
				button.classList.add( 'is-added' );
				window.setTimeout(
					() => {
						toViewCart( button );
						stripViewCartLinks( button );
					},
					reduceMotion ? 0 : ANIMATION_MS
				);
			}
		}
		window.setTimeout( () => stripViewCartLinks( button ), 0 );
		window.setTimeout( pulseCart, reduceMotion ? 0 : CART_PULSE_MS );
	};

	const bindPdpForm = () => {
		const form = document.querySelector( '.perfumes-pdp form.cart' );
		if ( ! form || form.dataset.omarCartBound === '1' ) {
			return;
		}
		form.dataset.omarCartBound = '1';
		form.addEventListener( 'submit', ( event ) => {
			const button = form.querySelector( '.add-to-cart-button' );
			if ( button?.classList.contains( 'is-in-cart' ) ) {
				event.preventDefault();
				window.location.href =
					window.omarPerfumesCart?.cartUrl || '/carrito/';
				return;
			}
			if (
				! button ||
				button.classList.contains( 'is-added' ) ||
				! window.jQuery
			) {
				event.preventDefault();
				return;
			}
			const ajaxTemplate =
				window.wc_add_to_cart_params?.wc_ajax_url || '';
			const ajaxUrl = ajaxTemplate.replace(
				'%%endpoint%%',
				'add_to_cart'
			);
			const productId =
				button.getAttribute( 'value' ) ||
				button.getAttribute( 'data-product_id' );
			const quantity =
				form.querySelector( 'input.qty' )?.value ||
				button.getAttribute( 'data-quantity' ) ||
				'1';
			if ( ! ajaxUrl || ! productId ) {
				return;
			}
			event.preventDefault();
			window.jQuery.post(
				ajaxUrl,
				{
					product_id: productId,
					quantity,
				},
				( response ) => {
					if ( ! response || response.error ) {
						form.submit();
						return;
					}
					window
						.jQuery( document.body )
						.trigger( 'added_to_cart', [
							response.fragments,
							response.cart_hash,
							window.jQuery( button ),
						] );
				}
			);
		} );
	};

	if ( ! window.jQuery ) {
		return;
	}

	window.jQuery( document.body ).on( 'added_to_cart', ( ...args ) => {
		const $button = args[ 3 ];
		const button = $button && $button.get ? $button.get( 0 ) : $button;
		markAdded( button );
	} );

	bindPdpForm();
} )();
