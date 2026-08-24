<?php
/**
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

$eyebrow           = $attributes['eyebrow'] ?? '';
$title             = $attributes['title'] ?? '';
$description       = $attributes['description'] ?? '';
$primary_cta       = $attributes['primaryCta'] ?? '';
$secondary_cta     = $attributes['secondaryCta'] ?? '';
$hero_image_url    = $attributes['heroImageUrl'] ?? '';
$categories        = is_array( $attributes['categories'] ?? null ) ? $attributes['categories'] : array();
$products          = is_array( $attributes['products'] ?? null ) ? $attributes['products'] : array();
$promo_title       = $attributes['promoTitle'] ?? '';
$promo_description = $attributes['promoDescription'] ?? '';
$promo_cta         = $attributes['promoCta'] ?? '';
$promo_image_url   = $attributes['promoImageUrl'] ?? '';
$benefits          = is_array( $attributes['benefits'] ?? null ) ? $attributes['benefits'] : array();

$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'perfumes-landing' ) );
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<section class="perfumes-hero">
		<div class="perfumes-hero__content">
			<?php if ( $eyebrow ) : ?>
				<p class="perfumes-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h1><?php echo wp_kses_post( $title ); ?></h1>
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
			</div>
		</div>
		<div class="perfumes-hero__media">
			<?php if ( $hero_image_url ) : ?>
				<img src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>" />
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $categories ) : ?>
		<nav class="perfumes-category-nav" aria-label="<?php echo esc_attr__( 'Categorias destacadas', 'perfumes' ); ?>">
			<?php foreach ( $categories as $category ) : ?>
				<?php if ( $category ) : ?>
					<span><?php echo esc_html( $category ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php if ( $products ) : ?>
		<section class="perfumes-products" id="perfumes-products">
			<div class="perfumes-section-heading">
				<p><?php echo esc_html__( 'Seleccionados', 'perfumes' ); ?></p>
				<h2><?php echo esc_html__( 'Top en ventas', 'perfumes' ); ?></h2>
			</div>
			<div class="perfumes-product-grid">
				<?php foreach ( $products as $product ) : ?>
					<?php
					$product_badge = $product['badge'] ?? '';
					$product_name  = $product['name'] ?? '';
					$product_brand = $product['brand'] ?? '';
					$product_price = $product['price'] ?? '';
					$product_image = $product['imageUrl'] ?? '';
					?>
					<article class="perfumes-product-card">
						<div class="perfumes-product-card__image">
							<?php if ( $product_image ) : ?>
								<img src="<?php echo esc_url( $product_image ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $product_name ) ); ?>" loading="lazy" />
							<?php endif; ?>
						</div>
						<?php if ( $product_badge ) : ?>
							<span class="perfumes-badge"><?php echo esc_html( $product_badge ); ?></span>
						<?php endif; ?>
						<?php if ( $product_brand ) : ?>
							<p class="perfumes-product-card__brand"><?php echo esc_html( $product_brand ); ?></p>
						<?php endif; ?>
						<?php if ( $product_name ) : ?>
							<h3><?php echo wp_kses_post( $product_name ); ?></h3>
						<?php endif; ?>
						<?php if ( $product_price ) : ?>
							<strong class="perfumes-product-card__price"><?php echo esc_html( $product_price ); ?></strong>
						<?php endif; ?>
						<a class="perfumes-product-card__button" href="#"><?php echo esc_html__( 'Agregar al carrito', 'perfumes' ); ?></a>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<section class="perfumes-promo">
		<div>
			<p class="perfumes-eyebrow"><?php echo esc_html__( 'Addi', 'perfumes' ); ?></p>
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
			<h3><?php echo esc_html__( 'Contacto', 'perfumes' ); ?></h3>
			<p><?php echo esc_html__( 'Bogota, Colombia', 'perfumes' ); ?></p>
		</div>
	</footer>
</div>
