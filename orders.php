<?php
/**
 * View: Orders with license keys.
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$table = \LicenseForge\Database::table( 'licenses' );

// Light query: distinct order ids where license is sold/delivered.
$rows = $wpdb->get_results( "SELECT order_id, COUNT(*) AS keys, MAX(delivered_at) AS delivered FROM $table WHERE order_id > 0 GROUP BY order_id ORDER BY delivered DESC LIMIT 50" ); // phpcs:ignore
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php esc_html_e( 'Orders with License Keys', 'licenseforge' ); ?></h1>
	</div>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Order', 'licenseforge' ); ?></th>
				<th><?php esc_html_e( 'Customer', 'licenseforge' ); ?></th>
				<th><?php esc_html_e( 'Keys', 'licenseforge' ); ?></th>
				<th><?php esc_html_e( 'Total', 'licenseforge' ); ?></th>
				<th><?php esc_html_e( 'Delivered', 'licenseforge' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="6"><?php esc_html_e( 'No orders with license keys yet.', 'licenseforge' ); ?></td></tr>
			<?php else :
				foreach ( $rows as $r ) :
					$order = wc_get_order( $r->order_id );
					if ( ! $order ) continue;
					?>
					<tr>
						<td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $r->order_id . '&action=edit' ) ); ?>">#<?php echo esc_html( $r->order_id ); ?></a></td>
						<td><?php echo esc_html( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ); ?><br><span class="lf-subtitle" style="font-size:11px;color:#646970"><?php echo esc_html( $order->get_billing_email() ); ?></span></td>
						<td><span class="lf-badge lf-badge-info"><?php echo esc_html( $r->keys ); ?></span></td>
						<td><?php echo $order->get_formatted_order_total(); // phpcs:ignore ?></td>
						<td><?php echo $r->delivered ? esc_html( mysql2date( 'M j, Y', $r->delivered ) ) : '<em>—</em>'; ?></td>
						<td><a class="button button-small" href="<?php echo esc_url( admin_url( 'post.php?post=' . $r->order_id . '&action=edit' ) ); ?>"><?php esc_html_e( 'View', 'licenseforge' ); ?></a></td>
					</tr>
				<?php endforeach;
			endif; ?>
		</tbody>
	</table>
</div>
