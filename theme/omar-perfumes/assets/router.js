import { getServerState, store, withSyncEvent } from '@wordpress/interactivity';

const ROUTE_CLASS_NAMES = new Set( [
	'home',
	'archive',
	'search',
	'single-product',
	'tax-product_cat',
	'woocommerce',
	'woocommerce-page',
	'woocommerce-shop',
] );
const ROUTE_CLASS_PREFIXES = [ 'post-type-archive', 'term-' ];
const LOADING_MESSAGES = [
	'Estamos preparando tu experiencia\u2026',
	'Seleccionando las mejores fragancias\u2026',
	'Ya casi est\u00e1 listo\u2026',
];
const ROUTE_TRANSITION_CLASS = 'omar-route-transition';
const INITIAL_ENTRY_CLASS = 'omar-initial-entry';
let lastRouteUrl = '';
let routeEventFrame = 0;
let loadingMessageTimer = 0;
let loadingStartedAt = 0;
let navigationSequence = 0;

function startLoading() {
	navigationSequence += 1;
	const sequence = navigationSequence;
	let messageIndex = 0;
	loadingStartedAt = Date.now();
	document.body.classList.remove( INITIAL_ENTRY_CLASS );
	document.body.classList.add( ROUTE_TRANSITION_CLASS );
	routerState.loadingMessage = LOADING_MESSAGES[ messageIndex ];
	routerState.isNavigating = true;
	routerState.isIdle = false;
	window.clearInterval( loadingMessageTimer );
	loadingMessageTimer = window.setInterval( () => {
		messageIndex = ( messageIndex + 1 ) % LOADING_MESSAGES.length;
		routerState.loadingMessage = LOADING_MESSAGES[ messageIndex ];
	}, 1400 );
	return sequence;
}

function finishLoading( sequence ) {
	const minimumVisibleTime = Math.max(
		0,
		650 - ( Date.now() - loadingStartedAt )
	);
	return new Promise( ( resolve ) => {
		window.setTimeout( () => {
			if ( sequence === navigationSequence ) {
				window.clearInterval( loadingMessageTimer );
				routerState.isNavigating = false;
				routerState.isIdle = true;
			}
			resolve();
		}, minimumVisibleTime );
	} );
}

function normalizedPath( pathname ) {
	const path = pathname.replace( /\/+$/, '' );
	return path || '/';
}

function isClientRoute( url ) {
	if ( url.searchParams.has( 's' ) ) {
		return true;
	}

	const path = normalizedPath( url.pathname );
	return (
		path === '/' ||
		path === '/tienda' ||
		path.startsWith( '/categoria-producto/' )
	);
}

function isRouteBodyClass( className ) {
	return (
		ROUTE_CLASS_NAMES.has( className ) ||
		ROUTE_CLASS_PREFIXES.some( ( prefix ) =>
			className.startsWith( prefix )
		)
	);
}

function syncBodyClasses( nextClasses = [] ) {
	[ ...document.body.classList ]
		.filter( isRouteBodyClass )
		.forEach( ( className ) =>
			document.body.classList.remove( className )
		);
	nextClasses.forEach( ( className ) => {
		if ( isRouteBodyClass( className ) ) {
			document.body.classList.add( className );
		}
	} );
}

function prepareHomeForClientNavigation( serverState ) {
	if ( ! serverState.isHome ) {
		return;
	}

	const root = document.querySelector( '.perfumes-landing' );
	if ( ! root ) {
		return;
	}

	root.querySelectorAll( '.preloader-mask, .preloader-progress-bar' ).forEach(
		( element ) => {
			element.style.display = 'none';
			element.style.visibility = 'hidden';
		}
	);

	const heroImage = root.querySelector( '.hero-product-primary__image' );
	if ( heroImage ) {
		heroImage.style.transform = 'scale(1)';
	}
}

