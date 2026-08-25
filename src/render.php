<?php
/**
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

if ( ! function_exists( 'perfumes_array_attr' ) ) {
	function perfumes_array_attr( $attributes, $key ) {
		return is_array( $attributes[ $key ] ?? null ) ? $attributes[ $key ] : array();
	}
}

if ( ! function_exists( 'perfumes_text_attr' ) ) {
	function perfumes_text_attr( $attributes, $key ) {
		return $attributes[ $key ] ?? '';
	}
}

if ( ! function_exists( 'perfumes_link_attr' ) ) {
	function perfumes_link_attr( $attributes, $key, $default = '' ) {
		$value = trim( (string) ( $attributes[ $key ] ?? $default ) );
		if ( '' === $value ) {
			return '';
		}
		if ( '#' === $value[0] || '/' === $value[0] || wp_http_validate_url( $value ) ) {
			return $value;
		}
		return '';
	}
}

if ( ! function_exists( 'perfumes_valid_hero_product' ) ) {
	function perfumes_valid_hero_product( $product, $exclude_id = 0, $require_available = false ) {
		return $product instanceof WC_Product
			&& $product->get_id() !== (int) $exclude_id
			&& 'publish' === $product->get_status()
			&& $product->is_visible()
			&& $product->get_image_id()
			&& ( ! $require_available || $product->is_in_stock() );
	}
}

if ( ! function_exists( 'perfumes_find_hero_product' ) ) {
	function perfumes_find_hero_product( $product_id, $search_term, $required_words, $exclude_id = 0 ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return null;
		}

		if ( $product_id ) {
			$product = wc_get_product( (int) $product_id );
			if ( perfumes_valid_hero_product( $product, $exclude_id ) ) {
				return $product;
			}
		}

		$candidate_ids = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				's'              => $search_term,
				'posts_per_page' => 30,
				'fields'         => 'ids',
			)
		);

		foreach ( $candidate_ids as $candidate_id ) {
			$product = wc_get_product( $candidate_id );
			$name    = $product ? strtolower( remove_accents( $product->get_name() ) ) : '';
			$matches = true;
			foreach ( $required_words as $word ) {
				if ( false === strpos( $name, strtolower( remove_accents( $word ) ) ) ) {
					$matches = false;
					break;
				}
			}
			if ( $matches && perfumes_valid_hero_product( $product, $exclude_id ) ) {
				return $product;
			}
		}

		$fallback_ids = wc_get_products(
			array(
				'status'       => 'publish',
				'stock_status' => 'instock',
				'limit'        => 50,
				'orderby'      => 'date',
				'order'        => 'DESC',
				'return'       => 'ids',
			)
		);
		foreach ( $fallback_ids as $fallback_id ) {
			$product = wc_get_product( $fallback_id );
			if ( perfumes_valid_hero_product( $product, $exclude_id, true ) ) {
				return $product;
			}
		}

		return null;
	}
}

$eyebrow            = perfumes_text_attr( $attributes, 'eyebrow' );
$title              = perfumes_text_attr( $attributes, 'title' );
$hero_brand         = perfumes_text_attr( $attributes, 'heroBrand' );
$description        = perfumes_text_attr( $attributes, 'description' );
$primary_cta        = perfumes_text_attr( $attributes, 'primaryCta' );
$primary_cta_url    = perfumes_link_attr( $attributes, 'primaryCtaUrl', '#perfumes-products' );
$secondary_cta      = perfumes_text_attr( $attributes, 'secondaryCta' );
$secondary_cta_url  = perfumes_link_attr( $attributes, 'secondaryCtaUrl' );
$hero_price         = perfumes_text_attr( $attributes, 'heroPrice' );
$hero_image_url     = perfumes_text_attr( $attributes, 'heroImageUrl' );
$hero_cta_image_url = perfumes_text_attr( $attributes, 'heroCtaImageUrl' );
$promo_title        = perfumes_text_attr( $attributes, 'promoTitle' );
$promo_description  = perfumes_text_attr( $attributes, 'promoDescription' );
$promo_cta          = perfumes_text_attr( $attributes, 'promoCta' );
$promo_cta_url      = perfumes_link_attr( $attributes, 'promoCtaUrl', '/tienda/' );
$promo_image_url    = perfumes_text_attr( $attributes, 'promoImageUrl' );
$brands             = perfumes_array_attr( $attributes, 'brands' );
$products           = perfumes_array_attr( $attributes, 'products' );
$benefits           = perfumes_array_attr( $attributes, 'benefits' );
$payment_methods    = perfumes_array_attr( $attributes, 'paymentMethods' );
$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'perfumes-landing is-intro-ready' ) );
$home_url           = home_url( '/' );
$shop_url           = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
$plugin_url         = plugin_dir_url( dirname( __DIR__ ) . '/perfumes-block.php' );
$plugin_path        = dirname( __DIR__ );
$asset_url          = static function ( $filename ) use ( $plugin_path, $plugin_url ) {
	$path    = $plugin_path . '/assets/' . $filename;
	$version = file_exists( $path ) ? filemtime( $path ) : '0.4.0';
	return add_query_arg( 'ver', $version, $plugin_url . 'assets/' . $filename );
};
$logo_light_url     = $asset_url( 'omar-logo-light-v1.png' );
$hero_product       = perfumes_find_hero_product(
	absint( $attributes['heroProductId'] ?? 0 ),
	'Amber Rouge Orientica',
	array( 'amber', 'rouge', 'orientica' )
);
$secondary_product  = perfumes_find_hero_product(
	absint( $attributes['heroSecondaryProductId'] ?? 0 ),
	'9 PM Afnan',
	array( '9', 'pm', 'afnan' ),
	$hero_product ? $hero_product->get_id() : 0
);
$hero_product_name  = $hero_product ? $hero_product->get_name() : __( 'Perfumería original', 'perfumes' );
$hero_product_url   = $hero_product ? $hero_product->get_permalink() : $shop_url;
$hero_product_image = $hero_product ? wp_get_attachment_image_url( $hero_product->get_image_id(), 'full' ) : '';
$hero_availability  = $hero_product && $hero_product->is_in_stock() ? __( 'Disponible', 'perfumes' ) : __( 'Agotado', 'perfumes' );
$secondary_name     = $secondary_product ? $secondary_product->get_name() : '';
$secondary_url      = $secondary_product ? $secondary_product->get_permalink() : $shop_url;
$secondary_image    = $secondary_product ? wp_get_attachment_image_url( $secondary_product->get_image_id(), 'large' ) : '';
$secondary_stock    = $secondary_product && $secondary_product->is_in_stock() ? __( 'Disponible', 'perfumes' ) : __( 'Agotado', 'perfumes' );

if ( function_exists( 'wc_get_products' ) ) {
	$woocommerce_products = wc_get_products( array( 'status' => 'publish', 'limit' => 12, 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( $woocommerce_products ) {
		$products = array_map(
			static function ( $product ) {
				$price = (float) $product->get_price();
				$regular_price = (float) $product->get_regular_price();
				return array( 'id' => $product->get_id(), 'discount' => $regular_price > $price ? '-' . (int) round( ( 1 - ( $price / $regular_price ) ) * 100 ) . '%' : '', 'name' => $product->get_name(), 'brand' => implode( ', ', wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) ) ), 'size' => $product->get_attribute( 'pa_concentracion' ) ?: __( 'Perfume original', 'perfumes' ), 'price' => '$ ' . number_format( $price, 0, ',', '.' ), 'oldPrice' => $regular_price > $price ? '$ ' . number_format( $regular_price, 0, ',', '.' ) : '', 'imageUrl' => wp_get_attachment_image_url( $product->get_image_id(), 'large' ) ?: '', 'url' => get_permalink( $product->get_id() ), 'addUrl' => $product->add_to_cart_url(), 'buttonText' => $product->add_to_cart_text(), 'purchasable' => $product->is_purchasable() && $product->is_in_stock() );
			},
			$woocommerce_products
		);
	}
}
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="preloader-progress-bar" aria-hidden="true">
		<div class="preloader-logo"><img class="logo-image" src="<?php echo esc_url( $logo_light_url ); ?>" alt="" width="900" height="277" /></div>
		<div class="preloader-bg"></div>
	</div>
	<div class="preloader-mask" aria-hidden="true">
		<div class="preloader-panel preloader-panel--top"></div>
		<div class="preloader-panel preloader-panel--bottom"></div>
	</div>

	<section class="hero-section">
		<div class="hero-atmosphere" aria-hidden="true">
			<?php if ( $hero_product_image ) : ?><img class="hero-blur hero-blur--primary" src="<?php echo esc_url( $hero_product_image ); ?>" alt="" /><?php endif; ?>
			<?php if ( $secondary_image ) : ?><img class="hero-blur hero-blur--secondary" src="<?php echo esc_url( $secondary_image ); ?>" alt="" /><?php endif; ?>
		</div>
		<div class="wrapper hero-layout">
			<div class="hero-copy">
				<p class="hero-eyebrow" data-fade-in="down"><?php echo esc_html( $eyebrow ?: __( 'Perfumería original · Colombia', 'perfumes' ) ); ?></p>
				<h1 class="hero-headline"><span class="hero-headline__text" data-hero-reveal><?php esc_html_e( 'Tu esencia, elevada.', 'perfumes' ); ?></span></h1>
				<p class="hero-featured-name" data-fade-in="left"><span><?php esc_html_e( 'Producto destacado', 'perfumes' ); ?></span><?php echo esc_html( $hero_product_name ); ?></p>
				<div class="hero-product-meta" data-fade-in="left">
					<span class="hero-availability <?php echo $hero_product && $hero_product->is_in_stock() ? 'is-in-stock' : 'is-out-of-stock'; ?>"><?php echo esc_html( $hero_availability ); ?></span>
					<?php if ( $hero_product ) : ?><span class="hero-price"><?php echo wp_kses_post( $hero_product->get_price_html() ); ?></span><?php endif; ?>
				</div>
				<a class="hero-primary-cta" href="<?php echo esc_url( $hero_product_url ); ?>" data-fade-in="left">
					<span><?php echo esc_html( $primary_cta ?: __( 'Ver producto', 'perfumes' ) ); ?></span>
					<span aria-hidden="true">↗</span>
				</a>
			</div>

			<?php if ( $hero_product_image ) : ?>
				<a class="hero-product-primary" href="<?php echo esc_url( $hero_product_url ); ?>" aria-label="<?php echo esc_attr( $hero_product_name ); ?>">
					<img class="hero-product-primary__image" src="<?php echo esc_url( $hero_product_image ); ?>" alt="<?php echo esc_attr( $hero_product_name ); ?>" fetchpriority="high" />
				</a>
			<?php endif; ?>

			<?php if ( $secondary_product ) : ?>
				<aside class="content-cta" data-fade-in="left">
					<?php if ( $hero_brand ) : ?>
						<div class="cta-marquee">
							<?php for ( $group = 0; $group < 2; $group++ ) : ?>
								<div class="marquee-group" <?php echo 1 === $group ? 'aria-hidden="true"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
									<?php for ( $item = 0; $item < 4; $item++ ) : ?>
										<span class="marquee-item"><span class="marquee-symbol" aria-hidden="true">✦</span><span><?php echo esc_html( $hero_brand ); ?></span></span>
									<?php endfor; ?>
								</div>
							<?php endfor; ?>
						</div>
					<?php endif; ?>
					<a class="img-wrapper" href="<?php echo esc_url( $secondary_url ); ?>">
						<?php if ( $secondary_image ) : ?><img src="<?php echo esc_url( $secondary_image ); ?>" alt="<?php echo esc_attr( $secondary_name ); ?>" /><?php endif; ?>
					</a>
					<div class="secondary-product-copy">
						<span><?php echo esc_html( $secondary_stock ); ?></span>
						<h2><a href="<?php echo esc_url( $secondary_url ); ?>"><?php echo esc_html( $secondary_name ); ?></a></h2>
						<div>
							<span class="hero-price"><?php echo wp_kses_post( $secondary_product->get_price_html() ); ?></span>
							<a class="secondary-product-link" href="<?php echo esc_url( $secondary_url ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Ver %s', 'perfumes' ), $secondary_name ) ); ?>">↗</a>
						</div>
					</div>
				</aside>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $brands ) : ?>
		<section class="perfumes-brand-strip">
			<p><?php echo esc_html__( 'Las marcas mas buscadas', 'perfumes' ); ?></p>
			<div>
				<?php foreach ( $brands as $brand ) : ?>
					<?php if ( $brand ) : ?>
						<a href="<?php echo esc_url( add_query_arg( array( 's' => $brand, 'post_type' => 'product' ), $home_url ) ); ?>"><?php echo esc_html( $brand ); ?></a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $products ) : ?>
		<section class="perfumes-products" id="perfumes-products">
			<div class="perfumes-section-heading">
				<h2><?php echo esc_html__( 'Top en ventas', 'perfumes' ); ?></h2>
				<a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html__( 'Ver todos los productos', 'perfumes' ); ?></a>
			</div>
			<div class="perfumes-product-grid">
				<?php foreach ( $products as $product ) : ?>
					<?php
					$product_discount  = $product['discount'] ?? '';
					$product_name      = $product['name'] ?? '';
					$product_brand     = $product['brand'] ?? '';
					$product_size      = $product['size'] ?? '';
					$product_price     = $product['price'] ?? '';
					$product_old_price = $product['oldPrice'] ?? '';
					$product_image     = $product['imageUrl'] ?? '';
					?>
					<article class="perfumes-product-card">
						<a class="perfumes-product-card__image" href="<?php echo esc_url( $product['url'] ?? $shop_url ); ?>">
							<?php if ( $product_image ) : ?>
								<img src="<?php echo esc_url( $product_image ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $product_name ) ); ?>" loading="lazy" />
							<?php endif; ?>
						</a>
						<?php if ( $product_discount ) : ?>
							<span class="perfumes-badge"><?php echo esc_html( $product_discount ); ?></span>
						<?php endif; ?>
						<div class="perfumes-product-card__body">
							<?php if ( $product_brand ) : ?>
								<p class="perfumes-product-card__brand"><?php echo esc_html( $product_brand ); ?></p>
							<?php endif; ?>
							<?php if ( $product_name ) : ?>
								<h3><a href="<?php echo esc_url( $product['url'] ?? '#' ); ?>"><?php echo wp_kses_post( $product_name ); ?></a></h3>
							<?php endif; ?>
							<?php if ( $product_size ) : ?>
								<p class="perfumes-product-card__size"><?php echo esc_html( $product_size ); ?></p>
							<?php endif; ?>
							<div class="perfumes-product-card__price-row">
								<?php if ( $product_price ) : ?>
									<strong class="perfumes-product-card__price"><?php echo esc_html( $product_price ); ?></strong>
								<?php endif; ?>
								<?php if ( $product_old_price ) : ?>
									<span class="perfumes-product-card__old-price"><?php echo esc_html( $product_old_price ); ?></span>
								<?php endif; ?>
							</div>
							<?php $product_id = $product['id'] ?? 0; ?>
							<?php if ( $product_id && ( $product['purchasable'] ?? false ) ) : ?>
								<a class="perfumes-product-card__button button add_to_cart_button ajax_add_to_cart" href="<?php echo esc_url( $product['addUrl'] ?? '' ); ?>" data-product_id="<?php echo esc_attr( $product_id ); ?>" data-quantity="1" rel="nofollow"><?php echo esc_html( $product['buttonText'] ?? __( 'Agregar al carrito', 'perfumes' ) ); ?></a>
							<?php else : ?>
								<a class="perfumes-product-card__button" href="<?php echo esc_url( $product['url'] ?? $shop_url ); ?>"><?php echo esc_html__( 'Ver producto', 'perfumes' ); ?></a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<section class="perfumes-promo">
		<div>
			<p class="perfumes-addi-wordmark"><?php echo esc_html__( 'addi', 'perfumes' ); ?><span></span><em><?php echo esc_html__( 'Aliado de pago', 'perfumes' ); ?></em></p>
			<?php if ( $promo_title ) : ?>
				<h2><?php echo wp_kses_post( $promo_title ); ?></h2>
			<?php endif; ?>
			<?php if ( $promo_description ) : ?>
				<p><?php echo wp_kses_post( $promo_description ); ?></p>
			<?php endif; ?>
			<?php if ( $promo_cta && $promo_cta_url ) : ?>
				<a class="perfumes-button perfumes-button--primary" href="<?php echo esc_url( $promo_cta_url ); ?>"><?php echo esc_html( $promo_cta ); ?></a>
			<?php endif; ?>
		</div>
		<div class="perfumes-promo__media">
			<?php if ( $promo_image_url ) : ?>
				<img src="<?php echo esc_url( $promo_image_url ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $promo_title ) ); ?>" loading="lazy" />
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $benefits ) : ?>
		<section class="perfumes-benefits">
			<?php foreach ( $benefits as $benefit ) : ?>
				<?php
				$benefit_title       = $benefit['title'] ?? '';
				$benefit_description = $benefit['description'] ?? '';
				?>
				<div class="perfumes-benefit">
					<span aria-hidden="true">+</span>
					<div>
						<?php if ( $benefit_title ) : ?>
							<h3><?php echo esc_html( $benefit_title ); ?></h3>
						<?php endif; ?>
						<?php if ( $benefit_description ) : ?>
							<p><?php echo esc_html( $benefit_description ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</section>
	<?php endif; ?>

	<?php if ( $payment_methods ) : ?>
		<section class="perfumes-payments">
			<p><?php echo esc_html__( 'Medios de pago', 'perfumes' ); ?></p>
			<div>
				<?php foreach ( $payment_methods as $method ) : ?>
					<?php if ( $method ) : ?>
						<span><?php echo esc_html( $method ); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

</div>
