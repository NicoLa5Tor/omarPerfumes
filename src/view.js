import { gsap } from 'gsap';
import { CustomEase } from 'gsap/CustomEase';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin( CustomEase, SplitText );

CustomEase.create(
	'omarReveal',
	'M0,0 C0.12,0.05 0.18,0.32 0.34,0.4 0.56,0.51 0.62,0.88 1,1'
);

function splitText( element, type = 'chars' ) {
	if ( ! element ) {
		return null;
	}
	gsap.set( element, { visibility: 'visible' } );
	return SplitText.create( element, {
		type,
		mask: type,
		charsClass: 'perfumes-split-char',
	} );
}

function preloaderAnimation( root, splits ) {
	const timeline = gsap.timeline();
	const intro = root.querySelector( '.perfumes-intro' );
	const progress = root.querySelector( '.perfumes-intro__progress' );
	const logo = splitText(
		root.querySelector( '.perfumes-intro__logo' ),
		'chars'
	);
	const heroImage = root.querySelector( '.perfumes-hero__media img' );

	if ( logo ) {
		splits.push( logo );
		timeline.from( logo.chars, {
			yPercent: -120,
			duration: 0.55,
			ease: 'power3.inOut',
			stagger: { each: 0.045, from: 'random' },
		} );
	}

	timeline
		.to( progress, { scaleX: 1, duration: 2.45, ease: 'omarReveal' }, 0 )
		.to(
			intro,
			{
				clipPath: 'inset(0 0 100% 0)',
				duration: 0.9,
				ease: 'power4.inOut',
			},
			2.05
		);

	if ( heroImage ) {
		timeline.fromTo(
			heroImage,
			{ scale: 1.18 },
			{ scale: 1, duration: 2.85, ease: 'power3.out' },
			1.9
		);
	}

	return timeline;
}

function heroAnimation( root, splits ) {
	const timeline = gsap.timeline();
	const title = splitText(
		root.querySelector( '.perfumes-hero h1' ),
		'chars'
	);
	const bodyElements = root.querySelectorAll(
		'.perfumes-eyebrow, .perfumes-hero__subtitle'
	);
	const card = root.querySelector( '.perfumes-hero-card' );

	bodyElements.forEach( ( element ) => {
		gsap.set( element, { visibility: 'visible' } );
	} );

	timeline.from( bodyElements, {
		yPercent: -80,
		opacity: 0,
		duration: 0.9,
		stagger: 0.12,
		ease: 'power3.inOut',
	} );

	if ( title ) {
		splits.push( title );
		timeline.from(
			title.chars,
			{
				xPercent: -110,
				opacity: 0,
				duration: 0.7,
				ease: 'power3.inOut',
				stagger: { each: 0.025, from: 'random' },
			},
			'-=0.55'
		);
	}

	timeline.from(
		card,
		{
			xPercent: 70,
			opacity: 0,
			filter: 'blur(24px)',
			duration: 1.15,
			ease: 'power4.inOut',
		},
		'-=0.8'
	);

	return timeline;
}

function runHeroIntro( root ) {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	const splits = [];
	root.classList.add( 'is-intro-ready' );

	try {
		const introTimeline = gsap.timeline( {
			onComplete: () => {
				root.classList.remove( 'is-intro-ready' );
				splits.forEach( ( split ) => split.revert() );
			},
		} );
		const preloaderTl = preloaderAnimation( root, splits );
		const heroTl = heroAnimation( root, splits );

		introTimeline.add( preloaderTl ).add( heroTl, '-=2.4' );
	} catch ( error ) {
		root.classList.remove( 'is-intro-ready' );
		// Keep the storefront usable if an animation API is unavailable.
		window.console.warn( 'Omar hero animation skipped.', error );
	}
}

function init() {
	const roots = document.querySelectorAll( '.perfumes-landing' );
	const fontsReady = document.fonts?.ready || Promise.resolve();
	if ( ! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		roots.forEach( ( root ) => root.classList.add( 'is-intro-ready' ) );
	}
	fontsReady.then( () => roots.forEach( runHeroIntro ) );
}

if ( document.readyState === 'complete' ) {
	init();
} else {
	window.addEventListener( 'load', init, { once: true } );
}
