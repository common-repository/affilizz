<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="affilizz-button affilizz-button--call">
	<div class="affilizz-button__inner">
		<a id="insert-affilizz-publication" class="insert-affilizz-publication" data-editor="content" href="#" title="<?php _e( 'Insert affiliate content', 'affilizz' ); ?>">
			<span class="affilizz-button-icon">
				<img src="<?php echo trailingslashit( esc_url( AFFILIZZ_URL ) ); ?>assets/dist/images/logo/logo-type-white.svg" alt="<?php _e( 'Affilizz logo', 'affilizz' ); ?>" />
			</span>
			<?php _e( 'Insert affiliate content', 'affilizz' ); ?>
		</a>
	</div>
</div>
<a id="affilizz-floating-button" class="insert-affilizz-publication" data-editor="content" href="#" title="<?php _e( 'Insert affiliate content', 'affilizz' ); ?>">
	<img src="<?php echo trailingslashit( esc_url( AFFILIZZ_URL ) ); ?>assets/dist/images/logo/logo-type-white.svg" alt="<?php _e( 'Affilizz logo', 'affilizz' ); ?>" width="20" style="top: 2px; position: relative;" />
	<i class="mce-ico mce-i-dashicon dashicons-plus-alt" style="color: #fff"></i>
</a>