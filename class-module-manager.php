<?php
/**
 * Module manager — loads only enabled modules to keep things fast.
 *
 * @package LicenseForge
 */

namespace LicenseForge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module_Manager {

	/**
	 * Available module definitions.
	 *
	 * Each module has:
	 *  - label      : User facing name
	 *  - description: Short description
	 *  - status     : 'stable' or 'coming_soon'
	 *  - file       : Bootstrap file (relative to modules/)
	 *  - class      : Class to instantiate when loaded
	 *  - heavy      : true if the module adds noticeable load (warn user)
	 *
	 * @var array
	 */
	private $registry = [];

	/** @var array */
	private $enabled  = [];

	public function __construct() {
		$this->define_registry();
		$this->enabled = (array) get_option( 'lf_modules_enabled', [] );
	}

	private function define_registry() {
		$this->registry = [
			'whatsapp'      => [
				'label'       => __( 'WhatsApp Delivery', 'licenseforge' ),
				'description' => __( 'Send license keys via WhatsApp on order completion. Requires Twilio / Meta API credentials.', 'licenseforge' ),
				'status'      => 'stable',
				'file'        => 'whatsapp/class-whatsapp.php',
				'class'       => 'LicenseForge\\Modules\\WhatsApp',
				'heavy'       => false,
			],
			'support_inbox' => [
				'label'       => __( 'Support Inbox', 'licenseforge' ),
				'description' => __( 'Capture customer replies (email + WhatsApp) into one admin inbox.', 'licenseforge' ),
				'status'      => 'stable',
				'file'        => 'support-inbox/class-support-inbox.php',
				'class'       => 'LicenseForge\\Modules\\Support_Inbox',
				'heavy'       => false,
			],
			'analytics'     => [
				'label'       => __( 'Analytics', 'licenseforge' ),
				'description' => __( 'Lightweight in-admin analytics — keys sold, deliveries, channel split.', 'licenseforge' ),
				'status'      => 'stable',
				'file'        => 'analytics/class-analytics.php',
				'class'       => 'LicenseForge\\Modules\\Analytics',
				'heavy'       => false,
			],
			'subscriptions' => [
				'label'       => __( 'Subscriptions', 'licenseforge' ),
				'description' => __( 'Auto-renewal & subscription tracking. (Coming soon)', 'licenseforge' ),
				'status'      => 'coming_soon',
				'file'        => '',
				'class'       => '',
				'heavy'       => true,
			],
			'reports'       => [
				'label'       => __( 'Reports', 'licenseforge' ),
				'description' => __( 'Sales / tax / inventory reports & PDF invoices. (Coming soon)', 'licenseforge' ),
				'status'      => 'coming_soon',
				'file'        => '',
				'class'       => '',
				'heavy'       => true,
			],
			'expiry_alerts' => [
				'label'       => __( 'Expiry Alerts', 'licenseforge' ),
				'description' => __( 'Auto reminders before license expiry. (Coming soon)', 'licenseforge' ),
				'status'      => 'coming_soon',
				'file'        => '',
				'class'       => '',
				'heavy'       => false,
			],
		];
	}

	public function all() {
		return $this->registry;
	}

	public function get( $slug ) {
		return isset( $this->registry[ $slug ] ) ? $this->registry[ $slug ] : null;
	}

	public function is_enabled( $slug ) {
		return ! empty( $this->enabled[ $slug ] );
	}

	/**
	 * Boot enabled modules. Coming-soon modules are never booted (no PHP load).
	 */
	public function boot() {
		foreach ( $this->registry as $slug => $def ) {
			if ( $def['status'] !== 'stable' ) {
				continue;
			}
			if ( empty( $this->enabled[ $slug ] ) ) {
				continue;
			}

			$file = LICENSEFORGE_PATH . 'modules/' . $def['file'];
			if ( file_exists( $file ) ) {
				require_once $file;
			}
			if ( ! empty( $def['class'] ) && class_exists( $def['class'] ) ) {
				new $def['class']();
			}
		}
	}

	/**
	 * Toggle a module on/off.
	 */
	public function set_enabled( $slug, $on ) {
		if ( ! isset( $this->registry[ $slug ] ) ) {
			return false;
		}
		if ( $this->registry[ $slug ]['status'] !== 'stable' ) {
			return false; // Cannot enable coming-soon modules.
		}
		$this->enabled[ $slug ] = $on ? 1 : 0;
		update_option( 'lf_modules_enabled', $this->enabled );
		return true;
	}
}
