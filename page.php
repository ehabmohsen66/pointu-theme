<?php
get_header();
while ( have_posts() ) :
	the_post();
	$is_elementor = class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->documents->get( get_the_ID() ) && \Elementor\Plugin::$instance->documents->get( get_the_ID() )->is_built_with_elementor();
	if ( $is_elementor ) {
		the_content();
		continue;
	}
	$wc_page = function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() || is_account_page() );
	$wide    = $wc_page || preg_match( '/\[(pointu_|products|product_category)/', get_post()->post_content );
	?>
	<div class="pt-wrap pt-page<?php echo $wc_page ? ' pt-page--wc' : ( $wide ? ' pt-page--wide' : ' pt-page--text' ); ?>">
		<header class="pt-page__head">
			<?php if ( ! $wc_page ) : ?><p class="pt-eyebrow">POINTU</p><?php endif; ?>
			<h1 class="pt-title"><?php the_title(); ?></h1>
		</header>
		<div class="pt-content"><?php the_content(); ?></div>
	</div>
	<?php
endwhile;
get_footer();
