<?php
/**
 * Shortcodes used inside Elementor (Shortcode widget) so the team can rearrange sections.
 *   [pointu_hero_slider limit="6" category="" tag="" ids="" autoplay="5000"]
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

/** Track whether the front-page hero slider has already rendered. */
function pointu_hero_slider_rendered( $set = null ) {
	static $rendered = false;
	if ( null !== $set ) {
		$rendered = (bool) $set;
	}
	return $rendered;
}

/** Track Elementor .pt-hero container nesting while rendering the page. */
function pointu_hero_container_stack( $action = 'check', $is_hero = false ) {
	static $stack = array();
	if ( 'push' === $action ) {
		$parent_hero = end( $stack ) ?: false;
		$stack[]     = $parent_hero || $is_hero;
	} elseif ( 'pop' === $action ) {
		array_pop( $stack );
	}
	return (bool) end( $stack );
}

/** Pick published, visible products with images for the hero slider. */
function pointu_hero_slider_products( $atts ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array();
	}
	$limit = max( 1, min( 12, (int) ( $atts['limit'] ?? 6 ) ) );

	if ( ! empty( $atts['ids'] ) ) {
		$ids = array_filter( array_map( 'absint', explode( ',', (string) $atts['ids'] ) ) );
		if ( $ids ) {
			$items = wc_get_products( array(
				'status'  => 'publish',
				'include' => $ids,
				'orderby' => 'post__in',
				'limit'   => $limit,
			) );
			return array_values( array_filter( $items, fn( $p ) => (bool) $p->get_image_id() ) );
		}
	}

	$query_args = array(
		'status'       => 'publish',
		'visibility'   => 'visible',
		'stock_status' => 'instock',
		'limit'        => 32,
		'orderby'      => 'date',
		'order'        => 'DESC',
	);
	if ( ! empty( $atts['category'] ) ) {
		$query_args['category'] = array_filter( array_map( 'trim', explode( ',', (string) $atts['category'] ) ) );
	}
	if ( ! empty( $atts['tag'] ) ) {
		$query_args['tag'] = array_filter( array_map( 'trim', explode( ',', (string) $atts['tag'] ) ) );
	}

	$pool = array_values( array_filter( wc_get_products( $query_args ), fn( $p ) => (bool) $p->get_image_id() ) );
	if ( ! $pool ) {
		return array();
	}

	$featured = array();
	$by_cat   = array();
	$shoes    = get_term_by( 'slug', 'shoes', 'product_cat' );
	foreach ( $pool as $p ) {
		if ( $p->is_featured() ) {
			$featured[] = $p;
			continue;
		}
		$cats   = wp_get_post_terms( $p->get_id(), 'product_cat', array( 'fields' => 'ids' ) );
		$cat_id = ( $cats && ! is_wp_error( $cats ) ) ? (int) $cats[0] : 0;
		$by_cat[ $cat_id ][] = $p;
	}

	if ( 'featured' === ( $atts['orderby'] ?? '' ) && $featured ) {
		return array_slice( $featured, 0, $limit );
	}

	// Lead with Shoes categories (matching the hero headline), then interleave remaining categories.
	if ( $shoes && ! is_wp_error( $shoes ) && ! empty( $by_cat ) ) {
		uksort( $by_cat, function ( $a, $b ) use ( $shoes ) {
			$a_shoe = $a === (int) $shoes->term_id || ( $a && term_is_ancestor_of( (int) $shoes->term_id, $a, 'product_cat' ) );
			$b_shoe = $b === (int) $shoes->term_id || ( $b && term_is_ancestor_of( (int) $shoes->term_id, $b, 'product_cat' ) );
			return (int) $b_shoe <=> (int) $a_shoe;
		} );
	}

	// Interleave categories round-robin so the slider spans shoes, bags, and accessories.
	$ordered = $featured;
	while ( count( $ordered ) < $limit && ! empty( $by_cat ) ) {
		foreach ( array_keys( $by_cat ) as $cid ) {
			$item = array_shift( $by_cat[ $cid ] );
			if ( $item ) {
				$ordered[] = $item;
			}
			if ( empty( $by_cat[ $cid ] ) ) {
				unset( $by_cat[ $cid ] );
			}
			if ( count( $ordered ) >= $limit ) {
				break;
			}
		}
	}

	return array_slice( $ordered, 0, $limit );
}

