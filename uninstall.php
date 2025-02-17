<?php
/**
 * Uninstall script for Coupon Exporter for WooCommerce
 *
 * @package RWC_Coupon_Exporter
 */

// Exit if uninstall is not called from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clean up plugin options
delete_option('rwc_coupon_exporter_errors');
delete_option('rwc_coupon_exporter_activated');