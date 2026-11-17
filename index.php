<?php get_header(); ?>
<div class="pt-wrap pt-page">
	<?php if ( have_posts() ) : ?>
		<header class="pt-page__head"><h1 class="pt-title"><?php echo is_search() ? esc_html( sprintf( __( 'Results for “%s”', 'pointu' ), get_search_query() ) ) : wp_kses_post( get_the_archive_title() ?: get_bloginfo( 'name' ) ); ?></h1></header>
		<div class="pt-posts">
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'pt-post' ); ?>><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article>
		<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'pointu' ); ?></p>
	<?php endif; ?>
</div>
<?php get_footer();
