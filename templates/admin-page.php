<?php
/**
 * Admin page template
 *
 * @package RWC_Coupon_Exporter
 * @since 1.0.0
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>Export WooCommerce Coupons</h1>
    
    <div class="notice notice-info">
        <p>Export your WooCommerce coupons to a CSV file.</p>
    </div>

    <div class="card">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('rwc_coupon_export_nonce', 'rwc_coupon_export_nonce'); ?>
            <input type="hidden" name="action" value="rwc_export_coupons">
            
            <p style="margin-bottom: 15px;">Click the button below to download all your coupon codes in a CSV file format. The file will include all coupon details such as discount amounts, expiry dates, and usage restrictions.</p>
            
            <p class="submit">
                <button type="submit" class="button button-primary">
                    Export Coupons
                </button>
            </p>
        </form>
    </div>
</div>