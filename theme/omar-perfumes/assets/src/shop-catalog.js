import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin( ScrollTrigger );

let catalogPinContext = null;
let catalogPinMediaQuery = null;
let filtersEscapeBound = false;

function getPinnedStartOffset() {
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

function getCatalogPinEnd( store ) {
	const productsBody = store.querySelector( '.perfumes-shop-products-body' );
	const toolbar = store.querySelector( '.perfumes-shop-toolbar' );

	if ( ! productsBody || ! toolbar ) {
		return 'bottom bottom';
	}

	const offset = getPinnedStartOffset();
	const viewportRoom =
		window.innerHeight - offset - toolbar.offsetHeight - 20;
	const scrollDistance = Math.max(
		0,
		productsBody.scrollHeight - viewportRoom
	);

	return `+=${ scrollDistance }`;
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

function setCatalogPinState( store, sidebar, toolbar, isActive ) {
	store.classList.toggle( 'is-catalog-pin-active', isActive );
	sidebar.classList.toggle( 'is-pin-active', isActive );
	toolbar.classList.toggle( 'is-pin-active', isActive );
}

function destroyCatalogPin() {
	catalogPinContext?.revert();
	catalogPinContext = null;

	document.querySelectorAll( '.perfumes-shop-store' ).forEach( ( store ) => {
		delete store.dataset.catalogPinReady;
		store.classList.remove( 'is-catalog-pin-active' );
	} );

	document
		.querySelectorAll( '.perfumes-shop-sidebar, .perfumes-shop-toolbar' )
		.forEach( ( node ) => {
			node.classList.remove( 'is-pin-active' );
		} );
}

function initCatalogPin() {
	if (
		window.matchMedia( '(max-width: 900px)' ).matches ||
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches
	) {
		return;
	}

	const store = document.querySelector( '.perfumes-shop-store' );
	if ( ! store || store.dataset.catalogPinReady === 'true' ) {
		return;
	}

	const sidebar = store.querySelector( '.perfumes-shop-sidebar' );
	const toolbar = store.querySelector( '.perfumes-shop-toolbar' );
	const productsBody = store.querySelector( '.perfumes-shop-products-body' );

	if ( ! sidebar || ! toolbar || ! productsBody ) {
		return;
	}

	store.dataset.catalogPinReady = 'true';

	catalogPinContext = gsap.context( () => {
		const pinConfig = {
			trigger: store,
			start: () => `top top+=${ getPinnedStartOffset() }`,
			end: () => getCatalogPinEnd( store ),
			invalidateOnRefresh: true,
			anticipatePin: 1,
			onToggle: ( self ) => {
				setCatalogPinState( store, sidebar, toolbar, self.isActive );
			},
		};

		ScrollTrigger.create( {
			...pinConfig,
			id: 'omar-catalog-sidebar',
			pin: sidebar,
			pinSpacing: false,
		} );

		ScrollTrigger.create( {
			...pinConfig,
			id: 'omar-catalog-toolbar',
			pin: toolbar,
			pinSpacing: false,
		} );
	}, store );
}

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

function initCatalogUi() {
	document
		.querySelectorAll( '.perfumes-shop-store' )
		.forEach( bindCatalogUi );
	bindFiltersEscape();
	initCatalogPin();
	window.requestAnimationFrame( () => ScrollTrigger.refresh() );
}

function handleCatalogRouteChange() {
	document
		.querySelectorAll( '.perfumes-shop-store' )
		.forEach( resetCatalogUi );
	destroyCatalogPin();
	initCatalogUi();
}

function bindCatalogPinMediaQuery() {
	if ( catalogPinMediaQuery ) {
		return;
	}

	catalogPinMediaQuery = window.matchMedia( '(min-width: 901px)' );
	catalogPinMediaQuery.addEventListener( 'change', () => {
		document
			.querySelectorAll( '.perfumes-shop-store' )
			.forEach( ( root ) => setFiltersOpen( root, false ) );
		destroyCatalogPin();
		initCatalogPin();
		ScrollTrigger.refresh();
	} );
}

initCatalogUi();
bindCatalogPinMediaQuery();
document.addEventListener( 'omar:routechange', handleCatalogRouteChange );
window.addEventListener( 'load', () => ScrollTrigger.refresh() );
