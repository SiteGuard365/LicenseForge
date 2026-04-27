<?php
/**
 * View: Analytics (only loaded when module enabled).
 *
 * Lightweight — pulls a few aggregate counts from cached transients
 * to avoid heavy queries on busy stores.
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use LicenseForge\Database;

global $wpdb;
$lic_table = Database::table( 'licenses' );
$log_table = Database::table( 'delivery_log' );

// 5-minute cache.
$stats = get_transient( 'lf_analytics_stats' );
if ( false === $stats ) {
	$stats = [
		'total'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $lic_table" ), // phpcs:ignore
		'sold'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $lic_table WHERE status='sold'" ), // phpcs:ignore
		'available' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $lic_table WHERE status='available'" ), // phpcs:ignore
		'delivered' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $log_table WHERE status='sent'" ), // phpcs:ignore
	];
	set_transient( 'lf_analytics_stats', $stats, 5 * MINUTE_IN_SECONDS );
}

// Last-7-days delivery counts.
$week = get_transient( 'lf_analytics_week' );
if ( false === $week ) {
	$week = [];
	for ( $i = 6; $i >= 0; $i-- ) {
		$d        = gmdate( 'Y-m-d', strtotime( "-$i days" ) );
		$count    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $log_table WHERE DATE(created_at)=%s AND status='sent'", $d ) ); // phpcs:ignore
		$week[] = [ 'd' => date_i18n( 'D', strtotime( $d ) ), 'v' => $count ];
	}
	set_transient( 'lf_analytics_week', $week, 5 * MINUTE_IN_SECONDS );
}
$max = max( 1, max( wp_list_pluck( $week, 'v' ) ) );
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php esc_html_e( 'Analytics', 'licenseforge' ); ?></h1>
		<span class="lf-subtitle"><?php esc_html_e( '(refreshes every 5 minutes)', 'licenseforge' ); ?></span>
	</div>

	<div class="lf-grid-4">
		<div class="lf-stat">
			<div class="lf-stat-icon bg-blue">🔑</div>
			<div class="lf-stat-label"><?php esc_html_e( 'Total Keys', 'licenseforge' ); ?></div>
			<div class="lf-stat-value"><?php echo number_format_i18n( $stats['total'] ); ?></div>
		</div>
		<div class="lf-stat">
			<div class="lf-stat-icon bg-green">✓</div>
			<div class="lf-stat-label"><?php esc_html_e( 'Sold', 'licenseforge' ); ?></div>
			<div class="lf-stat-value" style="color:#00a32a"><?php echo number_format_i18n( $stats['sold'] ); ?></div>
		</div>
		<div class="lf-stat">
			<div class="lf-stat-icon bg-warning">⏳</div>
			<div class="lf-stat-label"><?php esc_html_e( 'In Stock', 'licenseforge' ); ?></div>
			<div class="lf-stat-value" style="color:#dba617"><?php echo number_format_i18n( $stats['available'] ); ?></div>
		</div>
		<div class="lf-stat">
			<div class="lf-stat-icon bg-blue">✉</div>
			<div class="lf-stat-label"><?php esc_html_e( 'Emails Delivered', 'licenseforge' ); ?></div>
			<div class="lf-stat-value"><?php echo number_format_i18n( $stats['delivered'] ); ?></div>
		</div>
	</div>

	<div class="lf-card">
		<div class="lf-card-header"><h2 class="lf-card-title"><?php esc_html_e( 'Deliveries — Last 7 days', 'licenseforge' ); ?></h2></div>
		<div class="lf-card-body">
			<div class="lf-bars">
				<?php foreach ( $week as $d ) :
					$h = max( 8, round( $d['v'] / $max * 100 ) );
				?>
					<div class="col">
						<div class="v"><?php echo esc_html( $d['v'] ); ?></div>
						<div class="fill" style="height:<?php echo esc_attr( $h ); ?>%"></div>
					</div>
				<?php endforeach; ?>
			</div>
			<div style="display:flex;gap:6px;margin-top:8px">
				<?php foreach ( $week as $d ) : ?>
					<div style="flex:1;text-align:center;font-size:11px;color:#646970"><?php echo esc_html( $d['d'] ); ?></div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
