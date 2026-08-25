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
}

function animateLogoText( el ) {
	if ( ! el ) {
		return;
	}

	gsap.set( el, { visibility: 'visible' } );
	const split = SplitText.create( el, {
		type: 'chars',
		smartWrap: true,
		mask: 'chars',
	} );

	split.chars.forEach( ( charEl ) => {
		const text = charEl.textContent;
		charEl.textContent = '';
		const original = document.createElement( 'div' );
		original.className = 'og-char';
		original.textContent = text;
		const duplicate = document.createElement( 'div' );
		duplicate.className = 'duplicate-char';
		duplicate.textContent = text;
		charEl.append( original, duplicate );
	} );

	gsap.from( split.chars, {
		yPercent: -100,
		ease: 'power2.inOut',
		stagger: {
			each: 0.02,
			from: 'random',
		},
		duration: 0.5,
		repeat: 1,
		repeatDelay: 0.75,
	} );
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
	const logoText = root.querySelector( '.logo-text' );
	const fill = root.querySelector( '.preloader-bg' );
	const mask = root.querySelector( '.preloader-mask' );
	const progressBar = root.querySelector( '.preloader-progress-bar' );
	const preloaderLogo = root.querySelector( '.preloader-logo' );
	const heroImage = root.querySelector( '.hero-product-primary__image' );
	const fadeTargets = [ fill, preloaderLogo, progressBar ].filter( Boolean );

	timeline
		.set( mask, { scale: 1, transformOrigin: '50% 50%' } )
		.set( heroImage ? [ heroImage ] : [], { scale: 1.2 } )
		.call( () => {
			document.fonts.ready.then( () => animateLogoText( logoText ) );
		} )
		.to( fill, {
			scaleX: 1,
			ease: 'stutterEase',
			duration: 2.8,
		} )
		.to( mask, {
			scale: 3,
			duration: 0.9,
			ease: 'expoScale(0.5,7,power1.in)',
		} )
		.to(
			fadeTargets,
			{
				opacity: 0,
				duration: 0.85,
				ease: 'power2.inOut',
			},
			'<'
		)
		.to(
			heroImage ? [ heroImage ] : [],
			{
				scale: 1,
				duration: 2.85,
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

function runIntro( root, { showPreloader } ) {
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

			const preloaderTl = preloaderAnimation( root );
			const heroTl = heroAnimation( root );
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
