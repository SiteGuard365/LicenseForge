<?php
/**
 * Main plugin class.
 *
 * @package LicenseForge
 */

namespace LicenseForge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin singleton bootstrap.
 *
 * Lazy-loads classes via spl_autoload so the plugin keeps a tiny footprint
 * on every WP request — only the code actually needed for the current
 * page is loaded into memory.
 */
final class Plugin {

	/** @var Plugin */
	private static $instance = null;

	/** @var Module_Manager */
	public $modules;

	/** @var Admin */
	public $admin;

	/**
	 * Singleton.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Boot.
	 */
	private function __construct() {
		$this->register_autoloader();
		$this->load_core_files();
		$this->init_hooks();
	}

	/**
	 * Register PSR-style autoloader for classes inside includes/ and modules/.
	 */
	private function register_autoloader() {
		spl_autoload_register( function ( $class ) {
			if ( strpos( $class, 'LicenseForge\\' ) !== 0 ) {
				return;
			}

			$relative = str_replace( 'LicenseForge\\', '', $class );
			$parts    = explode( '\\', $relative );
			$file     = strtolower( str_replace( '_', '-', array_pop( $parts ) ) );

			$paths = [];
			if ( ! empty( $parts ) ) {
				$sub     = strtolower( implode( DIRECTORY_SEPARATOR, $parts ) );
				$paths[] = LICENSEFORGE_PATH . 'modules/' . $sub . '/class-' . $file . '.php';
				$paths[] = LICENSEFORGE_PATH . 'includes/' . $sub . '/class-' . $file . '.php';
			}
			$paths[] = LICENSEFORGE_PATH . 'modules/' . $file . '/class-' . $file . '.php';
			$paths[] = LICENSEFORGE_PATH . 'includes/class-' . $file . '.php';

			foreach ( $paths as $p ) {
				if ( file_exists( $p ) ) {
					require_once $p;
					return;
				}
			}
		} );
	}

	/**
	 * Files we always load (core).
	 */
	private function load_core_files() {
		require_once LICENSEFORGE_PATH . 'includes/class-activator.php';
		require_once LICENSEFORGE_PATH . 'includes/class-database.php';
		require_once LICENSEFORGE_PATH . 'includes/class-module-manager.php';
		require_once LICENSEFORGE_PATH . 'includes/class-licenses.php';
		require_once LICENSEFORGE_PATH . 'includes/class-helpers.php';
	}

	/**
	 * Boot.
	 */
	private function init_hooks() {

		// Run upgrade routines if needed.
		Database::maybe_upgrade();

		// Load enabled modules.
		$this->modules = new Module_Manager();
		$this->modules->boot();

		// Admin only.
		if ( is_admin() ) {
			require_once LICENSEFORGE_PATH . 'admin/class-admin.php';
			$this->admin = new Admin();
		}

		// Front-end (My Account, Thank-you page integration).
		require_once LICENSEFORGE_PATH . 'public/class-public.php';
		new Front_End();

		// WooCommerce hooks (license delivery on order completion).
		require_once LICENSEFORGE_PATH . 'includes/class-woo-integration.php';
		new Woo_Integration();
	}
}
