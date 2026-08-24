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

$site_title         = perfumes_text_attr( $attributes, 'siteTitle' );
$tagline            = perfumes_text_attr( $attributes, 'tagline' );
$search_placeholder = perfumes_text_attr( $attributes, 'searchPlaceholder' );
$cart_count         = perfumes_text_attr( $attributes, 'cartCount' );
$eyebrow            = perfumes_text_attr( $attributes, 'eyebrow' );
$title              = perfumes_text_attr( $attributes, 'title' );
$hero_brand         = perfumes_text_attr( $attributes, 'heroBrand' );
$description        = perfumes_text_attr( $attributes, 'description' );
$primary_cta        = perfumes_text_attr( $attributes, 'primaryCta' );
$secondary_cta      = perfumes_text_attr( $attributes, 'secondaryCta' );
$hero_price         = perfumes_text_attr( $attributes, 'heroPrice' );
$hero_image_url     = perfumes_text_attr( $attributes, 'heroImageUrl' );
$promo_title        = perfumes_text_attr( $attributes, 'promoTitle' );
$promo_description  = perfumes_text_attr( $attributes, 'promoDescription' );
$promo_cta          = perfumes_text_attr( $attributes, 'promoCta' );
$promo_image_url    = perfumes_text_attr( $attributes, 'promoImageUrl' );
$categories         = perfumes_array_attr( $attributes, 'categories' );
$brands             = perfumes_array_attr( $attributes, 'brands' );
$products           = perfumes_array_attr( $attributes, 'products' );
$benefits           = perfumes_array_attr( $attributes, 'benefits' );
$payment_methods    = perfumes_array_attr( $attributes, 'paymentMethods' );
$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'perfumes-landing' ) );

