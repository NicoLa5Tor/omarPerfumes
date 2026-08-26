( function () {
	const root = document.querySelector( '.perfumes-pdp' );
	if ( ! root ) {
		return;
	}

	const image = root.querySelector( '.perfumes-pdp__image' );
	const thumbs = root.querySelectorAll( '.perfumes-pdp__thumb' );
	if ( image && thumbs.length ) {
		thumbs.forEach( ( thumb ) => {
			thumb.addEventListener( 'click', () => {
				const src = thumb.getAttribute( 'data-src' );
				if ( ! src ) {
					return;
				}
				image.setAttribute( 'src', src );
				thumbs.forEach( ( item ) =>
					item.classList.remove( 'is-active' )
				);
				thumb.classList.add( 'is-active' );
			} );
		} );
	}

	const viewport = root.querySelector(
		'.perfumes-related__viewport .products'
	);
	const prev = root.querySelector( '[data-related-prev]' );
	const next = root.querySelector( '[data-related-next]' );
	if ( ! viewport || ! prev || ! next ) {
		return;
	}

	const controls = root.querySelector( '.perfumes-related__controls' );

	const cardStep = () => {
		const card = viewport.querySelector( 'li.product' );
		if ( ! card ) {
			return viewport.clientWidth;
		}
		const styles = window.getComputedStyle( viewport );
		const gap = parseFloat( styles.columnGap || styles.gap ) || 24;
		return card.getBoundingClientRect().width + gap;
	};

	const updateControls = () => {
		const maxScroll = Math.max(
			0,
			viewport.scrollWidth - viewport.clientWidth - 2
		);
		const canScroll = maxScroll > 0;
		if ( controls ) {
			controls.hidden = ! canScroll;
		}
		prev.disabled = ! canScroll || viewport.scrollLeft <= 2;
		next.disabled = ! canScroll || viewport.scrollLeft >= maxScroll;
	};

	const reduceMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	).matches;
	const scrollBehavior = reduceMotion ? 'auto' : 'smooth';

	prev.addEventListener( 'click', () => {
		viewport.scrollBy( { left: -cardStep(), behavior: scrollBehavior } );
	} );
	next.addEventListener( 'click', () => {
		viewport.scrollBy( { left: cardStep(), behavior: scrollBehavior } );
	} );
	viewport.addEventListener( 'scroll', updateControls, { passive: true } );
	window.addEventListener( 'resize', updateControls );
	updateControls();
} )();
