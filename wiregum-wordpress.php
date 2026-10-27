<?php
/**
 * Plugin Name: WireGum for WordPress
 * Plugin URI: https://github.com/getwiregum/wiregum-wordpress
 * Description: Add editorial products, native blocks and the WireGum storefront widget to WordPress.
 * Version: 1.0.0
 * Requires at least: 6.7
 * Requires PHP: 8.2
 * Author: WireGum
 * Author URI: https://wiregum.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/getwiregum/wiregum-wordpress
 * Text Domain: wiregum-wordpress
 */
namespace WireGum\Starter;
if (!defined('ABSPATH')) { exit; }
const VERSION = '1.0.0';

function settings() {
    $options = wp_parse_args(get_option('wiregum_storefront', array()), array(
        'widget_url' => 'https://wiregum.com/api/storefront/widget',
        'organization' => '', 'locale' => 'en',
    ));
    foreach (array('widget_url', 'organization', 'locale') as $key) {
        $constant = 'WIREGUM_' . strtoupper($key);
        $override = defined($constant) ? constant($constant) : getenv($constant);
        if (is_string($override) && $override !== '') { $options[$key] = $override; }
    }
    return $options;
}

function register_products() {
    register_post_type('wg_product', array(
        'labels' => array('name' => __('Products', 'wiregum-wordpress'), 'singular_name' => __('Product', 'wiregum-wordpress'), 'add_new_item' => __('Add product', 'wiregum-wordpress')),
        'public' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-products',
        'has_archive' => 'shop', 'rewrite' => array('slug' => 'shop', 'with_front' => false),
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes'),
    ));
    foreach (array('_wiregum_reference', '_wiregum_fallback_price') as $key) {
        register_post_meta('wg_product', $key, array(
            'type' => 'string', 'single' => true, 'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => function ($allowed, $meta_key, $post_id) { return current_user_can('edit_post', $post_id); },
        ));
    }
}
add_action('pre_get_posts', function ($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_post_type_archive('wg_product')) {
        $query->set('orderby', array('menu_order' => 'ASC', 'title' => 'ASC'));
    }
});
add_action('init', __NAMESPACE__ . '\\register_products');
register_activation_hook(__FILE__, function () { register_products(); flush_rewrite_rules(); });
register_deactivation_hook(__FILE__, function () { unregister_post_type('wg_product'); flush_rewrite_rules(); });

add_action('add_meta_boxes_wg_product', function () {
    add_meta_box('wiregum-product', __('WireGum product', 'wiregum-wordpress'), function ($post) {
        wp_nonce_field('wiregum_product', 'wiregum_product_nonce');
        foreach (array('_wiregum_reference' => __('Public product ID or slug', 'wiregum-wordpress'), '_wiregum_fallback_price' => __('Fallback price (display only)', 'wiregum-wordpress')) as $key => $label) {
            echo '<p><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label><input class="widefat" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr(get_post_meta($post->ID, $key, true)) . '"></p>';
        }
        echo '<p>' . esc_html__('Prices, stock and variants come from WireGum. Leave the reference empty to keep purchasing disabled.', 'wiregum-wordpress') . '</p>';
    }, 'wg_product', 'side');
});
add_action('save_post_wg_product', function ($post_id) {
    if (wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) { return; }
    if (!isset($_POST['wiregum_product_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wiregum_product_nonce'])), 'wiregum_product') || !current_user_can('edit_post', $post_id)) { return; }
    foreach (array('_wiregum_reference', '_wiregum_fallback_price') as $key) {
        if (isset($_POST[$key]) && is_string($_POST[$key])) { update_post_meta($post_id, $key, sanitize_text_field(wp_unslash($_POST[$key]))); }
    }
});

