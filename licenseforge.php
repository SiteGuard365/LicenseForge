<?php
/**
 * Plugin Name: LicenseForge
 * Plugin URI:  https://example.com/licenseforge
 * Description: Lightweight WooCommerce license-key manager with email & WhatsApp delivery, stock, support inbox and analytics. Modular — activate only what you need.
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * Text Domain: licenseforge
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * License:     GPLv2 or later
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------
define( 'LICENSEFORGE_VERSION',  '1.0.0' );
define( 'LICENSEFORGE_FILE',     __FILE__ );
define( 'LICENSEFORGE_PATH',     plugin_dir_path( __FILE__ ) );
define( 'LICENSEFORGE_URL',      plugin_dir_url( __FILE__ ) );
define( 'LICENSEFORGE_BASENAME', plugin_basename( __FILE__ ) );
define( 'LICENSEFORGE_DB_VER',   '1.0' );

// ---------------------------------------------------------------------------
// Autoloader
// ---------------------------------------------------------------------------
require_once LICENSEFORGE_PATH . 'includes/class-licenseforge.php';

// ---------------------------------------------------------------------------
// Activation / Deactivation / Uninstall hooks
// ---------------------------------------------------------------------------
register_activation_hook( __FILE__, [ 'LicenseForge\\Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'LicenseForge\\Activator', 'deactivate' ] );

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
add_action( 'plugins_loaded', function () {
	// Load text-domain.
	load_plugin_textdomain( 'licenseforge', false, dirname( LICENSEFORGE_BASENAME ) . '/languages' );

	// Init main class (singleton).
	LicenseForge\Plugin::instance();
}, 5 );
