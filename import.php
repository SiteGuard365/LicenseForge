<?php
/**
 * View: Import Keys.
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! empty( $_GET['lf_msg'] ) ) { // phpcs:ignore
	\LicenseForge\Helpers::admin_notice(
		sanitize_text_field( wp_unslash( $_GET['lf_msg'] ) ), // phpcs:ignore
		isset( $_GET['lf_type'] ) ? sanitize_key( wp_unslash( $_GET['lf_type'] ) ) : 'success' // phpcs:ignore
	);
}

$products = function_exists( 'wc_get_products' ) ? wc_get_products( [ 'limit' => 200, 'status' => 'publish' ] ) : [];
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php esc_html_e( 'Import License Keys', 'licenseforge' ); ?></h1>
	</div>

	<h2 class="nav-tab-wrapper lf-tabs-nav">
		<a href="#" class="nav-tab nav-tab-active lf-tab" data-target="lf-tab-paste"><?php esc_html_e( 'Paste Keys', 'licenseforge' ); ?></a>
		<a href="#" class="nav-tab lf-tab" data-target="lf-tab-csv"><?php esc_html_e( 'CSV Upload', 'licenseforge' ); ?></a>
	</h2>

	<div id="lf-tab-paste" class="lf-tab-content lf-card">
		<div class="lf-card-body">
			<form method="post">
				<?php wp_nonce_field( 'licenseforge_import_paste' ); ?>
				<input type="hidden" name="lf_action" value="import_paste">
				<table class="form-table">
					<tr>
						<th><label><?php esc_html_e( 'Product', 'licenseforge' ); ?></label></th>
						<td>
							<select name="product_id" required>
								<option value="0">— <?php esc_html_e( 'Choose product', 'licenseforge' ); ?> —</option>
								<?php foreach ( $products as $p ) : ?>
									<option value="<?php echo esc_attr( $p->get_id() ); ?>"><?php echo esc_html( $p->get_name() ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label><?php esc_html_e( 'Keys (one per line)', 'licenseforge' ); ?></label></th>
						<td><textarea name="keys_blob" rows="10" class="large-text" required placeholder="XXXXX-AAAAA-BBBBB-CCCCC&#10;XXXXX-DDDDD-EEEEE-FFFFF"></textarea></td>
					</tr>
				</table>
				<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Import Keys', 'licenseforge' ); ?>"></p>
			</form>
		</div>
	</div>

	<div id="lf-tab-csv" class="lf-tab-content lf-card" style="display:none">
		<div class="lf-card-body">
			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'licenseforge_import_csv' ); ?>
				<input type="hidden" name="lf_action" value="import_csv">
				<table class="form-table">
					<tr>
						<th><label><?php esc_html_e( 'Product', 'licenseforge' ); ?></label></th>
						<td>
							<select name="product_id" required>
								<option value="0">— <?php esc_html_e( 'Choose product', 'licenseforge' ); ?> —</option>
								<?php foreach ( $products as $p ) : ?>
									<option value="<?php echo esc_attr( $p->get_id() ); ?>"><?php echo esc_html( $p->get_name() ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label><?php esc_html_e( 'CSV File', 'licenseforge' ); ?></label></th>
						<td>
							<div class="lf-drop">
								<p style="font-weight:600;color:#1d2327"><?php esc_html_e( 'Required column: license_key', 'licenseforge' ); ?></p>
								<p><?php esc_html_e( 'Optional: expires_at (YYYY-MM-DD)', 'licenseforge' ); ?></p>
								<input type="file" name="csv" accept=".csv" required>
							</div>
						</td>
					</tr>
				</table>
				<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Upload &amp; Import', 'licenseforge' ); ?>"></p>
			</form>
		</div>
	</div>
</div>
