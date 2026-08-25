import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin( ScrollTrigger, SplitText );

const textAnimations = {
	headerAnimation: ( element ) => {
		const split = SplitText.create( element, {
			type: 'chars',
			smartWrap: true,
			mask: 'chars',
			charsClass: 'header-char',
		} );

		return gsap.from( split.chars, {
			xPercent: -100,
			ease: 'power2.inOut',
			stagger: { each: 0.02, from: 'random' },
			duration: 0.5,
		} );
	},
	bodyAnimation: ( element ) =>
		SplitText.create( element, {
			type: 'lines',
			mask: 'lines',
			autoSplit: true,
			onSplit: ( split ) =>
				gsap.from( split.lines, {
					opacity: 0,
					yPercent: -100,
					duration: 0.9,
					stagger: 0.1,
					ease: 'power3.inOut',
					scrollTrigger: {
						trigger: element,
						start: 'top 90%',
					},
				} ),
		} ),
};

function animateText( element ) {
	if ( ! element ) {
		return;
	}
	gsap.set( element, { visibility: 'visible' } );
	const animation = textAnimations[ element.dataset.textAnim ];
	if ( animation ) {
		animation( element );
	}
}

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
		.set( heroImageTargets, { scale: 1.08 } )
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
	const timeline = gsap.timeline();
	const fadeElements = [ ...root.querySelectorAll( '[data-fade-in]' ) ];
	const heading = root.querySelector( '.hero-copy h1' );
	const subtitle = root.querySelector( '.sub-title' );

	timeline
		.fromTo(
			fadeElements,
			{
				filter: 'blur(30px)',
				opacity: 0,
				yPercent: ( index, element ) =>
					element.dataset.fadeIn === 'down' ? -100 : 0,
				xPercent: ( index, element ) =>
					element.dataset.fadeIn === 'left' ? 100 : 0,
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
			'<-0.25'
		)
		.call( animateText, [ heading ], '<0.55' )
		.call( animateText, [ subtitle ], '-=0.75' );

	return timeline;
}

function initHomeHeader() {
	const header = document.querySelector( '.home .perfumes-global-header' );
	if ( ! header ) {
		return;
	}

	const backdrop = header.querySelector( '.perfumes-header__backdrop' );
	const inner = header.querySelector( '.perfumes-header' );
	const logo = header.querySelector( '.perfumes-logo' );

	if ( ! backdrop || ! inner || ! logo ) {
		return;
	}

	const nav = header.querySelector( '.perfumes-category-nav' );
	const actions = header.querySelector( '.perfumes-header__right' );
	const matchMedia = gsap.matchMedia();
	matchMedia.add(
		{
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
				.to( backdrop, { autoAlpha: 1, scaleY: 1 }, 0 )
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

	window.addEventListener( 'pagehide', () => matchMedia.revert(), {
		once: true,
	} );
}

function runIntro( root ) {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		root.classList.remove( 'is-intro-ready' );
		root.querySelectorAll( '[data-text-anim]' ).forEach( ( element ) => {
			element.style.visibility = 'visible';
		} );
		return;
	}

	try {
		const introTimeline = gsap.timeline( {
			onComplete: () => root.classList.remove( 'is-intro-ready' ),
		} );
		const preloaderTl = preloaderAnimation( root );
		const heroTl = heroAnimation( root );
		introTimeline.add( preloaderTl ).add( heroTl, '-=0.55' );
	} catch ( error ) {
		root.classList.remove( 'is-intro-ready' );
		window.console.warn( 'Omar hero animation skipped.', error );
	}
}

function init() {
	const root = document.querySelector( '.perfumes-landing' );
	if ( ! root ) {
		return;
	}
	initHomeHeader();
	runIntro( root );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', init, { once: true } );
} else {
	init();
}
