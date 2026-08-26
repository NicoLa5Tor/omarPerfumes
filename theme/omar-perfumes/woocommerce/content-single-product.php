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
$rating      = min( 5, max( 0, (float) $product->get_average_rating() ) );
$short       = $product->get_short_description();
$long        = $product->get_description();
$excerpt     = $short ? $short : ( $long ? wp_trim_words( wp_strip_all_tags( $long ), 22 ) : '' );
$watermark   = strtoupper( wp_strip_all_tags( $product->get_name() ) );
$watermark   = function_exists( 'mb_substr' ) ? mb_substr( $watermark, 0, 8 ) : substr( $watermark, 0, 8 );

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'perfumes-pdp', $product ); ?>>
	<div class="perfumes-pdp__card">
		<div class="perfumes-pdp__media">
			<?php if ( count( $gallery_ids ) > 1 ) : ?>
				<div class="perfumes-pdp__thumbs">
					<?php foreach ( $gallery_ids as $index => $attachment_id ) : ?>
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
				<span class="perfumes-pdp__stars" aria-label="<?php echo esc_attr( sprintf( __( 'Valoración: %s de 5', 'omar-perfumes' ), number_format_i18n( $rating, 1 ) ) ); ?>">
					<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
						<span class="<?php echo $star <= max( 1, (int) round( $rating ) ) ? 'is-active' : ''; ?>" aria-hidden="true">★</span>
					<?php endfor; ?>
				</span>
				<span class="perfumes-pdp__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</div>

			<h1 class="product_title entry-title perfumes-pdp__title"><?php echo esc_html( $product->get_name() ); ?></h1>

			<?php if ( $excerpt ) : ?>
				<div class="perfumes-pdp__excerpt"><?php echo wp_kses_post( $short ? wc_format_content( $short ) : esc_html( $excerpt ) ); ?></div>
			<?php endif; ?>

			<div class="perfumes-pdp__cart">
				<?php woocommerce_template_single_add_to_cart(); ?>
			</div>
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
