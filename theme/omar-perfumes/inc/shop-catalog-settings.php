<?php
/**
 * Editable shop catalog content via Theme Customizer.
 *
 * Layout and animations stay in code; copy and media are theme_mods.
 *
 * @package OmarPerfumes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default catalog content (current hard-coded copy).
 *
 * @return array<string, string|int>
 */
function omar_perfumes_catalog_content_defaults() {
	return array(
		'hero_eyebrow'       => __( 'Tienda oficial', 'omar-perfumes' ),
		'hero_title'         => __( 'Fragancias elegantes para cada presencia', 'omar-perfumes' ),
		'hero_description'   => __( 'Explora perfumes árabes, nicho, diseñador y sets seleccionados. Filtra por ocasión, intensidad y familia olfativa sin perder el estilo premium de Omar Perfumes.', 'omar-perfumes' ),
		'hero_chip_popular'  => __( 'Best sellers', 'omar-perfumes' ),
		'hero_chip_new'      => __( 'Nuevos', 'omar-perfumes' ),
		'hero_chip_gifts'    => __( 'Regalos premium', 'omar-perfumes' ),
		'hero_box_label'     => 'NOIR',
		'hero_bottle_label'  => 'OMAR',
		'hero_image'         => 0,
		'sidebar_title'      => __( 'Categorías', 'omar-perfumes' ),
		'sidebar_helper'     => __( 'Explora por tipo, ocasión o familia olfativa.', 'omar-perfumes' ),
		'filters_title'      => __( 'Filtros y orden', 'omar-perfumes' ),
		'filters_helper'     => __( 'Categorías, stock, precio y orden en un solo lugar.', 'omar-perfumes' ),
		'order_title'        => __( 'Ordenar por', 'omar-perfumes' ),
		'quick_filters_title'=> __( 'Filtros rápidos', 'omar-perfumes' ),
		'instock_label'      => __( 'Con stock', 'omar-perfumes' ),
		'price_min'          => 60,
		'price_max'          => 180,
		'price_label'        => '',
	);
}

/**
 * @param string $key Setting key without prefix.
 * @return string|int
 */
function omar_perfumes_catalog_content( $key ) {
	$defaults = omar_perfumes_catalog_content_defaults();
	if ( ! array_key_exists( $key, $defaults ) ) {
		return '';
	}

	$value = get_theme_mod( 'omar_shop_' . $key, $defaults[ $key ] );

	if ( in_array( $key, array( 'hero_image', 'price_min', 'price_max' ), true ) ) {
		return (int) $value;
	}

	$value = is_string( $value ) ? $value : (string) $value;
	if ( '' === trim( $value ) && '' !== (string) $defaults[ $key ] ) {
		return $defaults[ $key ];
	}

	return $value;
}

/**
 * Price filter chip label, with automatic fallback from min/max.
 *
 * @return string
 */
function omar_perfumes_catalog_price_label() {
	$custom = omar_perfumes_catalog_content( 'price_label' );
	if ( is_string( $custom ) && '' !== trim( $custom ) ) {
		return $custom;
	}

	$min = (int) omar_perfumes_catalog_content( 'price_min' );
	$max = (int) omar_perfumes_catalog_content( 'price_max' );

	/* translators: 1: min price, 2: max price */
	return sprintf( __( 'Precio: $%1$d - $%2$d', 'omar-perfumes' ), $min, $max );
}

/**
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @return void
 */
