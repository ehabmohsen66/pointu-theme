<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'pointu' ); ?></a>

<?php if ( pointu_opt( 'pointu_announcement' ) ) : ?>
<div class="pt-announce"><?php echo esc_html( pointu_opt( 'pointu_announcement' ) ); ?></div>
<?php endif; ?>

<header class="pt-header" id="pt-header">
	<div class="pt-wrap pt-header__row">
		<div class="pt-header__left">
			<button class="pt-iconbtn pt-burger" type="button" aria-controls="pt-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menu', 'pointu' ); ?>"><?php echo pointu_icon( 'menu' ); ?></button>
			<a class="pt-header__wa" href="<?php echo esc_url( pointu_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo pointu_icon( 'whatsapp' ); ?><span><?php echo esc_html( pointu_opt( 'pointu_whatsapp' ) ); ?></span></a>
		</div>
		<div class="pt-header__logo"><?php echo pointu_logo( 'burgundy' ); ?></div>
		<div class="pt-header__icons">
			<button class="pt-iconbtn pt-search-open" type="button" aria-controls="pt-search" aria-label="<?php esc_attr_e( 'Search', 'pointu' ); ?>"><?php echo pointu_icon( 'search' ); ?></button>
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<a class="pt-iconbtn pt-hide-sm" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'My account', 'pointu' ); ?>"><?php echo pointu_icon( 'account' ); ?></a>
			<a class="pt-iconbtn pt-cart-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'pointu' ); ?>"><?php echo pointu_icon( 'bag' ); ?><?php echo pointu_cart_count_html(); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<nav class="pt-nav" id="pt-nav" aria-label="<?php esc_attr_e( 'Main', 'pointu' ); ?>">
		<div class="pt-nav__head">
			<?php echo pointu_logo( 'burgundy' ); ?>
			<button class="pt-iconbtn pt-nav-close" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'pointu' ); ?>"><?php echo pointu_icon( 'close' ); ?></button>
		</div>
		<div class="pt-wrap">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'pt-menu',
			'fallback_cb'    => 'pointu_fallback_menu',
			'walker'         => new Pointu_Walker(),
			'depth'          => 3,
		) );
		?>
		</div>
		<div class="pt-nav__foot">
			<a href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url() ); ?>"><?php echo pointu_icon( 'account' ); ?> <?php esc_html_e( 'My account', 'pointu' ); ?></a>
			<a href="<?php echo esc_url( pointu_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo pointu_icon( 'whatsapp' ); ?> <?php echo esc_html( pointu_opt( 'pointu_whatsapp' ) ); ?></a>
		</div>
	</nav>
	<div class="pt-search" id="pt-search" hidden>
		<div class="pt-wrap">
			<form role="search" method="get" class="pt-search__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php echo pointu_icon( 'search' ); ?>
				<label class="screen-reader-text" for="pt-s"><?php esc_html_e( 'Search products', 'pointu' ); ?></label>
				<input id="pt-s" type="search" name="s" placeholder="<?php esc_attr_e( 'Search shoes, bags, scarves…', 'pointu' ); ?>" autocomplete="off">
				<input type="hidden" name="post_type" value="product">
				<button class="pt-iconbtn pt-search-close" type="button" aria-label="<?php esc_attr_e( 'Close search', 'pointu' ); ?>"><?php echo pointu_icon( 'close' ); ?></button>
			</form>
		</div>
	</div>
</header>
<div class="pt-overlay" hidden></div>
<main id="content" class="pt-main">
