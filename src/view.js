import { gsap } from 'gsap';
import { CustomEase } from 'gsap/CustomEase';
import { ExpoScaleEase } from 'gsap/EasePack';
import { SplitText } from 'gsap/SplitText';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin( ScrollTrigger, CustomEase, ExpoScaleEase, SplitText );
CustomEase.create(
	'stutterEase',
	'M0,0 C0,0 0.052,0.1 0.152,0.1 0.242,0.1 0.299,0.349 0.399,0.349 0.586,0.349 0.569,0.596 0.67,0.624 0.842,0.671 0.95,0.95 1,1'
);

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
let scrollUnlockTimer = null;

const SCROLL_LOCK_CLASS = 'omar-scroll-locked';
const PRELOADER_SEEN_KEY = 'omar-preloader-seen';

function hasSeenPreloader() {
	try {
		return sessionStorage.getItem( PRELOADER_SEEN_KEY ) === '1';
	} catch {
		return false;
	}
}

function markPreloaderSeen() {
	try {
		sessionStorage.setItem( PRELOADER_SEEN_KEY, '1' );
	} catch {
		// Ignore storage failures in private browsing.
	}
}

function shouldShowPreloader( clientNavigation ) {
	return (
		! clientNavigation &&
		! hasSeenPreloader() &&
		document.body.classList.contains( 'omar-initial-entry' )
	);
}

if ( hasSeenPreloader() ) {
	document.body.classList.remove( 'omar-initial-entry', SCROLL_LOCK_CLASS );
	document.documentElement.classList.remove( SCROLL_LOCK_CLASS );
} else if ( document.body.classList.contains( SCROLL_LOCK_CLASS ) ) {
	document.documentElement.classList.add( SCROLL_LOCK_CLASS );
}

function lockScroll() {
	document.documentElement.classList.add( SCROLL_LOCK_CLASS );
	document.body.classList.add( SCROLL_LOCK_CLASS );
}

function unlockScroll() {
	scrollUnlockTimer?.kill();
	scrollUnlockTimer = null;
	document.documentElement.classList.remove( SCROLL_LOCK_CLASS );
	document.body.classList.remove( SCROLL_LOCK_CLASS );
}

function scheduleScrollUnlockAfterHero( heroTimeline ) {
	scrollUnlockTimer?.kill();
	heroTimeline.call(
		() => {
			scrollUnlockTimer = gsap.delayedCall( 1, unlockScroll );
		},
		null,
		0
	);
}

function hidePreloader( root ) {
	const targets = [
		root.querySelector( '.preloader-progress-bar' ),
		root.querySelector( '.preloader-mask' ),
		root.querySelector( '.preloader-bg' ),
		root.querySelector( '.preloader-logo' ),
	].filter( Boolean );

	gsap.set( targets, { opacity: 0, display: 'none' } );
}

function completeInitialEntry( root ) {
	hidePreloader( root );
	root.classList.remove( 'is-intro-ready' );
	document.body.classList.remove( 'omar-initial-entry' );
	unlockScroll();
}

function animateHeadline( el ) {
	if ( ! el ) {
		return;
	}

	gsap.set( el, { visibility: 'visible' } );
	const split = SplitText.create( el, {
		type: 'chars',
		smartWrap: true,
		mask: 'chars',
		charsClass: 'header-char',
	} );

	gsap.from( split.chars, {
		xPercent: -100,
		ease: 'power2.inOut',
		stagger: {
			each: 0.02,
			from: 'random',
		},
		duration: 0.5,
	} );
}

