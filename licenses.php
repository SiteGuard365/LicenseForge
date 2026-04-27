<?php
/**
 * View: All Licenses.
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use LicenseForge\Licenses;
use LicenseForge\Helpers;

$paged    = max( 1, isset( $_GET['paged'] )      ? intval( $_GET['paged'] )       : 1 ); // phpcs:ignore
$status   = isset( $_GET['status'] )             ? sanitize_key( wp_unslash( $_GET['status'] ) )    : ''; // phpcs:ignore
$prod_id  = isset( $_GET['product_id'] )         ? intval( $_GET['product_id'] )                    : 0; // phpcs:ignore
$search   = isset( $_GET['s'] )                  ? sanitize_text_field( wp_unslash( $_GET['s'] ) )  : ''; // phpcs:ignore

$result = Licenses::query( [
	'paged'      => $paged,
	'status'     => $status,
	'product_id' => $prod_id,
	'search'     => $search,
	'per_page'   => 20,
] );

// Notice from query string
if ( ! empty( $_GET['lf_msg'] ) ) { // phpcs:ignore
	Helpers::admin_notice(
		sanitize_text_field( wp_unslash( $_GET['lf_msg'] ) ), // phpcs:ignore
		isset( $_GET['lf_type'] ) ? sanitize_key( wp_unslash( $_GET['lf_type'] ) ) : 'success' // phpcs:ignore
	);
}
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php esc_html_e( 'All Licenses', 'licenseforge' ); ?></h1>
		<a href="#TB_inline?inlineId=lf-add-key&width=540&height=520" class="button button-primary thickbox"><?php esc_html_e( 'Add New', 'licenseforge' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=licenseforge&page_view=import' ) ); ?>" class="button"><?php esc_html_e( 'Import', 'licenseforge' ); ?></a>
		<div class="lf-spacer"></div>
		<span class="lf-subtitle"><?php
			/* translators: %s: number of total licenses */
			printf( esc_html__( '%s licenses total', 'licenseforge' ), '<strong>' . number_format_i18n( $result['total'] ) . '</strong>' );
		?></span>
	</div>

	<form method="get">
		<input type="hidden" name="page" value="licenseforge">
		<p class="search-box">
			<label class="screen-reader-text" for="lf-search"><?php esc_html_e( 'Search Licenses', 'licenseforge' ); ?></label>
			<input type="search" id="lf-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search keys…', 'licenseforge' ); ?>">
			<select name="status">
				<option value=""><?php esc_html_e( 'All statuses', 'licenseforge' ); ?></option>
				<?php foreach ( [ 'available', 'sold', 'reserved', 'revoked' ] as $st ) : ?>
					<option value="<?php echo esc_attr( $st ); ?>" <?php selected( $status, $st ); ?>><?php echo esc_html( ucfirst( $st ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'licenseforge' ); ?>">
		</p>
	</form>

	<form method="post">
		<?php wp_nonce_field( 'licenseforge_delete_keys' ); ?>
		<input type="hidden" name="lf_action" value="delete_keys">

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<td class="check-column"><input type="checkbox" class="lf-check-all"></td>
					<th><?php esc_html_e( 'License Key', 'licenseforge' ); ?></th>
					<th><?php esc_html_e( 'Product', 'licenseforge' ); ?></th>
					<th><?php esc_html_e( 'Status', 'licenseforge' ); ?></th>
					<th><?php esc_html_e( 'Order', 'licenseforge' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'licenseforge' ); ?></th>
					<th><?php esc_html_e( 'Added', 'licenseforge' ); ?></th>
					<th><?php esc_html_e( 'Expires', 'licenseforge' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $result['rows'] ) ) : ?>
					<tr><td colspan="8"><?php esc_html_e( 'No licenses found. Import some keys to get started.', 'licenseforge' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $result['rows'] as $row ) :
						$product   = $row->product_id ? wc_get_product( $row->product_id ) : null;
						$user      = $row->user_id ? get_user_by( 'id', $row->user_id ) : null;
						$status_t  = ( 'sold' === $row->status ) ? 'success' : ( ( 'available' === $row->status ) ? 'warning' : ( ( 'revoked' === $row->status ) ? 'error' : 'gray' ) );
						?>
						<tr>
							<td><input type="checkbox" name="ids[]" value="<?php echo esc_attr( $row->id ); ?>" class="lf-check"></td>
							<td><span class="lf-key"><?php echo esc_html( $row->license_key ); ?> <span class="lf-copy" title="Copy">⎘</span></span></td>
							<td><?php echo $product ? esc_html( $product->get_name() ) : '<em>—</em>'; ?></td>
							<td><span class="lf-badge lf-badge-<?php echo esc_attr( $status_t ); ?>"><?php echo esc_html( $row->status ); ?></span></td>
							<td><?php echo $row->order_id ? '<a href="' . esc_url( admin_url( 'post.php?post=' . $row->order_id . '&action=edit' ) ) . '">#' . esc_html( $row->order_id ) . '</a>' : '<em>—</em>'; ?></td>
							<td><?php echo $user ? esc_html( $user->display_name ) : '<em>—</em>'; ?></td>
							<td><?php echo esc_html( mysql2date( 'M j, Y', $row->created_at ) ); ?></td>
							<td><?php echo $row->expires_at ? esc_html( mysql2date( 'M j, Y', $row->expires_at ) ) : '<em>' . esc_html__( 'Never', 'licenseforge' ) . '</em>'; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( ! empty( $result['rows'] ) ) : ?>
			<p class="tablenav-pages-wrapper" style="margin-top:10px">
				<input type="submit" class="button" value="<?php esc_attr_e( 'Delete selected', 'licenseforge' ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete selected licenses?', 'licenseforge' ) ); ?>');">

				<?php if ( $result['pages'] > 1 ) : ?>
					<span style="float:right">
						<?php
						echo paginate_links( [ // phpcs:ignore
							'base'    => add_query_arg( 'paged', '%#%' ),
							'format'  => '',
							'current' => $paged,
							'total'   => $result['pages'],
						] );
						?>
					</span>
				<?php endif; ?>
			</p>
		<?php endif; ?>
	</form>
