<?php
/**
 * View: Coming Soon screen.
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$labels = [
	'subscriptions' => [
		'title'   => __( 'Subscriptions & Auto-Renewal', 'licenseforge' ),
		'icon'    => '🔄',
		'desc'    => __( 'Auto-renew customer licenses, track MRR, and reduce churn.', 'licenseforge' ),
		'roadmap' => [
			__( 'Recurring billing on Stripe / Razorpay / PayPal', 'licenseforge' ),
			__( 'Customer-facing renewal dashboard', 'licenseforge' ),
			__( 'Pre-renewal email + WhatsApp notifications', 'licenseforge' ),
			__( 'Failed-payment retry logic with grace period', 'licenseforge' ),
			__( 'MRR / churn / LTV charts', 'licenseforge' ),
		],
	],
	'reports' => [
		'title'   => __( 'Reports & PDF Invoices', 'licenseforge' ),
		'icon'    => '📊',
		'desc'    => __( 'Branded PDF invoices, GST/VAT reports, sales exports.', 'licenseforge' ),
		'roadmap' => [
			__( 'Auto-generated PDF invoices with your logo', 'licenseforge' ),
			__( 'GST / VAT / tax breakdown', 'licenseforge' ),
			__( 'Sales reports — daily / monthly / annual', 'licenseforge' ),
			__( 'Inventory valuation report', 'licenseforge' ),
			__( 'CSV / XLSX exports for accountants', 'licenseforge' ),
		],
	],
	'expiry' => [
		'title'   => __( 'Expiry Alerts', 'licenseforge' ),
		'icon'    => '⏰',
		'desc'    => __( 'Auto reminders before license expiry to drive renewals.', 'licenseforge' ),
		'roadmap' => [
			__( '30 / 15 / 3 day pre-expiry email + WhatsApp', 'licenseforge' ),
			__( 'Auto-applied renewal discount codes', 'licenseforge' ),
			__( 'Conversion tracking on renewal reminders', 'licenseforge' ),
			__( 'Bulk send + custom rules per product', 'licenseforge' ),
		],
	],
];

$key  = isset( $feature ) ? $feature : 'subscriptions';
$info = $labels[ $key ] ?? $labels['subscriptions'];
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php echo esc_html( $info['title'] ); ?></h1>
		<span class="lf-badge lf-badge-info"><?php esc_html_e( 'Coming Soon', 'licenseforge' ); ?></span>
	</div>

	<div class="lf-coming-soon">
		<div style="font-size:48px;margin-bottom:8px"><?php echo esc_html( $info['icon'] ); ?></div>
		<h2><?php echo esc_html( $info['title'] ); ?></h2>
		<p><?php echo esc_html( $info['desc'] ); ?></p>

		<ul class="lf-roadmap-list">
			<?php foreach ( $info['roadmap'] as $item ) : ?>
				<li><?php echo esc_html( $item ); ?></li>
			<?php endforeach; ?>
		</ul>

		<p style="margin-top:24px"><em><?php esc_html_e( 'No code is loaded for this feature yet — your site stays light. We\'ll ship it as a free auto-update once stable.', 'licenseforge' ); ?></em></p>
	</div>
</div>
