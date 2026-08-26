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

	if ( comments_open() ) {
		wp_enqueue_script( 'comment-reply' );
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
		$classes[] = 'omar-scroll-locked';
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
 * Shop, category and search loops reuse the home product grid class.
 *
 * @param string $html Loop opening markup.
 * @return string
 */
function omar_perfumes_product_loop_start( $html ) {
	if ( 'related' === wc_get_loop_prop( 'name' ) || false !== strpos( $html, 'perfumes-product-grid' ) ) {
		return $html;
	}

	$updated = preg_replace( '/class="products/', 'class="products perfumes-product-grid', $html, 1 );
	return is_string( $updated ) ? $updated : $html;
}
add_filter( 'woocommerce_product_loop_start', 'omar_perfumes_product_loop_start' );

/**
 * Catalog archives must use the theme templates (home product card), not
 * WooCommerce's Product Collection blocks saved in the Site Editor.
 *
 * @return string[]
 */
function omar_perfumes_catalog_template_slugs() {
	return array(
		'archive-product',
		'taxonomy-product_cat',
		'taxonomy-product_tag',
		'product-search-results',
	);
}

/**
 * @param string $slug Template slug.
 * @return WP_Block_Template|null
 */
function omar_perfumes_theme_catalog_template( $slug ) {
	if ( ! in_array( $slug, omar_perfumes_catalog_template_slugs(), true ) || ! class_exists( 'WP_Block_Template' ) ) {
		return null;
	}

	$path = get_theme_file_path( 'templates/' . $slug . '.html' );
	if ( ! $path || ! file_exists( $path ) ) {
		return null;
	}

	$template                 = new WP_Block_Template();
	$template->id             = get_stylesheet() . '//' . $slug;
	$template->theme          = get_stylesheet();
	$template->content        = (string) file_get_contents( $path );
	$template->slug           = $slug;
	$template->source         = 'theme';
	$template->type           = 'wp_template';
	$template->title          = $slug;
	$template->status         = 'publish';
	$template->has_theme_file = true;
	$template->is_custom      = false;
	$template->origin         = 'theme';
	return $template;
}

/**
 * @param WP_Block_Template|null $template      Current template.
 * @param string                 $id            Template id (`theme//slug`).
 * @param string                 $template_type Template post type.
 * @return WP_Block_Template|null
 */
function omar_perfumes_pre_get_catalog_template( $template, $id, $template_type ) {
	if ( 'wp_template' !== $template_type || ! is_string( $id ) ) {
		return $template;
	}

	$slug   = false !== strpos( $id, '//' ) ? substr( $id, strpos( $id, '//' ) + 2 ) : $id;
	$loaded = omar_perfumes_theme_catalog_template( $slug );
	return $loaded ? $loaded : $template;
}
add_filter( 'pre_get_block_template', 'omar_perfumes_pre_get_catalog_template', 10, 3 );

/**
 * @param WP_Block_Template[] $query_result Templates.
 * @param array               $query        Query args.
 * @param string              $template_type Template post type.
 * @return WP_Block_Template[]
 */
function omar_perfumes_force_catalog_templates( $query_result, $query, $template_type ) {
	if ( 'wp_template' !== $template_type || ! is_array( $query_result ) ) {
		return $query_result;
	}

	foreach ( $query_result as $index => $item ) {
		if ( ! $item instanceof WP_Block_Template ) {
			continue;
		}
		$loaded = omar_perfumes_theme_catalog_template( $item->slug );
		if ( $loaded ) {
			$query_result[ $index ] = $loaded;
		}
	}

	return $query_result;
}
add_filter( 'get_block_templates', 'omar_perfumes_force_catalog_templates', 99, 3 );

/**
 * Top-selling product IDs for this request, ranked by WooCommerce total_sales.
 *
 * @param int $limit How many IDs to return.
 * @return int[]
 */
function omar_perfumes_top_selling_ids( $limit = 8 ) {
	static $cache = array();

	$limit = max( 1, (int) $limit );
	if ( isset( $cache[ $limit ] ) ) {
		return $cache[ $limit ];
	}

	if ( ! function_exists( 'wc_get_products' ) ) {
		$cache[ $limit ] = array();
		return $cache[ $limit ];
	}

	$ids = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => $limit,
			'orderby' => 'popularity',
			'order'   => 'DESC',
			'return'  => 'ids',
		)
	);

	$cache[ $limit ] = array_values( array_filter( array_map( 'intval', (array) $ids ) ) );
	return $cache[ $limit ];
}

