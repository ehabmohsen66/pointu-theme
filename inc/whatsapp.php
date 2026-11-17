<?php
/**
 * "Discuss on WhatsApp" on product pages — prefilled with product name, chosen options and link.
 */
defined( 'ABSPATH' ) || exit;

function pointu_product_wa_text( $product ) {
	return trim( pointu_opt( 'pointu_whatsapp_msg' ) ) . "\n" . $product->get_name() . ( $product->get_sku() ? ' (' . $product->get_sku() . ')' : '' ) . "\n" . get_permalink( $product->get_id() );
}

add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	if ( ! $product ) {
		return;
	}
	printf(
		'<div class="pt-actions-alt"><span class="pt-or">%1$s</span><a class="pt-btn pt-btn--wa" target="_blank" rel="noopener" href="%2$s" data-pt-wa data-intro="%3$s" data-name="%4$s" data-url="%5$s">%6$s<span>%7$s</span></a><p class="pt-wa-hint">%8$s</p></div>',
		esc_html__( 'or', 'pointu' ),
		esc_url( pointu_whatsapp_url( pointu_product_wa_text( $product ) ) ),
		esc_attr( pointu_opt( 'pointu_whatsapp_msg' ) ),
		esc_attr( $product->get_name() . ( $product->get_sku() ? ' (' . $product->get_sku() . ')' : '' ) ),
		esc_url( get_permalink( $product->get_id() ) ),
		pointu_icon( 'whatsapp' ), // phpcs:ignore
		esc_html__( 'Discuss on WhatsApp', 'pointu' ),
		esc_html__( 'Confirm sizing, customisation or payment with our team.', 'pointu' )
	);
}, 31 );
