# POINTU theme

WordPress + WooCommerce theme for https://pointuofficial.com.

## Deploy
Push to `main`, then cPanel → Git Version Control → pointu-theme → Manage → Pull or Deploy →
**Update from Remote** → **Deploy HEAD Commit**. `.cpanel.yml` copies the theme into
`public_html/wp-content/themes/pointu/`. Only code is deployed; products, pages and orders are untouched.

After CSS/JS changes, bump `POINTU_VER` in `functions.php` so browsers load the new files.
