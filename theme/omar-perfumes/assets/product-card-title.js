( function () {
	const MIN_RATIO = 0.68;
	const STEP_PX = 0.4;

	function fitCardTitle( title ) {
		const link = title.querySelector( 'a' ) || title;
		title.style.fontSize = '';
		link.style.fontSize = '';

		const computed = window.getComputedStyle( title );
		const baseSize = parseFloat( computed.fontSize );
		if ( ! baseSize || Number.isNaN( baseSize ) ) {
			return;
		}

		const minSize = Math.max( 10, baseSize * MIN_RATIO );
		let size = baseSize;
		title.style.fontSize = `${ size }px`;

		// Shrink until the full title fits the fixed 2-line slot.
		while (
			title.scrollHeight > title.clientHeight + 1 &&
			size > minSize
		) {
			size = Math.max( minSize, size - STEP_PX );
			title.style.fontSize = `${ size }px`;
		}
	}

	function fitAllProductCardTitles( root ) {
		const scope = root && root.querySelectorAll ? root : document;
		scope
			.querySelectorAll(
				'[data-product-card] .perfumes-product-card__title'
			)
			.forEach( fitCardTitle );
	}

	let resizeTimer = 0;
	const scheduleFit = () => {
		window.clearTimeout( resizeTimer );
		resizeTimer = window.setTimeout( () => fitAllProductCardTitles(), 80 );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', () =>
			fitAllProductCardTitles()
		);
	} else {
		fitAllProductCardTitles();
	}

	window.addEventListener( 'resize', scheduleFit );
	window.addEventListener( 'load', () => fitAllProductCardTitles() );
	document.addEventListener( 'omar:routechange', () => {
		window.requestAnimationFrame( () => fitAllProductCardTitles() );
	} );

	if ( 'fonts' in document ) {
		document.fonts.ready.then( () => fitAllProductCardTitles() );
	}
} )();
