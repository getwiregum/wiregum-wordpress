# WireGum for WordPress

Add [WireGum](https://wiregum.com) products, variants, cart and Stripe Checkout to an existing WordPress site. Keep your theme and publish product stories, images and pages with the native block editor.

**Want a complete storefront to start from?** [WordPressGum — the WordPress starter kit](https://github.com/getwiregum/starter-wordpress) pairs this plugin with a light Twenty Twenty-Five child theme, editable home and shop templates, example products and a local development environment. The starter consumes this plugin as a separate dependency; this repository is the source of truth for plugin code.

**WireGum itself does not require this plugin.** This is a convenience layer for WordPress editing, settings and ready-made commerce blocks. You can instead integrate the storefront widget directly into your existing theme: see the [manual WordPress integration guide](https://wiregum.com/docs/wordpress).

## What the plugin does

- Adds an editorial **Products** content type, with title, block content, excerpt, featured image, revisions and ordering.
- Adds a public WireGum product reference and a display-only fallback price to each product.
- Adds **Settings → WireGum** for the widget URL, workspace slug and storefront locale.
- Provides four native dynamic blocks: **WireGum products**, **WireGum buy box**, **WireGum shop link** and **WireGum cart**.
- Loads the widget once, safely encodes its public configuration, and supplies a native dialog cart with keyboard focus handling, Escape and background isolation.
- Preserves authored content when the widget fails. Purchase buttons start disabled and become available only after successful widget hydration.

WordPress owns editorial content. WireGum owns live prices, sellable variants, inventory, cart and orders. Stripe Checkout handles payment. The plugin does not synchronize WooCommerce products or orders, and it does not copy WireGum's product catalog into WordPress.

## Requirements

- WordPress 6.7 or newer and PHP 8.2 or newer.
- A WireGum workspace, with the exact storefront origin authorized and Stripe configured for checkout.
- No WooCommerce, Node.js, asset build step or particular WordPress theme is required to install and use the plugin.

## Install

### From a plugin ZIP

Download `wiregum-wordpress.zip` from a [GitHub release](https://github.com/getwiregum/wiregum-wordpress/releases) when available, then use **Plugins → Add New Plugin → Upload Plugin**, upload the ZIP and activate **WireGum for WordPress**.

If there is no release ZIP yet, use the source installation below or build the ZIP yourself. The standard GitHub source download can also be installed through WordPress, but the packaged ZIP uses the stable `wiregum-wordpress/` folder name for repeatable updates.

### From source

Clone or download [getwiregum/wiregum-wordpress](https://github.com/getwiregum/wiregum-wordpress). Place this repository directly at:

```text
wp-content/plugins/wiregum-wordpress/
├── wiregum-wordpress.php
├── assets/
└── blocks/
```

Activate **WireGum for WordPress** under Plugins, or run `wp plugin activate wiregum-wordpress` from the WordPress installation directory. The main PHP file is at the repository root: there is no nested plugin folder to find.

## Connect your workspace

1. In **Settings → WireGum**, set the widget URL (`https://wiregum.com/api/storefront/widget` by default), workspace slug and locale (`en`, `it`, etc.).
2. In WireGum → Storefront, authorize your exact origin, including scheme and port, and configure shipping rates. Connect Stripe for test checkout.
3. Create a product under **Products**. Use native blocks for its story, set an excerpt and featured image, and enter its public WireGum ID (`wg_…`) or slug in **WireGum product**. Set a display-only fallback price if desired.
4. Add a **WireGum products** grid and a **WireGum cart** trigger to a page or template using your theme's editor. The plugin supplies commerce markup and basic styling; adapt its layout and styling to your theme.
5. On a single-product template, insert a **WireGum buy box** with product post ID `0` to use the current product. On another page, set the block's WordPress product post ID explicitly. This numeric post ID is different from the public WireGum reference stored on the product.

The plugin registers `/shop/` as the product archive and `/shop/product-slug/` as product URLs. Avoid a page with the reserved slug `shop`; use another slug for a custom catalog page. Existing themes supply the archive and single-product layouts. This plugin does not replace their templates or automatically insert a buy box into every product's body. Use your theme's templates or the [starter](https://github.com/getwiregum/starter-wordpress) for a complete ready-made layout.

Create a `thank-you` page for the success return; if it is missing, success returns to the homepage. Cancellation returns to the generated shop archive URL. A thank-you page is informational and must not assert that payment has been confirmed.

Test the published page as a visitor: choose a variant, add a product, adjust quantity in the cart, select a shipping country and complete Stripe test checkout. Verify the order in WireGum and both return paths. Purchasing does not run inside the block editor's server-rendered previews.

## Configuration and data

Saved settings can be overridden with `WIREGUM_WIDGET_URL`, `WIREGUM_ORGANIZATION` and `WIREGUM_LOCALE` constants in `wp-config.php`, or environment variables with those names. Constants take priority over environment variables, which take priority over saved settings. No `.env` loader is included.

These are public settings. No Stripe secret or private WireGum API key belongs in the page markup or plugin settings. The fallback price is editorial text, not the checkout price: WireGum validates prices and stock server-side.

When a workspace is configured, the frontend loads JavaScript from the configured WireGum widget URL and makes product and checkout requests to that service. Stripe handles its hosted payment flow. Cart state is stored in the visitor's browser. Authorize each local, staging and production origin explicitly.

Deactivation leaves product content, metadata and settings in the WordPress database, but hides the Products administration section, product routes and dynamic commerce blocks until reactivation. Changing themes keeps the product type available while the plugin stays active. There is no uninstall routine that deletes your catalog.

## Updates

The plugin is distributed from GitHub. A repository or `Update URI` header does not implement a GitHub automatic updater. Install new release ZIPs manually, allowing WordPress to replace the existing plugin. Keep the plugin folder named `wiregum-wordpress` when updating a source installation. No WordPress.org directory listing or automatic update service is assumed.

## Build an installable ZIP

For maintainers, PHP with the Zip extension is enough:

```sh
php scripts/build.php
```

This creates `dist/wiregum-wordpress.zip`, with the main PHP file, runtime assets, block metadata and documentation inside one `wiregum-wordpress/` directory. Development files, tests and Git metadata are excluded. The output directory is ignored by Git. Tag releases and attach this ZIP to the corresponding GitHub release when publishing; update the version in the main file, `VERSION`, block metadata and `readme.txt` together.

For local work on the complete starter, clone both repositories side by side and follow its [local plugin development instructions](https://github.com/getwiregum/starter-wordpress#develop-the-plugin-and-theme-together).

## Verification

With the plugin active on a local WordPress installation:

```sh
wp eval-file /absolute/path/to/wiregum-wordpress/tests/smoke.php
```

These read-only checks cover registration, the four blocks, configuration, shared cart markup and packaged assets. They do not require the WordPressGum theme or example products and do not contact WireGum or Stripe. The starter repository has separate storefront integration checks for its seeded content and theme.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE). WordPress and the WireGum service are separate projects. No WordPress core, theme, example catalog, credentials or database is bundled here.