/**
 * Whether the product is in the live top sellers and has at least one sale.
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function omar_perfumes_is_bestseller( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 || ! in_array( $product_id, omar_perfumes_top_selling_ids( 8 ), true ) ) {
		return false;
	}

	$product = wc_get_product( $product_id );
	return $product instanceof WC_Product && (int) $product->get_total_sales() > 0;
}

/**
 * Card badge from live stock, sales rank and sale price.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function omar_perfumes_product_badge( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	if ( ! $product->is_in_stock() ) {
		return __( 'Agotado', 'omar-perfumes' );
	}

	if ( omar_perfumes_is_bestseller( $product->get_id() ) ) {
		return __( 'Más vendido', 'omar-perfumes' );
	}

	if ( $product->is_on_sale() ) {
		return __( 'Oferta', 'omar-perfumes' );
	}

	return '';
}

/**
 * Resolve a product ID from a product object, ID, or the current request.
 *
 * @param WC_Product|int|null $product Product object or ID.
 * @return int
 */
function omar_perfumes_resolve_product_id( $product ) {
	global $post;

	if ( $product instanceof WC_Product ) {
		return (int) $product->get_id();
	}

	if ( is_numeric( $product ) && (int) $product > 0 ) {
		return (int) $product;
	}

	if ( function_exists( 'wc_get_product' ) ) {
		$current = wc_get_product();
		if ( $current instanceof WC_Product ) {
			return (int) $current->get_id();
		}
	}

	if ( $post instanceof WP_Post && 'product' === $post->post_type ) {
		return (int) $post->ID;
	}

	$queried = get_queried_object_id();
	if ( $queried && 'product' === get_post_type( $queried ) ) {
		return (int) $queried;
	}

	return 0;
}

/**
 * Load approved product comments from the comments table.
 *
 * Matches WooCommerce_Comments::get_review_counts_for_product_ids() so reviews
 * are not lost to comments_clauses / mixed type__in queries.
 *
 * @param int  $product_id Product ID.
 * @param bool $strict     Use WooCommerce's review types and top-level only.
 * @return WP_Comment[]
 */
function omar_perfumes_query_product_comment_rows( $product_id, $strict = true ) {
	global $wpdb;

	$product_id = absint( $product_id );
	if ( $product_id < 1 ) {
		return array();
	}

	if ( $strict ) {
		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->comments}
			WHERE comment_post_ID = %d
				AND comment_parent = 0
				AND comment_approved = '1'
				AND comment_type IN ( 'review', 'comment', '' )
			ORDER BY comment_date_gmt DESC
			LIMIT 200",
			$product_id
		);
	} else {
		$sql = $wpdb->prepare(
			"SELECT * FROM {$wpdb->comments}
			WHERE comment_post_ID = %d
				AND comment_approved = '1'
				AND comment_type NOT IN ( 'order_note', 'webhook_delivery', 'action_log', 'pingback', 'trackback' )
			ORDER BY comment_date_gmt DESC
			LIMIT 200",
			$product_id
		);
	}

	$rows = $sql ? $wpdb->get_results( $sql ) : array();
	if ( ! $rows ) {
		return array();
	}

	$comments = array();
	foreach ( $rows as $row ) {
		$comments[] = new WP_Comment( $row );
	}

	return $comments;
}

