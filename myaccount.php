<?php
/**
 * View: My Account setup (admin info page).
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php esc_html_e( 'My Account — Customer Setup', 'licenseforge' ); ?></h1>
	</div>

	<div class="lf-card">
		<div class="lf-card-header"><h2 class="lf-card-title"><?php esc_html_e( 'How customers see their license keys', 'licenseforge' ); ?></h2></div>
		<div class="lf-card-body">
			<p><?php esc_html_e( 'LicenseForge automatically adds a "License Keys" tab to the WooCommerce My Account page. Customers will see their keys, copy them, and (optionally) deactivate or download a CSV.', 'licenseforge' ); ?></p>

			<p><strong><?php esc_html_e( 'Frontend URL:', 'licenseforge' ); ?></strong>
				<a href="<?php echo esc_url( \LicenseForge\Helpers::my_account_url() ); ?>" target="_blank"><?php echo esc_url( \LicenseForge\Helpers::my_account_url() ); ?></a>
			</p>

			<h3><?php esc_html_e( 'Frontend Preview', 'licenseforge' ); ?></h3>
			<div style="background:#f6f7f7;border-radius:6px;padding:24px">
				<div class="lf-fe-license">
					<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;margin-bottom:8px">
						<div>
							<div style="font-size:15px;font-weight:600;color:#1d2327">Windows 11 Pro</div>
							<div style="font-size:12px;color:#50575e">Order #5091 · Delivered Apr 25, 2026</div>
						</div>
						<span class="lf-badge lf-badge-success">Active</span>
					</div>
					<div class="lf-fe-license-key">
						<span>XXXXX-YRTWQ-9A12K-P002A</span>
						<span class="lf-copy">⎘</span>
					</div>
					<div style="font-size:12px;color:#50575e">Activations: 1 of 3 · Expires: Lifetime</div>
				</div>
			</div>

			<p style="margin-top:16px"><em><?php esc_html_e( 'Tip: You can hide this section by going to Settings → Display → "Show keys in My Account".', 'licenseforge' ); ?></em></p>
		</div>
	</div>

	<?php if ( get_option( 'permalink_structure' ) === '' ) : ?>
		<div class="lf-card" style="background:#fcf9e8;border-color:#dba617">
			<div class="lf-card-body">
				<strong>⚠ <?php esc_html_e( 'Plain permalinks detected', 'licenseforge' ); ?></strong><br>
				<?php
				/* translators: %s: link to permalinks */
				printf( esc_html__( 'For My Account licenses to work, please switch to a non-plain permalink structure %s.', 'licenseforge' ), '<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">→ Permalinks</a>' );
				?>
			</div>
		</div>
	<?php endif; ?>
</div>
