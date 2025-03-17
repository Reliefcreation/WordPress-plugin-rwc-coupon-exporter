<?php
/**
 * Plugin Name: RWC Coupon Exporter for WooCommerce
 * Plugin URI: https://github.com/Reliefcreation/WordPress-plugin-rwc-coupon-exporter
 * Description: Export WooCommerce coupons to CSV file
 * Version: 1.3.2
 * Author: RELIEF Creation
 * Author URI: https://reliefcreation.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rwc-coupon-exporter
 * Domain Path: /languages
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
define('RWC_COUPON_EXPORTER_VERSION', '1.3.2');
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
        /* translators: %s: Plugin name "WooCommerce" that needs to be installed and activated */
        wp_die(
            sprintf(
                esc_html__('RWC Coupon Exporter requires %s to be installed and activated.', 'rwc-coupon-exporter'),
                '<strong>WooCommerce</strong>'
            ),
            'Plugin Activation Error',
            array('back_link' => true)
        );
    }

    if (class_exists('WooCommerce') && version_compare(WC_VERSION, '3.0', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        /* translators: %s: Required minimum WooCommerce version number */
        wp_die(
            sprintf(
                esc_html__('RWC Coupon Exporter requires WooCommerce version %s or higher.', 'rwc-coupon-exporter'),
                '3.0'
            ),
            'Plugin Activation Error',
            array('back_link' => true)
        );
    }
}

/**
 * Initialize plugin
 */
function rwc_coupon_exporter_init() {
    // Load text domain
    load_plugin_textdomain('rwc-coupon-exporter', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Check WooCommerce dependency and version
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            /* translators: %s: Plugin name "WooCommerce" that needs to be installed and activated */
            $message = sprintf(
                esc_html__('RWC Coupon Exporter requires %s to be installed and activated.', 'rwc-coupon-exporter'),
                '<strong>WooCommerce</strong>'
            );
            echo '<div class="error"><p>' . wp_kses($message, array('strong' => array())) . '</p></div>';
        });
        return;
    }

    if (version_compare(WC_VERSION, '3.0', '<')) {
        add_action('admin_notices', function() {
            /* translators: %s: Required minimum WooCommerce version number */
            $message = sprintf(
                esc_html__('RWC Coupon Exporter requires WooCommerce version %s or higher.', 'rwc-coupon-exporter'),
                '3.0'
            );
            echo '<div class="error"><p>' . esc_html($message) . '</p></div>';
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