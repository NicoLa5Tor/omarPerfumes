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
	array_unique(
		array_filter(
			array_merge(
				array( $product->get_image_id() ),
				$product->get_gallery_image_ids()
			)
		)
	)
);
$main_image = $gallery_ids ? wp_get_attachment_image_url( $gallery_ids[0], 'full' ) : wc_placeholder_img_src( 'full' );
$short      = $product->get_short_description();
$long       = $product->get_description();
$excerpt    = $short ? wp_strip_all_tags( $short ) : ( $long ? wp_trim_words( wp_strip_all_tags( $long ), 28 ) : __( 'Perfume original.', 'omar-perfumes' ) );
$logo_url   = get_theme_file_uri( 'assets/omar-logo-light-v1.png' );

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
				<div class="perfumes-pdp__thumbs" role="tablist" aria-label="<?php esc_attr_e( 'Galería del producto', 'omar-perfumes' ); ?>">
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
			<img
				class="perfumes-pdp__watermark"
				src="<?php echo esc_url( $logo_url ); ?>"
				alt=""
				width="900"
				height="277"
				aria-hidden="true"
			/>
			<div class="perfumes-pdp__spark" aria-hidden="true">✦</div>
		</div>

		<div class="perfumes-pdp__panel">
			<div class="perfumes-pdp__rating">
				<?php
				if ( function_exists( 'omar_perfumes_star_rating_markup' ) ) {
					echo omar_perfumes_star_rating_markup( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$product,
						array(
							'class'      => 'perfumes-pdp__stars',
							'href'       => '#reviews',
							'show_count' => true,
						)
					);
				}
				?>
			</div>

			<div class="perfumes-pdp__price-row">
				<span class="perfumes-pdp__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</div>

			<h1 class="product_title entry-title perfumes-pdp__title"><?php echo esc_html( $product->get_name() ); ?></h1>
			<p class="perfumes-pdp__subtitle"><?php echo esc_html( $excerpt ); ?></p>

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
	global $post, $withcomments;
	$withcomments   = true;
	$product_post   = get_post( $product->get_id() );
	if ( $product_post instanceof WP_Post ) {
		$post = $product_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
	}
	if ( function_exists( 'wc_get_template' ) ) {
		wc_get_template( 'single-product-reviews.php' );
	} else {
		comments_template();
	}
	if ( $product_post instanceof WP_Post ) {
		wp_reset_postdata();
	}
	?>

	<?php
	woocommerce_output_related_products();
	?>
</div>
<?php
do_action( 'woocommerce_after_single_product' );
