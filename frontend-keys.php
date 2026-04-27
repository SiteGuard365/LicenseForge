<?php
/**
 * View: Frontend license-key block (Thank-you page).
 *
 * Variables: $keys (array of license rows), $title (string)
 *
 * @package LicenseForge
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( empty( $keys ) ) return;
?>
<section class="lf-frontend-keys">
	<h2><?php echo esc_html( isset( $title ) ? $title : __( 'Your License Keys', 'licenseforge' ) ); ?></h2>

	<?php foreach ( $keys as $row ) :
		$product = $row->product_id ? wc_get_product( $row->product_id ) : null;
		?>
		<div class="lf-fe-license">
			<?php if ( $product ) : ?>
				<div class="lf-fe-license-name"><?php echo esc_html( $product->get_name() ); ?></div>
			<?php endif; ?>

			<div class="lf-fe-license-key">
				<code><?php echo esc_html( $row->license_key ); ?></code>
				<button type="button" class="lf-copy-btn" data-key="<?php echo esc_attr( $row->license_key ); ?>"><?php esc_html_e( 'Copy', 'licenseforge' ); ?></button>
			</div>

			<div class="lf-fe-license-meta">
				<?php if ( $row->expires_at ) : ?>
					<span><?php
					/* translators: %s: expiry date */
					printf( esc_html__( 'Expires: %s', 'licenseforge' ), esc_html( mysql2date( get_option( 'date_format' ), $row->expires_at ) ) ); ?></span>
				<?php else : ?>
					<span><?php esc_html_e( 'Validity: Lifetime', 'licenseforge' ); ?></span>
				<?php endif; ?>
				<?php if ( (int) $row->max_activations > 0 ) : ?>
					· <span><?php
					/* translators: 1: used, 2: max */
					printf( esc_html__( 'Activations: %1$d / %2$d', 'licenseforge' ), (int) $row->activations, (int) $row->max_activations ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</section>

<script>
(function(){
	document.querySelectorAll('.lf-copy-btn').forEach(function(b){
		b.addEventListener('click', function(){
			var k = b.getAttribute('data-key');
			if (navigator.clipboard) navigator.clipboard.writeText(k);
			var t = b.textContent; b.textContent = '<?php echo esc_js( __( 'Copied!', 'licenseforge' ) ); ?>';
			setTimeout(function(){ b.textContent = t; }, 1400);
		});
	});
})();
</script>
