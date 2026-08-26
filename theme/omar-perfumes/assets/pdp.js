( function () {
	const mount = () => {
		const root = document.querySelector( '.perfumes-pdp' );
		if ( ! root || root.dataset.omarPdpBound === '1' ) {
			return;
		}

		root.dataset.omarPdpBound = '1';
		initGallery( root );
		initQuantity( root );
		initRelatedCarousel( root );
		initReviewReveal( root );
	};

	const initGallery = ( root ) => {
		const image = root.querySelector( '.perfumes-pdp__image' );
		const thumbs = root.querySelectorAll( '.perfumes-pdp__thumb' );
		if ( ! image || ! thumbs.length ) {
			return;
		}

		thumbs.forEach( ( thumb ) => {
			thumb.addEventListener( 'click', () => {
				const src = thumb.getAttribute( 'data-src' );
				if ( ! src ) {
					return;
				}
				image.setAttribute( 'src', src );
				thumbs.forEach( ( item ) =>
					item.classList.remove( 'is-active' )
				);
				thumb.classList.add( 'is-active' );
			} );
		} );
	};

	const initQuantity = ( root ) => {
		const wrap = root.querySelector( '[data-pdp-qty]' );
		const input = wrap ? wrap.querySelector( 'input.qty' ) : null;
		if ( ! wrap || ! input ) {
			return;
		}

		const buttons = [ ...wrap.querySelectorAll( '[data-qty]' ) ];
		const min = () => {
			const value = Number( input.min );
			return Number.isFinite( value ) && value > 0 ? value : 1;
		};
		const max = () => {
			const value = Number( input.max );
			return Number.isFinite( value ) && value > 0 ? value : 9999;
		};
		const step = () => {
			const value = Number( input.step );
			return Number.isFinite( value ) && value > 0 ? value : 1;
		};

		const syncButtons = () => {
			const current = Number( input.value ) || min();
			buttons.forEach( ( button ) => {
				const delta = Number( button.getAttribute( 'data-qty' ) );
				button.disabled =
					delta < 0 ? current <= min() : current >= max();
			} );
		};

		buttons.forEach( ( button ) => {
			button.addEventListener( 'click', () => {
				const delta = Number( button.getAttribute( 'data-qty' ) );
				const next = Math.min(
					max(),
					Math.max(
						min(),
						( Number( input.value ) || min() ) + delta * step()
					)
				);
				input.value = String( next );
				input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
				input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				syncButtons();
			} );
		} );

		input.addEventListener( 'change', syncButtons );
		syncButtons();
	};

	const initRelatedCarousel = ( root ) => {
		const viewport = root.querySelector(
			'.perfumes-related__viewport .products'
		);
		const prev = root.querySelector( '[data-related-prev]' );
		const next = root.querySelector( '[data-related-next]' );
		if ( ! viewport || ! prev || ! next ) {
			return;
		}

		const controls = root.querySelector( '.perfumes-related__controls' );

		const cardStep = () => {
			const card = viewport.querySelector( 'li.product' );
			if ( ! card ) {
				return viewport.clientWidth;
			}
			const styles = window.getComputedStyle( viewport );
			const gap = parseFloat( styles.columnGap || styles.gap ) || 24;
			return card.getBoundingClientRect().width + gap;
		};

		const updateControls = () => {
			const maxScroll = Math.max(
				0,
				viewport.scrollWidth - viewport.clientWidth - 2
			);
			const canScroll = maxScroll > 0;
			if ( controls ) {
				controls.hidden = ! canScroll;
			}
			prev.disabled = ! canScroll || viewport.scrollLeft <= 2;
			next.disabled = ! canScroll || viewport.scrollLeft >= maxScroll;
		};

		const reduceMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches;
		const scrollBehavior = reduceMotion ? 'auto' : 'smooth';

		prev.addEventListener( 'click', () => {
			viewport.scrollBy( {
				left: -cardStep(),
				behavior: scrollBehavior,
			} );
		} );
		next.addEventListener( 'click', () => {
			viewport.scrollBy( { left: cardStep(), behavior: scrollBehavior } );
		} );
		viewport.addEventListener( 'scroll', updateControls, {
			passive: true,
		} );
		window.addEventListener( 'resize', updateControls );
		updateControls();
	};

	const initReviewReveal = ( root ) => {
		const reviews = root.querySelector( '.perfumes-pdp-reviews' );
		const items = reviews
			? [ ...reviews.querySelectorAll( '.perfumes-chat__item' ) ]
			: [];
		if ( ! reviews || ! items.length ) {
			return;
		}

		const reduceMotion = window.matchMedia(
			'(prefers-reduced-motion: reduce)'
		).matches;

		if ( reduceMotion ) {
			reviews.classList.add( 'is-reviews-revealed' );
			return;
		}

		items.forEach( ( item, index ) => {
			item.style.setProperty( '--review-index', String( index ) );
		} );

		const reveal = () => {
			reviews.classList.add( 'is-reviews-revealed' );
		};

		if ( ! ( 'IntersectionObserver' in window ) ) {
			reveal();
			return;
		}

		const observer = new window.IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => {
					if ( ! entry.isIntersecting ) {
						return;
					}
					reveal();
					observer.disconnect();
				} );
			},
			{
				root: null,
				rootMargin: '0px 0px -8% 0px',
				threshold: 0.12,
			}
		);

		observer.observe( reviews );
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', mount, { once: true } );
	} else {
		mount();
	}

	document.addEventListener( 'omar:routechange', mount );
} )();
