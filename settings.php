<?php
/**
 * View: Settings (with Modules tab — heart of the plugin).
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use LicenseForge\Helpers;
use LicenseForge\Plugin;

$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'modules'; // phpcs:ignore
$settings = (array) get_option( 'lf_settings', [] );

if ( ! empty( $_GET['lf_msg'] ) ) Helpers::admin_notice( sanitize_text_field( wp_unslash( $_GET['lf_msg'] ) ) ); // phpcs:ignore

$mm       = Plugin::instance()->modules;
$registry = $mm->all();
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php esc_html_e( 'LicenseForge Settings', 'licenseforge' ); ?></h1>
	</div>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=licenseforge&page_view=settings&tab=modules' ) ); ?>" class="nav-tab <?php echo $tab === 'modules' ? 'nav-tab-active' : ''; ?>">⚡ <?php esc_html_e( 'Modules', 'licenseforge' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=licenseforge&page_view=settings&tab=general' ) ); ?>" class="nav-tab <?php echo $tab === 'general' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'General', 'licenseforge' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=licenseforge&page_view=settings&tab=display' ) ); ?>" class="nav-tab <?php echo $tab === 'display' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Display', 'licenseforge' ); ?></a>
	</h2>

	<?php if ( $tab === 'modules' ) : ?>

		<div class="lf-card">
			<div class="lf-card-header">
				<h2 class="lf-card-title"><?php esc_html_e( 'Optional Modules', 'licenseforge' ); ?></h2>
				<span class="lf-subtitle"><?php esc_html_e( 'Only enable what you need — disabled modules don\'t load any code.', 'licenseforge' ); ?></span>
			</div>
			<div class="lf-card-body no-pad">
				<form method="post">
					<?php wp_nonce_field( 'licenseforge_save_modules' ); ?>
					<input type="hidden" name="lf_action" value="save_modules">

					<?php foreach ( $registry as $slug => $def ) :
						$is_on        = $mm->is_enabled( $slug );
						$is_coming    = $def['status'] !== 'stable';
						?>
						<div class="lf-module-row <?php echo $is_coming ? 'locked' : ''; ?>">
							<div class="info">
								<strong><?php echo esc_html( $def['label'] ); ?></strong>
								<?php if ( $is_coming ) : ?>
									<span class="lf-badge lf-badge-info" style="margin-left:6px"><?php esc_html_e( 'Coming Soon', 'licenseforge' ); ?></span>
								<?php elseif ( ! empty( $def['heavy'] ) ) : ?>
									<span class="lf-badge lf-badge-warning" style="margin-left:6px"><?php esc_html_e( 'Heavy', 'licenseforge' ); ?></span>
								<?php endif; ?>
								<p><?php echo esc_html( $def['description'] ); ?></p>
							</div>
							<div class="meta">
								<?php if ( $is_coming ) : ?>
									<span class="dashicons dashicons-lock" style="color:#a7aaad"></span>
								<?php else : ?>
									<label class="lf-toggle">
										<input type="checkbox" name="modules[<?php echo esc_attr( $slug ); ?>]" value="1" <?php checked( $is_on, true ); ?>>
										<span class="lf-toggle-sl"></span>
									</label>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>

					<div style="padding:14px"><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Modules', 'licenseforge' ); ?>"></div>
				</form>
			</div>
		</div>

		<div class="lf-card" style="background:#f0f6fc;border-color:#72aee6">
			<div class="lf-card-body">
				<strong>💡 <?php esc_html_e( 'Performance tip', 'licenseforge' ); ?></strong><br>
				<?php esc_html_e( 'LicenseForge\'s core (licenses, stock, email delivery) is always-on. Optional modules like WhatsApp and Support Inbox only load their PHP, JS and CSS when enabled — disabling them returns the plugin to a near-zero overhead.', 'licenseforge' ); ?>
			</div>
		</div>

	<?php elseif ( $tab === 'general' ) : ?>

		<div class="lf-card"><div class="lf-card-body">
			<form method="post">
				<?php wp_nonce_field( 'licenseforge_save_settings' ); ?>
				<input type="hidden" name="lf_action" value="save_settings">
				<table class="form-table">
					<tr>
						<th><label><?php esc_html_e( 'Auto-deliver on order complete', 'licenseforge' ); ?></label></th>
						<td><label class="lf-toggle"><input type="checkbox" name="auto_delivery" value="1" <?php checked( ! empty( $settings['auto_delivery'] ) ); ?>><span class="lf-toggle-sl"></span></label></td>
					</tr>
					<tr>
						<th><label><?php esc_html_e( 'Notification Email', 'licenseforge' ); ?></label></th>
						<td><input type="email" name="admin_email" value="<?php echo esc_attr( $settings['admin_email'] ?? get_option( 'admin_email' ) ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><label><?php esc_html_e( 'Low Stock Threshold', 'licenseforge' ); ?></label></th>
						<td><input type="number" name="low_stock_threshold" value="<?php echo esc_attr( $settings['low_stock_threshold'] ?? 10 ); ?>" class="small-text"></td>
					</tr>
				</table>
				<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'licenseforge' ); ?>"></p>
			</form>
		</div></div>

	<?php else : ?>

		<div class="lf-card"><div class="lf-card-body">
			<form method="post">
				<?php wp_nonce_field( 'licenseforge_save_settings' ); ?>
				<input type="hidden" name="lf_action" value="save_settings">
				<table class="form-table">
					<tr>
						<th><label><?php esc_html_e( 'Show keys in My Account', 'licenseforge' ); ?></label></th>
						<td><label class="lf-toggle"><input type="checkbox" name="show_in_my_account" value="1" <?php checked( ! empty( $settings['show_in_my_account'] ) ); ?>><span class="lf-toggle-sl"></span></label></td>
					</tr>
					<tr>
						<th><label><?php esc_html_e( 'Show keys in WooCommerce order email', 'licenseforge' ); ?></label></th>
						<td><label class="lf-toggle"><input type="checkbox" name="show_in_order_email" value="1" <?php checked( ! empty( $settings['show_in_order_email'] ) ); ?>><span class="lf-toggle-sl"></span></label></td>
					</tr>
					<tr>
						<th><label><?php esc_html_e( 'Show keys on Thank-You page', 'licenseforge' ); ?></label></th>
						<td><label class="lf-toggle"><input type="checkbox" name="show_in_thankyou" value="1" <?php checked( ! empty( $settings['show_in_thankyou'] ) ); ?>><span class="lf-toggle-sl"></span></label></td>
					</tr>
				</table>
				<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save', 'licenseforge' ); ?>"></p>
			</form>
		</div></div>

	<?php endif; ?>
</div>
