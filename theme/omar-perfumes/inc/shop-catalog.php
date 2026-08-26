<?php
/**
 * Shop catalog chrome: hero, category sidebar, filters and toolbar.
 *
 * @package OmarPerfumes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request is a WooCommerce catalog view.
 *
 * @return bool
 */
function omar_perfumes_is_catalog_view() {
	if ( ! function_exists( 'is_shop' ) ) {
		return false;
	}

	if ( is_shop() || is_product_taxonomy() ) {
		return true;
	}

	return is_search() && 'product' === get_query_var( 'post_type' );
}

/**
 * Preserve active catalog filters when building navigation links.
 *
 * @param string $url Target URL.
 * @return string
 */
function omar_perfumes_catalog_preserve_args( $url ) {
	$preserve = array( 'orderby', 'instock', 'min_price', 'max_price' );

	foreach ( $preserve as $key ) {
		if ( empty( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			continue;
		}

		$url = add_query_arg(
			$key,
			sanitize_text_field( wp_unslash( $_GET[ $key ] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$url
		);
	}

	return $url;
}

/**
 * @return array<string, mixed>
 */
function omar_perfumes_catalog_context() {
	$shop_url      = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
	$queried       = get_queried_object();
	$is_shop       = function_exists( 'is_shop' ) && is_shop();
	$is_category   = is_tax( 'product_cat' );
	$current_term  = ( $is_category && $queried instanceof WP_Term ) ? $queried : null;
	$parent_term   = null;
	$active_parent = null;

	if ( $current_term ) {
		if ( 0 < (int) $current_term->parent ) {
			$parent_term = get_term( (int) $current_term->parent, 'product_cat' );
			if ( $parent_term instanceof WP_Term && ! is_wp_error( $parent_term ) ) {
				$active_parent = $parent_term;
			}
		} else {
			$active_parent = $current_term;
		}
	}

	$top_categories = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'exclude'    => get_option( 'default_product_cat' ),
		)
	);

	if ( is_wp_error( $top_categories ) ) {
		$top_categories = array();
	}

	$subcategories = array();
	if ( $active_parent instanceof WP_Term ) {
		$subcategories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => (int) $active_parent->term_id,
				'hide_empty' => true,
			)
		);
		if ( is_wp_error( $subcategories ) ) {
			$subcategories = array();
		}
	} elseif ( $is_shop && ! empty( $top_categories ) ) {
		$subcategories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => (int) $top_categories[0]->term_id,
				'hide_empty' => true,
			)
		);
		if ( is_wp_error( $subcategories ) ) {
			$subcategories = array();
		}
	}

	$total_products = 0;
	if ( function_exists( 'wc_get_loop_prop' ) && wc_get_loop_prop( 'total' ) ) {
		$total_products = (int) wc_get_loop_prop( 'total' );
	} elseif ( isset( $GLOBALS['wp_query'] ) && $GLOBALS['wp_query'] instanceof WP_Query ) {
		$total_products = (int) $GLOBALS['wp_query']->found_posts;
	}

	$title = __( 'Todos los perfumes', 'omar-perfumes' );
	if ( $current_term instanceof WP_Term ) {
		$title = $current_term->name;
	} elseif ( is_search() ) {
		/* translators: %s: search query */
		$title = sprintf( __( 'Resultados para “%s”', 'omar-perfumes' ), get_search_query() );
	}

	$selection_bits = array();
	if ( $current_term instanceof WP_Term ) {
		$selection_bits[] = $current_term->name;
	} elseif ( $is_shop ) {
		$selection_bits[] = __( 'Todos', 'omar-perfumes' );
	}

	if ( ! empty( $_GET['instock'] ) && '1' === $_GET['instock'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$selection_bits[] = __( 'Con stock', 'omar-perfumes' );
	}

	$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$orderby_labels = array(
		'popularity' => __( 'Más vendidos', 'omar-perfumes' ),
		'date'       => __( 'Más recientes', 'omar-perfumes' ),
		'price'      => __( 'Precio: menor a mayor', 'omar-perfumes' ),
		'price-desc' => __( 'Precio: mayor a menor', 'omar-perfumes' ),
		'rating'     => __( 'Mejor valorados', 'omar-perfumes' ),
	);
	if ( $orderby && isset( $orderby_labels[ $orderby ] ) ) {
		$selection_bits[] = $orderby_labels[ $orderby ];
	}

	$subcategory_heading = __( 'Subcategorías', 'omar-perfumes' );
	if ( $active_parent instanceof WP_Term ) {
		/* translators: %s: category name */
		$subcategory_heading = sprintf( __( 'Subcategorías de %s', 'omar-perfumes' ), $active_parent->name );
	} elseif ( $is_shop ) {
		$subcategory_heading = __( 'Subcategorías destacadas', 'omar-perfumes' );
	}

	return compact(
		'shop_url',
		'is_shop',
		'is_category',
		'current_term',
		'active_parent',
		'top_categories',
		'subcategories',
		'total_products',
		'title',
		'selection_bits',
		'subcategory_heading',
		'orderby'
	);
}

