</main>
<footer class="pt-footer">
	<div class="pt-wrap pt-footer__grid">
		<div class="pt-footer__brand">
			<?php echo pointu_logo( 'rose' ); ?>
			<p class="pt-eyebrow"><?php echo esc_html( pointu_opt( 'pointu_tagline' ) ); ?></p>
			<?php echo pointu_social_links(); ?>
		</div>
		<div class="pt-footer__col">
			<h4><?php esc_html_e( 'Shop', 'pointu' ); ?></h4>
			<?php wp_nav_menu( array( 'theme_location' => 'footer_shop', 'container' => false, 'depth' => 1, 'fallback_cb' => false ) ); ?>
		</div>
		<div class="pt-footer__col">
			<h4><?php esc_html_e( 'Help', 'pointu' ); ?></h4>
			<?php wp_nav_menu( array( 'theme_location' => 'footer_help', 'container' => false, 'depth' => 1, 'fallback_cb' => false ) ); ?>
		</div>
		<div class="pt-footer__col pt-footer__contact">
			<h4><?php esc_html_e( 'Contact', 'pointu' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( pointu_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo pointu_icon( 'whatsapp' ); ?><?php echo esc_html( pointu_opt( 'pointu_whatsapp' ) ); ?></a></li>
				<?php if ( pointu_opt( 'pointu_email' ) ) : ?>
				<li><a href="mailto:<?php echo esc_attr( pointu_opt( 'pointu_email' ) ); ?>"><?php echo pointu_icon( 'mail' ); ?><?php echo esc_html( pointu_opt( 'pointu_email' ) ); ?></a></li>
				<?php endif; ?>
				<li><a href="<?php echo esc_url( pointu_opt( 'pointu_map_url' ) ); ?>" target="_blank" rel="noopener"><?php echo pointu_icon( 'pin' ); ?><?php echo esc_html( pointu_opt( 'pointu_address' ) ); ?></a></li>
				<li><span><?php echo pointu_icon( 'truck' ); ?><?php echo esc_html( pointu_opt( 'pointu_delivery_note' ) ); ?></span></li>
			</ul>
		</div>
	</div>
	<div class="pt-wrap pt-footer__bottom">
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> POINTU. <?php esc_html_e( 'All rights reserved.', 'pointu' ); ?></span>
		<span class="pt-pay"><?php esc_html_e( 'Cash on delivery · Whish Money · Bank transfer', 'pointu' ); ?></span>
	</div>
</footer>

<a class="pt-wa-float" href="<?php echo esc_url( pointu_whatsapp_url( pointu_opt( 'pointu_whatsapp_msg' ) ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat with us on WhatsApp', 'pointu' ); ?>"><?php echo pointu_icon( 'whatsapp' ); ?></a>
<?php wp_footer(); ?>
</body>
</html>
