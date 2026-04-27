<?php
/**
 * WooCommerce integration.
 *
 * Hooks license delivery into the order lifecycle, exposes product
 * settings and Thank-You page output. Designed to short-circuit early
 * if WooCommerce is not active so site stays light.
 *
 * @package LicenseForge
 */

namespace LicenseForge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_Integration {

	public function __construct() {

		// Bail if WooCommerce is not active — keeps the site untouched.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Order completion → deliver license keys.
		add_action( 'woocommerce_order_status_completed', [ $this, 'deliver_keys_for_order' ], 20, 1 );

		// Refund / cancel → mark keys as revoked.
		add_action( 'woocommerce_order_status_refunded',  [ $this, 'revoke_keys_for_order' ], 20, 1 );
		add_action( 'woocommerce_order_status_cancelled', [ $this, 'revoke_keys_for_order' ], 20, 1 );

		// Show keys on Thank-You page.
		add_action( 'woocommerce_thankyou', [ $this, 'render_keys_thankyou' ], 20, 1 );

		// Show keys in customer email.
		add_action( 'woocommerce_email_after_order_table', [ $this, 'render_keys_in_email' ], 20, 4 );

		// Product data tab.
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'product_data_tab' ] );
		add_action( 'woocommerce_product_data_panels', [ $this, 'product_data_panel' ] );
		add_action( 'woocommerce_admin_process_product_object', [ $this, 'save_product_data' ] );
	}

	/**
	 * Deliver license keys when an order is completed.
	 */
	public function deliver_keys_for_order( $order_id ) {

		if ( ! Helpers::setting( 'auto_delivery', 1 ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		// Idempotency: skip if already delivered for this order.
		if ( $order->get_meta( '_lf_delivered' ) ) {
			return;
		}

		$delivered = [];

		foreach ( $order->get_items() as $item ) {

			$product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
			$product    = wc_get_product( $product_id );
			if ( ! $product ) {
				continue;
			}

			// Is this product flagged to deliver license keys?
			if ( 'yes' !== $product->get_meta( '_lf_sell_keys' ) ) {
				continue;
			}

			$keys_per_unit = max( 1, (int) $product->get_meta( '_lf_keys_per_unit' ) );
			$qty           = (int) $item->get_quantity() * $keys_per_unit;

			$rows = Licenses::reserve_for_product( $product_id, $qty );
			if ( count( $rows ) < $qty ) {
				// Out of stock — log and skip.
				$order->add_order_note(
					sprintf(
						/* translators: 1: product, 2: requested, 3: available */
						__( 'LicenseForge: not enough keys in stock for %1$s (requested %2$d, available %3$d).', 'licenseforge' ),
						$product->get_name(),
						$qty,
						count( $rows )
					)
				);
				continue;
			}

			$ids = wp_list_pluck( $rows, 'id' );
			Licenses::mark_sold( $ids, $order_id, $order->get_user_id() );
			Licenses::clear_stock_cache( $product_id );

			$delivered[] = [
				'product' => $product,
				'rows'    => $rows,
			];
		}

		if ( empty( $delivered ) ) {
			return;
		}

		// Hand off to delivery channels.
		do_action( 'licenseforge_deliver', $order, $delivered );

		// Default email delivery (always on).
		require_once LICENSEFORGE_PATH . 'includes/class-mailer.php';
		Mailer::send_keys_email( $order, $delivered );

		$order->update_meta_data( '_lf_delivered', current_time( 'mysql' ) );
		$order->add_order_note( __( 'LicenseForge: license keys delivered.', 'licenseforge' ) );
		$order->save();
	}

	/**
	 * Revoke (mark as revoked) license keys when an order is refunded / cancelled.
	 */
	public function revoke_keys_for_order( $order_id ) {
		global $wpdb;
		$wpdb->update(
			Database::table( 'licenses' ),
			[ 'status' => 'revoked', 'updated_at' => current_time( 'mysql' ) ],
			[ 'order_id' => (int) $order_id, 'status' => 'sold' ]
		);
	}

	/**
	 * Render keys on the Thank-You page.
	 */
	public function render_keys_thankyou( $order_id ) {
		if ( ! Helpers::setting( 'show_in_thankyou', 1 ) ) {
			return;
		}
		$keys = $this->get_keys_for_order( $order_id );
		if ( empty( $keys ) ) {
			return;
		}
		Helpers::view( 'frontend-keys', [
			'keys'  => $keys,
			'title' => __( '🔑 Your License Keys', 'licenseforge' ),
		] );
	}

	/**
	 * Render keys inside the WooCommerce order email.
	 */
	public function render_keys_in_email( $order, $sent_to_admin, $plain_text, $email ) {
		if ( ! Helpers::setting( 'show_in_order_email', 1 ) ) {
			return;
		}
		$keys = $this->get_keys_for_order( $order->get_id() );
		if ( empty( $keys ) ) {
			return;
		}
		if ( $plain_text ) {
			echo "\n\n" . __( 'Your License Keys:', 'licenseforge' ) . "\n";
			foreach ( $keys as $row ) {
				echo $row->license_key . "\n";
			}
			return;
		}
		echo '<h2>' . esc_html__( 'Your License Keys', 'licenseforge' ) . '</h2><ul>';
		foreach ( $keys as $row ) {
			echo '<li><code>' . esc_html( $row->license_key ) . '</code></li>';
		}
		echo '</ul>';
	}

	/**
	 * Get all keys for an order.
	 */
	public function get_keys_for_order( $order_id ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . Database::table( 'licenses' ) . ' WHERE order_id = %d ORDER BY id ASC',
				(int) $order_id
			)
		);
	}

	/**
	 * Add LicenseForge tab to product data.
	 */
	public function product_data_tab( $tabs ) {
		$tabs['licenseforge'] = [
			'label'    => __( 'LicenseForge', 'licenseforge' ),
			'target'   => 'lf_product_data',
			'class'    => [],
			'priority' => 65,
		];
		return $tabs;
	}

	/**
	 * Render product data panel.
	 */
	public function product_data_panel() {
		global $post;
		Helpers::view( 'product-panel', [ 'post_id' => $post->ID ] );
	}

	/**
	 * Save product fields.
	 */
	public function save_product_data( $product ) {
		$sell = isset( $_POST['_lf_sell_keys'] ) ? 'yes' : 'no'; // phpcs:ignore
		$product->update_meta_data( '_lf_sell_keys', $sell );

		if ( isset( $_POST['_lf_keys_per_unit'] ) ) { // phpcs:ignore
			$product->update_meta_data( '_lf_keys_per_unit', max( 1, intval( $_POST['_lf_keys_per_unit'] ) ) ); // phpcs:ignore
		}

		if ( isset( $_POST['_lf_max_activations'] ) ) { // phpcs:ignore
			$product->update_meta_data( '_lf_max_activations', max( 0, intval( $_POST['_lf_max_activations'] ) ) ); // phpcs:ignore
		}

		if ( isset( $_POST['_lf_validity_days'] ) ) { // phpcs:ignore
			$product->update_meta_data( '_lf_validity_days', max( 0, intval( $_POST['_lf_validity_days'] ) ) ); // phpcs:ignore
		}
	}
}
