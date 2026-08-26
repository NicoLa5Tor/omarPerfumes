<?php
/**
 * Product card used by shop, categories and related loops.
 *
 * @package OmarPerfumes
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
	return;
}

$in_stock    = $product->is_in_stock();
$is_featured = 'related' === wc_get_loop_prop( 'name' );
$prefix      = 'perfumes-product-card';
$categories  = wc_get_product_category_list( $product->get_id(), ', ' );
$rating      = min( 5, max( 0, (float) $product->get_average_rating() ) );
$label       = '';

if ( ! $in_stock ) {
	$label = __( 'Agotado', 'omar-perfumes' );
} elseif ( $product->is_on_sale() ) {
	$label = __( 'Oferta', 'omar-perfumes' );
} elseif ( $is_featured ) {
	$label = __( 'Más vendido', 'omar-perfumes' );
}
?>
<li <?php wc_product_class( $prefix, $product ); ?>>
	<?php if ( $label ) : ?>
		<span class="<?php echo esc_attr( $prefix ); ?>__badge<?php echo $in_stock ? '' : ' ' . esc_attr( $prefix ) . '__badge--out'; ?>"><?php echo esc_html( $label ); ?></span>
	<?php endif; ?>
	<a class="<?php echo esc_attr( $prefix ); ?>__media" href="<?php echo esc_url( $product->get_permalink() ); ?>">
		<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) ); ?>
	</a>
	<div class="<?php echo esc_attr( $prefix ); ?>__body">
		<span class="<?php echo esc_attr( $prefix ); ?>__category"><?php echo $categories ? wp_kses_post( $categories ) : '&nbsp;'; ?></span>
		<h2 class="woocommerce-loop-product__title <?php echo esc_attr( $prefix ); ?>__title">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</h2>
		<div class="<?php echo esc_attr( $prefix ); ?>__ratings" aria-label="<?php echo esc_attr( sprintf( __( 'Valoración: %s de 5', 'omar-perfumes' ), number_format_i18n( $rating, 1 ) ) ); ?>">
			<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
				<span class="<?php echo $star <= round( $rating ) ? 'is-active' : ''; ?>" aria-hidden="true">★</span>
			<?php endfor; ?>
		</div>
		<div class="<?php echo esc_attr( $prefix ); ?>__footer">
			<div class="<?php echo esc_attr( $prefix ); ?>__price-row">
				<span class="price <?php echo esc_attr( $prefix ); ?>__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</div>
			<?php if ( $in_stock ) : ?>
				<?php woocommerce_template_loop_add_to_cart(); ?>
			<?php else : ?>
				<a class="button <?php echo esc_attr( $prefix ); ?>__button" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'Ver producto', 'omar-perfumes' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</li>
