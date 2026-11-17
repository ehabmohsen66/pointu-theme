<?php
/**
 * POINTU theme bootstrap.
 */
defined( 'ABSPATH' ) || exit;

define( 'POINTU_VER', '1.0.8' );
define( 'POINTU_DIR', get_template_directory() );
define( 'POINTU_URI', get_template_directory_uri() );

require POINTU_DIR . '/inc/settings.php';
require POINTU_DIR . '/inc/template-tags.php';
require POINTU_DIR . '/inc/shortcodes.php';
if ( class_exists( 'WooCommerce' ) ) {
	require POINTU_DIR . '/inc/woocommerce.php';
	require POINTU_DIR . '/inc/whatsapp.php';
}

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'pointu', POINTU_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 480, 'flex-width' => true, 'flex-height' => true ) );
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 600,
		'single_image_width'    => 1000,
		'product_grid'          => array( 'default_columns' => 4, 'default_rows' => 4, 'min_columns' => 2, 'max_columns' => 5 ),
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support( 'elementor' );
	register_nav_menus( array(
		'primary'      => __( 'Main navigation', 'pointu' ),
		'footer_shop'  => __( 'Footer – Shop', 'pointu' ),
		'footer_help'  => __( 'Footer – Help & policies', 'pointu' ),
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'pointu-main', POINTU_URI . '/assets/css/main.css', array(), POINTU_VER );
	wp_enqueue_script( 'pointu-main', POINTU_URI . '/assets/js/main.js', array(), POINTU_VER, true );
	wp_localize_script( 'pointu-main', 'POINTU', array(
		'whatsapp' => pointu_whatsapp_digits(),
	) );
}, 20 );

// Preload the two most-used brand fonts.
add_action( 'wp_head', function () {
	foreach ( array( 'MyriadPro-Regular', 'MyriadPro-Light' ) as $f ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( POINTU_URI . '/assets/fonts/' . $f . '.woff2' ) );
	}
	printf( '<meta name="theme-color" content="#6e2d38">' . "\n" );
}, 1 );

// Elementor: let the theme own colours & fonts.
add_action( 'after_switch_theme', function () {
	update_option( 'elementor_disable_color_schemes', 'yes' );
	update_option( 'elementor_disable_typography_schemes', 'yes' );
} );

// Clean admin bar spacing on front end for logged-in previews is handled by WP.
add_filter( 'body_class', function ( $c ) {
	if ( is_front_page() ) {
		$c[] = 'pt-home';
	}
	return $c;
} );
