import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin( ScrollTrigger );

const RUNTIME_KEY = '__omarPerfumesShowcaseRuntime';
const previousRuntime = window[ RUNTIME_KEY ];
const isReplacementRuntime = Boolean( previousRuntime );
if ( previousRuntime ) {
	previousRuntime.destroy();
}

let activeRoot = null;
let activeHeader = null;
let activePageCleanup = () => {};
let mountFrame = 0;

function preloaderAnimation( root ) {
	const timeline = gsap.timeline();
	const progressBar = root.querySelector( '.preloader-progress-bar' );
	const preloaderLogo = root.querySelector( '.preloader-logo' );
	const logoImage = root.querySelector( '.logo-image' );
	const preloaderBackground = root.querySelector( '.preloader-bg' );
	const preloaderMask = root.querySelector( '.preloader-mask' );
	const panelTop = root.querySelector( '.preloader-panel--top' );
	const panelBottom = root.querySelector( '.preloader-panel--bottom' );
	const heroImage = root.querySelector( '.hero-product-primary__image' );
	const heroImageTargets = heroImage ? [ heroImage ] : [];

	timeline
		.set( heroImageTargets, { scale: 1.18 } )
		.fromTo(
			logoImage,
			{ autoAlpha: 0, y: 14, scale: 0.96 },
			{
				autoAlpha: 1,
				y: 0,
				scale: 1,
				duration: 0.45,
				ease: 'power2.out',
			}
		)
		.fromTo(
			preloaderBackground,
			{ scaleX: 0 },
			{ scaleX: 1, duration: 0.72, ease: 'power2.inOut' },
			'-=0.08'
		)
		.to(
			preloaderLogo,
			{ autoAlpha: 0, y: -12, duration: 0.2, ease: 'power2.in' },
			'+=0.08'
		)
		.to( progressBar, { autoAlpha: 0, duration: 0.12 }, '<' )
		.to(
			panelTop,
			{ yPercent: -101, duration: 0.78, ease: 'power4.inOut' },
			'<'
		)
		.to(
			panelBottom,
			{ yPercent: 101, duration: 0.78, ease: 'power4.inOut' },
			'<'
		)
		.to(
			heroImageTargets,
			{ scale: 1, duration: 1.05, ease: 'power3.out' },
			'<0.06'
		)
		.set( [ progressBar, preloaderMask ], { display: 'none' } );

	return timeline;
}

function heroAnimation( root ) {
	const timeline = gsap.timeline( {
		defaults: { duration: 0.72, ease: 'power3.out' },
	} );
	const fadeElements = [ ...root.querySelectorAll( '[data-fade-in]' ) ];
	const headline = root.querySelector( '[data-hero-reveal]' );

	if ( fadeElements.length ) {
		timeline.fromTo(
			fadeElements,
			{
				autoAlpha: 0,
				y: ( index, element ) =>
					element.dataset.fadeIn === 'down' ? -18 : 22,
			},
			{
				autoAlpha: 1,
				y: 0,
				stagger: 0.06,
			},
			0
		);
	}

	if ( headline ) {
		timeline.fromTo(
			headline,
			{ autoAlpha: 0, yPercent: 108 },
			{ autoAlpha: 1, yPercent: 0, duration: 0.88 },
			0.08
		);
	}

	return timeline;
}

function initHomeHeader() {
	const header = document.querySelector(
		'.perfumes-global-header.is-home-route'
	);
	if ( ! header ) {
		return () => {};
	}

	const backdrop = header.querySelector( '.perfumes-header__backdrop' );
	const inner = header.querySelector( '.perfumes-header' );
	const logo = header.querySelector( '.perfumes-logo' );

	if ( ! backdrop || ! inner || ! logo ) {
		return () => {};
	}

	const nav = header.querySelector( '.perfumes-category-nav' );
	const actions = header.querySelector( '.perfumes-header__right' );
	const matchMedia = gsap.matchMedia();
	matchMedia.add(
		{
			isDesktop: '(min-width: 783px)',
			isMobile: '(max-width: 782px)',
			reduceMotion: '(prefers-reduced-motion: reduce)',
		},
		( context ) => {
			const { isMobile, reduceMotion } = context.conditions;
			const secondaryElements = [ nav, actions ].filter( Boolean );
			const timeline = gsap.timeline( {
				paused: true,
				defaults: {
					duration: reduceMotion ? 0 : 0.42,
					ease: 'power3.inOut',
					overwrite: 'auto',
				},
			} );

			timeline
				.to( backdrop, { scaleY: isMobile ? 1 : 0.73 }, 0 )
				.to( inner, { y: isMobile ? 0 : -9 }, 0 )
				.to( logo, { scale: isMobile ? 1 : 0.86 }, 0 )
				.to(
					secondaryElements,
					{ y: isMobile ? 0 : -6, scale: isMobile ? 1 : 0.96 },
					0
				);

			let isCompact = window.scrollY >= 30;
			timeline.progress( isCompact ? 1 : 0 );

			const updateHeader = () => {
				const nextCompact = window.scrollY >= 30;
				if ( nextCompact === isCompact ) {
					return;
				}

				isCompact = nextCompact;
				if ( reduceMotion ) {
					timeline.progress( nextCompact ? 1 : 0 );
					return;
				}

				timeline[ nextCompact ? 'play' : 'reverse' ]();
			};

			window.addEventListener( 'scroll', updateHeader, {
				passive: true,
			} );
			return () => {
				window.removeEventListener( 'scroll', updateHeader );
				timeline.kill();
			};
		}
	);

	return () => matchMedia.revert();
}

