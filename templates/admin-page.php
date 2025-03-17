<?php
/**
 * Admin page template
 *
 * @package RWC_Coupon_Exporter
 * @since 1.3.2
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html__('Export WooCommerce Coupons', 'rwc-coupon-exporter'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php echo esc_html__('Export your WooCommerce coupons to a CSV file.', 'rwc-coupon-exporter'); ?></p>
    </div>

    <div class="card">
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('rwc_coupon_export_nonce', 'rwc_coupon_export_nonce'); ?>
            <input type="hidden" name="action" value="rwc_export_coupons">
            
            <p style="margin-bottom: 15px;"><?php echo esc_html__('Click the button below to download all your coupon codes in a CSV file format. The file will include all coupon details such as discount amounts, expiry dates, and usage restrictions.', 'rwc-coupon-exporter'); ?></p>
            <p style="margin-bottom: 20px;"><strong><?php echo esc_html__('Note:', 'rwc-coupon-exporter'); ?></strong> <?php echo esc_html__('The exported CSV file can be used for backup purposes or to analyze your coupon data in spreadsheet software.', 'rwc-coupon-exporter'); ?></p>
            
            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php echo esc_html__('Export Coupons', 'rwc-coupon-exporter'); ?>
                </button>
            </p>
        </form>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h2><?php echo esc_html__('Export Information', 'rwc-coupon-exporter'); ?></h2>
        <p><?php echo esc_html__('The exported CSV file will include the following information for each coupon:', 'rwc-coupon-exporter'); ?></p>
        <ul style="list-style-type: disc; margin-left: 20px;">
            <li><?php echo esc_html__('Coupon code', 'rwc-coupon-exporter'); ?></li>
            <li><?php echo esc_html__('Description', 'rwc-coupon-exporter'); ?></li>
            <li><?php echo esc_html__('Discount type and amount', 'rwc-coupon-exporter'); ?></li>
            <li><?php echo esc_html__('Expiry date', 'rwc-coupon-exporter'); ?></li>
            <li><?php echo esc_html__('Minimum and maximum spend requirements', 'rwc-coupon-exporter'); ?></li>
            <li><?php echo esc_html__('Usage restrictions and limits', 'rwc-coupon-exporter'); ?></li>
            <li><?php echo esc_html__('Product and category restrictions', 'rwc-coupon-exporter'); ?></li>
            <li><?php echo esc_html__('Email restrictions', 'rwc-coupon-exporter'); ?></li>
        </ul>
        <p style="margin-top: 15px;"><strong><?php echo esc_html__('Privacy Note:', 'rwc-coupon-exporter'); ?></strong> <?php echo esc_html__('If your coupons include email restrictions, these email addresses will be included in the export. Please handle the exported file according to your privacy policy.', 'rwc-coupon-exporter'); ?></p>
    </div>
</div>