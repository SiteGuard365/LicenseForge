<?php
/**
 * Helper utilities.
 *
 * @package LicenseForge
 */

namespace LicenseForge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpers {

	/**
	 * Get a setting value.
	 */
	public static function setting( $key, $default = '' ) {
		$opts = get_option( 'lf_settings', [] );
		return isset( $opts[ $key ] ) ? $opts[ $key ] : $default;
	}

	/**
	 * Update a setting value.
	 */
	public static function update_setting( $key, $value ) {
		$opts = get_option( 'lf_settings', [] );
		$opts[ $key ] = $value;
		update_option( 'lf_settings', $opts );
	}

	/**
	 * Get whether a module is enabled.
	 */
	public static function is_module_active( $slug ) {
		$enabled = get_option( 'lf_modules_enabled', [] );
		return ! empty( $enabled[ $slug ] );
	}

	/**
	 * Render an admin view with vars in scope.
	 */
	public static function view( $name, $vars = [] ) {
		$file = LICENSEFORGE_PATH . 'admin/views/' . sanitize_file_name( $name ) . '.php';
		if ( ! file_exists( $file ) ) {
			echo '<div class="lf-coming-soon"><h2>' . esc_html__( 'View not found', 'licenseforge' ) . '</h2><p>' . esc_html( $name ) . '</p></div>';
			return;
		}
		extract( $vars, EXTR_SKIP ); // phpcs:ignore
		include $file;
	}

	/**
	 * Replace placeholders {key} in a string.
	 */
	public static function replace_placeholders( $template, $data ) {
		foreach ( $data as $k => $v ) {
			$template = str_replace( '{' . $k . '}', (string) $v, $template );
		}
		return $template;
	}

	/**
	 * Mask a license key for display: ABCD-EFGH-IJKL → ABCD-****-IJKL
	 */
	public static function mask_key( $key ) {
		$parts = preg_split( '/[\-\.]/', $key );
		if ( count( $parts ) < 3 ) {
			return substr( $key, 0, 4 ) . str_repeat( '*', max( 0, strlen( $key ) - 8 ) ) . substr( $key, -4 );
		}
		$last = count( $parts ) - 1;
		for ( $i = 1; $i < $last; $i++ ) {
			$parts[ $i ] = str_repeat( '*', strlen( $parts[ $i ] ) );
		}
		return implode( '-', $parts );
	}

	/**
	 * Render a status pill (badge).
	 */
	public static function badge( $text, $type = 'gray' ) {
		$map = [
			'available' => 'gray',
			'active'    => 'success',
			'sold'      => 'success',
			'pending'   => 'warning',
			'expired'   => 'error',
			'revoked'   => 'error',
		];
		$class = isset( $map[ $type ] ) ? $map[ $type ] : $type;
		return '<span class="lf-badge lf-badge-' . esc_attr( $class ) . '">' . esc_html( ucfirst( $text ) ) . '</span>';
	}

	/**
	 * Currency-format a number using WooCommerce if available.
	 */
	public static function format_price( $amount ) {
		if ( function_exists( 'wc_price' ) ) {
			return wc_price( $amount );
		}
		return number_format( (float) $amount, 2 );
	}

	/**
	 * Get the WC My Account license URL.
	 */
	public static function my_account_url() {
		if ( function_exists( 'wc_get_account_endpoint_url' ) ) {
			return wc_get_account_endpoint_url( 'lf-licenses' );
		}
		return home_url( '/my-account/lf-licenses/' );
	}

	/**
	 * Wrap admin notice.
	 */
	public static function admin_notice( $message, $type = 'success' ) {
		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $type ),
			wp_kses_post( $message )
		);
	}
}
