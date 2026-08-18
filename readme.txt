=== Shipping Method Column for WooCommerce ===
Contributors: mikesewell
Tags: woocommerce, shipping, orders, admin, column
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a shipping method column to the WooCommerce orders list so you can see each order's shipping methods without opening it.

== Description ==

Shipping Method Column for WooCommerce adds a **Shipping Method** column to the WooCommerce orders list in wp-admin.

The column is inserted after **Order Status**. For each order it lists the titles of that order's shipping methods. If an order has more than one shipping method, they are shown together, separated by commas. If an order has no shipping methods, the cell shows "No shipping".

The plugin has no settings screen. After activation, open WooCommerce → Orders to see the column.

This version hooks the classic post-based orders list (`shop_order`). It does not register High-Performance Order Storage (HPOS) compatibility, so the column will not appear on the HPOS orders screen.

== Installation ==

1. Upload the `shipping-method-column-for-woocommerce` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Open WooCommerce → Orders to see the Shipping Method column after Order Status.

= Manual installation =

1. Download the plugin zip file.
2. In WordPress admin, go to Plugins → Add New → Upload Plugin.
3. Choose the zip file and click Install Now.
4. Activate the plugin.

== Frequently Asked Questions ==

= Does this plugin require any configuration? =

No. There are no settings. The column appears on the orders list after activation.

= Will this work with my theme? =

Yes. The plugin only changes the WooCommerce orders list in wp-admin.

= Can I change the column position? =

Not in this version. The column is always added after Order Status.

= Does it work with HPOS (High-Performance Order Storage)? =

No. It only hooks the traditional `shop_order` posts list. HPOS support is not included.

= What does the column show when there are several shipping methods? =

It lists every method title on the order, separated by commas.

= What if an order has no shipping? =

The column shows "No shipping".

== Screenshots ==

1. WooCommerce Orders list with the Shipping Method column after Order Status.
2. An order with more than one shipping method, titles shown comma-separated.
3. An order with no shipping methods, showing "No shipping".

== Changelog ==

= 1.0.0 =
* Initial release.
* Add a Shipping Method column to the classic WooCommerce orders list.
* Show one or more shipping method titles per order.
* Show "No shipping" when an order has no shipping methods.
* Basic admin CSS for the column width.

== Upgrade Notice ==

= 1.0.0 =
Initial release of Shipping Method Column for WooCommerce.
