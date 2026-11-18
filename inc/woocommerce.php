<?php
/**
 * WooCommerce customisations for POINTU.
 */
defined( 'ABSPATH' ) || exit;

/* ---------- Layout ---------- */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
add_filter( 'woocommerce_show_page_title', '__return_false' );
remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );

add_filter( 'loop_shop_columns', fn() => 4 );
add_filter( 'loop_shop_per_page', fn() => 16 );
add_filter( 'woocommerce_output_related_products_args', function ( $a ) {
	$a['posts_per_page'] = 4;
	$a['columns']        = 4;
	return $a;
} );

add_filter( 'woocommerce_breadcrumb_defaults', function ( $d ) {
	$d['delimiter']   = '<span class="sep">/</span>';
	$d['wrap_before'] = '<nav class="woocommerce-breadcrumb pt-crumbs" aria-label="Breadcrumb">';
	return $d;
} );

/* ---------- Shop header: title, category chips, filters ---------- */
add_action( 'pointu_before_shop', function () {
	$title = woocommerce_page_title( false );
	$eyebrow = __( 'The collection', 'pointu' );
	$desc  = '';
	$term  = is_product_taxonomy() ? get_queried_object() : null;
	if ( $term instanceof WP_Term ) {
		$desc    = term_description( $term );
		$eyebrow = 'product_brand' === $term->taxonomy ? __( 'Shop by brand', 'pointu' ) : __( 'Shop by category', 'pointu' );
		if ( $term->parent ) {
			$parent  = get_term( $term->parent, $term->taxonomy );
			$eyebrow = $parent && ! is_wp_error( $parent ) ? $parent->name : $eyebrow;
		}
	} elseif ( is_search() ) {
		$eyebrow = __( 'Search', 'pointu' );
	}
	echo '<section class="pt-shop-hero">';
	echo pointu_shop_collage_html( $term ); // phpcs:ignore
	echo '<div class="pt-wrap">';
	woocommerce_breadcrumb();
	echo '<p class="pt-eyebrow">' . esc_html( $eyebrow ) . '</p>';
	echo '<h1 class="pt-shop-hero__title">' . esc_html( $title ) . '</h1>';
	if ( $desc ) {
		echo '<div class="pt-shop-hero__desc">' . wp_kses_post( $desc ) . '</div>';
	}
	echo '</div></section>';
	pointu_category_chips();
} );

/** Render a looping product photo collage for the shop hero. */
function pointu_shop_collage_html( $term = null ) {
	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 10,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'fields'         => 'ids',
		'meta_query'     => array( array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ) ), // phpcs:ignore
	);
	if ( $term instanceof WP_Term ) {
		$args['tax_query'] = array( // phpcs:ignore
			array(
				'taxonomy' => $term->taxonomy,
				'field'    => 'term_id',
				'terms'    => $term->term_id,
			),
		);
	}
	$ids = get_posts( $args );
	if ( count( $ids ) < 4 && isset( $args['tax_query'] ) ) {
		unset( $args['tax_query'] );
		$ids = get_posts( $args );
	}
	if ( ! $ids ) {
		return '';
	}
	$items = '';
	foreach ( $ids as $id ) {
		$thumb = get_the_post_thumbnail( $id, 'woocommerce_thumbnail', array( 'loading' => 'eager', 'alt' => get_the_title( $id ) ) );
		if ( $thumb ) {
			$items .= '<a class="pt-shop-collage__item" href="' . esc_url( get_permalink( $id ) ) . '" tabindex="-1">' . $thumb . '</a>';
		}
	}
	if ( ! $items ) {
		return '';
	}
	return '<div class="pt-shop-collage" aria-hidden="true"><div class="pt-shop-collage__track">' . $items . $items . '</div></div>';
}

