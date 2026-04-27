<?php
/**
 * Centralised admin form handler.
 *
 * @package LicenseForge
 */

namespace LicenseForge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Form_Handler {

	/**
	 * Dispatch a POST action.
	 */
	public static function dispatch( $action ) {

		check_admin_referer( 'licenseforge_' . $action );

		switch ( $action ) {

			case 'add_key':
				self::add_key();
				break;

			case 'import_paste':
				self::import_paste();
				break;

			case 'import_csv':
				self::import_csv();
				break;

			case 'delete_keys':
				self::delete_keys();
				break;

			case 'save_settings':
				self::save_settings();
				break;

			case 'save_modules':
				self::save_modules();
				break;

			case 'save_email_template':
				self::save_email_template();
				break;

			case 'save_whatsapp':
				self::save_whatsapp();
				break;

			case 'send_test_email':
				Mailer::send_test( get_option( 'admin_email' ) );
				self::redirect_back( __( 'Test email sent.', 'licenseforge' ) );
				break;

			case 'cleanup':
				self::cleanup();
				break;
		}
	}

	private static function redirect_back( $msg = '', $type = 'success' ) {
		$url = wp_get_referer() ?: admin_url( 'admin.php?page=licenseforge' );
		$url = add_query_arg( [
			'lf_msg'  => rawurlencode( $msg ),
			'lf_type' => $type,
		], $url );
		wp_safe_redirect( $url );
		exit;
	}

	private static function add_key() {
		$key  = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';
		$prod = isset( $_POST['product_id'] )  ? intval( $_POST['product_id'] ) : 0;
		$st   = isset( $_POST['status'] )      ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'available';
		$exp  = isset( $_POST['expires_at'] ) && $_POST['expires_at'] ? sanitize_text_field( wp_unslash( $_POST['expires_at'] ) ) : null;
		$max  = isset( $_POST['max_activations'] ) ? max( 1, intval( $_POST['max_activations'] ) ) : 1;

		if ( empty( $key ) ) {
			self::redirect_back( __( 'License key is required.', 'licenseforge' ), 'error' );
		}

		$id = Licenses::create( [
			'license_key'     => $key,
			'product_id'      => $prod,
			'status'          => $st,
			'expires_at'      => $exp,
			'max_activations' => $max,
		] );

		if ( $id ) {
			Licenses::clear_stock_cache( $prod );
			self::redirect_back( __( 'License added.', 'licenseforge' ) );
		}
		self::redirect_back( __( 'Could not add license — duplicate key?', 'licenseforge' ), 'error' );
	}

	private static function import_paste() {
		$prod = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$blob = isset( $_POST['keys_blob'] ) ? sanitize_textarea_field( wp_unslash( $_POST['keys_blob'] ) ) : '';
		if ( empty( $blob ) ) {
			self::redirect_back( __( 'Paste at least one key.', 'licenseforge' ), 'error' );
		}
		$lines = preg_split( "/[\r\n]+/", $blob );
		$count = Licenses::bulk_insert( $lines, $prod );
		Licenses::clear_stock_cache( $prod );
		self::redirect_back( sprintf( /* translators: %d count */ __( '%d keys imported.', 'licenseforge' ), $count ) );
	}

	private static function import_csv() {
		$prod = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		if ( empty( $_FILES['csv']['tmp_name'] ) ) { // phpcs:ignore
			self::redirect_back( __( 'No file uploaded.', 'licenseforge' ), 'error' );
		}
		$fp = fopen( $_FILES['csv']['tmp_name'], 'r' ); // phpcs:ignore
		if ( ! $fp ) {
			self::redirect_back( __( 'Cannot read file.', 'licenseforge' ), 'error' );
		}
		$header = fgetcsv( $fp );
		$idx    = array_search( 'license_key', array_map( 'trim', (array) $header ), true );
		if ( false === $idx ) {
			$idx = 0;
		}
		$count = 0;
		while ( ( $row = fgetcsv( $fp ) ) !== false ) {
			$key = isset( $row[ $idx ] ) ? trim( (string) $row[ $idx ] ) : '';
			if ( '' === $key ) {
				continue;
			}
			if ( Licenses::create( [ 'license_key' => $key, 'product_id' => $prod ] ) ) {
				$count++;
			}
		}
		fclose( $fp );
		Licenses::clear_stock_cache( $prod );
		self::redirect_back( sprintf( __( '%d keys imported from CSV.', 'licenseforge' ), $count ) );
	}

