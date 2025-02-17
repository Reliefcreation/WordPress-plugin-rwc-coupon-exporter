<?php
/**
 * Admin functionality class
 *
 * @package RWC_Coupon_Exporter
 * @since 1.0.0
 */

class RWC_Coupon_Exporter_Admin {
    /**
     * Initialize admin functionality
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_post_rwc_export_coupons', array($this, 'handle_export'));
    }

    /**
     * Add menu to admin panel
     */
    public function add_admin_menu() {
        if (current_user_can('manage_woocommerce')) {
            add_submenu_page(
                'woocommerce',
                'Export Coupons',
                'Export Coupons',
                'manage_woocommerce',
                'coupon-exporter',
                array($this, 'render_admin_page')
            );
        }
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        // Verify nonce and check for errors
        if (isset($_GET['error']) && isset($_GET['message']) && check_admin_referer('rwc_coupon_export_error')) {
            $message = sanitize_text_field(wp_unslash($_GET['message']));
            echo '<div class="error"><p>' . esc_html($message) . '</p></div>';
        }

        require_once RWC_COUPON_EXPORTER_PATH . 'templates/admin-page.php';
    }

    /**
     * Handle export request
     */
    public function handle_export() {
        check_admin_referer('rwc_coupon_export_nonce', 'rwc_coupon_export_nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die('You do not have sufficient permissions to export coupons.');
        }

        $handler = new RWC_Coupon_Exporter_Handler();
        $handler->process_export();
    }
}