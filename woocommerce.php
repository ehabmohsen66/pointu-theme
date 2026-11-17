<?php
get_header();
if ( is_singular( 'product' ) ) {
	echo '<div class="pt-wrap pt-product-wrap">';
	woocommerce_content();
	echo '</div>';
} else {
	do_action( 'pointu_before_shop' );
	echo '<div class="pt-wrap pt-shop">';
	woocommerce_content();
	echo '</div>';
}
get_footer();