add_action('admin_init', function () {
    register_setting('wiregum_storefront', 'wiregum_storefront', array('sanitize_callback' => function ($input) {
        $input = is_array($input) ? $input : array();
        $url = esc_url_raw(is_string($input['widget_url'] ?? null) ? $input['widget_url'] : '', array('https', 'http'));
        if (!$url || !wp_http_validate_url($url)) { $url = 'https://wiregum.com/api/storefront/widget'; }
        return array('widget_url' => $url, 'organization' => sanitize_title(is_string($input['organization'] ?? null) ? $input['organization'] : ''), 'locale' => sanitize_text_field(is_string($input['locale'] ?? null) ? $input['locale'] : 'en'));
    }));
});
add_action('admin_menu', function () {
    add_options_page('WireGum', 'WireGum', 'manage_options', 'wiregum', function () {
        if (!current_user_can('manage_options')) { return; }
        $values = wp_parse_args(get_option('wiregum_storefront', array()), array('widget_url' => 'https://wiregum.com/api/storefront/widget', 'organization' => '', 'locale' => 'en'));
        echo '<div class="wrap"><h1>WireGum</h1><p>' . esc_html__('Connect a workspace and authorize this site’s exact origin in WireGum → Storefront. No API key or Stripe secret is needed.', 'wiregum-wordpress') . '</p><form method="post" action="options.php">';
        settings_fields('wiregum_storefront');
        echo '<table class="form-table" role="presentation">';
        foreach (array('widget_url' => __('Widget URL', 'wiregum-wordpress'), 'organization' => __('Workspace slug', 'wiregum-wordpress'), 'locale' => __('Locale', 'wiregum-wordpress')) as $key => $label) {
            echo '<tr><th><label for="wg-' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td><input class="regular-text" id="wg-' . esc_attr($key) . '" name="wiregum_storefront[' . esc_attr($key) . ']" value="' . esc_attr($values[$key]) . '"></td></tr>';
        }
        echo '</table><p>' . esc_html__('WIREGUM_WIDGET_URL, WIREGUM_ORGANIZATION and WIREGUM_LOCALE constants or environment variables override these settings.', 'wiregum-wordpress') . '</p>';
        submit_button(); echo '</form></div>';
    });
});

function buy_box($post_id) {
    if (get_post_type($post_id) !== 'wg_product') { return ''; }
    $ref = get_post_meta($post_id, '_wiregum_reference', true);
    $ready = $ref !== '' && settings()['organization'] !== '';
    ob_start(); ?>
    <div class="wg-buy-box" <?php if ($ref !== '') : ?>data-wiregum-product="<?php echo esc_attr($ref); ?>"<?php endif; ?> lang="<?php echo esc_attr(settings()['locale']); ?>">
        <p class="wg-price" data-wiregum-price><?php echo esc_html(get_post_meta($post_id, '_wiregum_fallback_price', true)); ?></p>
        <div data-wiregum-variant-selector></div>
        <div class="wg-actions">
            <button class="wp-element-button" data-wiregum-add-to-cart type="button" disabled><?php esc_html_e('Add to cart', 'wiregum-wordpress'); ?></button>
        </div>
        <?php if (!$ready) : ?><p class="wg-notice"><?php esc_html_e('Purchasing is not available yet.', 'wiregum-wordpress'); ?></p><?php endif; ?>
        <noscript><p><?php esc_html_e('Enable JavaScript to select options and purchase.', 'wiregum-wordpress'); ?></p></noscript>
    </div>
    <?php return ob_get_clean();
}

function product_grid($attributes) {
    $page = !empty($attributes['paginate']) ? max(1, (int) get_query_var('paged')) : 1;
    $query = !empty($attributes['paginate']) && is_post_type_archive('wg_product') ? $GLOBALS['wp_query'] : new \WP_Query(array('post_type' => 'wg_product', 'post_status' => 'publish', 'posts_per_page' => min(48, max(1, (int) ($attributes['perPage'] ?? 4))), 'paged' => $page, 'orderby' => array('menu_order' => 'ASC', 'title' => 'ASC')));
    ob_start();
    echo '<div ' . get_block_wrapper_attributes(array('class' => 'wg-products')) . '>';
    if (!$query->have_posts()) { echo '<p>' . esc_html__('Products are coming soon.', 'wiregum-wordpress') . '</p>'; }
    while ($query->have_posts()) {
        $query->the_post();
        echo '<article class="wg-product-card"><a href="' . esc_url(get_permalink()) . '">' . get_the_post_thumbnail(get_the_ID(), 'large', array('class' => 'wg-product-image')) . '<h2>' . esc_html(get_the_title()) . '</h2></a>';
        echo '<p>' . esc_html(get_the_excerpt()) . '</p>';
        echo buy_box(get_the_ID()); // Escaped by buy_box; never rendered from arbitrary post HTML.
        echo '</article>';
    }
    echo '</div>';
    if (!empty($attributes['paginate'])) {
        echo '<nav class="wg-pagination" aria-label="' . esc_attr__('Product pages', 'wiregum-wordpress') . '">' . wp_kses_post(paginate_links(array('total' => $query->max_num_pages, 'current' => $page)) ?? '') . '</nav>';
    }
    wp_reset_postdata(); return ob_get_clean();
}

