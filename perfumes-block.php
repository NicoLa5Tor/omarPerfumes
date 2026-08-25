<?php
/**
 * Plugin Name:       Perfumes Block
 * Description:       Bloque de WordPress construido con React (Gutenberg). Desarrollado localmente y desplegado por FTP con GitHub Actions.
 * Version:           0.6.0
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
 * Keep the landing controller and shared storefront styles available on every
 * route. The Interactivity Router can reach the landing from the catalog
 * without reloading the document, so these assets cannot be route-conditional.
 */
function perfumes_enqueue_storefront_assets() {
	$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'perfumes/showcase' );
	if ( ! $block_type ) {
		return;
	}

	$styles = wp_styles();
	foreach ( $block_type->style_handles as $style_handle ) {
		wp_enqueue_style( $style_handle );

		// The block CSS inherits block.json's static version, so browsers keep
		// serving a stale file after every deploy. Bust it with the compiled
		// file's modification time instead.
		$registered = $styles->registered[ $style_handle ] ?? null;
		if ( $registered && $registered->src ) {
			$file = __DIR__ . '/build/' . basename( strtok( $registered->src, '?' ) );
			if ( file_exists( $file ) ) {
				$registered->ver = (string) filemtime( $file );
			}
		}
	}

	foreach ( $block_type->view_script_handles as $script_handle ) {
		wp_enqueue_script( $script_handle );
	}
}
add_action( 'wp_enqueue_scripts', 'perfumes_enqueue_storefront_assets' );

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

/**
 * Publish the WooCommerce storefront after the Omar public experience ships.
 * The versioned guard makes this a one-time deployment migration.
 */
function perfumes_publish_storefront_once() {
	if ( '0.6.0' === get_option( 'perfumes_storefront_public_version' ) ) {
		return;
	}

	update_option( 'woocommerce_coming_soon', 'no' );
	update_option( 'woocommerce_store_pages_only', 'no' );
	update_option( 'perfumes_storefront_public_version', '0.6.0', false );
}
add_action( 'init', 'perfumes_publish_storefront_once', 110 );