/** Category pills: children of current category, or top level on the main shop. */
function pointu_category_chips() {
	$current = is_product_category() ? get_queried_object() : null;
	$parent_id = 0;
	if ( $current ) {
		$children = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $current->term_id, 'hide_empty' => false ) );
		$parent_id = $children ? $current->term_id : $current->parent;
	}
	if ( is_product_taxonomy() && ! $current ) {
		return; // brand / tag archives: no chips.
	}
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'parent'     => $parent_id,
		'hide_empty' => false,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		'orderby'    => 'meta_value_num',
		'meta_key'   => 'order',
	) );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}
	$all_url   = $parent_id ? get_term_link( $parent_id, 'product_cat' ) : wc_get_page_permalink( 'shop' );
	$all_label = __( 'All', 'pointu' );
	echo '<div class="pt-wrap"><nav class="pt-chips" aria-label="' . esc_attr__( 'Categories', 'pointu' ) . '">';
	$is_all = ( $current && $current->term_id === $parent_id ) || ( ! $current && is_shop() );
	echo '<a class="pt-chip' . ( $is_all ? ' is-active' : '' ) . '" href="' . esc_url( $all_url ) . '">' . esc_html( $all_label ) . '</a>';
	foreach ( $terms as $t ) {
		$active = $current && ( $current->term_id === $t->term_id );
		echo '<a class="pt-chip' . ( $active ? ' is-active' : '' ) . '" href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>';
	}
	echo '</nav></div>';
}

/** Is the current archive the Shoes category (or one of its children)? */
function pointu_is_shoes_archive() {
	if ( ! is_product_category() ) {
		return false;
	}
	$shoes = get_term_by( 'slug', 'shoes', 'product_cat' );
	if ( ! $shoes ) {
		return false;
	}
	$t = get_queried_object();
	return $t->term_id === $shoes->term_id || term_is_ancestor_of( $shoes, $t, 'product_cat' );
}

/* Toolbar: result count + filters (shoes only) + ordering */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
add_action( 'woocommerce_before_shop_loop', function () {
	echo '<div class="pt-toolbar">';
	if ( pointu_is_shoes_archive() ) {
		pointu_shoe_filters();
	} else {
		woocommerce_result_count();
	}
	woocommerce_catalog_ordering();
	echo '</div>';
}, 20 );

function pointu_shoe_filters() {
	$brands = taxonomy_exists( 'product_brand' ) ? get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => true ) ) : array();
	$sizes  = taxonomy_exists( 'pa_size' ) ? get_terms( array( 'taxonomy' => 'pa_size', 'hide_empty' => false, 'orderby' => 'name' ) ) : array();
	$g      = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification
	$prices = array( '' => __( 'Any price', 'pointu' ), '0-50' => __( 'Under $50', 'pointu' ), '50-100' => '$50 – $100', '100-150' => '$100 – $150', '150-9999' => __( '$150 +', 'pointu' ) );
	$cur_price = isset( $g['min_price'], $g['max_price'] ) ? (int) $g['min_price'] . '-' . (int) $g['max_price'] : '';
	?>
	<form class="pt-filters" method="get" action="">
		<span class="pt-filters__label"><?php esc_html_e( 'Filter', 'pointu' ); ?></span>
		<?php if ( $brands && ! is_wp_error( $brands ) ) : ?>
		<label><span class="screen-reader-text"><?php esc_html_e( 'Brand', 'pointu' ); ?></span>
			<select name="product_brand" onchange="this.form.submit()">
				<option value=""><?php esc_html_e( 'All brands', 'pointu' ); ?></option>
				<?php foreach ( $brands as $b ) : ?>
					<option value="<?php echo esc_attr( $b->slug ); ?>" <?php selected( $g['product_brand'] ?? '', $b->slug ); ?>><?php echo esc_html( $b->name ); ?></option>
				<?php endforeach; ?>
			</select></label>
		<?php endif; ?>
		<?php if ( $sizes && ! is_wp_error( $sizes ) ) : ?>
		<label><span class="screen-reader-text"><?php esc_html_e( 'Size', 'pointu' ); ?></span>
			<select name="filter_size" onchange="this.form.submit()">
				<option value=""><?php esc_html_e( 'All sizes', 'pointu' ); ?></option>
				<?php foreach ( $sizes as $s ) : ?>
					<option value="<?php echo esc_attr( $s->slug ); ?>" <?php selected( $g['filter_size'] ?? '', $s->slug ); ?>><?php echo esc_html( sprintf( __( 'EU %s', 'pointu' ), $s->name ) ); ?></option>
				<?php endforeach; ?>
			</select></label>
		<?php endif; ?>
		<label><span class="screen-reader-text"><?php esc_html_e( 'Price', 'pointu' ); ?></span>
			<select class="pt-price-select" onchange="pointuPrice(this)">
				<?php foreach ( $prices as $v => $l ) : ?>
					<option value="<?php echo esc_attr( $v ); ?>" <?php selected( $cur_price, $v ); ?>><?php echo esc_html( $l ); ?></option>
				<?php endforeach; ?>
			</select></label>
		<input type="hidden" name="min_price" value="<?php echo esc_attr( $g['min_price'] ?? '' ); ?>" <?php disabled( empty( $g['min_price'] ) && '0' !== ( $g['min_price'] ?? '' ) ); ?>>
		<input type="hidden" name="max_price" value="<?php echo esc_attr( $g['max_price'] ?? '' ); ?>" <?php disabled( empty( $g['max_price'] ) ); ?>>
		<?php if ( ! empty( $g['orderby'] ) ) : ?><input type="hidden" name="orderby" value="<?php echo esc_attr( $g['orderby'] ); ?>"><?php endif; ?>
		<?php if ( array_intersect_key( $g, array_flip( array( 'product_brand', 'filter_size', 'min_price', 'max_price' ) ) ) ) : ?>
			<a class="pt-filters__clear" href="<?php echo esc_url( get_term_link( get_queried_object() ) ); ?>"><?php esc_html_e( 'Clear', 'pointu' ); ?></a>
		<?php endif; ?>
		<?php woocommerce_result_count(); ?>
	</form>
	<?php
}

