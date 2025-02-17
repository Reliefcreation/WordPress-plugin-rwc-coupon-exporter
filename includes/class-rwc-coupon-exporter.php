<?php
/**
 * Main plugin class
 *
 * @package RWC_Coupon_Exporter
 */

class RWC_Coupon_Exporter {
    /**
     * Plugin instance
     *
     * @var RWC_Coupon_Exporter
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return RWC_Coupon_Exporter
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Check requirements first
        if (!$this->check_requirements()) {
            return;
        }

        // Initialize admin
        if (is_admin()) {
            $admin = new RWC_Coupon_Exporter_Admin();
            $admin->init();
        }
    }

    /**
     * Check plugin requirements
     *
     * @return bool Whether requirements are met
     */
    private function check_requirements() {
        $requirements_met = true;

        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function() {
                echo '<div class="error"><p>Coupon Exporter for WooCommerce requires WooCommerce to be installed and activated.</p></div>';
            });
            $requirements_met = false;
        }

        // Check WooCommerce version if it's active
        if (class_exists('WooCommerce')) {
            if (version_compare(WC_VERSION, '3.0', '<')) {
                add_action('admin_notices', function() {
                    echo '<div class="error"><p>Coupon Exporter for WooCommerce requires WooCommerce version 3.0 or higher.</p></div>';
                });
                $requirements_met = false;
            }
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', function() {
                echo '<div class="error"><p>Coupon Exporter for WooCommerce requires PHP version 7.4 or higher.</p></div>';
            });
            $requirements_met = false;
        }

        return $requirements_met;
    }
}