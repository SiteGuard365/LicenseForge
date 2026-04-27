<?php
/**
 * View: WhatsApp Delivery (only loaded when module enabled).
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use LicenseForge\Helpers;

$wa = wp_parse_args( get_option( 'lf_whatsapp_template', [] ), [
	'provider'   => 'twilio',
	'api_key'    => '',
	'api_secret' => '',
	'sender'     => '',
	'auto_send'  => 1,
	'message'    => '',
] );

if ( ! empty( $_GET['lf_msg'] ) ) Helpers::admin_notice( sanitize_text_field( wp_unslash( $_GET['lf_msg'] ) ) ); // phpcs:ignore
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php esc_html_e( 'WhatsApp Delivery', 'licenseforge' ); ?></h1>
		<span class="lf-badge lf-badge-success"><?php esc_html_e( 'Module Active', 'licenseforge' ); ?></span>
	</div>

	<div class="lf-grid-2">
		<div class="lf-card">
			<div class="lf-card-header"><h2 class="lf-card-title"><?php esc_html_e( 'API Settings', 'licenseforge' ); ?></h2></div>
			<div class="lf-card-body">
				<form method="post">
					<?php wp_nonce_field( 'licenseforge_save_whatsapp' ); ?>
					<input type="hidden" name="lf_action" value="save_whatsapp">
					<table class="form-table">
						<tr><th><label><?php esc_html_e( 'Provider', 'licenseforge' ); ?></label></th>
							<td>
								<select name="provider">
									<option value="twilio" <?php selected( $wa['provider'], 'twilio' ); ?>>Twilio WhatsApp</option>
									<option value="meta"   <?php selected( $wa['provider'], 'meta' ); ?>>Meta WhatsApp Cloud API</option>
									<option value="custom" <?php selected( $wa['provider'], 'custom' ); ?>>Custom Webhook</option>
								</select>
							</td></tr>
						<tr><th><label><?php esc_html_e( 'API Key / SID', 'licenseforge' ); ?></label></th>
							<td><input type="text" name="api_key" value="<?php echo esc_attr( $wa['api_key'] ); ?>" class="regular-text"></td></tr>
						<tr><th><label><?php esc_html_e( 'API Secret / Token', 'licenseforge' ); ?></label></th>
							<td><input type="password" name="api_secret" value="<?php echo esc_attr( $wa['api_secret'] ); ?>" class="regular-text"></td></tr>
						<tr><th><label><?php esc_html_e( 'Sender Number', 'licenseforge' ); ?></label></th>
							<td><input type="tel" name="sender" value="<?php echo esc_attr( $wa['sender'] ); ?>" class="regular-text" placeholder="+91 98765 00000"></td></tr>
						<tr><th><label><?php esc_html_e( 'Auto-send on order complete', 'licenseforge' ); ?></label></th>
							<td><label class="lf-toggle"><input type="checkbox" name="auto_send" value="1" <?php checked( $wa['auto_send'], 1 ); ?>><span class="lf-toggle-sl"></span></label></td></tr>
						<tr><th><label><?php esc_html_e( 'Message Template', 'licenseforge' ); ?></label></th>
							<td><textarea name="message" rows="9" class="large-text"><?php echo esc_textarea( $wa['message'] ); ?></textarea></td></tr>
					</table>

					<div class="lf-card" style="background:#f0f6fc;border-color:#72aee6;margin-top:14px">
						<div class="lf-card-body">
							<strong><?php esc_html_e( 'Placeholders:', 'licenseforge' ); ?></strong><br>
							<code>{customer_name}</code> <code>{order_id}</code> <code>{license_key}</code> <code>{product_name}</code> <code>{expiry_date}</code> <code>{site_name}</code>
						</div>
					</div>

					<p style="margin-top:14px"><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'licenseforge' ); ?>"></p>
				</form>
			</div>
		</div>

		<div>
			<div class="lf-subtitle" style="margin-bottom:8px;color:#646970;font-weight:600"><?php esc_html_e( 'Message Preview', 'licenseforge' ); ?></div>
			<div class="lf-wa-bg">
				<div style="font-size:11px;color:#999;text-align:center;margin-bottom:9px">Today · 3:45 PM</div>
				<div class="lf-wa-bub">
					<div>🎉 Hello <strong>Rahul</strong>!</div>
					<div style="margin:5px 0">Your order <strong>#5091</strong> is ready.</div>
					<div class="lf-wa-key">XXXXX-YRTWQ-9A12K-P002A</div>
					<div style="font-size:13px">Product: Windows 11 Pro<br>Expires: Lifetime</div>
				</div>
			</div>

			<div class="lf-card" style="margin-top:14px">
				<div class="lf-card-header"><h2 class="lf-card-title"><?php esc_html_e( 'Setup Guide', 'licenseforge' ); ?></h2></div>
				<div class="lf-card-body">
					<ol style="padding-left:20px;line-height:1.8">
						<li><?php esc_html_e( 'Choose a provider above (Twilio recommended).', 'licenseforge' ); ?></li>
						<li><?php esc_html_e( 'Paste your API credentials.', 'licenseforge' ); ?></li>
						<li><?php esc_html_e( 'Enable your WhatsApp Business sender number.', 'licenseforge' ); ?></li>
						<li><?php esc_html_e( 'Save — auto delivery will trigger on order complete.', 'licenseforge' ); ?></li>
					</ol>
				</div>
			</div>
		</div>
	</div>
</div>
