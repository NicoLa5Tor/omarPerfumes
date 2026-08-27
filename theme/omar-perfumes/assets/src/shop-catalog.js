function getStickyOffset() {
	const header = document.querySelector(
		'.perfumes-global-header:not(.is-home-route)'
	);
	const adminBar = document.getElementById( 'wpadminbar' );
	let offset = header ? header.offsetHeight : 92;

	if ( adminBar ) {
		offset += adminBar.offsetHeight;
	}

	return offset;
}

function syncCatalogStickyOffset() {
	const offset = `${ getStickyOffset() }px`;
	document.documentElement.style.setProperty(
		'--perfumes-catalog-sticky-top',
		offset
	);
}

function isMobileFiltersViewport() {
	return window.matchMedia( '(max-width: 900px)' ).matches;
}

function setFiltersOpen( root, isOpen ) {
	const toggle = root.querySelector( '[data-shop-filters-toggle]' );
	const sidebar = root.querySelector( '#perfumes-shop-sidebar' );

	if ( ! toggle || ! sidebar ) {
		return;
	}

	const backdrop = root.querySelector( '[data-shop-filters-backdrop]' );

	sidebar.classList.toggle( 'is-open', isOpen );
	toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
	document.body.classList.toggle( 'is-shop-filters-open', isOpen );

	if ( backdrop ) {
		backdrop.hidden = ! isOpen;
		backdrop.classList.toggle( 'is-visible', isOpen );
	}

	if ( isOpen && isMobileFiltersViewport() ) {
		sidebar.setAttribute( 'role', 'dialog' );
		sidebar.setAttribute( 'aria-modal', 'true' );
		sidebar.setAttribute(
			'aria-labelledby',
			'perfumes-shop-filters-title'
		);
		sidebar.setAttribute( 'tabindex', '-1' );
		const close = sidebar.querySelector( '[data-shop-filters-close]' );
		window.requestAnimationFrame( () => {
			( close || sidebar ).focus?.();
		} );
	} else {
		sidebar.removeAttribute( 'role' );
		sidebar.removeAttribute( 'aria-modal' );
		sidebar.removeAttribute( 'tabindex' );
		sidebar.setAttribute(
			'aria-labelledby',
			'perfumes-shop-sidebar-title'
		);
	}
}

function bindCatalogUi( root ) {
	const toggle = root.querySelector( '[data-shop-filters-toggle]' );
	const sidebar = root.querySelector( '#perfumes-shop-sidebar' );

	if ( ! toggle || ! sidebar ) {
		return;
	}

	if ( toggle.dataset.shopFiltersBound === 'true' ) {
		return;
	}

	const backdrop = root.querySelector( '[data-shop-filters-backdrop]' );
	const closers = root.querySelectorAll( '[data-shop-filters-close]' );

	toggle.dataset.shopFiltersBound = 'true';

	toggle.addEventListener( 'click', function () {
		const willOpen = ! sidebar.classList.contains( 'is-open' );
		setFiltersOpen( root, willOpen );
	} );

	closers.forEach( ( closer ) => {
		closer.addEventListener( 'click', function () {
			setFiltersOpen( root, false );
		} );
	} );

	if ( backdrop ) {
		backdrop.addEventListener( 'click', function () {
			setFiltersOpen( root, false );
		} );
	}

	sidebar.addEventListener( 'click', function ( event ) {
		const link = event.target?.closest?.( 'a[href]' );
		if ( ! link || ! isMobileFiltersViewport() ) {
			return;
		}
		// Closing immediately feels snappier on tap; SPA routechange also resets.
		window.setTimeout( () => setFiltersOpen( root, false ), 120 );
	} );
}

function resetCatalogUi( root ) {
	setFiltersOpen( root, false );
}

let filtersEscapeBound = false;
let stickyOffsetBound = false;

function bindFiltersEscape() {
	if ( filtersEscapeBound ) {
		return;
	}

	filtersEscapeBound = true;

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key !== 'Escape' ) {
			return;
		}

		document
			.querySelectorAll( '.perfumes-shop-store' )
			.forEach( ( root ) => {
				if ( root.querySelector( '#perfumes-shop-sidebar.is-open' ) ) {
					setFiltersOpen( root, false );
				}
			} );
	} );
}

function bindStickyOffset() {
	if ( stickyOffsetBound ) {
		return;
	}

	stickyOffsetBound = true;
	window.addEventListener( 'resize', syncCatalogStickyOffset );
}

function initCatalogUi() {
	syncCatalogStickyOffset();
	bindStickyOffset();
	document
		.querySelectorAll( '.perfumes-shop-store' )
		.forEach( bindCatalogUi );
	bindFiltersEscape();
}

function handleCatalogRouteChange() {
	document
		.querySelectorAll( '.perfumes-shop-store' )
		.forEach( resetCatalogUi );
	initCatalogUi();
}

initCatalogUi();
document.addEventListener( 'omar:routechange', handleCatalogRouteChange );
window.addEventListener( 'load', syncCatalogStickyOffset );
