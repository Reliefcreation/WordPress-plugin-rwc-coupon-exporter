=== Coupon Exporter for WooCommerce ===
Contributors: reliefcreation
Tags: woocommerce, coupons, export, csv
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 3.0
WC tested up to: 8.5

Export WooCommerce coupons to a CSV file easily and efficiently.

== Description ==

Coupon Exporter for WooCommerce allows you to export all your WooCommerce coupons into a CSV file. This is particularly useful for backup, analysis, or migration purposes.

= Features =
* Export all WooCommerce coupons to CSV with one click
* Includes comprehensive coupon data
* Secure export process
* Compatible with the latest WooCommerce version
* Batch processing for large exports
* Detailed error logging
* WordPress Multisite compatible
* PHP 8.x compatible

= Export Data Includes =
* Coupon code
* Description
* Discount type
* Amount
* Expiry date
* Minimum spend
* Maximum usage limit
* Usage limit per user
* Product restrictions
* Category restrictions
* Email restrictions

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/rwc-coupon-exporter` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the WooCommerce -> Export Coupons menu to access the exporter

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, WooCommerce must be installed and activated for this plugin to work.

= What data is exported in the CSV file? =

The exported CSV includes coupon codes, descriptions, discount types, amounts, expiry dates, usage limits, and restrictions.

= How does the plugin handle large numbers of coupons? =

The plugin processes coupons in batches of 100 to prevent memory issues and ensure smooth operation even with large numbers of coupons.

= Is the plugin compatible with WordPress Multisite? =

Yes, the plugin is fully compatible with WordPress Multisite installations.

= What PHP versions are supported? =

The plugin supports PHP 7.4 and higher, including PHP 8.x.

== Privacy ==

This plugin does not collect or store any personal data. When exporting coupons, it only processes data that already exists in your WordPress installation.

The exported CSV file may contain email addresses if they were used in coupon restrictions. Please handle the exported file according to your privacy policy and data protection requirements.

== Developer Notes ==

The plugin provides several filters to extend its functionality:

= Filters =

* `rwc_coupon_exporter_csv_headers` - Modify CSV headers
  ```php
  add_filter('rwc_coupon_exporter_csv_headers', function($headers) {
      $headers['my_field'] = 'My Field';
      return $headers;
  });
  ```

* `rwc_coupon_exporter_csv_row` - Modify row data before export
  ```php
  add_filter('rwc_coupon_exporter_csv_row', function($row, $wc_coupon) {
      $row[] = get_post_meta($wc_coupon->get_id(), 'my_custom_field', true);
      return $row;
  }, 10, 2);
  ```

== Screenshots ==

1. Export coupons page in the WooCommerce menu

== Changelog ==

= 1.3.1 =
* Improved code organization
* Enhanced error handling
* Added batch processing for large exports
* Improved error logging
* Enhanced accessibility with ARIA labels
* Added developer hooks documentation
* Added privacy section
* Improved PHP 8.x compatibility
* Added WordPress Multisite support
* Code refactoring for better maintainability

== Upgrade Notice ==

= 1.3.1 =
This version adds batch processing, improves error handling, and enhances compatibility with PHP 8.x and WordPress Multisite.