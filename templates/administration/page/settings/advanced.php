<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="affilizz-plugin__pane" id="affilizz-pane-advanced">
    <div class="affilizz-group affilizz-context-default affilizz-form">
        <div class="affilizz-plugin__pane__header">
            <div class="affilizz-plugin__pane__header__icon">
                <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/icon/settings-cog.svg" alt="<?php _e( 'Advanced settings', 'affilizz' ); ?>" />
            </div>
            <div class="affilizz-plugin__pane__header__text">
                <h3 class="affilizz-group-headline"><?php _e( 'Advanced settings', 'affilizz' ); ?></h3>
                <p class="affilizz-plugin__pane__header__description"><?php _e( 'Render options and advanced tasks', 'affilizz' ); ?></p>
            </div>
            <?php echo wp_kses( $submit_button, \Affilizz\Core::get_extended_allowed_tags() ); ?>
        </div>
		<?php
			include( trailingslashit( AFFILIZZ_DIRECTORY ) . 'templates/administration/page/settings/rendering.php' );
			include( trailingslashit( AFFILIZZ_DIRECTORY ) . 'templates/administration/page/settings/technical.php' );
		?>
    </div>
</div>