<?php
/**
 * Product card used by shop, product-category and related-product loops.
 *
 * @package OmarPerfumes
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
	return;
}

$in_stock   = $product->is_in_stock();
$categories = wc_get_product_category_list( $product->get_id(), ', ' );
$rating     = min( 5, max( 0, (float) $product->get_average_rating() ) );

if ( ! $in_stock ) {
	$label = __( 'Agotado', 'omar-perfumes' );
} elseif ( $product->is_on_sale() ) {
	$label = __( 'Oferta', 'omar-perfumes' );
} else {
	$label = __( 'Más vendido', 'omar-perfumes' );
}
?>
<li <?php wc_product_class( 'perfumes-catalog-card', $product ); ?>>
	<span class="perfumes-catalog-card__badge<?php echo $in_stock ? '' : ' perfumes-catalog-card__badge--out'; ?>"><?php echo esc_html( $label ); ?></span>
	<a class="perfumes-catalog-card__media" href="<?php echo esc_url( $product->get_permalink() ); ?>">
		<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) ); ?>
	</a>
	<div class="perfumes-catalog-card__body">
		<?php if ( $categories ) : ?>
			<span class="perfumes-catalog-card__category"><?php echo wp_kses_post( $categories ); ?></span>
		<?php endif; ?>
		<h2 class="woocommerce-loop-product__title perfumes-catalog-card__title">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</h2>
		<div class="perfumes-catalog-card__ratings" aria-label="<?php echo esc_attr( sprintf( __( 'Valoración: %s de 5', 'omar-perfumes' ), number_format_i18n( $rating, 1 ) ) ); ?>">
			<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
				<span class="<?php echo $star <= round( $rating ) ? 'is-active' : ''; ?>" aria-hidden="true">★</span>
			<?php endfor; ?>
		</div>
		<div class="perfumes-catalog-card__footer">
			<div class="perfumes-catalog-card__price-row">
				<span class="price perfumes-catalog-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</div>
			<?php if ( $in_stock ) : ?>
				<?php woocommerce_template_loop_add_to_cart(); ?>
			<?php else : ?>
				<a class="button perfumes-catalog-card__button" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'Ver producto', 'omar-perfumes' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</li>