/* Drop empty GET params so URLs stay clean. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( isset( $_GET['product_brand'] ) && '' === $_GET['product_brand'] ) { // phpcs:ignore
		$q->set( 'product_brand', '' );
	}
} );

/* ---------- Product cards ---------- */
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

add_action( 'woocommerce_before_shop_loop_item_title', function () {
	global $product;
	echo '<div class="pt-card__media">';
	echo pointu_badges( $product ); // phpcs:ignore
	echo woocommerce_get_product_thumbnail( 'woocommerce_thumbnail' ); // phpcs:ignore
	$gallery = $product->get_gallery_image_ids();
	if ( $gallery ) {
		echo wp_get_attachment_image( $gallery[0], 'woocommerce_thumbnail', false, array( 'class' => 'pt-card__alt', 'loading' => 'lazy', 'alt' => '' ) );
	}
	echo '</div>';
}, 10 );

add_action( 'woocommerce_shop_loop_item_title', function () {
	global $product;
	$brands = taxonomy_exists( 'product_brand' ) ? wp_get_post_terms( $product->get_id(), 'product_brand', array( 'fields' => 'names' ) ) : array();
	if ( $brands && ! is_wp_error( $brands ) ) {
		echo '<span class="pt-card__brand">' . esc_html( $brands[0] ) . '</span>';
	}
}, 5 );

function pointu_badges( $product ) {
	$b = array();
	if ( $product->is_on_sale() ) {
		$b[] = '<span class="pt-badge pt-badge--sale">' . esc_html__( 'Sale', 'pointu' ) . '</span>';
	}
	if ( pointu_is_new( $product ) ) {
		$b[] = '<span class="pt-badge pt-badge--new">' . esc_html__( 'New', 'pointu' ) . '</span>';
	}
	if ( has_term( 'customizable', 'product_tag', $product->get_id() ) ) {
		$b[] = '<span class="pt-badge pt-badge--custom">' . esc_html__( 'Customizable', 'pointu' ) . '</span>';
	}
	if ( ! $product->is_in_stock() ) {
		$b[] = '<span class="pt-badge pt-badge--out">' . esc_html__( 'Sold out', 'pointu' ) . '</span>';
	}
	return $b ? '<div class="pt-badges">' . implode( '', $b ) . '</div>' : '';
}

