<?php
defined( 'ABSPATH' ) || exit;

function pointu_icon( $name ) {
	$i = array(
		'search'    => '<path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Zm10 3-4.6-4.6" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
		'account'   => '<path d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm-8 9c.6-4 3.9-6.5 8-6.5s7.4 2.5 8 6.5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
		'bag'       => '<path d="M5 8h14l-1 13H6L5 8Zm4 0V6a3 3 0 0 1 6 0v2" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>',
		'menu'      => '<path d="M3 7h18M3 12h18M3 17h18" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
		'close'     => '<path d="m5 5 14 14M19 5 5 19" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
		'whatsapp'  => '<path fill="currentColor" d="M19.1 4.9A9.9 9.9 0 0 0 3.5 16.8L2 22l5.3-1.4A9.9 9.9 0 0 0 22 12a9.8 9.8 0 0 0-2.9-7.1ZM12 20.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3.1.8.8-3-.2-.3a8.2 8.2 0 1 1 7 3.9Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.1 5.1 0 0 0 1.1 2.7 11.7 11.7 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.3c0-.1-.2-.2-.5-.3Z"/>',
		'instagram' => '<path fill="none" stroke="currentColor" stroke-width="1.5" d="M7.5 3h9A4.5 4.5 0 0 1 21 7.5v9a4.5 4.5 0 0 1-4.5 4.5h-9A4.5 4.5 0 0 1 3 16.5v-9A4.5 4.5 0 0 1 7.5 3Zm4.5 13.2a4.2 4.2 0 1 0 0-8.4 4.2 4.2 0 0 0 0 8.4ZM17.3 6.7h.01"/>',
		'facebook'  => '<path fill="currentColor" d="M13.5 21v-7.6h2.6l.4-3h-3V8.5c0-.9.3-1.5 1.5-1.5h1.6V4.3a21 21 0 0 0-2.3-.1c-2.3 0-3.9 1.4-3.9 4V10.4H7.8v3h2.6V21h3.1Z"/>',
		'mail'      => '<path d="M3 6h18v12H3V6Zm0 0 9 7 9-7" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>',
		'pin'       => '<path d="M12 21s7-6.2 7-11.5a7 7 0 1 0-14 0C5 14.8 12 21 12 21Zm0-9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" fill="none" stroke="currentColor" stroke-width="1.4"/>',
		'truck'     => '<path d="M2 6h12v10H2V6Zm12 4h4l3 3v3h-7v-6ZM6.5 19a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6Zm11 0a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6Z" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>',
		'arrow'     => '<path d="M4 12h15m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>',
	);
	return '<svg class="pt-icon pt-icon-' . esc_attr( $name ) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . ( $i[ $name ] ?? '' ) . '</svg>';
}

function pointu_logo( $variant = 'burgundy' ) {
	if ( 'burgundy' === $variant && has_custom_logo() ) {
		return get_custom_logo();
	}
	return sprintf(
		'<a class="pt-logo" href="%s" rel="home" aria-label="POINTU – home"><img src="%s" alt="POINTU" width="900" height="220"></a>',
		esc_url( home_url( '/' ) ),
		esc_url( POINTU_URI . '/assets/img/logo-' . $variant . '.png' )
	);
}

function pointu_whatsapp_url( $text = '' ) {
	$url = 'https://wa.me/' . pointu_whatsapp_digits();
	return $text ? $url . '?text=' . rawurlencode( $text ) : $url;
}

function pointu_social_links() {
	$out = array();
	if ( pointu_opt( 'pointu_instagram' ) ) {
		$out[] = '<a href="' . esc_url( pointu_opt( 'pointu_instagram' ) ) . '" target="_blank" rel="noopener" aria-label="Instagram">' . pointu_icon( 'instagram' ) . '</a>';
	}
	if ( pointu_opt( 'pointu_facebook' ) ) {
		$out[] = '<a href="' . esc_url( pointu_opt( 'pointu_facebook' ) ) . '" target="_blank" rel="noopener" aria-label="Facebook">' . pointu_icon( 'facebook' ) . '</a>';
	}
	$out[] = '<a href="' . esc_url( pointu_whatsapp_url() ) . '" target="_blank" rel="noopener" aria-label="WhatsApp">' . pointu_icon( 'whatsapp' ) . '</a>';
	return '<div class="pt-social">' . implode( '', $out ) . '</div>';
}

function pointu_cart_count_html() {
	$n = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	return '<span class="pt-cart-count' . ( $n ? '' : ' is-empty' ) . '">' . (int) $n . '</span>';
}

/** Fallback menu when none is assigned. */
function pointu_fallback_menu() {
	echo '<ul class="pt-menu"><li><a href="' . esc_url( home_url( '/shop/' ) ) . '">Shop</a></li></ul>';
}

/** Walker that adds a caret button to parents (mobile accordion). */
class Pointu_Walker extends Walker_Nav_Menu {
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= "</li>\n";
	}
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		parent::start_el( $output, $item, $depth, $args, $id );
		if ( in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
			$output .= '<button class="pt-sub-toggle" type="button" aria-label="' . esc_attr__( 'Open submenu', 'pointu' ) . '" aria-expanded="false"></button>';
		}
	}
}
