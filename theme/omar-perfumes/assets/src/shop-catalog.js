import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin( ScrollTrigger );

let catalogPinContext = null;
let catalogPinMediaQuery = null;

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

function initCatalogUi() {
	document
		.querySelectorAll( '.perfumes-shop-store' )
		.forEach( bindCatalogUi );
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
		destroyCatalogPin();
		initCatalogPin();
		ScrollTrigger.refresh();
	} );
}

initCatalogUi();
bindCatalogPinMediaQuery();
document.addEventListener( 'omar:routechange', handleCatalogRouteChange );
window.addEventListener( 'load', () => ScrollTrigger.refresh() );
