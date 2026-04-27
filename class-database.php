<?php
/**
 * Database installer / upgrader.
 *
 * @package LicenseForge
 */

namespace LicenseForge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Database {

	/**
	 * Get table name.
	 */
	public static function table( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'lf_' . $name;
	}

	/**
	 * Create / upgrade DB tables. Uses dbDelta so it can run on upgrade safely.
	 */
	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		// Licenses table.
		$licenses = self::table( 'licenses' );
		$sql      = "CREATE TABLE $licenses (
			id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			license_key     VARCHAR(191) NOT NULL,
			product_id      BIGINT UNSIGNED NOT NULL DEFAULT 0,
			variation_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
			order_id        BIGINT UNSIGNED NOT NULL DEFAULT 0,
			user_id         BIGINT UNSIGNED NOT NULL DEFAULT 0,
			status          VARCHAR(20) NOT NULL DEFAULT 'available',
			max_activations INT NOT NULL DEFAULT 1,
			activations     INT NOT NULL DEFAULT 0,
			valid_for_days  INT DEFAULT NULL,
			expires_at      DATETIME DEFAULT NULL,
			delivered_at    DATETIME DEFAULT NULL,
			notes           TEXT,
			created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY license_key (license_key),
			KEY product_id (product_id),
			KEY order_id (order_id),
			KEY user_id (user_id),
			KEY status_product (status, product_id)
		) $charset;";
		dbDelta( $sql );

		// Delivery log.
		$log = self::table( 'delivery_log' );
		$sql = "CREATE TABLE $log (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			license_id  BIGINT UNSIGNED NOT NULL,
			order_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
			channel     VARCHAR(20) NOT NULL,
			status      VARCHAR(20) NOT NULL,
			recipient   VARCHAR(191) DEFAULT NULL,
			response    TEXT,
			created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY license_id (license_id),
			KEY order_id (order_id),
			KEY channel_status (channel, status)
		) $charset;";
		dbDelta( $sql );

		// Inbox (support messages).
		$inbox = self::table( 'inbox' );
		$sql   = "CREATE TABLE $inbox (
			id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			thread_id   VARCHAR(64) NOT NULL,
			user_id     BIGINT UNSIGNED NOT NULL DEFAULT 0,
			channel     VARCHAR(20) NOT NULL DEFAULT 'email',
			direction   VARCHAR(10) NOT NULL DEFAULT 'in',
			from_addr   VARCHAR(191) DEFAULT NULL,
			subject     VARCHAR(191) DEFAULT NULL,
			body        LONGTEXT,
			is_read     TINYINT(1) NOT NULL DEFAULT 0,
			status      VARCHAR(20) NOT NULL DEFAULT 'open',
			created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY thread_id (thread_id),
			KEY user_id (user_id),
			KEY status_channel (status, channel)
		) $charset;";
		dbDelta( $sql );

		update_option( 'lf_db_version', LICENSEFORGE_DB_VER );
	}

	/**
	 * Run dbDelta if version differs.
	 */
	public static function maybe_upgrade() {
		$installed = get_option( 'lf_db_version' );
		if ( $installed !== LICENSEFORGE_DB_VER ) {
			self::install();
		}
	}
}