/**
 * @param WP_Term $term Category term.
 * @return bool
 */
function omar_perfumes_catalog_term_is_active( WP_Term $term, array $context ) {
	if ( ! empty( $context['current_term'] ) && $context['current_term'] instanceof WP_Term ) {
		return (int) $context['current_term']->term_id === (int) $term->term_id
			|| (int) $context['current_term']->parent === (int) $term->term_id;
	}

	return false;
}

/**
 * @param string $key Filter key.
 * @return bool
 */
function omar_perfumes_catalog_filter_is_active( $key ) {
	switch ( $key ) {
		case 'instock':
			return ! empty( $_GET['instock'] ) && '1' === $_GET['instock']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		case 'price':
			return ! empty( $_GET['min_price'] ) || ! empty( $_GET['max_price'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		default:
			return false;
	}
}

/**
 * @param string $key Filter key.
 * @return string
 */
function omar_perfumes_catalog_filter_url( $key ) {
	$context = omar_perfumes_catalog_context();
	$base    = $context['is_shop'] ? $context['shop_url'] : get_term_link( $context['current_term'] );
	if ( is_wp_error( $base ) ) {
		$base = $context['shop_url'];
	}

	switch ( $key ) {
		case 'instock':
			if ( omar_perfumes_catalog_filter_is_active( 'instock' ) ) {
				return remove_query_arg( 'instock', $base );
			}
			return add_query_arg( 'instock', '1', $base );
		case 'price':
			if ( omar_perfumes_catalog_filter_is_active( 'price' ) ) {
				return remove_query_arg( array( 'min_price', 'max_price' ), $base );
			}
			return add_query_arg(
				array(
					'min_price' => 60,
					'max_price' => 180,
				),
				$base
			);
		default:
			return $base;
	}
}

/**
 * @param string $chip Chip identifier.
 * @return string
 */
function omar_perfumes_catalog_chip_url( $chip ) {
	$context = omar_perfumes_catalog_context();
	$base    = $context['shop_url'];

	switch ( $chip ) {
		case 'popular':
			return add_query_arg( 'orderby', 'popularity', $base );
		case 'new':
			return add_query_arg( 'orderby', 'date', $base );
		case 'gifts':
			$gift_term = get_term_by( 'slug', 'sets-de-regalo', 'product_cat' );
			if ( ! $gift_term ) {
				$gift_term = get_term_by( 'slug', 'regalos', 'product_cat' );
			}
			if ( $gift_term instanceof WP_Term ) {
				return omar_perfumes_catalog_preserve_args( get_term_link( $gift_term ) );
			}
			return $base;
		default:
			return $base;
	}
}

/**
 * @param string $chip Chip identifier.
 * @return bool
 */
function omar_perfumes_catalog_chip_is_active( $chip ) {
	$context = omar_perfumes_catalog_context();

	switch ( $chip ) {
		case 'popular':
			return 'popularity' === $context['orderby'];
		case 'new':
			return 'date' === $context['orderby'];
		case 'gifts':
			return ! empty( $context['current_term'] ) && in_array( $context['current_term']->slug, array( 'sets-de-regalo', 'regalos' ), true );
		default:
		 return false;
	}
}

/**
 * @param WP_Query $query Product query.
 * @return void
 */
function omar_perfumes_catalog_product_query( $query ) {
	if ( is_admin() || ! omar_perfumes_is_catalog_view() ) {
		return;
	}

	if ( empty( $_GET['instock'] ) || '1' !== $_GET['instock'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$meta_query   = (array) $query->get( 'meta_query' );
	$meta_query[] = array(
		'key'   => '_stock_status',
		'value' => 'instock',
	);
	$query->set( 'meta_query', $meta_query );
}
add_action( 'woocommerce_product_query', 'omar_perfumes_catalog_product_query', 20 );

/**
 * @return void
 */
function omar_perfumes_catalog_body_class( $classes ) {
	if ( omar_perfumes_is_catalog_view() ) {
		$classes[] = 'omar-catalog-view';
	}
	return $classes;
}
add_filter( 'body_class', 'omar_perfumes_catalog_body_class' );

/**
 * @return void
 */
function omar_perfumes_catalog_assets() {
	if ( ! omar_perfumes_is_catalog_view() ) {
		return;
	}

	$path = get_theme_file_path( 'assets/shop-catalog.js' );
	wp_enqueue_script(
		'omar-perfumes-shop-catalog',
		get_theme_file_uri( 'assets/shop-catalog.js' ),
		array(),
		file_exists( $path ) ? filemtime( $path ) : wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'omar_perfumes_catalog_assets', 25 );

/**
 * @return void
 */
function omar_perfumes_catalog_setup() {
	if ( ! omar_perfumes_is_catalog_view() ) {
		return;
	}

	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header', 10 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

	add_action( 'woocommerce_before_main_content', 'omar_perfumes_shop_hero', 5 );
	add_action( 'woocommerce_before_main_content', 'omar_perfumes_shop_layout_open', 15 );
	add_action( 'woocommerce_before_shop_loop', 'omar_perfumes_shop_products_open', 4 );
	add_action( 'woocommerce_before_shop_loop', 'omar_perfumes_shop_toolbar', 5 );
	add_action( 'woocommerce_after_shop_loop', 'omar_perfumes_shop_layout_close', 999 );
	add_action( 'woocommerce_no_products_found', 'omar_perfumes_shop_products_open', 4 );
	add_action( 'woocommerce_no_products_found', 'omar_perfumes_shop_toolbar', 5 );
	add_action( 'woocommerce_no_products_found', 'omar_perfumes_shop_layout_close', 999 );
}
add_action( 'wp', 'omar_perfumes_catalog_setup' );

/**
 * @return void
 */
function omar_perfumes_shop_hero() {
	$context = omar_perfumes_catalog_context();
	$chips   = array(
		'popular' => __( 'Best sellers', 'omar-perfumes' ),
		'new'     => __( 'Nuevos', 'omar-perfumes' ),
		'gifts'   => __( 'Regalos premium', 'omar-perfumes' ),
	);
	?>
	<section class="perfumes-shop-hero" aria-labelledby="perfumes-shop-hero-title">
		<div class="perfumes-shop-hero__inner">
			<div class="perfumes-shop-hero__copy">
				<p class="perfumes-shop-hero__eyebrow"><?php esc_html_e( 'Tienda oficial', 'omar-perfumes' ); ?></p>
				<h1 class="perfumes-shop-hero__title" id="perfumes-shop-hero-title"><?php esc_html_e( 'Fragancias elegantes para cada presencia', 'omar-perfumes' ); ?></h1>
				<p class="perfumes-shop-hero__description"><?php esc_html_e( 'Explora perfumes árabes, nicho, diseñador y sets seleccionados. Filtra por ocasión, intensidad y familia olfativa sin perder el estilo premium de Omar Perfumes.', 'omar-perfumes' ); ?></p>
				<ul class="perfumes-shop-hero__quick-filters" aria-label="<?php esc_attr_e( 'Filtros destacados', 'omar-perfumes' ); ?>">
					<?php foreach ( $chips as $chip_key => $chip_label ) : ?>
						<li>
							<a
								class="perfumes-shop-hero__chip<?php echo omar_perfumes_catalog_chip_is_active( $chip_key ) ? ' perfumes-shop-hero__chip--active' : ''; ?>"
								href="<?php echo esc_url( omar_perfumes_catalog_chip_url( $chip_key ) ); ?>"
							><?php echo esc_html( $chip_label ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="perfumes-shop-hero__composition" aria-hidden="true">
				<span class="perfumes-shop-hero__halo"></span>
				<span class="perfumes-shop-hero__box">NOIR</span>
				<span class="perfumes-shop-hero__cap perfumes-shop-hero__cap--primary"></span>
				<span class="perfumes-shop-hero__bottle perfumes-shop-hero__bottle--primary"></span>
				<span class="perfumes-shop-hero__label perfumes-shop-hero__label--primary">OMAR</span>
				<span class="perfumes-shop-hero__cap perfumes-shop-hero__cap--secondary"></span>
				<span class="perfumes-shop-hero__bottle perfumes-shop-hero__bottle--secondary"></span>
				<span class="perfumes-shop-hero__reflection"></span>
			</div>
		</div>
	</section>
	<?php
}

/**
 * @return void
 */
function omar_perfumes_shop_layout_open() {
	$context = omar_perfumes_catalog_context();
	?>
	<div class="perfumes-shop-store">
		<aside class="perfumes-shop-sidebar" id="perfumes-shop-sidebar" aria-labelledby="perfumes-shop-sidebar-title">
			<h2 class="perfumes-shop-sidebar__title" id="perfumes-shop-sidebar-title"><?php esc_html_e( 'Categorías', 'omar-perfumes' ); ?></h2>
			<p class="perfumes-shop-sidebar__helper"><?php esc_html_e( 'Explora por tipo, ocasión o familia olfativa.', 'omar-perfumes' ); ?></p>

			<section class="perfumes-shop-sidebar__group" aria-label="<?php esc_attr_e( 'Categorías principales', 'omar-perfumes' ); ?>">
				<ul class="perfumes-shop-category-list">
					<li class="perfumes-shop-category-list__item">
						<?php
						$all_active = $context['is_shop'] && empty( $context['current_term'] );
						$all_count  = wp_count_posts( 'product' )->publish ?? 0;
						?>
						<a
							class="perfumes-shop-category-list__link<?php echo $all_active ? ' perfumes-shop-category-list__link--active' : ''; ?>"
							href="<?php echo esc_url( omar_perfumes_catalog_preserve_args( $context['shop_url'] ) ); ?>"
						>
							<span class="perfumes-shop-category-list__label"><?php esc_html_e( 'Todos', 'omar-perfumes' ); ?></span>
							<span class="perfumes-shop-category-list__count"><?php echo esc_html( (string) $all_count ); ?></span>
						</a>
					</li>
					<?php foreach ( $context['top_categories'] as $term ) : ?>
						<?php if ( ! $term instanceof WP_Term ) { continue; } ?>
						<li class="perfumes-shop-category-list__item">
							<a
								class="perfumes-shop-category-list__link<?php echo omar_perfumes_catalog_term_is_active( $term, $context ) ? ' perfumes-shop-category-list__link--active' : ''; ?>"
								href="<?php echo esc_url( omar_perfumes_catalog_preserve_args( get_term_link( $term ) ) ); ?>"
							>
								<span class="perfumes-shop-category-list__label"><?php echo esc_html( $term->name ); ?></span>
								<span class="perfumes-shop-category-list__count"><?php echo esc_html( (string) $term->count ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<?php if ( ! empty( $context['subcategories'] ) ) : ?>
				<section class="perfumes-shop-sidebar__group" aria-labelledby="perfumes-shop-subcategory-title">
					<h3 class="perfumes-shop-sidebar__group-title" id="perfumes-shop-subcategory-title"><?php echo esc_html( $context['subcategory_heading'] ); ?></h3>
					<ul class="perfumes-shop-subcategory-list">
						<?php foreach ( $context['subcategories'] as $term ) : ?>
							<?php if ( ! $term instanceof WP_Term ) { continue; } ?>
							<?php
							$sub_active = ! empty( $context['current_term'] ) && (int) $context['current_term']->term_id === (int) $term->term_id;
							?>
							<li>
								<a
									class="perfumes-shop-subcategory-list__link<?php echo $sub_active ? ' perfumes-shop-subcategory-list__link--active' : ''; ?>"
									href="<?php echo esc_url( omar_perfumes_catalog_preserve_args( get_term_link( $term ) ) ); ?>"
								><?php echo esc_html( $term->name ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<section class="perfumes-shop-sidebar__group" aria-labelledby="perfumes-shop-filter-title">
				<h3 class="perfumes-shop-sidebar__group-title" id="perfumes-shop-filter-title"><?php esc_html_e( 'Filtros rápidos', 'omar-perfumes' ); ?></h3>
				<ul class="perfumes-shop-filter-list">
					<li>
						<a
							class="perfumes-shop-filter-list__link<?php echo omar_perfumes_catalog_filter_is_active( 'price' ) ? ' is-active' : ''; ?>"
							href="<?php echo esc_url( omar_perfumes_catalog_filter_url( 'price' ) ); ?>"
						><?php esc_html_e( 'Precio: $60 - $180', 'omar-perfumes' ); ?></a>
					</li>
					<li>
						<a
							class="perfumes-shop-filter-list__link<?php echo omar_perfumes_catalog_filter_is_active( 'instock' ) ? ' is-active' : ''; ?>"
							href="<?php echo esc_url( omar_perfumes_catalog_filter_url( 'instock' ) ); ?>"
						><?php esc_html_e( 'Con stock', 'omar-perfumes' ); ?></a>
					</li>
				</ul>
			</section>
		</aside>
	<?php
}

/**
 * @return void
 */
function omar_perfumes_shop_products_open() {
	static $opened = false;
	if ( $opened ) {
		return;
	}
	$opened = true;
	echo '<section class="perfumes-shop-products" aria-labelledby="perfumes-shop-products-title">';
}

/**
 * @return void
 */
function omar_perfumes_shop_toolbar() {
	static $rendered = false;
	if ( $rendered ) {
		return;
	}
	$rendered = true;

	$context = omar_perfumes_catalog_context();
	$meta    = array();

	if ( $context['total_products'] > 0 ) {
		/* translators: %d: product count */
		$meta[] = sprintf( _n( '%d producto', '%d productos', $context['total_products'], 'omar-perfumes' ), $context['total_products'] );
	}

	if ( ! empty( $context['selection_bits'] ) ) {
		$meta[] = implode( ' · ', $context['selection_bits'] );
	}

	$meta[] = __( 'Actualizado hoy', 'omar-perfumes' );
	?>
	<header class="perfumes-shop-toolbar">
		<div class="perfumes-shop-toolbar__summary">
			<h2 class="perfumes-shop-toolbar__title" id="perfumes-shop-products-title"><?php echo esc_html( $context['title'] ); ?></h2>
			<?php if ( ! empty( $meta ) ) : ?>
				<p class="perfumes-shop-toolbar__meta"><?php echo esc_html( implode( ' · ', $meta ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="perfumes-shop-toolbar__controls" aria-label="<?php esc_attr_e( 'Filtros y orden', 'omar-perfumes' ); ?>">
			<button class="perfumes-shop-toolbar__button perfumes-shop-toolbar__button--primary" type="button" data-shop-filters-toggle aria-controls="perfumes-shop-sidebar" aria-expanded="false">
				<svg class="perfumes-shop-toolbar__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 21v-7m0-4V3m8 18v-9m0-4V3m8 18v-5m0-4V3M1 14h6M9 8h6m2 8h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
				<?php esc_html_e( 'Filtros', 'omar-perfumes' ); ?>
			</button>
			<div class="perfumes-shop-toolbar__ordering">
				<?php woocommerce_catalog_ordering(); ?>
			</div>
		</div>
	</header>
	<?php
}

/**
 * @return void
 */
function omar_perfumes_shop_layout_close() {
	static $closed = false;
	if ( $closed ) {
		return;
	}
	$closed = true;
	echo '</section></div>';
}