/**
 * Approved product reviews for a product.
 *
 * WooCommerce stores reviews as comment_type = review. Generic get_comments()
 * and comments_template() often miss them in block themes, so we query the
 * same way WooCommerce counts reviews, then fall back to a wider read.
 *
 * @param WC_Product|int|null $product Product object or ID.
 * @return WP_Comment[]
 */
function omar_perfumes_get_product_reviews( $product ) {
	static $cache = array();

	$product_id = omar_perfumes_resolve_product_id( $product );
	if ( $product_id < 1 ) {
		return array();
	}

	if ( isset( $cache[ $product_id ] ) ) {
		return $cache[ $product_id ];
	}

	$comments = omar_perfumes_query_product_comment_rows( $product_id, true );
	if ( ! $comments ) {
		$comments = omar_perfumes_query_product_comment_rows( $product_id, false );
	}

	$cache[ $product_id ] = $comments;

	return $cache[ $product_id ];
}

/**
 * Average stars and review count from approved product comments.
 *
 * WooCommerce product meta (_wc_average_rating) is often stale or empty
 * when reviews were saved as comments, so we read the comment ratings too.
 *
 * @param WC_Product $product Product.
 * @return array{rating: float, count: int}
 */
function omar_perfumes_product_review_stats( $product ) {
	static $cache = array();

	if ( ! $product instanceof WC_Product ) {
		return array(
			'rating' => 0.0,
			'count'  => 0,
		);
	}

	$product_id = (int) $product->get_id();
	if ( isset( $cache[ $product_id ] ) ) {
		return $cache[ $product_id ];
	}

	$stored_rating = (float) get_post_meta( $product_id, '_wc_average_rating', true );
	$stored_count  = (int) get_post_meta( $product_id, '_wc_review_count', true );
	$values        = array();
	$review_count  = 0;
	$comments      = omar_perfumes_get_product_reviews( $product );

	foreach ( $comments as $comment ) {
		if ( in_array( $comment->comment_type, array( 'pingback', 'trackback', 'order_note' ), true ) ) {
			continue;
		}

		++$review_count;
		$value = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
		if ( $value >= 1 && $value <= 5 ) {
			$values[] = $value;
		}
	}

	$rating = $values ? array_sum( $values ) / count( $values ) : $stored_rating;
	$count  = max( $review_count, $stored_count, count( $values ) );

	if ( $values || $review_count ) {
		omar_perfumes_sync_product_review_meta( $product_id, $rating, $count, $values );
	}

	$cache[ $product_id ] = array(
		'rating' => min( 5, max( 0, (float) $rating ) ),
		'count'  => (int) $count,
	);

	return $cache[ $product_id ];
}

/**
 * Keep WooCommerce product rating meta in sync with approved comments.
 *
 * @param int   $product_id Product ID.
 * @param float $rating     Average rating.
 * @param int   $count      Review count.
 * @param int[] $values     Individual ratings.
 */
function omar_perfumes_sync_product_review_meta( $product_id, $rating, $count, $values ) {
	$product_id = absint( $product_id );
	if ( $product_id < 1 ) {
		return;
	}

	$formatted = function_exists( 'wc_format_decimal' )
		? wc_format_decimal( $rating, 2 )
		: number_format( (float) $rating, 2, '.', '' );

	update_post_meta( $product_id, '_wc_average_rating', $formatted );
	update_post_meta( $product_id, '_wc_review_count', (int) $count );

	if ( $values ) {
		$rating_counts = array_count_values( array_map( 'intval', $values ) );
		ksort( $rating_counts );
		update_post_meta( $product_id, '_wc_rating_count', $rating_counts );
	}
}

/**
 * Stars from WooCommerce review ratings (average of approved product comments).
 *
 * @param WC_Product $product Product.
 * @param array      $args    Optional class, href and show_count.
 * @return string
 */
