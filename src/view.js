import { gsap } from 'gsap';
import { CustomEase } from 'gsap/CustomEase';
import { ExpoScaleEase } from 'gsap/EasePack';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin( CustomEase, ExpoScaleEase, ScrollTrigger, SplitText );

CustomEase.create(
	'stutterEase',
	'M0,0 C0,0 0.052,0.1 0.152,0.1 0.242,0.1 0.299,0.349 0.399,0.349 0.586,0.349 0.569,0.596 0.67,0.624 0.842,0.671 0.95,0.95 1,1'
);

const textAnimations = {
	logoAnimation: ( element ) => {
		const split = SplitText.create( element, {
			type: 'chars',
			smartWrap: true,
			mask: 'chars',
		} );

		split.chars.forEach( ( character ) => {
			const text = character.innerText;
			character.innerHTML = '';
			const original = document.createElement( 'div' );
			original.className = 'og-char';
			original.innerText = text;
			const duplicate = document.createElement( 'div' );
			duplicate.className = 'duplicate-char';
			duplicate.innerText = text;
			character.appendChild( original );
			character.appendChild( duplicate );
		} );

		return gsap.from( split.chars, {
			yPercent: -100,
			ease: 'power2.inOut',
			stagger: { each: 0.02, from: 'random' },
			duration: 0.5,
			repeat: 1,
			repeatDelay: 0.75,
		} );
	},
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
	const logoText = root.querySelector( '.logo-text' );
	const preloaderBackground = root.querySelector( '.preloader-bg' );
	const preloaderMask = root.querySelector( '.preloader-mask' );
	const heroImage = root.querySelector( '.hero-img img' );

	timeline
		.call( animateText, [ logoText ] )
		.to( preloaderBackground, {
			scaleX: 1,
			ease: 'stutterEase',
			duration: 2.8,
		} )
		.to( preloaderMask, {
			scale: 3,
			duration: 0.9,
			ease: 'expoScale(0.5,7,power1.in)',
		} )
		.to(
			[ preloaderBackground, preloaderLogo, progressBar ],
			{ opacity: 0, duration: 0.85, ease: 'power2.inOut' },
			'<'
		)
		.to(
			heroImage,
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
	const tagline = document.querySelector( '.home .perfumes-logo__tagline' );
	const divider = document.querySelector( '.home .divider' );
	const fadeElements = [
		...document.querySelectorAll( '.home [data-fade-in]' ),
		...root.querySelectorAll( '[data-fade-in]' ),
	];
	const heading = root.querySelector( '.content-main h1' );
	const subtitle = root.querySelector( '.sub-title' );

	timeline
		.call( animateText, [ tagline ] )
		.fromTo(
			divider,
			{ scaleY: 0, transformOrigin: 'top' },
			{ scaleY: 1, duration: 0.5, ease: 'back.inOut' },
			'+=0.5'
		)
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

function runIntro( root ) {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	root.classList.add( 'is-intro-ready' );
	try {
		const introTimeline = gsap.timeline( {
			onComplete: () => root.classList.remove( 'is-intro-ready' ),
		} );
		const preloaderTl = preloaderAnimation( root );
		const heroTl = heroAnimation( root );
		introTimeline.add( preloaderTl ).add( heroTl, '-=2.4' );
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
	if ( ! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		root.classList.add( 'is-intro-ready' );
	}
	const fontsReady = document.fonts?.ready || Promise.resolve();
	fontsReady.then( () => runIntro( root ) );
}

if ( document.readyState === 'complete' ) {
	init();
} else {
	window.addEventListener( 'load', init, { once: true } );
}
