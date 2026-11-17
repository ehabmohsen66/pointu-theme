<?php
/**
 * Shortcodes used inside Elementor (Shortcode widget) so the team can rearrange sections.
 *   [pointu_new_arrivals limit="8" video=""]   – optional video + latest products
 *   [pointu_categories slugs="shoes,bags,women-accessories,men-accessories"]
 *   [pointu_brands]
 *   [pointu_products tag="customizable" limit="4"]
 */
defined( 'ABSPATH' ) || exit;

add_shortcode( 'pointu_new_arrivals', function ( $atts ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}
	$a      = shortcode_atts( array( 'limit' => 8, 'video' => '', 'poster' => '' ), $atts );
	$video  = $a['video'] ?: pointu_opt( 'pointu_newin_video' );
	$poster = $a['poster'] ?: pointu_opt( 'pointu_newin_poster' );
	$limit  = $video ? min( 6, (int) $a['limit'] ) : (int) $a['limit'];
	$cols   = $video ? 3 : 4;
	$html   = '<div class="pt-newin' . ( $video ? ' has-video' : '' ) . '">';
	if ( $video ) {
		$html .= '<div class="pt-newin__video"><video src="' . esc_url( $video ) . '"' . ( $poster ? ' poster="' . esc_url( $poster ) . '"' : '' ) . ' autoplay muted loop playsinline></video><span class="pt-newin__tag">' . esc_html__( 'Just landed', 'pointu' ) . '</span></div>';
	}
	$html .= '<div class="pt-newin__grid">' . do_shortcode( sprintf( '[products limit="%d" columns="%d" orderby="date" order="DESC" visibility="visible"]', $limit, $cols ) ) . '</div>';
	$html .= '</div>';
	return $html;
} );

add_shortcode( 'pointu_products', function ( $atts ) {
	$a = shortcode_atts( array( 'tag' => '', 'category' => '', 'limit' => 4, 'columns' => 4, 'orderby' => 'date' ), $atts );
	return do_shortcode( sprintf( '[products tag="%s" category="%s" limit="%d" columns="%d" orderby="%s" order="DESC"]', esc_attr( $a['tag'] ), esc_attr( $a['category'] ), (int) $a['limit'], (int) $a['columns'], esc_attr( $a['orderby'] ) ) );
} );

add_shortcode( 'pointu_categories', function ( $atts ) {
	$a     = shortcode_atts( array( 'slugs' => 'shoes,bags,women-accessories,men-accessories' ), $atts );
	$cards = '';
	foreach ( array_map( 'trim', explode( ',', $a['slugs'] ) ) as $i => $slug ) {
		$t = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $t ) {
			continue;
		}
		$thumb = get_term_meta( $t->term_id, 'thumbnail_id', true );
		$img   = $thumb ? wp_get_attachment_image( $thumb, 'large', false, array( 'loading' => 'lazy' ) ) : '<span class="pt-cat__fallback">' . esc_html( $t->name ) . '</span>';
		$subs  = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $t->term_id, 'hide_empty' => false, 'number' => 4 ) );
		$sub_l = '';
		if ( $subs && ! is_wp_error( $subs ) ) {
			$sub_l = '<span class="pt-cat__subs">' . esc_html( implode( ' · ', wp_list_pluck( $subs, 'name' ) ) ) . '</span>';
		}
		$cards .= sprintf(
			'<a class="pt-cat pt-cat--%1$d" href="%2$s"><span class="pt-cat__media">%3$s</span><span class="pt-cat__label"><strong>%4$s</strong>%5$s<em>%6$s →</em></span></a>',
			$i,
			esc_url( get_term_link( $t ) ),
			$img,
			esc_html( $t->name ),
			$sub_l,
			esc_html__( 'Shop', 'pointu' )
		);
	}
	return '<div class="pt-cats">' . $cards . '</div>';
} );

add_shortcode( 'pointu_brands', function () {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return '';
	}
	$brands = get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => false ) );
	if ( ! $brands || is_wp_error( $brands ) ) {
		return '';
	}
	$out = '';
	foreach ( $brands as $b ) {
		$thumb = get_term_meta( $b->term_id, 'thumbnail_id', true );
		$mark  = $thumb ? wp_get_attachment_image( $thumb, 'thumbnail' ) : esc_html( mb_substr( $b->name, 0, 1 ) );
		$out  .= '<a class="pt-brand" href="' . esc_url( get_term_link( $b ) ) . '"><span class="pt-brand__mark">' . $mark . '</span><span class="pt-brand__name">' . esc_html( $b->name ) . '</span></a>';
	}
	return '<div class="pt-brands">' . $out . '</div>';
} );
