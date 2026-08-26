( function () {
	const markAdded = ( button ) => {
		if ( ! button || ! button.classList.contains( 'add-to-cart-button' ) ) {
			return;
		}
		button.classList.add( 'is-added' );
		window.setTimeout( () => {
			button.classList.remove( 'is-added' );
		}, 2000 );
	};

	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'added_to_cart', ( ...args ) => {
			const $button = args[ 3 ];
			const button = $button && $button.get ? $button.get( 0 ) : $button;
			markAdded( button );
		} );
	}
} )();