function omar_perfumes_catalog_customize_register( $wp_customize ) {
	$defaults = omar_perfumes_catalog_content_defaults();

	$wp_customize->add_panel(
		'omar_shop_panel',
		array(
			'title'       => __( 'Tienda (catálogo)', 'omar-perfumes' ),
			'description' => __( 'Textos e imágenes del catálogo. El layout y las animaciones no se editan aquí.', 'omar-perfumes' ),
			'priority'    => 160,
		)
	);

	$wp_customize->add_section(
		'omar_shop_hero',
		array(
			'title' => __( 'Hero de tienda', 'omar-perfumes' ),
			'panel' => 'omar_shop_panel',
		)
	);

	$wp_customize->add_section(
		'omar_shop_sidebar',
		array(
			'title' => __( 'Sidebar y filtros', 'omar-perfumes' ),
			'panel' => 'omar_shop_panel',
		)
	);

	$text_settings = array(
		'hero_eyebrow'        => array( 'section' => 'omar_shop_hero', 'label' => __( 'Eyebrow', 'omar-perfumes' ), 'type' => 'text' ),
		'hero_title'          => array( 'section' => 'omar_shop_hero', 'label' => __( 'Título', 'omar-perfumes' ), 'type' => 'text' ),
		'hero_description'    => array( 'section' => 'omar_shop_hero', 'label' => __( 'Descripción', 'omar-perfumes' ), 'type' => 'textarea' ),
		'hero_chip_popular'   => array( 'section' => 'omar_shop_hero', 'label' => __( 'Chip: Best sellers', 'omar-perfumes' ), 'type' => 'text' ),
		'hero_chip_new'       => array( 'section' => 'omar_shop_hero', 'label' => __( 'Chip: Nuevos', 'omar-perfumes' ), 'type' => 'text' ),
		'hero_chip_gifts'     => array( 'section' => 'omar_shop_hero', 'label' => __( 'Chip: Regalos', 'omar-perfumes' ), 'type' => 'text' ),
		'hero_box_label'      => array( 'section' => 'omar_shop_hero', 'label' => __( 'Etiqueta caja (decorativa)', 'omar-perfumes' ), 'type' => 'text' ),
		'hero_bottle_label'   => array( 'section' => 'omar_shop_hero', 'label' => __( 'Etiqueta botella (decorativa)', 'omar-perfumes' ), 'type' => 'text' ),
		'sidebar_title'       => array( 'section' => 'omar_shop_sidebar', 'label' => __( 'Título sidebar', 'omar-perfumes' ), 'type' => 'text' ),
		'sidebar_helper'      => array( 'section' => 'omar_shop_sidebar', 'label' => __( 'Ayuda sidebar', 'omar-perfumes' ), 'type' => 'textarea' ),
		'filters_title'       => array( 'section' => 'omar_shop_sidebar', 'label' => __( 'Título modal móvil', 'omar-perfumes' ), 'type' => 'text' ),
		'filters_helper'      => array( 'section' => 'omar_shop_sidebar', 'label' => __( 'Ayuda modal móvil', 'omar-perfumes' ), 'type' => 'textarea' ),
		'order_title'         => array( 'section' => 'omar_shop_sidebar', 'label' => __( 'Título ordenar', 'omar-perfumes' ), 'type' => 'text' ),
		'quick_filters_title' => array( 'section' => 'omar_shop_sidebar', 'label' => __( 'Título filtros rápidos', 'omar-perfumes' ), 'type' => 'text' ),
		'instock_label'       => array( 'section' => 'omar_shop_sidebar', 'label' => __( 'Etiqueta “Con stock”', 'omar-perfumes' ), 'type' => 'text' ),
		'price_label'         => array( 'section' => 'omar_shop_sidebar', 'label' => __( 'Etiqueta de precio (opcional)', 'omar-perfumes' ), 'type' => 'text' ),
	);

	foreach ( $text_settings as $key => $args ) {
		$setting_id = 'omar_shop_' . $key;
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'textarea' === $args['type'] ? 'sanitize_textarea_field' : 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'   => $args['label'],
				'section' => $args['section'],
				'type'    => $args['type'],
			)
		);
	}

	$wp_customize->add_setting(
		'omar_shop_hero_image',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'omar_shop_hero_image',
			array(
				'label'     => __( 'Imagen del hero (opcional)', 'omar-perfumes' ),
				'description' => __( 'Si subes una imagen, reemplaza la composición decorativa de botellas.', 'omar-perfumes' ),
				'section'   => 'omar_shop_hero',
				'mime_type' => 'image',
			)
		)
	);

	foreach ( array( 'price_min' => __( 'Precio mínimo filtro', 'omar-perfumes' ), 'price_max' => __( 'Precio máximo filtro', 'omar-perfumes' ) ) as $key => $label ) {
		$setting_id = 'omar_shop_' . $key;
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => (int) $defaults[ $key ],
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'   => $label,
				'section' => 'omar_shop_sidebar',
				'type'    => 'number',
				'input_attrs' => array(
					'min' => 0,
					'step' => 1,
				),
			)
		);
	}
}
add_action( 'customize_register', 'omar_perfumes_catalog_customize_register' );
