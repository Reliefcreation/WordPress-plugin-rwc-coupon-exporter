<?php
/**
 * Export handler class
 *
 * @package RWC_Coupon_Exporter
 * @since 1.0.0
 */

class RWC_Coupon_Exporter_Handler {
    /**
     * Logger instance
     *
     * @var WC_Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @since 1.3.1
     */
    public function __construct() {
        $this->logger = wc_get_logger();
    }

    /**
     * Process export request
     *
     * @since 1.0.0
     */
    public function process_export() {
        try {
            $this->validate_requirements();
            $coupons = $this->get_coupons();
            $this->export_coupons($coupons);
        } catch (Exception $e) {
            $this->handle_error($e);
        }
    }

    /**
     * Validate export requirements
     *
     * @since 1.3.1
     * @throws Exception If requirements are not met
     */
    private function validate_requirements() {
        if (!class_exists('WC_Coupon')) {
            throw new Exception('WooCommerce Coupon functionality is not available.');
        }

        if (!isset($_POST['rwc_coupon_export_nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rwc_coupon_export_nonce'])), 'rwc_coupon_export_nonce')) {
            throw new Exception('Security check failed.');
        }

        if (!current_user_can('manage_woocommerce')) {
            throw new Exception('You do not have sufficient permissions to export coupons.');
        }
    }

    /**
     * Get coupons for export
     *
     * @since 1.3.1
     * @return array Array of coupon IDs
     * @throws Exception If no coupons are found
     */
    private function get_coupons() {
        add_filter('posts_per_page', array($this, 'limit_batch_size'));

        $coupons = get_posts(array(
            'posts_per_page' => -1,
            'post_type'      => 'shop_coupon',
            'post_status'    => 'publish',
            'fields'         => 'ids'
        ));

        remove_filter('posts_per_page', array($this, 'limit_batch_size'));

        if (empty($coupons)) {
            throw new Exception('No coupons found to export.');
        }

        return $coupons;
    }

    /**
     * Limit batch size for large exports
     *
     * @since 1.3.1
     * @param int $limit Current limit
     * @return int Modified limit
     */
    public function limit_batch_size($limit) {
        return min(100, $limit);
    }

    /**
     * Export coupons to CSV
     *
     * @since 1.3.1
     * @param array $coupons Array of coupon IDs
     */
    private function export_coupons($coupons) {
        wp_raise_memory_limit('admin');
        
        while (ob_get_level()) {
            ob_end_clean();
        }

        $filename = sanitize_file_name('woocommerce-coupons-' . gmdate('Y-m-d-His') . '.csv');
        $headers = $this->get_csv_headers();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, array_values($headers));

        foreach ($coupons as $coupon_id) {
            $row = $this->get_coupon_data($coupon_id);
            if ($row) {
                fputcsv($output, array_map('esc_html', $row));
            }
        }

        wp_die();
    }

    /**
     * Get CSV headers
     *
     * @since 1.3.1
     * @return array CSV headers
     */
    private function get_csv_headers() {
        $headers = array(
            'code'                => 'Code',
            'description'         => 'Description',
            'discount_type'       => 'Discount Type',
            'amount'             => 'Amount',
            'expiry_date'        => 'Expiry Date',
            'minimum_spend'      => 'Minimum Spend',
            'maximum_spend'      => 'Maximum Spend',
            'individual_use'     => 'Individual Use',
            'exclude_sale_items' => 'Exclude Sale Items',
            'usage_limit'        => 'Usage Limit',
            'usage_count'        => 'Usage Count',
            'products'           => 'Products',
            'exclude_products'   => 'Exclude Products',
            'categories'         => 'Product Categories',
            'exclude_categories' => 'Exclude Categories',
            'email_restrictions' => 'Email Restrictions'
        );

        return apply_filters('rwc_coupon_exporter_csv_headers', $headers);
    }

    /**
     * Get coupon data
     *
     * @since 1.3.1
     * @param int $coupon_id Coupon ID
     * @return array|false Coupon data or false on error
     */
    private function get_coupon_data($coupon_id) {
        try {
            $wc_coupon = new WC_Coupon($coupon_id);
            
            if (!$wc_coupon || is_wp_error($wc_coupon)) {
                return false;
            }
            
            $row = array(
                $wc_coupon->get_code(),
                $wc_coupon->get_description(),
                $wc_coupon->get_discount_type(),
                $wc_coupon->get_amount(),
                $wc_coupon->get_date_expires() ? $wc_coupon->get_date_expires()->date('Y-m-d') : '',
                $wc_coupon->get_minimum_amount(),
                $wc_coupon->get_maximum_amount(),
                $wc_coupon->get_individual_use() ? 'yes' : 'no',
                $wc_coupon->get_exclude_sale_items() ? 'yes' : 'no',
                $wc_coupon->get_usage_limit(),
                $wc_coupon->get_usage_count(),
                $this->format_product_list($wc_coupon->get_product_ids()),
                $this->format_product_list($wc_coupon->get_excluded_product_ids()),
                $this->format_category_list($wc_coupon->get_product_categories()),
                $this->format_category_list($wc_coupon->get_excluded_product_categories()),
                implode(', ', array_map('esc_html', $wc_coupon->get_email_restrictions()))
            );

            return apply_filters('rwc_coupon_exporter_csv_row', $row, $wc_coupon);
        } catch (Exception $e) {
            $this->log_error('Error processing coupon #' . $coupon_id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format product list for CSV
     *
     * @since 1.0.0
     * @param array $product_ids Array of product IDs
     * @return string Formatted product list
     */
    private function format_product_list($product_ids) {
        $products = array();
        if (!empty($product_ids) && is_array($product_ids)) {
            foreach ($product_ids as $product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    $products[] = $product->get_name();
                }
            }
        }
        return implode(', ', array_map('esc_html', $products));
    }

    /**
     * Format category list for CSV
     *
     * @since 1.0.0
     * @param array $category_ids Array of category IDs
     * @return string Formatted category list
     */
    private function format_category_list($category_ids) {
        $names = array();
        if (!empty($category_ids) && is_array($category_ids)) {
            foreach ($category_ids as $category_id) {
                try {
                    $term = get_term($category_id, 'product_cat');
                    if ($term && !is_wp_error($term)) {
                        $names[] = $term->name;
                    }
                } catch (Exception $e) {
                    $this->log_error('Error processing category #' . $category_id . ': ' . $e->getMessage());
                    continue;
                }
            }
        }
        return implode(', ', array_map('esc_html', $names));
    }

    /**
     * Handle export error
     *
     * @since 1.3.1
     * @param Exception $e Exception object
     */
    private function handle_error($e) {
        $this->log_error($e->getMessage());
        wp_safe_redirect(add_query_arg(
            array(
                'page' => 'coupon-exporter',
                'error' => '1',
                'message' => urlencode($e->getMessage())
            ),
            admin_url('admin.php')
        ));
        wp_die();
    }

    /**
     * Log error message
     *
     * @since 1.3.1
     * @param string $message Error message
     */
    private function log_error($message) {
        if (class_exists('WC_Logger')) {
            $this->logger->error(
                'Coupon Export Error: ' . $message,
                array('source' => 'rwc-coupon-exporter')
            );
        }
    }
}