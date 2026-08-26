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
 * Animated add-to-cart control used on product cards.
 *
 * @param int    $product_id Product ID.
 * @param string $url        Add-to-cart URL.
 * @param string $name       Product name for the aria-label.
 * @return string
 */
function omar_perfumes_add_to_cart_button( $product_id, $url, $name = '' ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 || ! $url ) {
		return '';
	}

	$label = sprintf(
		/* translators: %s: product name */
		__( 'Añadir %s al carrito', 'omar-perfumes' ),
		wp_strip_all_tags( $name )
	);

	ob_start();
	?>
	<a
		class="button add-to-cart-button add_to_cart_button ajax_add_to_cart product_type_simple"
		href="<?php echo esc_url( $url ); ?>"
		data-product_id="<?php echo esc_attr( (string) $product_id ); ?>"
		data-quantity="1"
		aria-label="<?php echo esc_attr( $label ); ?>"
		rel="nofollow"
	>
		<span class="add-to-cart-button__label add-to-cart-button__label--idle"><?php esc_html_e( 'Añadir', 'omar-perfumes' ); ?></span>
		<span class="add-to-cart-button__label add-to-cart-button__label--added"><?php esc_html_e( 'Añadido', 'omar-perfumes' ); ?></span>
		<span class="add-to-cart-button__cart" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
				<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10V6a3 3 0 0 1 3-3 3 3 0 0 1 3 3v4m3-2 .917 11.923A1 1 0 0 1 17.92 21H6.08a1 1 0 0 1-.997-1.077L6 8h12Z" />
			</svg>
		</span>
		<span class="add-to-cart-button__box" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
				<path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M21 8 12 3 3 8l9 5 9-5Z" />
				<path stroke="currentColor" stroke-linejoin="round" stroke-width="2" d="M3 8v8l9 5 9-5V8" />
				<path stroke="currentColor" stroke-width="2" d="M12 13v8" />
			</svg>
		</span>
	</a>
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
	if ( wp_script_is( 'wc-add-to-cart', 'registered' ) ) {
		wp_enqueue_script( 'wc-add-to-cart' );
	}

	$path = get_theme_file_path( 'assets/cart-button.js' );
	wp_enqueue_script(
		'omar-perfumes-cart-button',
		get_theme_file_uri( 'assets/cart-button.js' ),
		array( 'jquery' ),
		file_exists( $path ) ? filemtime( $path ) : wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'omar_perfumes_cart_button_assets', 26 );
