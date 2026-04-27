<?php
/**
 * View: Support Inbox (only loaded when module enabled).
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$msgs = $wpdb->get_results( "SELECT * FROM " . \LicenseForge\Database::table( 'inbox' ) . " ORDER BY created_at DESC LIMIT 50" ); // phpcs:ignore
?>
<div class="wrap lf-wrap">
	<div class="lf-page-header">
		<h1><?php esc_html_e( 'Support Inbox', 'licenseforge' ); ?></h1>
		<span class="lf-badge lf-badge-success"><?php esc_html_e( 'Module Active', 'licenseforge' ); ?></span>
	</div>

	<?php if ( empty( $msgs ) ) : ?>
		<div class="lf-card"><div class="lf-card-body" style="text-align:center;padding:40px">
			<h2 style="margin-bottom:8px">📭 <?php esc_html_e( 'Inbox is empty', 'licenseforge' ); ?></h2>
			<p><?php esc_html_e( 'Customer replies to license emails (and WhatsApp messages, when configured) will land here.', 'licenseforge' ); ?></p>
		</div></div>
	<?php else : ?>
		<div class="lf-inbox">
			<div class="lf-inbox-list">
				<?php foreach ( $msgs as $i => $m ) : ?>
					<div class="lf-inbox-item <?php echo $i === 0 ? 'active' : ''; ?>">
						<div style="display:flex;justify-content:space-between;font-size:11px;color:#646970"><span><?php echo esc_html( $m->from_addr ); ?></span><span><?php echo esc_html( human_time_diff( strtotime( $m->created_at ) ) ); ?></span></div>
						<div style="font-size:13px;font-weight:<?php echo $m->is_read ? 400 : 600; ?>;margin:3px 0"><?php echo esc_html( wp_trim_words( $m->subject ?: $m->body, 8 ) ); ?></div>
						<div style="font-size:12px;color:#646970;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html( wp_trim_words( $m->body, 12 ) ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="lf-inbox-detail">
				<?php $first = $msgs[0]; ?>
				<div style="border-bottom:1px solid #dcdcde;padding-bottom:10px;margin-bottom:8px">
					<div style="font-size:15px;font-weight:600"><?php echo esc_html( $first->from_addr ); ?></div>
					<div style="font-size:12px;color:#646970"><?php echo esc_html( ucfirst( $first->channel ) ); ?> · <?php echo esc_html( mysql2date( 'M j, Y g:i a', $first->created_at ) ); ?></div>
				</div>
				<div style="flex:1;overflow-y:auto">
					<div class="lf-msg in"><?php echo wp_kses_post( wpautop( $first->body ) ); ?></div>
				</div>
				<div style="border-top:1px solid #dcdcde;padding-top:10px;margin-top:10px">
					<textarea rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Type your reply…', 'licenseforge' ); ?>"></textarea>
					<p><button class="button button-primary"><?php esc_html_e( 'Reply', 'licenseforge' ); ?></button></p>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
