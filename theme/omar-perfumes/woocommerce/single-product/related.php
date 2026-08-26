<?php
/**
 * Related products carousel on the single product page.
 *
 * @package OmarPerfumes
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $related_products ) ) {
	return;
}

$heading = apply_filters( 'woocommerce_product_related_products_heading', __( 'Productos relacionados', 'omar-perfumes' ) );
?>
<section class="related products perfumes-related">
	<div class="perfumes-related__header">
		<?php if ( $heading ) : ?>
			<h2><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>
		<div class="perfumes-related__controls">
			<button type="button" class="perfumes-related__btn" data-related-prev aria-label="<?php esc_attr_e( 'Productos anteriores', 'omar-perfumes' ); ?>"></button>
			<button type="button" class="perfumes-related__btn perfumes-related__btn--next" data-related-next aria-label="<?php esc_attr_e( 'Productos siguientes', 'omar-perfumes' ); ?>"></button>
		</div>
	</div>
	<div class="perfumes-related__viewport">
		<?php woocommerce_product_loop_start(); ?>
			<?php foreach ( $related_products as $related_product ) : ?>
				<?php
				$post_object = get_post( $related_product->get_id() );
				setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
				wc_get_template_part( 'content', 'product' );
				?>
			<?php endforeach; ?>
		<?php woocommerce_product_loop_end(); ?>
	</div>
</section>
<?php
wp_reset_postdata();
