<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<script type="text/html" id="tmpl-affilizz-publication-block">
	<style type="text/css">
		<?php include_once( AFFILIZZ_DIRECTORY . 'assets/dist/css/admin.css' ); ?>
	</style>
	<div class="affilizz-inline">
		<div class="affilizz-inline__indicator">
			<span class="affilizz-button-icon">
				<img src="<?php echo trailingslashit( esc_url( AFFILIZZ_URL ) ); ?>assets/dist/images/logo/logo-type-white.svg" alt="<?php _e( 'Affilizz logo', 'affilizz' ); ?>" width="12" />
			</span>
			<?php _e( 'Affilizz affiliate content', 'affilizz'); ?>
		</div>
		<div class="affilizz-inline-inner" id="affilizz-inline-{{ data.id }}">
			{{{ data.render }}}<br>
		</div>
	</div>
</script>