if ( function_exists( 'wc_get_products' ) ) {
	$woocommerce_products = wc_get_products( array( 'status' => 'publish', 'limit' => 12, 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( $woocommerce_products ) {
		$products = array_map(
			static function ( $product ) {
				$price = (float) $product->get_price();
				$regular_price = (float) $product->get_regular_price();
				return array( 'id' => $product->get_id(), 'discount' => $regular_price > $price ? '-' . (int) round( ( 1 - ( $price / $regular_price ) ) * 100 ) . '%' : '', 'name' => $product->get_name(), 'brand' => implode( ', ', wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) ) ), 'size' => $product->get_attribute( 'pa_concentracion' ) ?: __( 'Perfume original', 'perfumes' ), 'price' => '$ ' . number_format( $price, 0, ',', '.' ), 'oldPrice' => $regular_price > $price ? '$ ' . number_format( $regular_price, 0, ',', '.' ) : '', 'imageUrl' => wp_get_attachment_image_url( $product->get_image_id(), 'large' ) ?: '', 'url' => get_permalink( $product->get_id() ) );
			},
			$woocommerce_products
		);
	}
	if ( function_exists( 'WC' ) && WC()->cart ) {
		$cart_count = (string) WC()->cart->get_cart_contents_count();
	}
}
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<header class="perfumes-header">
		<div class="perfumes-header__social" aria-hidden="true">
			<span>Ig</span>
			<span>Fb</span>
			<span>Tk</span>
		</div>
		<div class="perfumes-logo">
			<?php if ( $site_title ) : ?>
				<div class="perfumes-logo__title"><?php echo esc_html( $site_title ); ?></div>
			<?php endif; ?>
			<?php if ( $tagline ) : ?>
				<div class="perfumes-logo__tagline"><?php echo esc_html( $tagline ); ?></div>
			<?php endif; ?>
		</div>
		<div class="perfumes-header__right">
			<div class="perfumes-search">
				<span aria-hidden="true">Search</span>
				<span><?php echo esc_html( $search_placeholder ); ?></span>
			</div>
			<a class="perfumes-cart" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '#' ); ?>" aria-label="<?php echo esc_attr__( 'Carrito', 'perfumes' ); ?>">
				<span aria-hidden="true">Bag</span>
				<strong data-perfumes-cart-count><?php echo esc_html( $cart_count ); ?></strong>
			</a>
		</div>
	</header>

	<?php if ( $categories ) : ?>
		<nav class="perfumes-category-nav" aria-label="<?php echo esc_attr__( 'Categorias destacadas', 'perfumes' ); ?>">
			<?php foreach ( $categories as $category ) : ?>
				<?php if ( $category ) : ?>
					<span><?php echo esc_html( $category ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<section class="perfumes-hero">
		<div class="perfumes-hero__content">
			<span class="perfumes-hero__rule" aria-hidden="true"></span>
			<?php if ( $eyebrow ) : ?>
				<p class="perfumes-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h1><?php echo wp_kses_post( $title ); ?></h1>
			<?php endif; ?>
			<?php if ( $hero_brand ) : ?>
				<p class="perfumes-hero__brand"><?php echo esc_html( $hero_brand ); ?></p>
			<?php endif; ?>
			<?php if ( $description ) : ?>
				<p class="perfumes-hero__description"><?php echo wp_kses_post( $description ); ?></p>
			<?php endif; ?>
			<div class="perfumes-hero__actions">
				<?php if ( $primary_cta ) : ?>
					<a class="perfumes-button perfumes-button--primary" href="#perfumes-products"><?php echo esc_html( $primary_cta ); ?></a>
				<?php endif; ?>
				<?php if ( $secondary_cta ) : ?>
					<a class="perfumes-button perfumes-button--ghost" href="#perfumes-products"><?php echo esc_html( $secondary_cta ); ?></a>
				<?php endif; ?>
				<?php if ( $hero_price ) : ?>
					<strong class="perfumes-hero__price"><?php echo esc_html( $hero_price ); ?></strong>
				<?php endif; ?>
			</div>
		</div>
		<div class="perfumes-hero__media">
			<?php if ( $hero_image_url ) : ?>
				<img src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>" />
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $brands ) : ?>
		<section class="perfumes-brand-strip">
			<p><?php echo esc_html__( 'Las marcas mas buscadas', 'perfumes' ); ?></p>
			<div>
				<?php foreach ( $brands as $brand ) : ?>
					<?php if ( $brand ) : ?>
						<span><?php echo esc_html( $brand ); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $products ) : ?>
		<section class="perfumes-products" id="perfumes-products">
			<div class="perfumes-section-heading">
				<h2><?php echo esc_html__( 'Top en ventas', 'perfumes' ); ?></h2>
				<a href="#"><?php echo esc_html__( 'Ver todos los productos', 'perfumes' ); ?></a>
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
						<div class="perfumes-product-card__image">
							<?php if ( $product_image ) : ?>
								<img src="<?php echo esc_url( $product_image ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $product_name ) ); ?>" loading="lazy" />
							<?php endif; ?>
						</div>
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
							<a class="perfumes-product-card__button" href="<?php echo esc_url( $product_id ? add_query_arg( 'add-to-cart', $product_id, get_permalink() ) : '#' ); ?>" data-perfumes-add="<?php echo esc_attr( $product_id ); ?>"><?php echo esc_html__( 'Agregar al carrito', 'perfumes' ); ?></a>
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
			<?php if ( $promo_cta ) : ?>
				<a class="perfumes-button perfumes-button--primary" href="#"><?php echo esc_html( $promo_cta ); ?></a>
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

	<footer class="perfumes-footer-preview">
		<div>
			<h2><?php echo esc_html__( 'Omar Perfumes', 'perfumes' ); ?></h2>
			<p><?php echo esc_html__( 'Fragancias originales, asesoria cercana y seleccion premium.', 'perfumes' ); ?></p>
		</div>
		<div>
			<h3><?php echo esc_html__( 'Informacion de interes', 'perfumes' ); ?></h3>
			<p><?php echo esc_html__( 'Politicas, envios, cambios y pagos seguros.', 'perfumes' ); ?></p>
		</div>
		<div>
			<h3><?php echo esc_html__( 'Soporte', 'perfumes' ); ?></h3>
			<p><?php echo esc_html__( 'ventas@omarperfumes.com.co', 'perfumes' ); ?></p>
			<p><?php echo esc_html__( '+57 314 250 8890', 'perfumes' ); ?></p>
			<p><?php echo esc_html__( 'Bogota D.C., Colombia', 'perfumes' ); ?></p>
		</div>
	</footer>

	<a class="perfumes-whatsapp" href="#" aria-label="<?php echo esc_attr__( 'WhatsApp', 'perfumes' ); ?>">WA</a>
</div>
