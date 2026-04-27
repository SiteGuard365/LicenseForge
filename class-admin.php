<?php
/**
 * Admin bootstrap — registers menu, scripts, page router.
 *
 * @package LicenseForge
 */

namespace LicenseForge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	/** @var string */
	const PAGE_SLUG = 'licenseforge';

	/** @var string */
	const CAPABILITY = 'manage_woocommerce';

	public function __construct() {
		add_action( 'admin_menu',            [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_init',            [ $this, 'handle_post' ] );
	}

	/**
	 * Capability used everywhere — falls back to manage_options if WC not present.
	 */
	public static function cap() {
		return current_user_can( self::CAPABILITY ) ? self::CAPABILITY : 'manage_options';
	}

	/**
	 * Build the admin menu. Coming-soon pages still appear (greyed out) so
	 * the user knows what's on the roadmap, but they don't load any
	 * heavy code.
	 */
	public function register_menu() {

		$cap  = self::cap();
		$icon = 'dashicons-admin-network';

		add_menu_page(
			__( 'LicenseForge', 'licenseforge' ),
			__( 'LicenseForge', 'licenseforge' ),
			$cap,
			self::PAGE_SLUG,
			[ $this, 'route' ],
			$icon,
			56
		);

		// ─── Licenses ───
		add_submenu_page( self::PAGE_SLUG, __( 'Licenses',  'licenseforge' ), __( 'All Licenses',  'licenseforge' ), $cap, self::PAGE_SLUG, [ $this, 'route' ] );
		add_submenu_page( self::PAGE_SLUG, __( 'Stock Manager', 'licenseforge' ), __( 'Stock Manager', 'licenseforge' ), $cap, self::PAGE_SLUG . '&page_view=stock', [ $this, 'route' ] );
		add_submenu_page( self::PAGE_SLUG, __( 'Import Keys',   'licenseforge' ), __( 'Import Keys',   'licenseforge' ), $cap, self::PAGE_SLUG . '&page_view=import', [ $this, 'route' ] );

		// ─── Delivery ───
		add_submenu_page( self::PAGE_SLUG, __( 'Email Templates', 'licenseforge' ), __( '— Email Templates', 'licenseforge' ), $cap, self::PAGE_SLUG . '&page_view=email', [ $this, 'route' ] );

		if ( Helpers::is_module_active( 'whatsapp' ) ) {
			add_submenu_page( self::PAGE_SLUG, __( 'WhatsApp', 'licenseforge' ), __( '— WhatsApp', 'licenseforge' ), $cap, self::PAGE_SLUG . '&page_view=whatsapp', [ $this, 'route' ] );
		}

		// ─── Customers ───
		add_submenu_page( self::PAGE_SLUG, __( 'Orders', 'licenseforge' ), __( 'Orders', 'licenseforge' ), $cap, self::PAGE_SLUG . '&page_view=orders', [ $this, 'route' ] );
		add_submenu_page( self::PAGE_SLUG, __( 'My Account', 'licenseforge' ), __( '— My Account Setup', 'licenseforge' ), $cap, self::PAGE_SLUG . '&page_view=myaccount', [ $this, 'route' ] );

		if ( Helpers::is_module_active( 'support_inbox' ) ) {
			add_submenu_page( self::PAGE_SLUG, __( 'Support Inbox', 'licenseforge' ), __( '— Support Inbox', 'licenseforge' ), $cap, self::PAGE_SLUG . '&page_view=inbox', [ $this, 'route' ] );
		}

		add_submenu_page( self::PAGE_SLUG, __( 'Subscriptions', 'licenseforge' ), __( '— Subscriptions', 'licenseforge' ) . ' 🔜', $cap, self::PAGE_SLUG . '&page_view=subscriptions', [ $this, 'route' ] );

		// ─── Smart Tools ───
		if ( Helpers::is_module_active( 'analytics' ) ) {
			add_submenu_page( self::PAGE_SLUG, __( 'Analytics', 'licenseforge' ), __( 'Analytics', 'licenseforge' ), $cap, self::PAGE_SLUG . '&page_view=analytics', [ $this, 'route' ] );
		}
		add_submenu_page( self::PAGE_SLUG, __( 'Reports',       'licenseforge' ), __( 'Reports', 'licenseforge' ) . ' 🔜', $cap, self::PAGE_SLUG . '&page_view=reports', [ $this, 'route' ] );
		add_submenu_page( self::PAGE_SLUG, __( 'Expiry Alerts', 'licenseforge' ), __( 'Expiry Alerts', 'licenseforge' ) . ' 🔜', $cap, self::PAGE_SLUG . '&page_view=expiry', [ $this, 'route' ] );

		// ─── Configure ───
		add_submenu_page( self::PAGE_SLUG, __( 'Settings', 'licenseforge' ), __( 'Settings', 'licenseforge' ), $cap, self::PAGE_SLUG . '&page_view=settings', [ $this, 'route' ] );
		add_submenu_page( self::PAGE_SLUG, __( 'Tools', 'licenseforge' ),    __( 'Tools',    'licenseforge' ), $cap, self::PAGE_SLUG . '&page_view=tools', [ $this, 'route' ] );
		add_submenu_page( self::PAGE_SLUG, __( 'Docs', 'licenseforge' ),     __( 'Docs',     'licenseforge' ), $cap, self::PAGE_SLUG . '&page_view=docs', [ $this, 'route' ] );
	}

	/**
	 * Load admin scripts only on our pages.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( (string) $hook, 'licenseforge' ) && false === strpos( (string) ( $_GET['page'] ?? '' ), 'licenseforge' ) ) { // phpcs:ignore
			return;
		}

		wp_enqueue_style(  'licenseforge-admin', LICENSEFORGE_URL . 'admin/css/admin.css', [], LICENSEFORGE_VERSION );
		wp_enqueue_script( 'licenseforge-admin', LICENSEFORGE_URL . 'admin/js/admin.js', [ 'jquery' ], LICENSEFORGE_VERSION, true );

		wp_localize_script( 'licenseforge-admin', 'LicenseForge', [
			'ajax'  => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'licenseforge' ),
			'i18n'  => [
				'copied' => __( 'Copied!', 'licenseforge' ),
				'sure'   => __( 'Are you sure?', 'licenseforge' ),
			],
		] );
	}

	/**
	 * Page router — every LicenseForge admin page goes through here.
	 */
	public function route() {

		if ( ! current_user_can( self::cap() ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'licenseforge' ) );
		}

		$view = isset( $_GET['page_view'] ) ? sanitize_key( wp_unslash( $_GET['page_view'] ) ) : 'licenses'; // phpcs:ignore

		// Map → file.
		$map = [
			'licenses'      => 'licenses',
			'stock'         => 'stock',
			'import'        => 'import',
			'email'         => 'email',
			'whatsapp'      => 'whatsapp',
			'orders'        => 'orders',
			'myaccount'     => 'myaccount',
			'inbox'         => 'inbox',
			'subscriptions' => 'subscriptions',
			'analytics'     => 'analytics',
			'reports'       => 'reports',
			'expiry'        => 'expiry',
			'settings'      => 'settings',
			'tools'         => 'tools',
			'docs'          => 'docs',
		];

		$slug = isset( $map[ $view ] ) ? $map[ $view ] : 'licenses';

		// Coming-soon gate.
		$coming_soon = [ 'subscriptions', 'reports', 'expiry' ];
		if ( in_array( $slug, $coming_soon, true ) ) {
			Helpers::view( 'coming-soon', [ 'feature' => $slug ] );
			return;
		}

		// Module gate — show prompt to enable in Settings if module is off.
		$module_required = [
			'whatsapp'  => 'whatsapp',
			'inbox'     => 'support_inbox',
			'analytics' => 'analytics',
		];
		if ( isset( $module_required[ $slug ] ) && ! Helpers::is_module_active( $module_required[ $slug ] ) ) {
			Helpers::view( 'module-disabled', [ 'module' => $module_required[ $slug ] ] );
			return;
		}

		Helpers::view( $slug );
	}

	/**
	 * Handle settings & form submissions.
	 */
	public function handle_post() {
		if ( empty( $_POST['lf_action'] ) || empty( $_POST['_wpnonce'] ) ) { // phpcs:ignore
			return;
		}
		if ( ! current_user_can( self::cap() ) ) {
			return;
		}
		$action = sanitize_key( wp_unslash( $_POST['lf_action'] ) ); // phpcs:ignore

		require_once LICENSEFORGE_PATH . 'admin/class-form-handler.php';
		Form_Handler::dispatch( $action );
	}
}
