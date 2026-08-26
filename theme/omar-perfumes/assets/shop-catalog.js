( function () {
	'use strict';

	const toggle = document.querySelector( '[data-shop-filters-toggle]' );
	const sidebar = document.getElementById( 'perfumes-shop-sidebar' );

	if ( ! toggle || ! sidebar ) {
		return;
	}

	toggle.addEventListener( 'click', function () {
		const isOpen = sidebar.classList.toggle( 'is-open' );
		toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
	} );

	document.addEventListener( 'click', function ( event ) {
		if ( ! sidebar.classList.contains( 'is-open' ) ) {
			return;
		}

		if (
			sidebar.contains( event.target ) ||
			toggle.contains( event.target )
		) {
			return;
		}

		sidebar.classList.remove( 'is-open' );
		toggle.setAttribute( 'aria-expanded', 'false' );
	} );
} )();
