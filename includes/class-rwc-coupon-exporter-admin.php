<?php
/**
 * Admin functionality class
 *
 * @package RWC_Coupon_Exporter
 * @since 1.3.2
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
                /* translators: Admin page title for the coupon exporter */
                esc_html__('Export Coupons', 'rwc-coupon-exporter'),
                /* translators: Admin menu item label for the coupon exporter */
                esc_html__('Export Coupons', 'rwc-coupon-exporter'),
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
            /* translators: Error message shown when a user tries to access a page without proper permissions */
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'rwc-coupon-exporter'));
        }

        // Verify nonce and check for errors
        if (isset($_GET['error']) && isset($_GET['message']) && isset($_GET['_wpnonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'rwc_coupon_export_error')) {
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
            /* translators: Error message shown when a user tries to export coupons without proper permissions */
            wp_die(esc_html__('You do not have sufficient permissions to export coupons.', 'rwc-coupon-exporter'));
        }

        $handler = new RWC_Coupon_Exporter_Handler();
        $handler->process_export();
    }
}