add_action('init', function () {
    wp_register_script('wiregum-editor', plugins_url('assets/editor.js', __FILE__), array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n'), VERSION, true);
    foreach (array('buy-box', 'products', 'cart-link', 'shop-link') as $block) {
        register_block_type(__DIR__ . '/blocks/' . $block, array('render_callback' => function ($attributes, $content, $block_instance) use ($block) {
            if ($block === 'products') { return product_grid($attributes); }
            if ($block === 'buy-box') { return buy_box((int) (($attributes['productId'] ?? 0) ?: ($block_instance->context['postId'] ?? get_the_ID()))); }
            if ($block === 'shop-link') { return '<a class="wg-shop-link" href="' . esc_url(get_post_type_archive_link('wg_product')) . '">' . esc_html__('Shop', 'wiregum-wordpress') . '</a>'; }
            return '<button class="wg-cart-link" type="button" data-wg-cart-open aria-haspopup="dialog" aria-controls="wg-cart">' . esc_html__('Cart', 'wiregum-wordpress') . ' (<span data-wiregum-cart-count>0</span>)</button>';
        }));
    }
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('wiregum-wordpress', plugins_url('assets/storefront.css', __FILE__), array(), VERSION);
    wp_enqueue_script('wiregum-ui', plugins_url('assets/storefront.js', __FILE__), array(), VERSION, true);
    $config = settings();
    if ($config['organization'] !== '') {
        wp_enqueue_script('wiregum-widget', esc_url_raw($config['widget_url']), array('wiregum-ui'), null, true);
    }
});
add_action('enqueue_block_editor_assets', function () {
    wp_enqueue_style('wiregum-editor', plugins_url('assets/storefront.css', __FILE__), array(), VERSION);
});
add_action('wp_head', function () {
    $config = settings();
    $thanks = get_page_by_path('thank-you');
    $shop = get_post_type_archive_link('wg_product');
    $payload = array('organization' => $config['organization'], 'locale' => $config['locale'],
        'successPath' => wp_make_link_relative($thanks ? get_permalink($thanks) : home_url('/')),
        'cancelPath' => wp_make_link_relative($shop));
    echo '<script type="application/json" data-wiregum-config>' . wp_json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . '</script>';
});
add_action('wp_footer', function () {
    ?>
    <dialog id="wg-cart" class="wg-cart" aria-labelledby="wg-cart-title">
        <header class="wg-cart-header"><h2 id="wg-cart-title"><?php esc_html_e('Your cart', 'wiregum-wordpress'); ?></h2><button type="button" data-wg-cart-close><?php esc_html_e('Close', 'wiregum-wordpress'); ?></button></header>
        <div data-wiregum-cart>
            <p data-wiregum-cart-empty><?php esc_html_e('Your cart is empty.', 'wiregum-wordpress'); ?> <a href="<?php echo esc_url(get_post_type_archive_link('wg_product')); ?>"><?php esc_html_e('Browse the shop', 'wiregum-wordpress'); ?></a></p>
            <ul data-wiregum-cart-items>
                <li data-wiregum-cart-item hidden>
                    <strong data-wiregum-cart-item-name></strong><span data-wiregum-cart-item-price></span>
                    <div class="wg-cart-controls"><button type="button" data-wiregum-cart-decrement aria-label="<?php esc_attr_e('Decrease quantity', 'wiregum-wordpress'); ?>">−</button><span data-wiregum-cart-item-quantity></span><button type="button" data-wiregum-cart-increment aria-label="<?php esc_attr_e('Increase quantity', 'wiregum-wordpress'); ?>">+</button><span data-wiregum-cart-item-subtotal></span></div>
                    <button type="button" data-wiregum-cart-remove><?php esc_html_e('Remove', 'wiregum-wordpress'); ?></button>
                </li>
            </ul>
            <div data-wiregum-country-selector></div>
            <p class="wg-cart-total"><?php esc_html_e('Total', 'wiregum-wordpress'); ?> <strong data-wiregum-cart-total>—</strong></p>
            <button class="wp-element-button" type="button" data-wiregum-checkout disabled><?php esc_html_e('Checkout', 'wiregum-wordpress'); ?></button>
            <p class="wg-notice"><?php esc_html_e('Secure payment via Stripe Checkout. Shipping is calculated at checkout.', 'wiregum-wordpress'); ?></p>
        </div>
    </dialog>
    <?php
});
