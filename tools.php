<?php
/**
 * View: Tools — DB check, cleanup, system status.
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use LicenseForge\Database;
use LicenseForge\Helpers;

if ( ! empty( $_GET['lf_msg'] ) ) Helpers::admin_notice( sanitize_text_field( wp_unslash( $_GET['lf_msg'] ) ) ); // phpcs:ignore

global $wpdb;
$tables = [
	'licenses'     => Database::table( 'licenses' ),
	'delivery_log' => Database::table( 'delivery_log' ),
	'inbox'        => Database::table( 'inbox' ),
];

$rows = [];
foreach ( $tables as $name => $table ) {
	$exists      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	$rows[ $name ] = [
		'exists' => $exists,
		'count'  => $exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" ) : 0, // phpcs:ignore
	];
}

$wc_active = class_exists( 'WooCommerce' );
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php esc_html_e( 'Tools', 'licenseforge' ); ?></h1>
	</div>

	<div class="lf-grid-2">
		<div class="lf-card">
			<div class="lf-card-header"><h2 class="lf-card-title"><?php esc_html_e( 'Database', 'licenseforge' ); ?></h2></div>
			<div class="lf-card-body">
				<table class="wp-list-table widefat striped" style="border:none">
					<thead><tr><th><?php esc_html_e( 'Table', 'licenseforge' ); ?></th><th><?php esc_html_e( 'Status', 'licenseforge' ); ?></th><th><?php esc_html_e( 'Rows', 'licenseforge' ); ?></th></tr></thead>
					<tbody>
						<?php foreach ( $rows as $name => $r ) : ?>
							<tr>
								<td><code>wp_lf_<?php echo esc_html( $name ); ?></code></td>
								<td>
									<?php if ( $r['exists'] ) : ?>
										<span class="lf-badge lf-badge-success">✓ <?php esc_html_e( 'OK', 'licenseforge' ); ?></span>
									<?php else : ?>
										<span class="lf-badge lf-badge-error">✗ <?php esc_html_e( 'Missing', 'licenseforge' ); ?></span>
									<?php endif; ?>
								</td>
								<td><?php echo number_format_i18n( $r['count'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<p style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap">
					<?php
					$check_url = wp_nonce_url(
						admin_url( 'admin.php?page=licenseforge&page_view=tools&lf_repair=1' ),
						'licenseforge_repair'
					);

					// Handle repair on this page load.
					if ( isset( $_GET['lf_repair'] ) && check_admin_referer( 'licenseforge_repair' ) ) { // phpcs:ignore
						Database::install();
						echo '<div class="notice notice-success"><p>' . esc_html__( 'Database tables checked & repaired.', 'licenseforge' ) . '</p></div>';
					}
					?>
					<a href="<?php echo esc_url( $check_url ); ?>" class="button"><?php esc_html_e( 'Check &amp; Repair Tables', 'licenseforge' ); ?></a>

					<form method="post" style="margin:0">
						<?php wp_nonce_field( 'licenseforge_cleanup' ); ?>
						<input type="hidden" name="lf_action" value="cleanup">
						<button class="button" onclick="return confirm('<?php echo esc_js( __( 'Delete delivery logs older than 90 days?', 'licenseforge' ) ); ?>');"><?php esc_html_e( 'Cleanup Old Logs', 'licenseforge' ); ?></button>
					</form>
				</p>
			</div>
		</div>

		<div class="lf-card">
			<div class="lf-card-header"><h2 class="lf-card-title"><?php esc_html_e( 'System Status', 'licenseforge' ); ?></h2></div>
			<div class="lf-card-body">
				<table class="wp-list-table widefat striped" style="border:none">
					<tbody>
						<tr><td><?php esc_html_e( 'WordPress', 'licenseforge' ); ?></td><td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td></tr>
						<tr><td><?php esc_html_e( 'WooCommerce', 'licenseforge' ); ?></td><td><?php echo $wc_active ? esc_html( WC()->version ) . ' <span class="lf-badge lf-badge-success">Active</span>' : '<span class="lf-badge lf-badge-error">Not active</span>'; ?></td></tr>
						<tr><td><?php esc_html_e( 'PHP', 'licenseforge' ); ?></td><td><?php echo esc_html( PHP_VERSION ); ?></td></tr>
						<tr><td><?php esc_html_e( 'MySQL', 'licenseforge' ); ?></td><td><?php echo esc_html( $wpdb->db_version() ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Plugin Version', 'licenseforge' ); ?></td><td><?php echo esc_html( LICENSEFORGE_VERSION ); ?></td></tr>
						<tr><td><?php esc_html_e( 'DB Schema', 'licenseforge' ); ?></td><td><?php echo esc_html( get_option( 'lf_db_version', '—' ) ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Cron healthy', 'licenseforge' ); ?></td><td><?php echo wp_next_scheduled( 'licenseforge_daily_cleanup' ) ? '<span class="lf-badge lf-badge-success">Yes</span>' : '<span class="lf-badge lf-badge-warning">No</span>'; ?></td></tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="lf-card">
		<div class="lf-card-header"><h2 class="lf-card-title"><?php esc_html_e( 'Active Modules', 'licenseforge' ); ?></h2></div>
		<div class="lf-card-body">
			<?php
			$mm    = \LicenseForge\Plugin::instance()->modules;
			$reg   = $mm->all();
			$count = 0;
			foreach ( $reg as $slug => $def ) {
				$on = $mm->is_enabled( $slug ) && $def['status'] === 'stable';
				if ( $on ) $count++;
				echo '<div style="display:inline-block;margin:0 12px 8px 0">';
				echo '<span class="lf-badge lf-badge-' . ( $on ? 'success' : 'gray' ) . '">' . ( $on ? '●' : '○' ) . ' ' . esc_html( $def['label'] ) . '</span>';
				echo '</div>';
			}
			?>
			<p style="margin-top:12px;color:#646970"><?php
			/* translators: %d count */
			printf( esc_html__( '%d module(s) currently loading. Disable any module in Settings → Modules to reduce load.', 'licenseforge' ), $count );
			?></p>
		</div>
	</div>
</div>
