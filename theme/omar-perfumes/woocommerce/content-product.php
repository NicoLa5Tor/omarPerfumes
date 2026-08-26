<?php
/**
 * Same product card as the home “Top en ventas” grid.
 *
 * @package OmarPerfumes
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product || ! $product->is_visible() ) {
	return;
}

$in_stock = $product->is_in_stock();
$prefix   = 'perfumes-product-card';
$categories = wc_get_product_category_list( $product->get_id(), ', ' );
$label    = function_exists( 'omar_perfumes_product_badge' ) ? omar_perfumes_product_badge( $product ) : '';
?>
<li <?php wc_product_class( $prefix, $product ); ?> data-product-card>
	<?php if ( $label ) : ?>
		<span class="<?php echo esc_attr( $prefix ); ?>__badge<?php echo $in_stock ? '' : ' ' . esc_attr( $prefix ) . '__badge--out'; ?>"><?php echo esc_html( $label ); ?></span>
	<?php endif; ?>
	<a class="<?php echo esc_attr( $prefix ); ?>__media" href="<?php echo esc_url( $product->get_permalink() ); ?>">
		<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) ); ?>
	</a>
	<div class="<?php echo esc_attr( $prefix ); ?>__body">
		<span class="<?php echo esc_attr( $prefix ); ?>__category"><?php echo $categories ? wp_kses_post( $categories ) : '&nbsp;'; ?></span>
		<h3 class="<?php echo esc_attr( $prefix ); ?>__title">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</h3>
		<?php
		if ( function_exists( 'omar_perfumes_star_rating_markup' ) ) {
			echo omar_perfumes_star_rating_markup( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
		<div class="<?php echo esc_attr( $prefix ); ?>__footer">
			<div class="<?php echo esc_attr( $prefix ); ?>__price-row">
				<strong class="price <?php echo esc_attr( $prefix ); ?>__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></strong>
			</div>
			<?php if ( $in_stock ) : ?>
				<?php woocommerce_template_loop_add_to_cart(); ?>
			<?php else : ?>
				<a class="button <?php echo esc_attr( $prefix ); ?>__button" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'Ver producto', 'omar-perfumes' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</li>
