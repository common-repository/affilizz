<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<section id="step-connect" class="step__content step--current__content">
    <h2>👋&nbsp;&nbsp;<?php _e( 'Welcome to Affilizz', 'affilizz' ); ?></h2>

    <div class="step__help">
        <div class="step__help__icon">
            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>assets/dist/images/wizard/wordpress-link.svg" width="32" alt="<?php _e( 'Illustration of the link between WordPress and Affilizz', 'affilizz' ); ?>" />
        </div>

        <div class="step__help__content">
            <h3><?php _e( 'You are just a few clicks away from connecting your WordPress installation with Affilizz', 'affilizz' ); ?></h3>
            <p><?php _e( 'Our plugin grants you the ability to easily add your affiliate content in your pages.', 'affilizz' ); ?></p>
        </div>
    </div>

    <h3><?php _e( 'Fill in your Affilizz API key to link your WordPress installation to the Affilizz dashboard.', 'affilizz' ); ?></h3>

    <p>
        <a href="<?php echo esc_url( AFFILIZZ_API_KEY_HELP_URL ); ?>" title="<?php _e( 'Go to the key location help page', 'affilizz' ); ?>" target="_blank">
            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/wizard/help-icon-green.svg" width="16" alt="<?php _e( 'Illustration of the link between WordPress and Affilizz', 'affilizz' ); ?>" />
            <?php _e( 'Where can I find my key?', 'affilizz' ); ?>
        </a>
    </p>

    <label for="affilizz-api-key"><?php _e( 'Did you find your API key?', 'affilizz' ); ?></label>
    <input type="text" id="affilizz-api-key" name="affilizz-api-key" placeholder="<?php esc_attr_e( 'Enter your API Key here', 'affilizz' ); ?>" value="<?php echo esc_attr( get_option( 'affilizz_api_key', '' ) ); ?>">
    <?php wp_nonce_field( 'affilizz-api-key', '_wpnonce_key' ); ?>

    <div id="step-connect__error" class="step__error step__error--connect step__error--ajax"></div>

    <div class="step__actions step__actions--mono-link">
        <button type="button" id="step-connect-button" class="step__button affilizz-button affilizz-button--primary">
            <?php _e( 'Next', 'affilizz' ); ?>
            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/wizard/arrow-right-white.svg" width="8" alt="<?php _e( 'Icon of a white right arrow', 'affilizz' ); ?>" />
        </button>
    </div>
</section>