function omar_perfumes_star_rating_markup( $product, $args = array() ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$args   = wp_parse_args(
		$args,
		array(
			'class'      => 'perfumes-product-card__ratings',
			'href'       => '',
			'show_count' => false,
		)
	);
	$stats  = omar_perfumes_product_review_stats( $product );
	$rating = $stats['rating'];
	$count  = $stats['count'];

	$filled = (int) round( $rating );
	$label  = $count
		? sprintf(
			/* translators: 1: average rating 2: review count */
			__( 'Valoración: %1$s de 5, %2$s opiniones', 'omar-perfumes' ),
			number_format_i18n( $rating, 1 ),
			number_format_i18n( $count )
		)
		: __( 'Sin valoraciones todavía', 'omar-perfumes' );

	ob_start();
	if ( $args['href'] ) :
		?>
	<a class="<?php echo esc_attr( $args['class'] ); ?>" href="<?php echo esc_url( $args['href'] ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
		<?php else : ?>
	<div class="<?php echo esc_attr( $args['class'] ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
		<?php
	endif;
	for ( $star = 1; $star <= 5; $star++ ) :
		?>
		<span class="<?php echo $star <= $filled ? 'is-active' : ''; ?>" aria-hidden="true">★</span>
		<?php
	endfor;
	if ( ! empty( $args['show_count'] ) ) :
		?>
		<span class="perfumes-star-rating__count">
			<?php
			if ( $count ) {
				echo esc_html( number_format_i18n( $rating, 1 ) );
				echo ' · ';
				printf(
					esc_html( _n( '%s opinión', '%s opiniones', $count, 'omar-perfumes' ) ),
					esc_html( number_format_i18n( $count ) )
				);
			} else {
				esc_html_e( 'Sin opiniones', 'omar-perfumes' );
			}
			?>
		</span>
		<?php
	endif;
	if ( $args['href'] ) :
		?>
	</a>
		<?php else : ?>
	</div>
		<?php
	endif;
	return (string) ob_get_clean();
}

/**
 * Ensure product reviews are saved with the correct comment type and rating.
 *
 * @param array $comment_data Comment data.
 * @return array
 */
