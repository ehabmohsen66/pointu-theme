<?php get_header(); ?>
<div class="pt-wrap pt-page pt-404">
	<p class="pt-eyebrow">404</p>
	<h1 class="pt-title"><?php esc_html_e( 'This page stepped out.', 'pointu' ); ?></h1>
	<p><?php esc_html_e( 'The page you are looking for does not exist. Let’s get you back to the collection.', 'pointu' ); ?></p>
	<a class="pt-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to shop', 'pointu' ); ?></a>
</div>
<?php get_footer();
