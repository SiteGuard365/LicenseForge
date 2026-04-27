<?php
/**
 * View: WooCommerce product data panel — LicenseForge tab.
 *
 * Renders inside the product edit screen.
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$post_id = isset( $post_id ) ? (int) $post_id : 0;
$sell    = get_post_meta( $post_id, '_lf_sell_keys', true );
$kpu     = (int) get_post_meta( $post_id, '_lf_keys_per_unit', true );
$max_act = (int) get_post_meta( $post_id, '_lf_max_activations', true );
$valid   = (int) get_post_meta( $post_id, '_lf_validity_days', true );
$stock   = \LicenseForge\Licenses::stock_count( $post_id );
?>
<div id="lf_product_data" class="panel woocommerce_options_panel">
	<div class="options_group">

		<p class="form-field">
			<label for="_lf_sell_keys"><?php esc_html_e( 'Sell license keys', 'licenseforge' ); ?></label>
			<input type="checkbox" id="_lf_sell_keys" name="_lf_sell_keys" value="yes" <?php checked( $sell, 'yes' ); ?>>
			<span class="description"><?php esc_html_e( 'Auto-deliver license keys when this product is purchased.', 'licenseforge' ); ?></span>
		</p>

		<p class="form-field">
			<label for="_lf_keys_per_unit"><?php esc_html_e( 'Keys per purchase', 'licenseforge' ); ?></label>
			<input type="number" id="_lf_keys_per_unit" name="_lf_keys_per_unit" value="<?php echo esc_attr( $kpu ?: 1 ); ?>" min="1" max="50" class="short">
			<span class="description"><?php esc_html_e( 'How many keys to deliver per purchased unit.', 'licenseforge' ); ?></span>
		</p>

		<p class="form-field">
			<label for="_lf_max_activations"><?php esc_html_e( 'Max activations per key', 'licenseforge' ); ?></label>
			<input type="number" id="_lf_max_activations" name="_lf_max_activations" value="<?php echo esc_attr( $max_act ?: 1 ); ?>" min="0" class="short">
			<span class="description"><?php esc_html_e( '0 = unlimited.', 'licenseforge' ); ?></span>
		</p>

		<p class="form-field">
			<label for="_lf_validity_days"><?php esc_html_e( 'Validity (days)', 'licenseforge' ); ?></label>
			<input type="number" id="_lf_validity_days" name="_lf_validity_days" value="<?php echo esc_attr( $valid ); ?>" min="0" class="short">
			<span class="description"><?php esc_html_e( '0 or empty = lifetime.', 'licenseforge' ); ?></span>
		</p>

		<p class="form-field" style="background:#f0f6fc;border-left:4px solid #2271b1;padding:10px 12px;margin-top:12px">
			<strong><?php esc_html_e( 'Current stock:', 'licenseforge' ); ?></strong>
			<?php
			/* translators: %d: number of available keys */
			printf( esc_html__( '%d keys available', 'licenseforge' ), (int) $stock );
			?>
			·
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=licenseforge&page_view=import' ) ); ?>"><?php esc_html_e( 'Import more keys →', 'licenseforge' ); ?></a>
		</p>

	</div>
</div>
