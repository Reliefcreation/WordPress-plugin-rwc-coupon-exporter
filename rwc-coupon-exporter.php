<?php
/**
 * Plugin Name: Coupon Exporter for WooCommerce
 * Plugin URI: https://github.com/Reliefcreation/WordPress-plugin-rwc-coupon-exporter
 * Description: Export WooCommerce coupons to CSV file
 * Version: 1.3.1
 * Author: RELIEF Creation
 * Author URI: https://reliefcreation.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 3.0
 * WC tested up to: 8.5
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RWC_COUPON_EXPORTER_VERSION', '1.3.1');
define('RWC_COUPON_EXPORTER_FILE', __FILE__);
define('RWC_COUPON_EXPORTER_PATH', plugin_dir_path(__FILE__));

// Load required files
require_once RWC_COUPON_EXPORTER_PATH . 'includes/class-rwc-coupon-exporter.php';
require_once RWC_COUPON_EXPORTER_PATH . 'includes/class-rwc-coupon-exporter-admin.php';
require_once RWC_COUPON_EXPORTER_PATH . 'includes/class-rwc-coupon-exporter-handler.php';

/**
 * Check if WooCommerce is active and compatible
 */
function rwc_coupon_exporter_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            'Coupon Exporter for WooCommerce requires WooCommerce to be installed and activated.',
            'Plugin Activation Error',
            array('back_link' => true)
        );
    }

    if (class_exists('WooCommerce') && version_compare(WC_VERSION, '3.0', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            'Coupon Exporter for WooCommerce requires WooCommerce version 3.0 or higher.',
            'Plugin Activation Error',
            array('back_link' => true)
        );
    }
}

/**
 * Initialize plugin
 */
function rwc_coupon_exporter_init() {
    // Check WooCommerce dependency and version
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>Coupon Exporter for WooCommerce requires WooCommerce to be installed and activated.</p></div>';
        });
        return;
    }

    if (version_compare(WC_VERSION, '3.0', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>Coupon Exporter for WooCommerce requires WooCommerce version 3.0 or higher.</p></div>';
        });
        return;
    }

    // Initialize main plugin class
    $plugin = RWC_Coupon_Exporter::get_instance();
    $plugin->init();
}

// Register activation hook
register_activation_hook(__FILE__, 'rwc_coupon_exporter_check_woocommerce');

// Initialize plugin
add_action('plugins_loaded', 'rwc_coupon_exporter_init');