</div>

<!-- Modal: Add Key -->
<div id="lf-add-key" style="display:none;padding:14px">
	<form method="post">
		<?php wp_nonce_field( 'licenseforge_add_key' ); ?>
		<input type="hidden" name="lf_action" value="add_key">
		<h2 style="margin-top:0"><?php esc_html_e( 'Add License Key', 'licenseforge' ); ?></h2>
		<table class="form-table">
			<tr><th><label><?php esc_html_e( 'Key', 'licenseforge' ); ?></label></th>
				<td><input type="text" name="license_key" required class="regular-text" placeholder="XXXXX-YYYYY-ZZZZZ-AAAAA"></td></tr>
			<tr><th><label><?php esc_html_e( 'Product', 'licenseforge' ); ?></label></th>
				<td>
					<select name="product_id">
						<option value="0">— <?php esc_html_e( 'Choose product', 'licenseforge' ); ?> —</option>
						<?php
						if ( function_exists( 'wc_get_products' ) ) {
							$products = wc_get_products( [ 'limit' => 200, 'status' => 'publish' ] );
							foreach ( $products as $p ) {
								echo '<option value="' . esc_attr( $p->get_id() ) . '">' . esc_html( $p->get_name() ) . '</option>';
							}
						}
						?>
					</select>
				</td></tr>
			<tr><th><label><?php esc_html_e( 'Status', 'licenseforge' ); ?></label></th>
				<td>
					<select name="status">
						<option value="available"><?php esc_html_e( 'Available', 'licenseforge' ); ?></option>
						<option value="reserved"><?php esc_html_e( 'Reserved', 'licenseforge' ); ?></option>
					</select>
				</td></tr>
			<tr><th><label><?php esc_html_e( 'Expires At', 'licenseforge' ); ?></label></th>
				<td><input type="date" name="expires_at"></td></tr>
			<tr><th><label><?php esc_html_e( 'Max Activations', 'licenseforge' ); ?></label></th>
				<td><input type="number" name="max_activations" value="1" min="1" class="small-text"></td></tr>
		</table>
		<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Add Key', 'licenseforge' ); ?>"></p>
	</form>
</div>
<?php add_thickbox(); ?>
