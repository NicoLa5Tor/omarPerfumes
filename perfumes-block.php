<?php
/**
 * Plugin Name:       Perfumes Block
 * Description:       Bloque de WordPress construido con React (Gutenberg). Desarrollado localmente y desplegado por FTP con GitHub Actions.
 * Version:           0.3.0
 * Author:            Tu Nombre
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       perfumes
 *
 * @package Perfumes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Evita acceso directo.
}

/**
 * Registra el bloque usando los metadatos del archivo block.json en /build.
 */
function perfumes_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'perfumes_block_init' );

/**
 * Load the same storefront styling on WooCommerce pages that use the global
 * Omar template parts. This keeps the header/footer visually identical to
 * the landing rather than inheriting theme styles.
 */
function perfumes_enqueue_storefront_chrome() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return;
	}

	if ( ! ( is_woocommerce() || is_cart() || is_checkout() ) ) {
		return;
	}

	$style_path = __DIR__ . '/build/style-index.css';
	wp_enqueue_style(
		'perfumes-storefront-chrome',
		plugins_url( 'build/style-index.css', __FILE__ ),
		array(),
		file_exists( $style_path ) ? filemtime( $style_path ) : null
	);
}
add_action( 'wp_enqueue_scripts', 'perfumes_enqueue_storefront_chrome' );

/** Load WooCommerce's native AJAX add-to-cart behavior on the landing. */
function perfumes_enqueue_woocommerce_cart_assets() {
	if ( function_exists( 'is_woocommerce' ) && has_block( 'perfumes/showcase' ) ) {
		wp_enqueue_script( 'wc-add-to-cart' );
	}
}
add_action( 'wp_enqueue_scripts', 'perfumes_enqueue_woocommerce_cart_assets', 30 );

/** Activate the versioned Omar theme once it has been deployed beside the plugin. */
function perfumes_activate_omar_theme_once() {
	if ( get_option( 'perfumes_omar_theme_activated' ) ) {
		return;
	}

	$theme = wp_get_theme( 'omar-perfumes' );
	if ( ! $theme->exists() || $theme->errors() ) {
		return;
	}

	if ( 'omar-perfumes' !== get_stylesheet() ) {
		switch_theme( 'omar-perfumes' );
	}
	update_option( 'perfumes_omar_theme_activated', gmdate( 'c' ), false );
}
add_action( 'init', 'perfumes_activate_omar_theme_once', 100 );
