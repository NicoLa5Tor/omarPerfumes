( function () {
	const root = document.querySelector( '.perfumes-pdp' );
	if ( ! root ) {
		return;
	}

	const image = root.querySelector( '.perfumes-pdp__image' );
	const thumbs = root.querySelectorAll( '.perfumes-pdp__thumb' );
	if ( ! image || ! thumbs.length ) {
		return;
	}

	thumbs.forEach( ( thumb ) => {
		thumb.addEventListener( 'click', () => {
			const src = thumb.getAttribute( 'data-src' );
			if ( ! src ) {
				return;
			}
			image.setAttribute( 'src', src );
			thumbs.forEach( ( item ) => item.classList.remove( 'is-active' ) );
			thumb.classList.add( 'is-active' );
		} );
	} );
} )();
