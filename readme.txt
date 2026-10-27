=== WireGum for WordPress ===
Tags: ecommerce, blocks, storefront
Requires at least: 6.7
Tested up to: 7.1
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add editorial products, native blocks and the WireGum storefront widget to WordPress.

== Description ==

Keep your WordPress theme and publish product stories and images in the block editor. WireGum provides live prices, variants, inventory, cart, orders and Stripe Checkout.

This optional plugin adds a Products content type, public product reference and fallback price fields, Settings → WireGum, four native blocks and a shared accessible cart dialog. It does not synchronize a WooCommerce catalog.

WireGum can also be integrated without a plugin. Manual guide: https://wiregum.com/docs/wordpress

Complete storefront starter with a Twenty Twenty-Five child theme and demo content: https://github.com/getwiregum/starter-wordpress

Plugin source and releases: https://github.com/getwiregum/wiregum-wordpress

This plugin is distributed from GitHub; a WordPress.org listing and automatic GitHub updates are not included.

== Installation ==

1. Upload wiregum-wordpress.zip through Plugins → Add New Plugin → Upload Plugin, or copy the source into wp-content/plugins/wiregum-wordpress.
2. Activate WireGum for WordPress.
3. Configure your workspace in Settings → WireGum and authorize your exact site origin in WireGum.
4. Create editorial products with public WireGum references, then place the product and cart blocks using your theme's editor.

== Frequently Asked Questions ==

= Does this require a specific theme or WooCommerce? =

No. Use the blocks with your own theme, adapting its product templates and styling, or use the separate WordPress starter for the complete demo layout.

= Is a WordPress plugin required to use WireGum? =

No. This plugin supplies editing conveniences and prepared markup. The storefront widget can also be integrated directly into a theme.

= Where are prices, variants and stock managed? =

In WireGum. WordPress stores editorial content, a public product reference and an optional display-only fallback price.

= Which external services are used? =

When configured, the frontend loads the widget from the saved WireGum URL and sends product and checkout requests to that service. Stripe processes payment in its hosted checkout. No private API key is required. Cart state stays in the visitor's browser.

WireGum service and terms: https://wiregum.com/terms
WireGum privacy policy: https://wiregum.com/privacy
Stripe privacy policy: https://stripe.com/privacy

= Does deactivation delete products? =

No. Content, metadata and settings stay in the database, while the Products UI, routes and commerce blocks become unavailable until reactivation.

== Changelog ==

= 1.0.0 =
* Initial standalone plugin extracted from the WordPress starter.
