<?php
/**
 * Email delivery for license keys.
 *
 * @package LicenseForge
 */

namespace LicenseForge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mailer {

	/**
	 * Send the license email after a successful order.
	 *
	 * @param \WC_Order $order
	 * @param array     $delivered Array of [ product, rows ].
	 */
	public static function send_keys_email( $order, $delivered ) {

		$tpl     = get_option( 'lf_email_template', [] );
		$subject = isset( $tpl['subject'] ) ? $tpl['subject'] : '🔑 Your License Key — Order #{order_id}';
		$header  = isset( $tpl['header'] )  ? $tpl['header']  : 'Your License Key is Ready!';
		$body    = isset( $tpl['body'] )    ? $tpl['body']    : '';
		$footer  = isset( $tpl['footer'] )  ? $tpl['footer']  : '';
		$accent  = isset( $tpl['accent'] )  ? $tpl['accent']  : '#2271b1';

		$placeholders = [
			'customer_name' => $order->get_billing_first_name(),
			'order_id'      => $order->get_id(),
			'order_total'   => strip_tags( wc_price( $order->get_total() ) ),
			'site_name'     => get_bloginfo( 'name' ),
		];

		$subject = Helpers::replace_placeholders( $subject, $placeholders );
		$header  = Helpers::replace_placeholders( $header,  $placeholders );
		$body_t  = Helpers::replace_placeholders( $body,    $placeholders );

		// Build keys block.
		$keys_html = '';
		foreach ( $delivered as $d ) {
			$keys_html .= '<div style="margin:10px 0;padding:14px;border:1.5px dashed ' . esc_attr( $accent ) . ';background:#f0f6fc;border-radius:4px">';
			$keys_html .= '<div style="font-size:11px;text-transform:uppercase;color:#50575e;margin-bottom:6px">' . esc_html( $d['product']->get_name() ) . '</div>';
			foreach ( $d['rows'] as $row ) {
				$keys_html .= '<div style="font-family:monospace;font-size:15px;font-weight:700;color:' . esc_attr( $accent ) . '">' . esc_html( $row->license_key ) . '</div>';
			}
			$keys_html .= '</div>';
		}

		$html  = '<!DOCTYPE html><html><body style="margin:0;font-family:Helvetica,Arial,sans-serif;background:#f4f5f7;padding:24px">';
		$html .= '<div style="max-width:560px;margin:0 auto;background:#fff;border-radius:6px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.08)">';
		$html .= '<div style="background:' . esc_attr( $accent ) . ';color:#fff;padding:22px 26px"><h1 style="margin:0;font-size:18px">' . esc_html( $header ) . '</h1></div>';
		$html .= '<div style="padding:24px 26px;color:#1d2327;font-size:14px;line-height:1.6">';
		$html .= '<div>' . wpautop( esc_html( $body_t ) ) . '</div>';
		$html .= $keys_html;
		$html .= '<p style="font-size:12px;color:#50575e">' . esc_html( $footer ) . '</p>';
		$html .= '<p style="margin-top:18px"><a href="' . esc_url( Helpers::my_account_url() ) . '" style="color:' . esc_attr( $accent ) . '">' . esc_html__( 'View in My Account', 'licenseforge' ) . ' →</a></p>';
		$html .= '</div></div></body></html>';

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		$sent = wp_mail( $order->get_billing_email(), $subject, $html, $headers );

		// Log delivery.
		global $wpdb;
		foreach ( $delivered as $d ) {
			foreach ( $d['rows'] as $row ) {
				$wpdb->insert( Database::table( 'delivery_log' ), [
					'license_id' => (int) $row->id,
					'order_id'   => (int) $order->get_id(),
					'channel'    => 'email',
					'status'     => $sent ? 'sent' : 'failed',
					'recipient'  => $order->get_billing_email(),
					'created_at' => current_time( 'mysql' ),
				] );
			}
		}

		return $sent;
	}

	/**
	 * Send a test email to admin.
	 */
	public static function send_test( $to ) {
		$tpl  = get_option( 'lf_email_template', [] );
		$body = '<p>This is a test email from LicenseForge.</p><p><strong>License Key:</strong> <code>TEST-XXXX-YYYY-ZZZZ</code></p>';
		return wp_mail( $to, ( $tpl['subject'] ?? 'LicenseForge Test' ) . ' (TEST)', $body, [ 'Content-Type: text/html; charset=UTF-8' ] );
	}
}