/** A product is "new" if tagged new-arrival, or created in the last 30 days. */
function pointu_is_new( $product ) {
	if ( has_term( 'new-arrival', 'product_tag', $product->get_id() ) ) {
		return true;
	}
	$created = $product->get_date_created();
	return $created && ( time() - $created->getTimestamp() ) < 30 * DAY_IN_SECONDS;
}

/* ---------- Single product ---------- */
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
add_action( 'woocommerce_before_single_product', 'woocommerce_breadcrumb', 5 );

add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	$brands = taxonomy_exists( 'product_brand' ) ? wp_get_post_terms( $product->get_id(), 'product_brand' ) : array();
	if ( $brands && ! is_wp_error( $brands ) ) {
		echo '<a class="pt-eyebrow pt-single-brand" href="' . esc_url( get_term_link( $brands[0] ) ) . '">' . esc_html( $brands[0]->name ) . '</a>';
	}
}, 4 );

add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	$color = get_post_meta( $product->get_id(), '_pointu_color_desc', true );
	if ( $color ) {
		echo '<p class="pt-color-desc"><span>' . esc_html__( 'Colour', 'pointu' ) . '</span> ' . esc_html( $color ) . '</p>';
	}
	if ( has_term( 'customizable', 'product_tag', $product->get_id() ) ) {
		echo '<div class="pt-custom-note"><strong>' . esc_html__( 'Make it yours', 'pointu' ) . '</strong> ' . esc_html__( 'Choose your colour, material and heel height below. Each pair is prepared to order.', 'pointu' ) . '</div>';
	}
}, 25 );

add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	echo '<ul class="pt-assurance">';
	echo '<li>' . pointu_icon( 'truck' ) . esc_html( pointu_opt( 'pointu_delivery_note' ) ) . '</li>'; // phpcs:ignore
	if ( $product->get_sku() ) {
		echo '<li class="pt-sku">' . esc_html__( 'Ref.', 'pointu' ) . ' <span class="sku">' . esc_html( $product->get_sku() ) . '</span></li>';
	}
	echo '</ul>';
}, 45 );

/* "Purchase Online" wording + go straight to cart after adding. */
add_filter( 'woocommerce_product_single_add_to_cart_text', fn() => __( 'Purchase online', 'pointu' ) );
add_filter( 'woocommerce_product_add_to_cart_text', function ( $t, $p ) {
	return $p->is_type( 'variable' ) ? __( 'Choose options', 'pointu' ) : __( 'Add to bag', 'pointu' );
}, 10, 2 );
add_filter( 'pre_option_woocommerce_cart_redirect_after_add', fn() => 'yes' );

/* Color description admin field (simple text, shows on product page). */
add_action( 'woocommerce_product_options_general_product_data', function () {
	woocommerce_wp_text_input( array(
		'id'          => '_pointu_color_desc',
		'label'       => __( 'Colour description', 'pointu' ),
		'placeholder' => __( 'e.g. Bordeaux patent leather with gold hardware', 'pointu' ),
		'desc_tip'    => true,
		'description' => __( 'Shown under the price on the product page.', 'pointu' ),
	) );
} );
add_action( 'woocommerce_admin_process_product_object', function ( $product ) {
	if ( isset( $_POST['_pointu_color_desc'] ) ) { // phpcs:ignore
		$product->update_meta_data( '_pointu_color_desc', sanitize_text_field( wp_unslash( $_POST['_pointu_color_desc'] ) ) ); // phpcs:ignore
	}
} );

/* Tabs: simpler labels. */
add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
	unset( $tabs['reviews'] );
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = __( 'Details', 'pointu' );
	}
	$tabs['pointu_delivery'] = array(
		'title'    => __( 'Delivery & returns', 'pointu' ),
		'priority' => 40,
		'callback' => function () {
			$page = get_page_by_path( 'delivery-information' );
			echo '<p>' . esc_html( pointu_opt( 'pointu_delivery_note' ) ) . '</p>';
			echo '<p>' . esc_html__( 'Payment by cash on delivery, Whish Money or bank transfer. Customised pairs are made to order and cannot be exchanged unless faulty.', 'pointu' ) . '</p>';
			if ( $page ) {
				echo '<p><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html__( 'Read the full delivery information', 'pointu' ) . ' →</a></p>';
			}
		},
	);
	return $tabs;
}, 98 );

