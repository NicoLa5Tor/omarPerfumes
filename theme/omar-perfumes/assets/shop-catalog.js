( function () {
	'use strict';

	function bindCatalogUi( root ) {
		const toggle = root.querySelector( '[data-shop-filters-toggle]' );
		const sidebar = root.querySelector( '#perfumes-shop-sidebar' );

		if ( ! toggle || ! sidebar ) {
			return;
		}

		if ( toggle.dataset.shopFiltersBound === 'true' ) {
			return;
		}

		toggle.dataset.shopFiltersBound = 'true';

		toggle.addEventListener( 'click', function () {
			const isOpen = sidebar.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );
	}

	function resetCatalogUi( root ) {
		const toggle = root.querySelector( '[data-shop-filters-toggle]' );
		const sidebar = root.querySelector( '#perfumes-shop-sidebar' );

		if ( sidebar ) {
			sidebar.classList.remove( 'is-open' );
		}

		if ( toggle ) {
			toggle.setAttribute( 'aria-expanded', 'false' );
		}
	}

	function initCatalogUi() {
		document
			.querySelectorAll( '.perfumes-shop-store' )
			.forEach( bindCatalogUi );
	}

	initCatalogUi();

	document.addEventListener( 'omar:routechange', function () {
		document
			.querySelectorAll( '.perfumes-shop-store' )
			.forEach( resetCatalogUi );
		initCatalogUi();
	} );
} )();