/** Return the primary brand and/or category label for a hero slider product. */
function pointu_hero_slider_meta( $product ) {
	$parts  = array();
	$brands = taxonomy_exists( 'product_brand' ) ? wp_get_post_terms( $product->get_id(), 'product_brand', array( 'fields' => 'names' ) ) : array();
	if ( $brands && ! is_wp_error( $brands ) ) {
		$parts[] = $brands[0];
	}
	$cats = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
	if ( $cats && ! is_wp_error( $cats ) ) {
		$cat = $cats[0];
		if ( 'uncategorized' !== strtolower( $cat ) && ! in_array( $cat, $parts, true ) ) {
			$parts[] = $cat;
		}
	}
	return implode( ' · ', $parts );
}

/** Render the hero product slider markup. */
function pointu_hero_slider_html( $atts = array(), $fallback_html = '' ) {
	$a = shortcode_atts( array(
		'limit'    => 6,
		'orderby'  => 'auto',
		'category' => '',
		'tag'      => '',
		'ids'      => '',
		'autoplay' => 5000,
		'badge'    => __( 'New Arrival', 'pointu' ),
	), $atts );

	pointu_hero_slider_rendered( true );
	$products = pointu_hero_slider_products( $a );

	if ( ! $products ) {
		if ( $fallback_html ) {
			return $fallback_html;
		}
		$shop_url = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
		return '<div class="pt-hero-slider pt-hero-slider--empty"><div class="pt-hero-slider__card pt-hero-slider__empty"><p class="pt-eyebrow">' . esc_html__( 'The collection', 'pointu' ) . '</p><p class="pt-hero-slider__empty-title">' . esc_html__( 'New pieces arriving soon.', 'pointu' ) . '</p><a class="pt-btn" href="' . esc_url( $shop_url ) . '">' . esc_html__( 'Browse the shop', 'pointu' ) . '</a></div></div>';
	}

	$total      = count( $products );
	$autoplay   = max( 0, (int) $a['autoplay'] );
	$badge_text = trim( (string) $a['badge'] );
	$badge_html = $badge_text
		? '<span class="pt-badge pt-badge--new pt-hero-slider__badge">' . esc_html( $badge_text ) . '</span>'
		: '';
	$slides     = '';
	$segments   = '';

	foreach ( $products as $idx => $p ) {
		$is_first = ( 0 === $idx );
		$title    = $p->get_name();
		$url      = get_permalink( $p->get_id() );
		$meta     = pointu_hero_slider_meta( $p );
		$price    = $p->get_price_html();
		$img      = wp_get_attachment_image( $p->get_image_id(), 'large', false, array(
			'class'         => 'pt-hero-slider__img',
			'loading'       => $is_first ? 'eager' : 'lazy',
			'decoding'      => 'async',
			'fetchpriority' => $is_first ? 'high' : 'auto',
			'alt'           => $title,
		) );

		$bottom = $price
			? '<span class="pt-hero-slider__price">' . wp_kses_post( $price ) . '</span>'
			: '<span class="pt-hero-slider__cta">' . esc_html__( 'View piece', 'pointu' ) . '</span>';

		$slide_label = sprintf(
			/* translators: 1: slide number, 2: total slides, 3: product title */
			__( '%1$d of %2$d: %3$s', 'pointu' ),
			$idx + 1,
			$total,
			$title
		);

		$slides .= sprintf(
			'<div class="pt-hero-slider__slide%1$s" data-slide="%2$d" role="group" aria-roledescription="slide" aria-label="%3$s" aria-hidden="%4$s"><a class="pt-hero-slider__link" href="%5$s" tabindex="%6$s"><div class="pt-hero-slider__media">%7$s%8$s</div><div class="pt-hero-slider__info">%9$s<span class="pt-hero-slider__title">%10$s</span>%11$s</div></a></div>',
			$is_first ? ' is-active' : '',
			$idx,
			esc_attr( $slide_label ),
			$is_first ? 'false' : 'true',
			esc_url( $url ),
			$is_first ? '0' : '-1',
			$badge_html,
			$img,
			$meta ? '<span class="pt-hero-slider__meta">' . esc_html( $meta ) . '</span>' : '',
			esc_html( $title ),
			$bottom
		);
		$segments .= '<span class="pt-hero-slider__seg' . ( $is_first ? ' is-active' : '' ) . '"></span>';
	}

	if ( 1 === $total ) {
		return '<div class="pt-hero-slider pt-hero-slider--single"><div class="pt-hero-slider__card"><div class="pt-hero-slider__viewport"><div class="pt-hero-slider__track">' . $slides . '</div></div></div></div>';
	}

	$count_text = sprintf( '%02d / %02d', 1, $total );
	$controls   = '<button type="button" class="pt-hero-slider__btn pt-hero-slider__prev" aria-label="' . esc_attr__( 'Previous product', 'pointu' ) . '">' . pointu_icon( 'chevron-left' ) . '</button>';
	if ( $autoplay > 0 ) {
		$controls .= '<button type="button" class="pt-hero-slider__btn pt-hero-slider__toggle" aria-label="' . esc_attr__( 'Pause product slider', 'pointu' ) . '" aria-pressed="false">' . pointu_icon( 'pause' ) . pointu_icon( 'play' ) . '</button>';
	}
	$controls .= '<button type="button" class="pt-hero-slider__btn pt-hero-slider__next" aria-label="' . esc_attr__( 'Next product', 'pointu' ) . '">' . pointu_icon( 'chevron-right' ) . '</button>';

	return sprintf(
		'<section class="pt-hero-slider" data-pt-hero-slider data-autoplay="%1$d" aria-roledescription="carousel" aria-label="%2$s"><div class="pt-hero-slider__card"><div class="pt-hero-slider__viewport"><div class="pt-hero-slider__track">%3$s</div></div><div class="pt-hero-slider__bar"><div class="pt-hero-slider__status"><span class="pt-hero-slider__count" aria-live="off">%4$s</span><div class="pt-hero-slider__segments" aria-hidden="true">%5$s</div></div><div class="pt-hero-slider__controls">%6$s</div></div></div></section>',
		$autoplay,
		esc_attr__( 'Featured products', 'pointu' ),
		$slides,
		esc_html( $count_text ),
		$segments,
		$controls
	);
}

