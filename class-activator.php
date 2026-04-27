<?php
/**
 * Activation handler.
 *
 * @package LicenseForge
 */

namespace LicenseForge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Activator {

	/**
	 * Run on activation.
	 */
	public static function activate() {

		// Make sure the database class is loaded (autoloader may not yet be active).
		require_once LICENSEFORGE_PATH . 'includes/class-database.php';

		Database::install();

		// Default options.
		$defaults = [
			'lf_version'           => LICENSEFORGE_VERSION,
			'lf_db_version'        => LICENSEFORGE_DB_VER,
			'lf_modules_enabled'   => self::default_modules(),
			'lf_settings'          => self::default_settings(),
			'lf_email_template'    => self::default_email_template(),
			'lf_whatsapp_template' => self::default_whatsapp_template(),
		];

		foreach ( $defaults as $k => $v ) {
			if ( get_option( $k ) === false ) {
				add_option( $k, $v );
			}
		}

		// Schedule cleanup.
		if ( ! wp_next_scheduled( 'licenseforge_daily_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'licenseforge_daily_cleanup' );
		}

		// Flush rewrite (My Account endpoint).
		flush_rewrite_rules();
	}

	/**
	 * Run on deactivation.
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'licenseforge_daily_cleanup' );
		flush_rewrite_rules();
	}

	/**
	 * Default enabled modules.
	 *
	 * Heavy / optional modules are OFF by default to keep the site light.
	 */
	public static function default_modules() {
		return [
			'whatsapp'      => 0, // off by default
			'support_inbox' => 0, // off by default
			'analytics'     => 1, // light, on by default
			'subscriptions' => 0, // coming soon
			'reports'       => 0, // coming soon
			'expiry_alerts' => 0, // coming soon
		];
	}

	public static function default_settings() {
		return [
			'auto_delivery'        => 1,
			'show_in_my_account'   => 1,
			'show_in_order_email'  => 1,
			'show_in_thankyou'     => 1,
			'low_stock_threshold'  => 10,
			'admin_email'          => get_option( 'admin_email' ),
			'currency'             => get_option( 'woocommerce_currency', 'INR' ),
		];
	}

	public static function default_email_template() {
		return [
			'subject' => __( '🔑 Your License Key — Order #{order_id}', 'licenseforge' ),
			'header'  => __( 'Your License Key is Ready!', 'licenseforge' ),
			'body'    => __( "Hi {customer_name},\n\nThank you for your purchase! Your license key is below. Keep it safe — you'll need it to activate your software.", 'licenseforge' ),
			'footer'  => __( 'Need help? Reply to this email.', 'licenseforge' ),
			'accent'  => '#2271b1',
		];
	}

	public static function default_whatsapp_template() {
		return [
			'provider'    => 'twilio',
			'api_key'     => '',
			'api_secret'  => '',
			'sender'      => '',
			'auto_send'   => 1,
			'message'     => __( "🎉 Hello {customer_name}!\n\nYour order *#{order_id}* from {site_name} is ready.\n\n🔑 License Key:\n{license_key}\n\nProduct: {product_name}\nExpires: {expiry_date}\n\nNeed help? Reply to this message.", 'licenseforge' ),
		];
	}
}