/* ---------- Cart count fragment ---------- */
add_filter( 'woocommerce_add_to_cart_fragments', function ( $f ) {
	$f['span.pt-cart-count'] = pointu_cart_count_html();
	return $f;
} );

/* ---------- Checkout tuned for Lebanon ---------- */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	foreach ( array( 'billing', 'shipping' ) as $g ) {
		unset( $fields[ $g ][ $g . '_company' ], $fields[ $g ][ $g . '_postcode' ] );
		if ( isset( $fields[ $g ][ $g . '_state' ] ) ) {
			$fields[ $g ][ $g . '_state' ]['required'] = false;
		}
		if ( isset( $fields[ $g ][ $g . '_address_1' ] ) ) {
			$fields[ $g ][ $g . '_address_1' ]['placeholder'] = __( 'Area, street, building, floor', 'pointu' );
		}
		if ( isset( $fields[ $g ][ $g . '_city' ] ) ) {
			$fields[ $g ][ $g . '_city' ]['label'] = __( 'City / Town', 'pointu' );
		}
	}
	$fields['billing']['billing_phone']['required'] = true;
	$fields['billing']['billing_phone']['priority'] = 25;
	$fields['billing']['billing_phone']['label']    = __( 'Mobile (WhatsApp)', 'pointu' );
	$fields['billing']['billing_email']['required'] = false;
	return $fields;
} );
add_filter( 'default_checkout_billing_country', fn() => 'LB' );
add_filter( 'default_checkout_shipping_country', fn() => 'LB' );

/* ---------- Whish Money offline gateway ---------- */
add_action( 'plugins_loaded', 'pointu_register_whish', 20 );
pointu_register_whish();
function pointu_register_whish() {
	static $done = false;
	if ( $done || ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}
	$done = true;
	require_once POINTU_DIR . '/inc/class-pointu-gateway-whish.php';
	add_filter( 'woocommerce_payment_gateways', function ( $g ) {
		$g[] = 'Pointu_Gateway_Whish';
		return $g;
	} );
}

/* No "added to your cart" banner — customers land on the cart and see the item already. */
add_filter( 'wc_add_to_cart_message_html', '__return_empty_string' );

/* ---------- Checkout simplification ---------- */
// Ship to the billing address only (no "Ship to a different address?").
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );
add_filter( 'pre_option_woocommerce_ship_to_destination', fn() => 'billing_only' );

// Coupon: move from the big banner at the top into the "Your order" box.
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
add_action( 'woocommerce_checkout_order_review', function () {
	if ( ! wc_coupons_enabled() ) {
		return;
	}
	?>
	<div class="pt-co-coupon">
		<button type="button" class="pt-co-coupon__toggle" aria-expanded="false"><?php esc_html_e( 'Have a coupon code?', 'pointu' ); ?></button>
		<div class="pt-co-coupon__row" hidden>
			<input type="text" class="pt-co-coupon__input" placeholder="<?php esc_attr_e( 'Coupon code', 'pointu' ); ?>" aria-label="<?php esc_attr_e( 'Coupon code', 'pointu' ); ?>">
			<button type="button" class="button pt-co-coupon__apply"><?php esc_html_e( 'Apply', 'pointu' ); ?></button>
		</div>
		<div class="pt-co-coupon__msg" role="status"></div>
	</div>
	<?php
}, 15 );
add_filter( 'gettext', function ( $t, $text, $domain ) {
	if ( 'woocommerce' === $domain && in_array( $text, array( 'Billing &amp; Shipping', 'Billing details' ), true ) ) {
		return __( 'Delivery details', 'pointu' );
	}
	return $t;
}, 10, 3 );