function preloaderAnimation( root ) {
	const timeline = gsap.timeline();
	const logoImage = root.querySelector( '.logo-image' );
	const fill = root.querySelector( '.preloader-bg' );
	const mask = root.querySelector( '.preloader-mask' );
	const preloaderLogo = root.querySelector( '.preloader-logo' );
	const heroImage = root.querySelector( '.hero-product-primary__image' );
	const brandTargets = [ preloaderLogo, fill ].filter( Boolean );

	timeline
		.set( mask, { '--bubble-r': '0vmax' } )
		.set( heroImage ? [ heroImage ] : [], { scale: 1.2 } )
		.fromTo(
			logoImage,
			{ autoAlpha: 0, scale: 0.92 },
			{ autoAlpha: 1, scale: 1, duration: 0.5, ease: 'power2.out' }
		)
		.to(
			fill,
			{
				scaleX: 1,
				ease: 'stutterEase',
				duration: 2.2,
			},
			'-=0.15'
		)
		.to(
			brandTargets,
			{
				autoAlpha: 0,
				duration: 0.4,
				ease: 'power2.in',
			},
			'-=0.15'
		)
		.to(
			mask,
			{
				'--bubble-r': '130vmax',
				duration: 1.15,
				ease: 'power3.inOut',
			},
			'-=0.1'
		)
		.to(
			heroImage ? [ heroImage ] : [],
			{
				scale: 1,
				duration: 2.4,
				ease: 'expoScale(0.5,7,power1.out)',
			},
			'<'
		);

	return timeline;
}

function heroAnimation( root ) {
	const timeline = gsap.timeline();
	const fadeElements = [ ...root.querySelectorAll( '[data-fade-in]' ) ];
	const headline = root.querySelector( '[data-text-anim="headerAnimation"]' );

	if ( fadeElements.length ) {
		timeline.fromTo(
			fadeElements,
			{
				filter: 'blur(30px)',
				opacity: 0,
				yPercent: ( index, element ) =>
					element.getAttribute( 'data-fade-in' ) === 'down'
						? -100
						: 0,
				xPercent: ( index, element ) =>
					element.getAttribute( 'data-fade-in' ) === 'left' ? 100 : 0,
			},
			{
				yPercent: 0,
				xPercent: 0,
				filter: 'blur(0px)',
				opacity: 1,
				duration: 1.25,
				ease: 'power4.inOut',
				stagger: 0.08,
			},
			0
		);
	}

	timeline.call(
		() => {
			document.fonts.ready.then( () => animateHeadline( headline ) );
		},
		null,
		fadeElements.length ? 0.55 : 0
	);

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

function runIntro( root, { clientNavigation = false } = {} ) {
	const showPreloader = shouldShowPreloader( clientNavigation );

	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		hidePreloader( root );
		completeInitialEntry( root );
		gsap.set( root.querySelector( '.hero-section' ), {
			autoAlpha: 1,
		} );
		gsap.set( root.querySelector( '.hero-product-primary__image' ), {
			scale: 1,
		} );
		return () => {};
	}

	let activeTimeline = null;
	let failsafe = null;
	try {
		const context = gsap.context( () => {
			if ( ! showPreloader ) {
				unlockScroll();
				hidePreloader( root );
				gsap.set( root.querySelector( '.hero-section' ), {
					autoAlpha: 1,
				} );
				gsap.set(
					root.querySelector( '.hero-product-primary__image' ),
					{
						scale: 1,
						autoAlpha: 1,
					}
				);
				activeTimeline = heroAnimation( root );
				activeTimeline.eventCallback( 'onComplete', () =>
					completeInitialEntry( root )
				);
				return;
			}

			markPreloaderSeen();
			lockScroll();
			const preloaderTl = preloaderAnimation( root );
			const heroTl = heroAnimation( root );
			scheduleScrollUnlockAfterHero( heroTl );
			activeTimeline = gsap.timeline( {
				onComplete: () => {
					failsafe?.kill();
					completeInitialEntry( root );
				},
			} );
			activeTimeline.add( preloaderTl ).add( heroTl, '-=2.4' );
			failsafe = gsap.delayedCall( 10, () => {
				completeInitialEntry( root );
			} );
		}, root );

		return () => {
			failsafe?.kill();
			activeTimeline?.kill();
			unlockScroll();
			context.revert();
		};
	} catch ( error ) {
		hidePreloader( root );
		completeInitialEntry( root );
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
		cleanups.push( runIntro( root, { clientNavigation } ) );
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
