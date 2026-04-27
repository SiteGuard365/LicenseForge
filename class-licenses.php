<?php
/**
 * License repository — CRUD operations on the licenses table.
 *
 * @package LicenseForge
 */

namespace LicenseForge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Licenses {

	/**
	 * Insert a license key.
	 *
	 * @param array $args
	 * @return int|false ID of the inserted row.
	 */
	public static function create( $args ) {
		global $wpdb;
		$table = Database::table( 'licenses' );

		$defaults = [
			'license_key'     => '',
			'product_id'      => 0,
			'variation_id'    => 0,
			'order_id'        => 0,
			'user_id'         => 0,
			'status'          => 'available',
			'max_activations' => 1,
			'activations'     => 0,
			'valid_for_days'  => null,
			'expires_at'      => null,
			'notes'           => '',
		];
		$data = wp_parse_args( $args, $defaults );

		if ( empty( $data['license_key'] ) ) {
			return false;
		}

		$ok = $wpdb->insert(
			$table,
			[
				'license_key'     => $data['license_key'],
				'product_id'      => (int) $data['product_id'],
				'variation_id'    => (int) $data['variation_id'],
				'order_id'        => (int) $data['order_id'],
				'user_id'         => (int) $data['user_id'],
				'status'          => $data['status'],
				'max_activations' => (int) $data['max_activations'],
				'activations'     => (int) $data['activations'],
				'valid_for_days'  => $data['valid_for_days'],
				'expires_at'      => $data['expires_at'],
				'notes'           => $data['notes'],
				'created_at'      => current_time( 'mysql' ),
				'updated_at'      => current_time( 'mysql' ),
			]
		);

		return $ok ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Bulk insert keys.
	 *
	 * @param array  $keys     List of key strings.
	 * @param int    $product  WooCommerce product id.
	 * @param array  $extra    Other column overrides.
	 * @return int Number of inserted rows.
	 */
	public static function bulk_insert( $keys, $product, $extra = [] ) {
		$count = 0;
		foreach ( $keys as $k ) {
			$k = trim( $k );
			if ( '' === $k ) {
				continue;
			}
			$id = self::create( array_merge(
				[ 'license_key' => $k, 'product_id' => $product ],
				$extra
			) );
			if ( $id ) {
				$count++;
			}
		}
		return $count;
	}

	/**
	 * Update a license row.
	 */
	public static function update( $id, $data ) {
		global $wpdb;
		$data['updated_at'] = current_time( 'mysql' );
		return $wpdb->update( Database::table( 'licenses' ), $data, [ 'id' => (int) $id ] );
	}

	/**
	 * Delete by ID.
	 */
	public static function delete( $id ) {
		global $wpdb;
		return $wpdb->delete( Database::table( 'licenses' ), [ 'id' => (int) $id ] );
	}

	/**
	 * Find by ID.
	 */
	public static function get( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Database::table( 'licenses' ) . ' WHERE id = %d', (int) $id ) );
	}

	/**
	 * Find by key string.
	 */
	public static function find_by_key( $key ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Database::table( 'licenses' ) . ' WHERE license_key = %s', $key ) );
	}

	/**
	 * Reserve N available keys for a product. Returns rows.
	 *
	 * Uses a SELECT ... FOR UPDATE inside a transaction for safety.
	 *
	 * @param int $product_id
	 * @param int $qty
	 * @return array list of license rows
	 */
	public static function reserve_for_product( $product_id, $qty = 1 ) {
		global $wpdb;
		$table = Database::table( 'licenses' );
		$rows  = [];

		$wpdb->query( 'START TRANSACTION' );

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT id FROM $table WHERE product_id = %d AND status = 'available' ORDER BY id ASC LIMIT %d FOR UPDATE",
				(int) $product_id,
				(int) $qty
			)
		);

		if ( count( $ids ) < (int) $qty ) {
			$wpdb->query( 'ROLLBACK' );
			return [];
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $table SET status = 'reserved', updated_at = %s WHERE id IN ($placeholders)",
				array_merge( [ current_time( 'mysql' ) ], $ids )
			)
		);

		$rows = $wpdb->get_results(
			"SELECT * FROM $table WHERE id IN ($placeholders) ORDER BY id ASC", // phpcs:ignore
			OBJECT
		);

		$wpdb->query( 'COMMIT' );
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE id IN ($placeholders) ORDER BY id ASC",
				$ids
			)
		);
	}

	/**
	 * Mark licenses as sold and assign to order.
	 */
	public static function mark_sold( $license_ids, $order_id, $user_id = 0 ) {
		global $wpdb;
		if ( empty( $license_ids ) ) {
			return 0;
		}
		$table        = Database::table( 'licenses' );
		$placeholders = implode( ',', array_fill( 0, count( $license_ids ), '%d' ) );
		$now          = current_time( 'mysql' );

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $table SET status = 'sold', order_id = %d, user_id = %d, delivered_at = %s, updated_at = %s WHERE id IN ($placeholders)",
				array_merge( [ (int) $order_id, (int) $user_id, $now, $now ], $license_ids )
			)
		);

		return count( $license_ids );
	}

	/**
	 * Count by product+status (for stock display).
	 */
	public static function count( $product_id = null, $status = null ) {
		global $wpdb;
		$table = Database::table( 'licenses' );
		$sql   = "SELECT COUNT(*) FROM $table WHERE 1=1";
		$args  = [];
		if ( null !== $product_id ) {
			$sql   .= ' AND product_id = %d';
			$args[] = (int) $product_id;
		}
		if ( null !== $status ) {
			$sql   .= ' AND status = %s';
			$args[] = $status;
		}
		return (int) ( $args ? $wpdb->get_var( $wpdb->prepare( $sql, $args ) ) : $wpdb->get_var( $sql ) );
	}

	/**
	 * Available stock count for a product (cached briefly).
	 */
	public static function stock_count( $product_id ) {
		$key   = 'lf_stock_' . (int) $product_id;
		$cache = wp_cache_get( $key, 'licenseforge' );
		if ( false !== $cache ) {
			return (int) $cache;
		}
		$count = self::count( $product_id, 'available' );
		wp_cache_set( $key, $count, 'licenseforge', 60 ); // 60s
		return $count;
	}

	/**
	 * Clear stock cache for a product.
	 */
	public static function clear_stock_cache( $product_id ) {
		wp_cache_delete( 'lf_stock_' . (int) $product_id, 'licenseforge' );
	}

	/**
	 * Paginated list — used by the All Licenses screen.
	 */
	public static function query( $args = [] ) {
		global $wpdb;
		$table = Database::table( 'licenses' );

		$args = wp_parse_args( $args, [
			'product_id' => 0,
			'order_id'   => 0,
			'status'     => '',
			'search'     => '',
			'orderby'    => 'id',
			'order'      => 'DESC',
			'paged'      => 1,
			'per_page'   => 20,
		] );

		$where  = [ '1=1' ];
		$params = [];

		if ( ! empty( $args['product_id'] ) ) {
			$where[]  = 'product_id = %d';
			$params[] = (int) $args['product_id'];
		}
		if ( ! empty( $args['order_id'] ) ) {
			$where[]  = 'order_id = %d';
			$params[] = (int) $args['order_id'];
		}
		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['search'] ) ) {
			$where[]  = 'license_key LIKE %s';
			$params[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
		}

		$orderby = in_array( $args['orderby'], [ 'id', 'created_at', 'product_id', 'status' ], true ) ? $args['orderby'] : 'id';
		$order   = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		$per_page = max( 1, (int) $args['per_page'] );
		$paged    = max( 1, (int) $args['paged'] );
		$offset   = ( $paged - 1 ) * $per_page;

		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM $table WHERE $where_sql";
		$total     = $params ? (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : (int) $wpdb->get_var( $count_sql );

		$list_sql = "SELECT * FROM $table WHERE $where_sql ORDER BY $orderby $order LIMIT %d OFFSET %d";
		$rows     = $wpdb->get_results( $wpdb->prepare( $list_sql, array_merge( $params, [ $per_page, $offset ] ) ) );

		return [
			'rows'     => $rows ?: [],
			'total'    => $total,
			'pages'    => (int) ceil( $total / $per_page ),
			'paged'    => $paged,
			'per_page' => $per_page,
		];
	}
}
