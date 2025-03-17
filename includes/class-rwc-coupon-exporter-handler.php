<?php
/**
 * Export handler class
 *
 * @package RWC_Coupon_Exporter
 * @since 1.3.2
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
     * @since 1.3.2
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
            /* translators: Error message displayed when WooCommerce coupon functionality is not available */
            throw new Exception(esc_html__('WooCommerce Coupon functionality is not available.', 'rwc-coupon-exporter'));
        }

        if (!isset($_POST['rwc_coupon_export_nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rwc_coupon_export_nonce'])), 'rwc_coupon_export_nonce')) {
            /* translators: Error message displayed when the security verification fails */
            throw new Exception(esc_html__('Security check failed.', 'rwc-coupon-exporter'));
        }

        if (!current_user_can('manage_woocommerce')) {
            /* translators: Error message displayed when the user doesn't have sufficient permissions */
            throw new Exception(esc_html__('You do not have sufficient permissions to export coupons.', 'rwc-coupon-exporter'));
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
        $args = array(
            'posts_per_page' => -1,
            'post_type'      => 'shop_coupon',
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'orderby'        => 'ID',
            'order'          => 'ASC'
        );

        $coupons = get_posts($args);

        if (empty($coupons)) {
            /* translators: Error message displayed when no coupons are found in the database */
            throw new Exception(esc_html__('No coupons found to export.', 'rwc-coupon-exporter'));
        }

        return $coupons;
    }

    /**
     * Export coupons to CSV
     *
     * @since 1.3.2
     * @param array $coupons Array of coupon IDs
     */
    private function export_coupons($coupons) {
        try {
            // Clean output buffer
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename=woocommerce-coupons-' . date('Y-m-d-His') . '.csv');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Create output handle
            $output = fopen('php://output', 'w');

            // Add UTF-8 BOM
            fputs($output, "\xEF\xBB\xBF");

            // Get and write headers
            $headers = array_values($this->get_csv_headers());
            fputcsv($output, $headers);

            // Write coupon data
            foreach ($coupons as $coupon_id) {
                $row = $this->get_coupon_data($coupon_id);
                if ($row) {
                    fputcsv($output, $row);
                }
            }

            // Close the output
            fclose($output);
            exit();

        } catch (Exception $e) {
            $this->handle_error($e);
        }
    }

    /**
     * Get CSV headers
     *
     * @since 1.3.1
     * @return array CSV headers
     */
    private function get_csv_headers() {
        $headers = array(
            /* translators: CSV column header for the coupon code field */
            'code'                => esc_html__('Code', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the coupon description field */
            'description'         => esc_html__('Description', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the discount type field (e.g., percentage, fixed amount) */
            'discount_type'       => esc_html__('Discount Type', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the discount amount field */
            'amount'             => esc_html__('Amount', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the coupon expiry date field */
            'expiry_date'        => esc_html__('Expiry Date', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the minimum spend requirement field */
            'minimum_spend'      => esc_html__('Minimum Spend', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the maximum spend limit field */
            'maximum_spend'      => esc_html__('Maximum Spend', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the individual use only setting field */
            'individual_use'     => esc_html__('Individual Use', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the exclude sale items setting field */
            'exclude_sale_items' => esc_html__('Exclude Sale Items', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the usage limit field */
            'usage_limit'        => esc_html__('Usage Limit', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the current usage count field */
            'usage_count'        => esc_html__('Usage Count', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the included products field */
            'products'           => esc_html__('Products', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the excluded products field */
            'exclude_products'   => esc_html__('Exclude Products', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the included product categories field */
            'categories'         => esc_html__('Product Categories', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the excluded product categories field */
            'exclude_categories' => esc_html__('Exclude Categories', 'rwc-coupon-exporter'),
            /* translators: CSV column header for the email restrictions field */
            'email_restrictions' => esc_html__('Email Restrictions', 'rwc-coupon-exporter')
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
                implode(', ', array_map('esc_html', (array)$wc_coupon->get_email_restrictions()))
            );

            return apply_filters('rwc_coupon_exporter_csv_row', $row, $wc_coupon);
        } catch (Exception $e) {
            /* translators: %1$d: coupon ID, %2$s: error message */
            $this->log_error(sprintf(esc_html__('Error processing coupon #%1$d: %2$s', 'rwc-coupon-exporter'), $coupon_id, $e->getMessage()));
            return false;
        }
    }

    /**
     * Format product list for CSV
     *
     * @since 1.3.2
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
     * @since 1.3.2
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
                    /* translators: %1$d: category ID, %2$s: error message */
                    $this->log_error(sprintf(esc_html__('Error processing category #%1$d: %2$s', 'rwc-coupon-exporter'), $category_id, $e->getMessage()));
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
                'message' => urlencode($e->getMessage()),
                '_wpnonce' => wp_create_nonce('rwc_coupon_export_error')
            ),
            admin_url('admin.php')
        ));
        exit();
    }

    /**
     * Log error message
     *
     * @since 1.3.1
     * @param string $message Error message
     */
    private function log_error($message) {
        if ($this->logger) {
            $this->logger->error(
                'Coupon Export Error: ' . $message,
                array('source' => 'rwc-coupon-exporter')
            );
        }
    }
}