<?php
/**
 * Single product card for Omar Perfumes.
 *
 * @package OmarPerfumes
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

$gallery_ids = array_values(
	array_filter(
		array_merge(
			array( $product->get_image_id() ),
			$product->get_gallery_image_ids()
		)
	)
);
$main_image  = $gallery_ids ? wp_get_attachment_image_url( $gallery_ids[0], 'full' ) : wc_placeholder_img_src( 'full' );
$short       = $product->get_short_description();
$long        = $product->get_description();
$excerpt     = $short ? wp_strip_all_tags( $short ) : ( $long ? wp_trim_words( wp_strip_all_tags( $long ), 18 ) : __( 'Perfume original.', 'omar-perfumes' ) );
$watermark   = 'OMAR';
$volume      = '';
if ( preg_match( '/(\d+)\s*ML/i', $product->get_name(), $volume_match ) ) {
	$volume = $volume_match[1] . ' ML';
}
$sizes       = array( '50 ML', '80 ML', '90 ML', '100 ML' );
$thumb_ids   = $gallery_ids ? $gallery_ids : array();
while ( count( $thumb_ids ) < 3 && $gallery_ids ) {
	$thumb_ids[] = $gallery_ids[0];
}
$whatsapp    = 'https://wa.me/573142508890?text=' . rawurlencode( sprintf( __( 'Hola, me interesa %s', 'omar-perfumes' ), $product->get_name() ) );

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'perfumes-pdp', $product ); ?>>
	<div class="perfumes-pdp__card">
		<div class="perfumes-pdp__media">
			<?php if ( $thumb_ids ) : ?>
				<div class="perfumes-pdp__thumbs">
					<?php foreach ( $thumb_ids as $index => $attachment_id ) : ?>
						<button
							type="button"
							class="perfumes-pdp__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
							data-src="<?php echo esc_url( wp_get_attachment_image_url( $attachment_id, 'full' ) ); ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Imagen %d', 'omar-perfumes' ), $index + 1 ) ); ?>"
						></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<img
				class="perfumes-pdp__image"
				src="<?php echo esc_url( $main_image ); ?>"
				alt="<?php echo esc_attr( $product->get_name() ); ?>"
			/>
			<div class="perfumes-pdp__watermark"><?php echo esc_html( $watermark ); ?></div>
			<div class="perfumes-pdp__spark" aria-hidden="true">✦</div>
		</div>

		<div class="perfumes-pdp__panel">
			<div class="perfumes-pdp__meta">
				<span class="perfumes-pdp__stars" aria-hidden="true">★★★★★</span>
				<span class="perfumes-pdp__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</div>

			<h1 class="product_title entry-title perfumes-pdp__title"><?php echo esc_html( $product->get_name() ); ?></h1>
			<p class="perfumes-pdp__subtitle"><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></p>

			<h3 class="perfumes-pdp__label"><?php esc_html_e( 'Color:', 'omar-perfumes' ); ?></h3>
			<div class="perfumes-pdp__swatches" aria-hidden="true">
				<span class="perfumes-pdp__swatch"></span>
				<span class="perfumes-pdp__swatch is-selected"></span>
				<span class="perfumes-pdp__swatch"></span>
				<span class="perfumes-pdp__swatch"></span>
			</div>

			<?php if ( $volume ) : ?>
				<h3 class="perfumes-pdp__label"><?php esc_html_e( 'Tamaño:', 'omar-perfumes' ); ?></h3>
				<div class="perfumes-pdp__sizes">
					<?php foreach ( $sizes as $size ) : ?>
						<span class="perfumes-pdp__size<?php echo $size === $volume ? ' is-selected' : ''; ?>"><?php echo esc_html( str_replace( ' ML', '', $size ) ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="perfumes-pdp__cart">
				<?php woocommerce_template_single_add_to_cart(); ?>
			</div>
			<a class="perfumes-pdp__wishlist" href="<?php echo esc_url( $whatsapp ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Add to Wishlist', 'omar-perfumes' ); ?></a>
		</div>
	</div>

	<?php if ( $long ) : ?>
		<section class="perfumes-pdp__details">
			<h2><?php esc_html_e( 'Descripción', 'omar-perfumes' ); ?></h2>
			<?php echo wp_kses_post( wc_format_content( $long ) ); ?>
		</section>
	<?php endif; ?>

	<?php
	woocommerce_output_related_products();
	?>
</div>
<?php
do_action( 'woocommerce_after_single_product' );
