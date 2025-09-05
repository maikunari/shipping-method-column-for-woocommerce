<?php
/**
 * Plugin Name: WooCommerce Shipping Method Column
 * Plugin URI: https://sonicpixel.ca
 * Description: Adds a shipping method column to the WooCommerce orders list page.
 * Version: 1.0.0
 * Author: Mike Sewell
 * License: GPL v2 or later
 * Requires at least: 5.0
 * Tested up to: 6.3
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * Requires PHP: 7.0
 *
 * @category Admin
 * @package  WooCommerce_Shipping_Method_Column
 * @author   Mike Sewell <mike@sonicpixel.ca>
 * @license  https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link     https://sonicpixel.ca
 */

// Prevent direct access.
if (! defined('ABSPATH') ) {
    exit;
}

// Check if WooCommerce is active.
if (! in_array(
    'woocommerce/woocommerce.php',
    apply_filters('active_plugins', get_option('active_plugins'))
) 
) {
    return;
}

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
            $new_columns['shipping_method'] = __('Shipping Method', 'woocommerce');
        }
    }

    return $new_columns;
}
add_filter('manage_edit-shop_order_columns', 'wcsmc_add_shipping_method_column', 20);

/**
 * Display shipping method data in the new column.
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 *
 * @return void
 */
function wcsmc_show_shipping_method_content( $column, $post_id )
{
    if ('shipping_method' === $column ) {
        $order = wc_get_order($post_id);

        if ($order ) {
            $shipping_methods = array();

            // Get all shipping methods for the order.
            foreach ( $order->get_shipping_methods() as $shipping_method ) {
                $shipping_methods[] = $shipping_method->get_method_title();
            }

            if (! empty($shipping_methods) ) {
                echo esc_html(implode(', ', $shipping_methods));
            } else {
                echo '<span style="color: #999;">' .
                __('No shipping', 'woocommerce') . '</span>';
            }
        }
    }
}
add_action(
    'manage_shop_order_posts_custom_column',
    'wcsmc_show_shipping_method_content',
    20,
    2
);

/**
 * Add some basic CSS styling for the column.
 *
 * @return void
 */
function wcsmc_admin_styles()
{
    $screen = get_current_screen();
    if ($screen && 'edit-shop_order' === $screen->id ) {
        echo '<style>
			.wp-list-table .column-shipping_method {
				width: 150px;
			}
			.wp-list-table .column-shipping_method span {
				font-style: italic;
			}
		</style>';
    }
}
add_action('admin_head', 'wcsmc_admin_styles');