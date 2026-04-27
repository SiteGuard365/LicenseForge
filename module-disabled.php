<?php
/**
 * View: Module Disabled prompt.
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$mod    = isset( $module ) ? $module : '';
$labels = [
	'whatsapp'      => [ '📱', __( 'WhatsApp Delivery', 'licenseforge' ),  __( 'Send license keys via WhatsApp on order completion.', 'licenseforge' ) ],
	'support_inbox' => [ '💬', __( 'Support Inbox', 'licenseforge' ),       __( 'Capture customer replies (email + WhatsApp) into one admin inbox.', 'licenseforge' ) ],
	'analytics'     => [ '📊', __( 'Analytics', 'licenseforge' ),           __( 'Lightweight in-admin analytics — keys sold, deliveries, channel split.', 'licenseforge' ) ],
];
$info = $labels[ $mod ] ?? [ '⚙', __( 'Module', 'licenseforge' ), '' ];
$url  = admin_url( 'admin.php?page=licenseforge&page_view=settings&tab=modules' );
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php echo esc_html( $info[1] ); ?></h1>
	</div>

	<div class="lf-coming-soon">
		<div style="font-size:48px;margin-bottom:8px"><?php echo esc_html( $info[0] ); ?></div>
		<h2><?php
			/* translators: %s: module name */
			printf( esc_html__( '%s is currently disabled', 'licenseforge' ), esc_html( $info[1] ) );
		?></h2>
		<p><?php echo esc_html( $info[2] ); ?></p>
		<p><?php esc_html_e( 'Enable this module to load it. Disabled modules don\'t consume any PHP, JS or CSS — your site stays light.', 'licenseforge' ); ?></p>
		<p style="margin-top:18px">
			<a href="<?php echo esc_url( $url ); ?>" class="button button-primary button-hero"><?php esc_html_e( 'Enable in Settings → Modules', 'licenseforge' ); ?></a>
		</p>
	</div>
</div>
