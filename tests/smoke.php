<?php
/** Read-only checks: wp eval-file /absolute/path/to/wiregum-wordpress/tests/smoke.php */
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$GLOBALS['wiregum_plugin_checks'] = 0;
function wiregum_plugin_assert($condition, $message) {
    if (!$condition) { WP_CLI::error($message); }
    $GLOBALS['wiregum_plugin_checks']++;
}
wiregum_plugin_assert(post_type_exists('wg_product'), 'Products are registered independently of the theme.');
wiregum_plugin_assert(get_post_type_object('wg_product')->show_in_rest, 'Products support the native block editor.');
$meta = get_registered_meta_keys('post', 'wg_product');
foreach (array('_wiregum_reference', '_wiregum_fallback_price') as $key) {
    wiregum_plugin_assert(isset($meta[$key]) && $meta[$key]['show_in_rest'], 'Product metadata registered: ' . $key);
}
$registry = WP_Block_Type_Registry::get_instance();
foreach (array('products', 'buy-box', 'shop-link', 'cart-link') as $name) {
    $block = $registry->get_registered('wiregum/' . $name);
    wiregum_plugin_assert($block && $block->is_dynamic(), 'Dynamic block registered: ' . $name);
}
foreach (array('cart-link' => 'data-wg-cart-open', 'shop-link' => 'href=') as $block => $hook) {
    $html = render_block(array('blockName' => 'wiregum/' . $block, 'attrs' => array(), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array()));
    wiregum_plugin_assert(str_contains($html, $hook), 'Block renders: ' . $block);
}
$_SERVER['SERVER_NAME'] = wp_parse_url(home_url(), PHP_URL_HOST);
ob_start(); do_action('wp_head'); $head = ob_get_clean();
wiregum_plugin_assert(substr_count($head, 'data-wiregum-config') === 1, 'Exactly one config block.');
wiregum_plugin_assert(preg_match('/data-wiregum-config>(.*?)<\/script>/s', $head, $match) === 1 && is_array(json_decode($match[1], true)), 'Config is valid JSON.');
ob_start(); do_action('wp_footer'); $footer = ob_get_clean();
wiregum_plugin_assert(substr_count($footer, 'id="wg-cart"') === 1, 'Exactly one shared cart dialog.');
wiregum_plugin_assert(str_contains($footer, 'data-wiregum-cart-increment') && str_contains($footer, 'data-wiregum-country-selector'), 'Cart includes quantity and shipping hooks.');
foreach (array('assets/editor.js', 'assets/storefront.js', 'assets/storefront.css', 'blocks/products/block.json') as $path) {
    wiregum_plugin_assert(is_file(dirname(__DIR__) . '/' . $path), 'Standalone asset exists: ' . $path);
}
WP_CLI::success($GLOBALS['wiregum_plugin_checks'] . ' plugin smoke checks passed.');
