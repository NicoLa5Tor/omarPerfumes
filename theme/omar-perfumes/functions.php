<?php
/**
 * Omar Perfumes theme setup.
 *
 * @package OmarPerfumes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function omar_perfumes_setup() {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/theme.css' );
}
add_action( 'after_setup_theme', 'omar_perfumes_setup' );

function omar_perfumes_assets() {
	$path = get_theme_file_path( 'assets/theme.css' );
	wp_enqueue_style(
		'omar-perfumes',
		get_theme_file_uri( 'assets/theme.css' ),
		array(),
		file_exists( $path ) ? filemtime( $path ) : wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'omar_perfumes_assets', 20 );

/** Restrict the global storefront search to WooCommerce products. */
function omar_perfumes_product_search( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() && ! $query->get( 'post_type' ) ) {
		$query->set( 'post_type', 'product' );
	}
}
add_action( 'pre_get_posts', 'omar_perfumes_product_search' );

function omar_perfumes_body_class( $classes ) {
	$classes[] = 'omar-perfumes-site';
	return $classes;
}
add_filter( 'body_class', 'omar_perfumes_body_class' );

/** Replace WooCommerce's default product loop fragments with the Omar card. */
function omar_perfumes_register_product_card() {
	if ( is_admin() || ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
	remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

	add_action( 'woocommerce_before_shop_loop_item', 'omar_perfumes_product_card_open', 10 );
	add_action( 'woocommerce_shop_loop_item_title', 'omar_perfumes_product_card_title', 10 );
	add_action( 'woocommerce_after_shop_loop_item_title', 'omar_perfumes_product_card_rating', 10 );
	add_action( 'woocommerce_after_shop_loop_item', 'omar_perfumes_product_card_close', 10 );
}
add_action( 'wp', 'omar_perfumes_register_product_card' );

/** Render the badge, image and category at the start of a product card. */
function omar_perfumes_product_card_open() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$label      = $product->is_on_sale() ? __( 'Oferta', 'omar-perfumes' ) : __( 'Más vendido', 'omar-perfumes' );
	$categories = wc_get_product_category_list( $product->get_id(), ', ' );
	?>
	<span class="perfumes-catalog-card__badge"><?php echo esc_html( $label ); ?></span>
	<a class="perfumes-catalog-card__media" href="<?php echo esc_url( $product->get_permalink() ); ?>">
		<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) ); ?>
	</a>
	<div class="perfumes-catalog-card__body">
		<?php if ( $categories ) : ?>
			<span class="perfumes-catalog-card__category"><?php echo wp_kses_post( $categories ); ?></span>
		<?php endif; ?>
	<?php
}

/** Render the linked product name. */
function omar_perfumes_product_card_title() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}
	?>
	<h2 class="woocommerce-loop-product__title perfumes-catalog-card__title">
		<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
	</h2>
	<?php
}

/** Render a stable five-star rating row, including unrated products. */
function omar_perfumes_product_card_rating() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$rating = min( 5, max( 0, (float) $product->get_average_rating() ) );
	?>
	<div class="perfumes-catalog-card__ratings" aria-label="<?php echo esc_attr( sprintf( __( 'Valoración: %s de 5', 'omar-perfumes' ), number_format_i18n( $rating, 1 ) ) ); ?>">
		<?php for ( $star = 1; $star <= 5; $star++ ) : ?>
			<span class="<?php echo $star <= round( $rating ) ? 'is-active' : ''; ?>" aria-hidden="true">★</span>
		<?php endfor; ?>
	</div>
	<?php
}

/** Render the price/button footer and close the product card body. */
function omar_perfumes_product_card_close() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}
	?>
		<div class="perfumes-catalog-card__footer">
			<span class="price perfumes-catalog-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			<?php woocommerce_template_loop_add_to_cart(); ?>
		</div>
	</div>
	<?php
}
