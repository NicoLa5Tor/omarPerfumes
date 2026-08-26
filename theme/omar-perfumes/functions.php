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

function omar_perfumes_pdp_assets() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$path = get_theme_file_path( 'assets/pdp.js' );
	wp_enqueue_script(
		'omar-perfumes-pdp',
		get_theme_file_uri( 'assets/pdp.js' ),
		array(),
		file_exists( $path ) ? filemtime( $path ) : wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'omar_perfumes_pdp_assets', 25 );

/** Restrict the global storefront search to WooCommerce products. */
function omar_perfumes_product_search( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() && ! $query->get( 'post_type' ) ) {
		$query->set( 'post_type', 'product' );
	}
}
add_action( 'pre_get_posts', 'omar_perfumes_product_search' );

function omar_perfumes_body_class( $classes ) {
	$classes[] = 'omar-perfumes-site';
	if ( is_front_page() && ! is_search() ) {
		$classes[] = 'omar-initial-entry';
	}
	return $classes;
}
add_filter( 'body_class', 'omar_perfumes_body_class' );

/**
 * Allow the client router to be disabled instantly without reverting a deploy.
 */
function omar_perfumes_client_navigation_enabled() {
	if ( defined( 'OMAR_CLIENT_NAVIGATION' ) ) {
		return (bool) OMAR_CLIENT_NAVIGATION;
	}

	return (bool) apply_filters( 'omar_perfumes_client_navigation_enabled', true );
}

/**
 * Return the stable route type consumed by the persistent site chrome.
 */
function omar_perfumes_route_type() {
	if ( is_front_page() && ! is_search() ) {
		return 'home';
	}
	if ( is_search() ) {
		return 'search';
	}
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return 'catalog';
	}
	if ( is_tax( 'product_cat' ) ) {
		return 'category';
	}
	if ( function_exists( 'is_product' ) && is_product() ) {
		return 'product';
	}
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return 'cart';
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return 'checkout';
	}
	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		return 'account';
	}

	return 'page';
}

/**
 * Keep only route-related body classes. Persistent session/UI classes must not
 * be replaced when the Interactivity Router updates the main region.
 */
function omar_perfumes_route_body_classes() {
	$exact = array(
		'home',
		'archive',
		'search',
		'single-product',
		'tax-product_cat',
		'woocommerce',
		'woocommerce-page',
		'woocommerce-shop',
	);

	return array_values(
		array_filter(
			get_body_class(),
			static function ( $class_name ) use ( $exact ) {
				return in_array( $class_name, $exact, true )
					|| 0 === strpos( $class_name, 'post-type-archive' )
					|| 0 === strpos( $class_name, 'term-' );
			}
		)
	);
}

/**
 * Enqueue the progressive router on every public page. Every destination still
 * renders complete HTML, so disabling JavaScript keeps normal navigation.
 */
