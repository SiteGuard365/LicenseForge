<?php
/**
 * View: Stock Manager.
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use LicenseForge\Licenses;
use LicenseForge\Helpers;

// Pull all products that have LicenseForge enabled, with stock counts.
$threshold = (int) Helpers::setting( 'low_stock_threshold', 10 );
$rows      = [];

if ( function_exists( 'wc_get_products' ) ) {
	$products = wc_get_products( [
		'limit'       => 100,
		'status'      => 'publish',
		'meta_key'    => '_lf_sell_keys', // phpcs:ignore
		'meta_value'  => 'yes',          // phpcs:ignore
	] );
	foreach ( $products as $p ) {
		$rows[] = [
			'id'    => $p->get_id(),
			'name'  => $p->get_name(),
			'avail' => Licenses::stock_count( $p->get_id() ),
			'sold'  => Licenses::count( $p->get_id(), 'sold' ),
		];
	}
}
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php esc_html_e( 'Stock Manager', 'licenseforge' ); ?></h1>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=licenseforge&page_view=import' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Import Keys', 'licenseforge' ); ?></a>
	</div>

	<?php if ( empty( $rows ) ) : ?>
		<div class="lf-card"><div class="lf-card-body">
			<p><?php esc_html_e( 'No products are configured to sell license keys yet.', 'licenseforge' ); ?></p>
			<p><?php esc_html_e( 'Edit a WooCommerce product → LicenseForge tab → enable "Sell license keys".', 'licenseforge' ); ?></p>
		</div></div>
		<?php return; ?>
	<?php endif; ?>

	<div class="lf-grid-2">
		<div class="lf-card">
			<div class="lf-card-header"><h2 class="lf-card-title"><?php esc_html_e( 'Stock Levels', 'licenseforge' ); ?></h2></div>
			<div class="lf-card-body">
				<?php foreach ( $rows as $r ) :
					$total = max( 1, $r['avail'] + $r['sold'] );
					$pct   = max( 3, round( $r['avail'] / $total * 100 ) );
					$col   = $r['avail'] <= $threshold ? '#d63638' : ( $r['avail'] <= ( $threshold * 2 ) ? '#dba617' : '#00a32a' );
					?>
					<div class="lf-stock-row">
						<div class="name"><?php echo esc_html( $r['name'] ); ?></div>
						<div class="lf-pbar"><div class="lf-pbar-fill" style="width:<?php echo esc_attr( $pct ); ?>%;background:<?php echo esc_attr( $col ); ?>"></div></div>
						<div class="num" style="color:<?php echo esc_attr( $col ); ?>"><?php echo esc_html( $r['avail'] ); ?> <?php esc_html_e( 'left', 'licenseforge' ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="lf-card">
			<div class="lf-card-header"><h2 class="lf-card-title"><?php esc_html_e( 'All Products', 'licenseforge' ); ?></h2></div>
			<div class="lf-card-body no-pad">
				<table class="wp-list-table widefat striped" style="border:none">
					<thead><tr>
						<th><?php esc_html_e( 'Product', 'licenseforge' ); ?></th>
						<th><?php esc_html_e( 'Available', 'licenseforge' ); ?></th>
						<th><?php esc_html_e( 'Sold', 'licenseforge' ); ?></th>
						<th><?php esc_html_e( 'Status', 'licenseforge' ); ?></th>
					</tr></thead>
					<tbody>
						<?php foreach ( $rows as $r ) :
							$badge = $r['avail'] <= 0 ? 'error' : ( $r['avail'] <= $threshold ? 'warning' : 'success' );
							$lbl   = $r['avail'] <= 0 ? __( 'Out of stock', 'licenseforge' ) : ( $r['avail'] <= $threshold ? __( 'Low', 'licenseforge' ) : __( 'OK', 'licenseforge' ) );
						?>
							<tr>
								<td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $r['id'] . '&action=edit' ) ); ?>"><?php echo esc_html( $r['name'] ); ?></a></td>
								<td><strong><?php echo esc_html( $r['avail'] ); ?></strong></td>
								<td><?php echo esc_html( $r['sold'] ); ?></td>
								<td><span class="lf-badge lf-badge-<?php echo esc_attr( $badge ); ?>"><?php echo esc_html( $lbl ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="lf-card">
		<div class="lf-card-header"><h2 class="lf-card-title"><?php esc_html_e( 'Stock Settings', 'licenseforge' ); ?></h2></div>
		<div class="lf-card-body">
			<form method="post">
				<?php wp_nonce_field( 'licenseforge_save_settings' ); ?>
				<input type="hidden" name="lf_action" value="save_settings">
				<table class="form-table">
					<tr>
						<th><label><?php esc_html_e( 'Low Stock Threshold', 'licenseforge' ); ?></label></th>
						<td>
							<input type="number" name="low_stock_threshold" value="<?php echo esc_attr( $threshold ); ?>" min="0" class="small-text">
							<p class="description"><?php esc_html_e( 'Email alert when stock falls below this number.', 'licenseforge' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label><?php esc_html_e( 'Notification Email', 'licenseforge' ); ?></label></th>
						<td><input type="email" name="admin_email" value="<?php echo esc_attr( Helpers::setting( 'admin_email', get_option( 'admin_email' ) ) ); ?>" class="regular-text"></td>
					</tr>
				</table>
				<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save', 'licenseforge' ); ?>"></p>
			</form>
		</div>
	</div>
</div>