add_shortcode( 'pointu_hero_slider', fn( $atts ) => pointu_hero_slider_html( (array) $atts ) );

add_action( 'elementor/frontend/container/before_render', function ( $element ) {
	$classes = (string) $element->get_settings( 'css_classes' );
	pointu_hero_container_stack( 'push', false !== strpos( $classes, 'pt-hero' ) );
} );

add_action( 'elementor/frontend/container/after_render', function () {
	pointu_hero_container_stack( 'pop' );
} );

add_filter( 'elementor/element/is_dynamic_content', function ( $is_dynamic, $raw_data ) {
	$classes = $raw_data['settings']['_css_classes'] ?? ( $raw_data['settings']['css_classes'] ?? '' );
	if ( false !== strpos( (string) $classes, 'pt-hero-art' ) || false !== strpos( (string) $classes, 'pt-hero' ) ) {
		return true;
	}
	return $is_dynamic;
}, 10, 2 );

add_filter( 'elementor/widget/render_content', function ( $content, $widget ) {
	if ( is_admin() || ! is_front_page() || ! class_exists( 'WooCommerce' ) ) {
		return $content;
	}
	if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
		return $content;
	}
	if ( 'image' !== $widget->get_name() || pointu_hero_slider_rendered() ) {
		return $content;
	}
	$classes = (string) $widget->get_settings( '_css_classes' );
	if ( false !== strpos( $classes, 'pt-hero-art' ) || pointu_hero_container_stack() ) {
		return pointu_hero_slider_html( array(), $content );
	}
	return $content;
}, 10, 2 );
