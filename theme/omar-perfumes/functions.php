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
	$is_main    = 'main' === ( $block['attrs']['tagName'] ?? '' )
		&& ( false !== strpos( $class_name, 'perfumes-main' ) || false !== strpos( $class_name, 'perfumes-content-shell' ) );

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