	private static function delete_keys() {
		$ids = isset( $_POST['ids'] ) ? array_map( 'intval', (array) $_POST['ids'] ) : [];
		foreach ( $ids as $id ) {
			Licenses::delete( $id );
		}
		self::redirect_back( sprintf( __( '%d keys deleted.', 'licenseforge' ), count( $ids ) ) );
	}

	private static function save_settings() {
		$settings = (array) get_option( 'lf_settings', [] );

		$bools = [ 'auto_delivery', 'show_in_my_account', 'show_in_order_email', 'show_in_thankyou' ];
		foreach ( $bools as $b ) {
			$settings[ $b ] = ! empty( $_POST[ $b ] ) ? 1 : 0; // phpcs:ignore
		}

		if ( isset( $_POST['low_stock_threshold'] ) ) { // phpcs:ignore
			$settings['low_stock_threshold'] = max( 0, intval( $_POST['low_stock_threshold'] ) ); // phpcs:ignore
		}
		if ( isset( $_POST['admin_email'] ) ) { // phpcs:ignore
			$settings['admin_email'] = sanitize_email( wp_unslash( $_POST['admin_email'] ) ); // phpcs:ignore
		}

		update_option( 'lf_settings', $settings );
		self::redirect_back( __( 'Settings saved.', 'licenseforge' ) );
	}

	private static function save_modules() {
		$registry = ( Plugin::instance()->modules )->all();
		$enabled  = [];
		foreach ( $registry as $slug => $def ) {
			if ( $def['status'] !== 'stable' ) {
				$enabled[ $slug ] = 0;
				continue;
			}
			$enabled[ $slug ] = ! empty( $_POST['modules'][ $slug ] ) ? 1 : 0; // phpcs:ignore
		}
		update_option( 'lf_modules_enabled', $enabled );
		self::redirect_back( __( 'Modules updated. Refresh menu to see changes.', 'licenseforge' ) );
	}

	private static function save_email_template() {
		$tpl = [
			'subject' => isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '',
			'header'  => isset( $_POST['header'] )  ? sanitize_text_field( wp_unslash( $_POST['header'] ) )  : '',
			'body'    => isset( $_POST['body'] )    ? sanitize_textarea_field( wp_unslash( $_POST['body'] ) ) : '',
			'footer'  => isset( $_POST['footer'] )  ? sanitize_text_field( wp_unslash( $_POST['footer'] ) )  : '',
			'accent'  => isset( $_POST['accent'] )  ? sanitize_hex_color( wp_unslash( $_POST['accent'] ) )  : '#2271b1',
		];
		update_option( 'lf_email_template', $tpl );
		self::redirect_back( __( 'Email template saved.', 'licenseforge' ) );
	}

	private static function save_whatsapp() {
		$cur = (array) get_option( 'lf_whatsapp_template', [] );
		$cur['provider']   = isset( $_POST['provider'] ) ? sanitize_key( wp_unslash( $_POST['provider'] ) ) : 'twilio';
		$cur['api_key']    = isset( $_POST['api_key'] )    ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) )    : '';
		$cur['api_secret'] = isset( $_POST['api_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['api_secret'] ) ) : '';
		$cur['sender']     = isset( $_POST['sender'] )     ? sanitize_text_field( wp_unslash( $_POST['sender'] ) )     : '';
		$cur['auto_send']  = ! empty( $_POST['auto_send'] ) ? 1 : 0;
		$cur['message']    = isset( $_POST['message'] )    ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

		update_option( 'lf_whatsapp_template', $cur );
		self::redirect_back( __( 'WhatsApp settings saved.', 'licenseforge' ) );
	}

	private static function cleanup() {
		global $wpdb;
		$days  = 90;
		$ts    = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );
		$rows  = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Database::table( 'delivery_log' ) . ' WHERE created_at < %s', $ts ) );
		self::redirect_back( sprintf( __( 'Cleanup done — %d log rows removed.', 'licenseforge' ), (int) $rows ) );
	}
}
