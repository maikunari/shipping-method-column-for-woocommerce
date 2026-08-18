<?php
/**
 * Plugin Name: Shipping Method Column for WooCommerce
 * Plugin URI: https://sonicpixel.ca
 * Description: Adds a shipping method column to the WooCommerce orders list page.
 * Version: 1.0.0
 * Author: Mike Sewell
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Tested up to: 7.0
 * WC requires at least: 5.0
 * WC tested up to: 11.0
 * Requires PHP: 7.0
 * Requires Plugins: woocommerce
 * Text Domain: shipping-method-column-for-woocommerce
 *
 * @category Admin
 * @package  Shipping_Method_Column_For_WooCommerce
 * @author   Mike Sewell <mike@sonicpixel.ca>
 * @license  https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link     https://sonicpixel.ca
 */

// Prevent direct access.
if (! defined('ABSPATH') ) {
    exit;
}

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage.
 *
 * @return void
 */
function wcsmc_declare_hpos_compatibility()
{
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );
    }
}
add_action('before_woocommerce_init', 'wcsmc_declare_hpos_compatibility');

/**
 * Register admin hooks once all plugins are loaded, if WooCommerce is active.
 *
 * @return void
 */
function wcsmc_init()
{
    if (! class_exists('WooCommerce') ) {
        return;
    }

    // Classic (post-based) orders list.
    add_filter('manage_edit-shop_order_columns', 'wcsmc_add_shipping_method_column', 20);
    add_action('manage_shop_order_posts_custom_column', 'wcsmc_show_shipping_method_content', 20, 2);

    // High-Performance Order Storage (HPOS) orders list.
    add_filter('manage_woocommerce_page_wc-orders_columns', 'wcsmc_add_shipping_method_column', 20);
    add_action('manage_woocommerce_page_wc-orders_custom_column', 'wcsmc_show_hpos_shipping_method_content', 20, 2);

    add_action('admin_enqueue_scripts', 'wcsmc_admin_styles');
}
add_action('plugins_loaded', 'wcsmc_init');

/**
 * Add shipping method column to WooCommerce orders list.
 *
 * @param array $columns Existing columns.
 *
 * @return array Modified columns.
 */
function wcsmc_add_shipping_method_column( $columns )
{
    $new_columns = array();

    foreach ( $columns as $column_name => $column_info ) {
        $new_columns[ $column_name ] = $column_info;

        // Add shipping method column after the order status column.
        if ('order_status' === $column_name ) {
            $new_columns['shipping_method'] = esc_html__('Shipping Method', 'shipping-method-column-for-woocommerce');
        }
    }

    return $new_columns;
}

/**
 * Output the shipping method titles for an order.
 *
 * @param WC_Order|false $order Order object.
 *
 * @return void
 */
function wcsmc_render_shipping_methods( $order )
{
    if (! $order ) {
        return;
    }

    $shipping_methods = array();

    // Get all shipping methods for the order.
    foreach ( $order->get_shipping_methods() as $shipping_method ) {
        $shipping_methods[] = $shipping_method->get_method_title();
    }

    if (! empty($shipping_methods) ) {
        echo esc_html(implode(', ', $shipping_methods));
    } else {
        echo '<span style="color: #999;">' .
        esc_html__('No shipping', 'shipping-method-column-for-woocommerce') . '</span>';
    }
}

/**
 * Display shipping method data in the new column (classic orders list).
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 *
 * @return void
 */
function wcsmc_show_shipping_method_content( $column, $post_id )
{
    if ('shipping_method' === $column ) {
        wcsmc_render_shipping_methods(wc_get_order($post_id));
    }
}

/**
 * Display shipping method data in the new column (HPOS orders list).
 *
 * @param string   $column Column name.
 * @param WC_Order $order  Order object.
 *
 * @return void
 */
function wcsmc_show_hpos_shipping_method_content( $column, $order )
{
    if ('shipping_method' === $column ) {
        wcsmc_render_shipping_methods($order);
    }
}

/**
 * Add some basic CSS styling for the column.
 *
 * @return void
 */
function wcsmc_admin_styles()
{
    $screen = get_current_screen();

    if (! $screen
        || ! in_array($screen->id, array( 'edit-shop_order', 'woocommerce_page_wc-orders' ), true)
    ) {
        return;
    }

    wp_register_style('wcsmc-admin', false, array(), '1.0.0');
    wp_enqueue_style('wcsmc-admin');
    wp_add_inline_style(
        'wcsmc-admin',
        '.wp-list-table .column-shipping_method { width: 150px; }
        .wp-list-table .column-shipping_method span { font-style: italic; }'
    );
}
