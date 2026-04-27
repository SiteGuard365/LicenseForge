<?php
/**
 * View: Email Templates.
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use LicenseForge\Helpers;

$tpl = wp_parse_args( get_option( 'lf_email_template', [] ), [
	'subject' => '🔑 Your License Key — Order #{order_id}',
	'header'  => 'Your License Key is Ready!',
	'body'    => '',
	'footer'  => '',
	'accent'  => '#2271b1',
] );

if ( ! empty( $_GET['lf_msg'] ) ) Helpers::admin_notice( sanitize_text_field( wp_unslash( $_GET['lf_msg'] ) ) ); // phpcs:ignore
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php esc_html_e( 'Email Templates', 'licenseforge' ); ?></h1>
		<form method="post" style="margin:0">
			<?php wp_nonce_field( 'licenseforge_send_test_email' ); ?>
			<input type="hidden" name="lf_action" value="send_test_email">
			<button class="button"><?php esc_html_e( 'Send Test Email', 'licenseforge' ); ?></button>
		</form>
	</div>

	<div class="lf-grid-2">
		<div class="lf-card">
			<div class="lf-card-header"><h2 class="lf-card-title"><?php esc_html_e( 'License Delivery Email', 'licenseforge' ); ?></h2></div>
			<div class="lf-card-body">
				<form method="post">
					<?php wp_nonce_field( 'licenseforge_save_email_template' ); ?>
					<input type="hidden" name="lf_action" value="save_email_template">
					<table class="form-table">
						<tr><th><label><?php esc_html_e( 'Subject', 'licenseforge' ); ?></label></th>
							<td><input type="text" name="subject" value="<?php echo esc_attr( $tpl['subject'] ); ?>" class="large-text"></td></tr>
						<tr><th><label><?php esc_html_e( 'Header Title', 'licenseforge' ); ?></label></th>
							<td><input type="text" name="header" value="<?php echo esc_attr( $tpl['header'] ); ?>" class="large-text"></td></tr>
						<tr><th><label><?php esc_html_e( 'Body Message', 'licenseforge' ); ?></label></th>
							<td><textarea name="body" rows="5" class="large-text"><?php echo esc_textarea( $tpl['body'] ); ?></textarea></td></tr>
						<tr><th><label><?php esc_html_e( 'Footer Text', 'licenseforge' ); ?></label></th>
							<td><input type="text" name="footer" value="<?php echo esc_attr( $tpl['footer'] ); ?>" class="large-text"></td></tr>
						<tr><th><label><?php esc_html_e( 'Accent Color', 'licenseforge' ); ?></label></th>
							<td><input type="color" name="accent" value="<?php echo esc_attr( $tpl['accent'] ); ?>"></td></tr>
					</table>

					<div class="lf-card" style="background:#f0f6fc;border-color:#72aee6;margin-top:14px">
						<div class="lf-card-body">
							<strong><?php esc_html_e( 'Available placeholders:', 'licenseforge' ); ?></strong><br>
							<code>{customer_name}</code> <code>{order_id}</code> <code>{order_total}</code> <code>{site_name}</code>
						</div>
					</div>

					<p style="margin-top:14px"><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Template', 'licenseforge' ); ?>"></p>
				</form>
			</div>
		</div>

		<div>
			<div class="lf-subtitle" style="margin-bottom:8px;color:#646970;font-weight:600"><?php esc_html_e( 'Live Preview', 'licenseforge' ); ?></div>
			<div class="lf-ep">
				<div class="lf-ep-hdr" style="background:<?php echo esc_attr( $tpl['accent'] ); ?>"><h2><?php echo esc_html( $tpl['header'] ); ?></h2></div>
				<div class="lf-ep-body">
					<p>Hi <strong>Customer</strong>,</p>
					<p><?php echo esc_html( $tpl['body'] ); ?></p>
					<div class="lf-ep-key" style="border-color:<?php echo esc_attr( $tpl['accent'] ); ?>">
						<div style="font-size:11px;color:#50575e;margin-bottom:4px">LICENSE KEY</div>
						<div style="font-family:monospace;font-size:15px;font-weight:700;color:<?php echo esc_attr( $tpl['accent'] ); ?>">XXXXX-YRTWQ-9A12K-P002A</div>
					</div>
					<p style="font-size:12px;color:#50575e"><?php echo esc_html( $tpl['footer'] ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
