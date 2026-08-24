<?php
/**
 * Plugin Name:       Perfumes Block
 * Description:       Bloque de WordPress construido con React (Gutenberg). Desarrollado localmente y desplegado por FTP con GitHub Actions.
 * Version:           0.1.1
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