function emitRouteChange( serverState ) {
	const currentUrl = window.location.href;
	if ( currentUrl === lastRouteUrl ) {
		return;
	}

	const clientNavigation = lastRouteUrl !== '';
	if ( clientNavigation ) {
		prepareHomeForClientNavigation( serverState );
	}
	document.body.classList.remove( ROUTE_TRANSITION_CLASS );
	lastRouteUrl = currentUrl;
	window.cancelAnimationFrame( routeEventFrame );
	routeEventFrame = window.requestAnimationFrame( () => {
		document.dispatchEvent(
			new CustomEvent( 'omar:routechange', {
				detail: {
					clientNavigation,
					routeType: serverState.routeType || 'page',
					url: currentUrl,
				},
			} )
		);
	} );
}

function getLinkFromEvent( event ) {
	if ( ! ( event.target instanceof window.Element ) ) {
		return null;
	}

	return event.target.closest( 'a[href]' );
}

function shouldUseClientNavigation( event, link, url ) {
	if (
		event.defaultPrevented ||
		event.button !== 0 ||
		event.metaKey ||
		event.ctrlKey ||
		event.shiftKey ||
		event.altKey ||
		link.hasAttribute( 'download' ) ||
		link.hasAttribute( 'data-no-client-navigation' ) ||
		link.matches( '.add_to_cart_button, .ajax_add_to_cart' ) ||
		link.target === '_blank' ||
		url.origin !== window.location.origin ||
		url.searchParams.has( 'add-to-cart' )
	) {
		return false;
	}

	if (
		url.pathname === window.location.pathname &&
		url.search === window.location.search
	) {
		return false;
	}

	return (
		isClientRoute( new URL( window.location.href ) ) && isClientRoute( url )
	);
}

const { state: routerState } = store( 'omar/router', {
	state: {
		isNavigating: false,
		isIdle: true,
		loadingMessage: LOADING_MESSAGES[ 0 ],
		get isHome() {
			return Boolean( getServerState().isHome );
		},
	},
	actions: {
		navigate: withSyncEvent( function* ( event ) {
			const link = getLinkFromEvent( event );
			if ( ! link ) {
				return;
			}

			const url = new URL( link.href, window.location.href );
			if ( ! shouldUseClientNavigation( event, link, url ) ) {
				return;
			}

			event.preventDefault();
			const sequence = startLoading();
			try {
				const { actions } = yield import(
					'@wordpress/interactivity-router'
				);
				yield actions.navigate( url.href );
				yield finishLoading( sequence );
			} catch ( error ) {
				window.clearInterval( loadingMessageTimer );
				document.body.classList.remove( ROUTE_TRANSITION_CLASS );
				routerState.isNavigating = false;
				routerState.isIdle = true;
				window.console.warn(
					'Omar client navigation failed; using a full page load.',
					error
				);
				window.location.assign( url.href );
			}
		} ),
		search: withSyncEvent( function* ( event ) {
			const form = event.target;
			if (
				! ( form instanceof window.HTMLFormElement ) ||
				! form.matches( '.wp-block-search' )
			) {
				return;
			}

			const url = new URL( form.action || '/', window.location.href );
			const formData = new window.FormData( form );
			formData.forEach( ( value, name ) => {
				if ( typeof value === 'string' ) {
					url.searchParams.set( name, value );
				}
			} );

			if (
				! isClientRoute( new URL( window.location.href ) ) ||
				! isClientRoute( url )
			) {
				return;
			}

			event.preventDefault();
			const sequence = startLoading();
			try {
				const { actions } = yield import(
					'@wordpress/interactivity-router'
				);
				yield actions.navigate( url.href );
				yield finishLoading( sequence );
			} catch ( error ) {
				window.clearInterval( loadingMessageTimer );
				document.body.classList.remove( ROUTE_TRANSITION_CLASS );
				routerState.isNavigating = false;
				routerState.isIdle = true;
				window.console.warn(
					'Omar search navigation failed; using a full page load.',
					error
				);
				window.location.assign( url.href );
			}
		} ),
	},
	callbacks: {
		syncRoute() {
			const serverState = getServerState();
			syncBodyClasses( serverState.routeBodyClasses );
			emitRouteChange( serverState );
		},
	},
} );