function omar_perfumes_client_navigation_assets() {
	if ( ! omar_perfumes_client_navigation_enabled() || ! function_exists( 'wp_enqueue_script_module' ) ) {
		return;
	}

	$path = get_theme_file_path( 'assets/router.js' );
	wp_enqueue_script_module(
		'omar-perfumes-router',
		get_theme_file_uri( 'assets/router.js' ),
		array(
			'@wordpress/interactivity',
			array(
				'id'     => '@wordpress/interactivity-router',
				'import' => 'dynamic',
			),
		),
		file_exists( $path ) ? filemtime( $path ) : wp_get_theme()->get( 'Version' ),
		array( 'in_footer' => true )
	);

	if ( function_exists( 'wp_interactivity' ) ) {
		wp_interactivity()->add_client_navigation_support_to_script_module( 'omar-perfumes-router' );
	}

	if ( function_exists( 'wp_interactivity_state' ) ) {
		wp_interactivity_state(
			'omar/router',
			array(
				'enabled'          => true,
				'isHome'           => 'home' === omar_perfumes_route_type(),
				'routeType'        => omar_perfumes_route_type(),
				'routeBodyClasses' => omar_perfumes_route_body_classes(),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'omar_perfumes_client_navigation_assets', 30 );

/**
 * Add router directives to the persistent chrome and the replaceable main.
 * The filter is deliberately limited to Omar's named Group blocks.
 */
function omar_perfumes_router_block_attributes( $block_content, $block ) {
	if ( ! omar_perfumes_client_navigation_enabled() || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$class_name = $block['attrs']['className'] ?? '';
	$is_header  = false !== strpos( $class_name, 'perfumes-global-header' );
	$is_footer  = false !== strpos( $class_name, 'perfumes-footer-preview' );
	$is_main    = 'main' === ( $block['attrs']['tagName'] ?? '' );

	if ( ! $is_header && ! $is_footer && ! $is_main ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( ! $processor->next_tag() ) {
		return $block_content;
	}

	$processor->set_attribute( 'data-wp-interactive', 'omar/router' );
	$processor->set_attribute( 'data-wp-on--click', 'actions.navigate' );

	if ( $is_header ) {
		$processor->set_attribute( 'data-wp-on--submit', 'actions.search' );
		$processor->set_attribute( 'data-wp-class--is-home-route', 'state.isHome' );
		$processor->set_attribute( 'data-wp-watch', 'callbacks.syncRoute' );
		if ( 'home' === omar_perfumes_route_type() ) {
			$processor->add_class( 'is-home-route' );
		}
	}

	if ( $is_main ) {
		$processor->set_attribute( 'data-wp-router-region', 'omar/router/main' );
		$processor->set_attribute( 'data-wp-key', 'route-' . omar_perfumes_route_type() );
	}

	return $processor->get_updated_html();
}
add_filter( 'render_block', 'omar_perfumes_router_block_attributes', 9, 2 );

/**
 * Ask WooCommerce for more related products so the PDP carousel can scroll.
 */
function omar_perfumes_related_products_args( $args ) {
	$args['posts_per_page'] = 12;
	$args['columns']        = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'omar_perfumes_related_products_args' );

/**
 * Prefer the same brand category, then the parent family, ordered by sales.
 *
 * @param int[] $related_posts Related product IDs from WooCommerce.
 * @param int   $product_id    Current product ID.
 * @param array $args          Query args including limit.
 * @return int[]
 */
function omar_perfumes_related_products( $related_posts, $product_id, $args ) {
	$limit = isset( $args['limit'] ) ? max( 1, (int) $args['limit'] ) : 12;
	$ids   = omar_perfumes_related_product_ids( (int) $product_id, $limit );

	return $ids ? $ids : $related_posts;
}
add_filter( 'woocommerce_related_products', 'omar_perfumes_related_products', 10, 3 );
add_filter( 'woocommerce_product_related_posts_relate_by_tag', '__return_false' );

/**
 * @param int $product_id Current product.
 * @param int $limit      How many related IDs to return.
 * @return int[]
 */
function omar_perfumes_related_product_ids( $product_id, $limit ) {
	$exclude  = array( $product_id );
	$term_ids = function_exists( 'wc_get_product_term_ids' ) ? wc_get_product_term_ids( $product_id, 'product_cat' ) : array();
	if ( ! $term_ids ) {
		return array();
	}

	$brand_ids  = array();
	$family_ids = array();

	foreach ( $term_ids as $term_id ) {
		$term = get_term( (int) $term_id, 'product_cat' );
		if ( ! $term || is_wp_error( $term ) || 'uncategorized' === $term->slug ) {
			continue;
		}

		if ( $term->parent ) {
			$brand_ids[]  = (int) $term->term_id;
			$family_ids[] = (int) $term->parent;
		} else {
			$family_ids[] = (int) $term->term_id;
		}
	}

	$brand_ids  = array_values( array_unique( $brand_ids ) );
	$family_ids = array_values( array_unique( $family_ids ) );
	$found      = omar_perfumes_query_related_ids( $brand_ids, $exclude, $limit );

	if ( count( $found ) < $limit ) {
		$found = array_merge(
			$found,
			omar_perfumes_query_related_ids( $family_ids, array_merge( $exclude, $found ), $limit - count( $found ) )
		);
	}

	return array_slice( $found, 0, $limit );
}

/**
 * @param int[] $term_ids Category term IDs.
 * @param int[] $exclude  Product IDs to skip.
 * @param int   $limit    Max results.
 * @return int[]
 */
function omar_perfumes_query_related_ids( $term_ids, $exclude, $limit ) {
	if ( empty( $term_ids ) || $limit < 1 || ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$slugs = array();
	foreach ( $term_ids as $term_id ) {
		$term = get_term( (int) $term_id, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) && ! empty( $term->slug ) ) {
			$slugs[] = $term->slug;
		}
	}

	if ( ! $slugs ) {
		return array();
	}

	$ids = wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => $limit,
			'exclude'  => array_values( array_filter( array_map( 'intval', $exclude ) ) ),
			'orderby'  => 'popularity',
			'order'    => 'DESC',
			'return'   => 'ids',
			'category' => $slugs,
		)
	);

	return array_map( 'intval', (array) $ids );
}

/**
 * Animated add-to-cart control used on product cards and the product page.
 *
 * @param int    $product_id Product ID.
 * @param string $url        Add-to-cart URL.
 * @param string $name       Product name for the aria-label.
 * @param bool   $as_submit  Whether to render a submit button for the single product form.
 * @return string
 */
function omar_perfumes_add_to_cart_button( $product_id, $url, $name = '', $as_submit = false ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 || ( ! $as_submit && ! $url ) ) {
		return '';
	}

	$label = sprintf(
		/* translators: %s: product name */
		__( 'Añadir %s al carrito', 'omar-perfumes' ),
		wp_strip_all_tags( $name )
	);

	ob_start();
	if ( $as_submit ) :
		?>
	<button
		type="submit"
		name="add-to-cart"
		value="<?php echo esc_attr( (string) $product_id ); ?>"
		class="button alt add-to-cart-button single_add_to_cart_button"
		data-product_id="<?php echo esc_attr( (string) $product_id ); ?>"
		data-quantity="1"
		aria-label="<?php echo esc_attr( $label ); ?>"
	>
		<?php else : ?>
	<a
		class="button add-to-cart-button add_to_cart_button ajax_add_to_cart product_type_simple"
		href="<?php echo esc_url( $url ); ?>"
		data-product_id="<?php echo esc_attr( (string) $product_id ); ?>"
		data-quantity="1"
		aria-label="<?php echo esc_attr( $label ); ?>"
		rel="nofollow"
	>
		<?php endif; ?>
		<span class="add-to-cart-button__label add-to-cart-button__label--idle"><?php esc_html_e( 'Añadir', 'omar-perfumes' ); ?></span>
		<span class="add-to-cart-button__label add-to-cart-button__label--added"><?php esc_html_e( 'Ver carrito', 'omar-perfumes' ); ?></span>
		<span class="add-to-cart-button__cart" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
				<path fill="currentColor" d="M0 24C0 10.7 10.7 0 24 0h45.5c22 0 41.5 12.8 50.6 32H531c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H170.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24 10.7 24 24s-10.7 24-24 24H199.7c-34.6 0-64.3-24.6-70.7-58.5L77.4 54.5c-.7-3.8-4-6.5-7.9-6.5H24C10.7 48 0 37.3 0 24zm128 440a48 48 0 1 1 96 0 48 48 0 1 1-96 0zm336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z" />
			</svg>
		</span>
		<span class="add-to-cart-button__box" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
				<path fill="currentColor" d="M50.7 58.5 0 160h448L397.3 58.5C387.8 41.6 369.7 32 350.1 32H97.9c-19.6 0-37.7 9.6-47.2 26.5zM0 192v272c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V192H0z" />
			</svg>
		</span>
	<?php if ( $as_submit ) : ?>
	</button>
		<?php else : ?>
	</a>
		<?php endif; ?>
	<?php
	return (string) ob_get_clean();
}

/**
 * Swap the loop add-to-cart link for the animated card button.
 *
 * @param string     $html    Default markup.
 * @param WC_Product $product Product.
 * @return string
 */
function omar_perfumes_loop_add_to_cart_link( $html, $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_purchasable() || ! $product->is_in_stock() || ! $product->is_type( 'simple' ) ) {
		return $html;
	}

	return omar_perfumes_add_to_cart_button(
		$product->get_id(),
		$product->add_to_cart_url(),
		$product->get_name()
	);
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'omar_perfumes_loop_add_to_cart_link', 10, 2 );

function omar_perfumes_cart_button_assets() {
	$deps = array( 'jquery' );
	if ( wp_script_is( 'wc-add-to-cart', 'registered' ) ) {
		wp_enqueue_script( 'wc-add-to-cart' );
		$deps[] = 'wc-add-to-cart';
	}

	$path = get_theme_file_path( 'assets/cart-button.js' );
	wp_enqueue_script(
		'omar-perfumes-cart-button',
		get_theme_file_uri( 'assets/cart-button.js' ),
		$deps,
		file_exists( $path ) ? filemtime( $path ) : wp_get_theme()->get( 'Version' ),
		true
	);
	wp_localize_script(
		'omar-perfumes-cart-button',
		'omarPerfumesCart',
		array(
			'cartUrl'  => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/carrito/' ),
			'viewCart' => __( 'Ver carrito', 'omar-perfumes' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'omar_perfumes_cart_button_assets', 26 );