function hidePreloader( root ) {
	const elements = [
		root.querySelector( '.preloader-progress-bar' ),
		root.querySelector( '.preloader-mask' ),
	].filter( Boolean );

	gsap.set( elements, { autoAlpha: 0, display: 'none' } );
}

function runIntro( root, { showPreloader } ) {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		hidePreloader( root );
		root.classList.remove( 'is-intro-ready' );
		root.querySelectorAll( '[data-text-anim]' ).forEach( ( element ) => {
			element.style.visibility = 'visible';
		} );
		return () => {};
	}

	let activeTimeline = null;
	try {
		const context = gsap.context( () => {
			if ( ! showPreloader ) {
				hidePreloader( root );
				root.classList.remove( 'is-intro-ready' );
				const heroImage = root.querySelector(
					'.hero-product-primary__image'
				);
				gsap.set( heroImage, { clearProps: 'transform' } );
				activeTimeline = heroAnimation( root );
				return;
			}

			activeTimeline = gsap.timeline( {
				onComplete: () => root.classList.remove( 'is-intro-ready' ),
			} );
			const preloaderTl = preloaderAnimation( root );
			const heroTl = heroAnimation( root );
			activeTimeline.add( preloaderTl ).add( heroTl, '-=0.1' );
		}, root );

		return () => {
			activeTimeline?.kill();
			context.revert();
		};
	} catch ( error ) {
		hidePreloader( root );
		root.classList.remove( 'is-intro-ready' );
		window.console.warn( 'Omar hero animation skipped.', error );
		return () => {};
	}
}

function initProductCards( root ) {
	const cards = [ ...root.querySelectorAll( '[data-product-card]' ) ];
	if ( ! cards.length ) {
		return () => {};
	}

	const context = gsap.context( () => {
		const reduceMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches;

		if ( reduceMotion ) {
			gsap.set( cards, { autoAlpha: 1, y: 0 } );
			return;
		}

		gsap.set( cards, { autoAlpha: 0, y: 28 } );

		const grid = root.querySelector( '.perfumes-product-grid' );
		ScrollTrigger.create( {
			trigger: grid || cards[ 0 ],
			start: 'top 85%',
			once: true,
			onEnter: () => {
				gsap.to( cards, {
					autoAlpha: 1,
					y: 0,
					duration: 0.6,
					ease: 'power3.out',
					stagger: 0.08,
				} );
			},
		} );
	}, root );

	return () => context.revert();
}

function mountPage( { clientNavigation = false } = {} ) {
	const header = document.querySelector( '.perfumes-global-header' );
	const root = document.querySelector( '.perfumes-landing' );
	if ( root === activeRoot && header === activeHeader ) {
		return;
	}

	activePageCleanup();
	activeRoot = root;
	activeHeader = header;
	const cleanups = [];

	if ( header?.classList.contains( 'is-home-route' ) ) {
		cleanups.push( initHomeHeader() );
	}

	if ( root ) {
		cleanups.push(
			runIntro( root, { showPreloader: ! clientNavigation } )
		);
		cleanups.push( initProductCards( root ) );
	}

	activePageCleanup = () => {
		cleanups.reverse().forEach( ( cleanup ) => cleanup() );
	};

	window.requestAnimationFrame( () => ScrollTrigger.refresh() );
}

function scheduleMount( clientNavigation ) {
	window.cancelAnimationFrame( mountFrame );
	mountFrame = window.requestAnimationFrame( () => {
		mountPage( { clientNavigation } );
	} );
}

function handleRouteChange( event ) {
	scheduleMount( Boolean( event.detail?.clientNavigation ) );
}

function destroyRuntime() {
	window.cancelAnimationFrame( mountFrame );
	document.removeEventListener( 'omar:routechange', handleRouteChange );
	window.removeEventListener( 'pagehide', destroyRuntime );
	activePageCleanup();
	activePageCleanup = () => {};
	activeRoot = null;
	activeHeader = null;
}

if ( document.readyState === 'loading' ) {
	document.addEventListener(
		'DOMContentLoaded',
		() => scheduleMount( isReplacementRuntime ),
		{ once: true }
	);
} else {
	scheduleMount( isReplacementRuntime );
}

document.addEventListener( 'omar:routechange', handleRouteChange );
window.addEventListener( 'pagehide', destroyRuntime, { once: true } );
window[ RUNTIME_KEY ] = { destroy: destroyRuntime };
