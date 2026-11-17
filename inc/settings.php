<?php
/**
 * Brand settings in Appearance → Customize → POINTU Store Settings.
 */
defined( 'ABSPATH' ) || exit;

function pointu_defaults() {
	return array(
		'pointu_announcement'    => 'COMPLIMENTARY GIFT WRAPPING  ·  DELIVERY ALL OVER LEBANON',
		'pointu_whatsapp'        => '+961 79 067 677',
		'pointu_whatsapp_msg'    => "Hello POINTU, I'd like to ask about:",
		'pointu_instagram'       => 'https://www.instagram.com/pointuofficial',
		'pointu_facebook'        => '',
		'pointu_email'           => '',
		'pointu_address'         => 'Dbayeh, Lebanon',
		'pointu_map_url'         => 'https://maps.google.com/?q=Dbayeh,Lebanon',
		'pointu_delivery_note'   => 'Delivery all over Lebanon within 2–5 working days.',
		'pointu_newin_video'     => '',
		'pointu_newin_poster'    => '',
		'pointu_tagline'         => 'DESIGNED TO YOUR POINT',
	);
}

function pointu_opt( $key ) {
	$d = pointu_defaults();
	return get_theme_mod( $key, $d[ $key ] ?? '' );
}

function pointu_whatsapp_digits() {
	return preg_replace( '/\D+/', '', (string) pointu_opt( 'pointu_whatsapp' ) );
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'pointu_store', array(
		'title'    => __( 'POINTU Store Settings', 'pointu' ),
		'priority' => 30,
	) );
	$fields = array(
		'pointu_announcement'  => array( __( 'Announcement bar text', 'pointu' ), 'text' ),
		'pointu_tagline'       => array( __( 'Footer tagline', 'pointu' ), 'text' ),
		'pointu_whatsapp'      => array( __( 'WhatsApp number (with country code)', 'pointu' ), 'text' ),
		'pointu_whatsapp_msg'  => array( __( 'WhatsApp pre-filled message intro', 'pointu' ), 'text' ),
		'pointu_instagram'     => array( __( 'Instagram URL', 'pointu' ), 'url' ),
		'pointu_facebook'      => array( __( 'Facebook page URL (leave empty to hide)', 'pointu' ), 'url' ),
		'pointu_email'         => array( __( 'Contact email (leave empty to hide)', 'pointu' ), 'email' ),
		'pointu_address'       => array( __( 'Address / location', 'pointu' ), 'text' ),
		'pointu_map_url'       => array( __( 'Google Maps link', 'pointu' ), 'url' ),
		'pointu_delivery_note' => array( __( 'Delivery note (footer & product page)', 'pointu' ), 'textarea' ),
		'pointu_newin_video'   => array( __( 'New Arrivals video URL (.mp4) – optional', 'pointu' ), 'url' ),
		'pointu_newin_poster'  => array( __( 'New Arrivals video poster image URL – optional', 'pointu' ), 'url' ),
	);
	$d = pointu_defaults();
	foreach ( $fields as $id => $f ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $d[ $id ],
			'sanitize_callback' => 'url' === $f[1] ? 'esc_url_raw' : ( 'email' === $f[1] ? 'sanitize_email' : 'sanitize_textarea_field' ),
		) );
		$wp_customize->add_control( $id, array( 'label' => $f[0], 'section' => 'pointu_store', 'type' => $f[1] ) );
	}
} );
