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

$hero_wordmark      = perfumes_text_attr( $attributes, 'heroWordmark' ) ?: 'OMAR®';
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
$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'perfumes-landing' ) );
$shop_url           = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
$plugin_url         = plugin_dir_url( dirname( __DIR__ ) . '/perfumes-block.php' );
$hero_bg_url        = $hero_image_url ?: $plugin_url . 'assets/hero-bg.jpg';
$cta_image_url      = $hero_cta_image_url ?: $plugin_url . 'assets/cta-img.jpg';
$mask_url           = $plugin_url . 'assets/omar-mask.svg';

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
		<div class="preloader-bg"></div>
		<div class="preloader-logo"><p class="logo-text" data-text-anim="logoAnimation"><?php echo esc_html( $hero_wordmark ); ?></p></div>
	</div>
	<div class="preloader-mask" style="--omar-mask: url('<?php echo esc_url( $mask_url ); ?>')" aria-hidden="true"></div>

	<section class="hero-section">
		<div class="wrapper">
			<div class="hero-img"><img src="<?php echo esc_url( $hero_bg_url ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>" /></div>
			<div class="hero-content">
				<div class="content-main">
					<?php if ( $title ) : ?><p class="sub-title" data-text-anim="bodyAnimation"><?php echo wp_kses_post( $title ); ?></p><?php endif; ?>
					<h1 data-text-anim="headerAnimation"><?php echo esc_html( $hero_wordmark ); ?></h1>
				</div>
				<div class="content-cta" data-fade-in="left">
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
					<div class="img-wrapper"><img src="<?php echo esc_url( $cta_image_url ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $primary_cta ) ); ?>" /></div>
					<?php if ( $primary_cta && $primary_cta_url ) : ?>
						<a class="register-button" href="<?php echo esc_url( $primary_cta_url ); ?>">
							<span><?php echo esc_html( $primary_cta ); ?></span>
							<span class="btn-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 12 12"><path fill="currentColor" d="M1.753.5H11.5v9.747H9.728V3.525L1.753 11.5.5 10.247l7.975-7.975H1.753V.5Z" /></svg></span>
						</a>
					<?php endif; ?>
				</div>
			</div>
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