function omar_perfumes_preprocess_product_review( $comment_data ) {
	$post_id = isset( $comment_data['comment_post_ID'] ) ? (int) $comment_data['comment_post_ID'] : 0;
	if ( ! $post_id || 'product' !== get_post_type( $post_id ) ) {
		return $comment_data;
	}

	$comment_data['comment_type'] = 'review';

	if (
		function_exists( 'wc_review_ratings_enabled' ) &&
		wc_review_ratings_enabled() &&
		function_exists( 'wc_review_ratings_required' ) &&
		wc_review_ratings_required() &&
		empty( $_POST['rating'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
	) {
		wp_die(
			esc_html__( 'Por favor elige una valoración antes de publicar tu opinión.', 'omar-perfumes' ),
			esc_html__( 'Opinión incompleta', 'omar-perfumes' ),
			array(
				'response'  => 403,
				'back_link' => true,
			)
		);
	}

	return $comment_data;
}
add_filter( 'preprocess_comment', 'omar_perfumes_preprocess_product_review', 0 );

/**
 * Persist review star ratings and refresh WooCommerce product stats.
 *
 * @param int        $comment_id       Comment ID.
 * @param int|string $comment_approved Approval status.
 * @param array      $comment_data     Comment data.
 */
function omar_perfumes_save_product_review_rating( $comment_id, $comment_approved, $comment_data ) {
	$post_id = isset( $comment_data['comment_post_ID'] ) ? (int) $comment_data['comment_post_ID'] : 0;
	if ( ! $post_id || 'product' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( isset( $_POST['rating'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$rating = (int) wp_unslash( $_POST['rating'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $rating >= 1 && $rating <= 5 ) {
			update_comment_meta( $comment_id, 'rating', $rating );
		}
	}

	if ( class_exists( 'WC_Comments' ) ) {
		WC_Comments::clear_transients( $post_id );
	}
}
add_action( 'comment_post', 'omar_perfumes_save_product_review_rating', 10, 3 );

/**
 * Ask WordPress for product reviews, not generic comments.
 *
 * @param array $comment_args Comments template query args.
 * @return array
 */
function omar_perfumes_product_comments_query_args( $comment_args ) {
	$post_id = isset( $comment_args['post_id'] ) ? absint( $comment_args['post_id'] ) : get_queried_object_id();
	if ( ! $post_id || 'product' !== get_post_type( $post_id ) ) {
		return $comment_args;
	}

	$comment_args['type']   = 'review';
	$comment_args['status'] = 'approve';
	unset( $comment_args['type__in'], $comment_args['type__not_in'] );

	return $comment_args;
}
add_filter( 'comments_template_query_args', 'omar_perfumes_product_comments_query_args' );

/**
 * Always load the WooCommerce product reviews template for products.
 *
 * @param string $template Comments template path.
 * @return string
 */
function omar_perfumes_comments_template( $template ) {
	if ( 'product' !== get_post_type() ) {
		return $template;
	}

	$file = get_stylesheet_directory() . '/woocommerce/single-product-reviews.php';
	return file_exists( $file ) ? $file : $template;
}
add_filter( 'comments_template', 'omar_perfumes_comments_template', 99 );

/**
 * Do not let WooCommerce's review-list walker drop legacy product comments.
 *
 * @param array $args wp_list_comments args.
 * @return array
 */
function omar_perfumes_review_list_args( $args ) {
	$args['type']  = 'all';
	$args['style'] = 'ol';
	return $args;
}
add_filter( 'woocommerce_product_review_list_args', 'omar_perfumes_review_list_args', 20 );

/**
 * Keep review rows in comment queries for the current product.
 *
 * @param array            $clauses       SQL clauses.
 * @param WP_Comment_Query $comment_query Query.
 * @return array
 */
function omar_perfumes_keep_product_reviews_in_comment_queries( $clauses, $comment_query ) {
	$post_id = 0;
	if ( isset( $comment_query->query_vars['post_id'] ) ) {
		$post_id = absint( $comment_query->query_vars['post_id'] );
	}

	if ( ! $post_id || 'product' !== get_post_type( $post_id ) || empty( $clauses['where'] ) ) {
		return $clauses;
	}

	$clauses['where'] = preg_replace( "/\\s+AND\\s+comment_type\\s*!=\\s*'review'/i", '', $clauses['where'] );
	$clauses['where'] = preg_replace( '/\\s+AND\\s+comment_type\\s*<>\\s*\'review\'/i', '', $clauses['where'] );

	return $clauses;
}
add_filter( 'comments_clauses', 'omar_perfumes_keep_product_reviews_in_comment_queries', 99, 2 );

/**
 * Product reviews are comments; keep that support registered.
 */
function omar_perfumes_product_comments_support() {
	add_post_type_support( 'product', 'comments' );
}
add_action( 'init', 'omar_perfumes_product_comments_support', 20 );

/**
 * Show feedback after a review is submitted.
 */
function omar_perfumes_review_submission_notice() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	if ( isset( $_GET['unapproved'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<p class="perfumes-review-notice perfumes-review-notice--pending">';
		esc_html_e( 'Gracias. Tu opinión fue enviada y aparecerá en cuanto sea aprobada.', 'omar-perfumes' );
		echo '</p>';
		return;
	}

	if ( empty( $_GET['replytocom'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$comment_id = absint( wp_unslash( $_GET['replytocom'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $comment_id || ! wp_get_comment_status( $comment_id ) ) {
		return;
	}

	echo '<p class="perfumes-review-notice perfumes-review-notice--success">';
	esc_html_e( 'Gracias. Tu opinión ya fue publicada.', 'omar-perfumes' );
	echo '</p>';
}

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
