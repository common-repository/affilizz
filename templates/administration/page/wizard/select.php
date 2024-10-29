<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<section id="step-select" class="step__content">
    <h2><?php _e( 'Set the connection between us', 'affilizz' ); ?></h2>

    <div class="step__help">
        <div class="step__card__icon">
            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/wizard/media-pictogram.svg" width="40" alt="<?php _e( 'Pictogram of the medias in Affilizz', 'affilizz' ); ?>" />
        </div>

        <div class="step__help__content">
            <h3><?php _e( 'Link your organization, media and channel with your WordPress installation.', 'affilizz' ); ?></h3>
            <p><?php _e( 'A link will be established so you will able to see any affiliate content you have created with Affilizz in your WordPress Affilizz plugin.', 'affilizz' ); ?></p>
            <p><?php printf( __( '%1$sWarning%2$s : Please note you will not be able to change this later unless if you desinstall and reinstall this plugin.', 'affilizz' ), '<strong>', '</strong>' ); ?></p>
        </div>
    </div>

    <h3><?php _e( 'Select your organization, media and channel', 'affilizz' ); ?></h3>

    <p class="form__field">
        <label for="affilizz-organization">
            <?php _e( 'Organization', 'affilizz' ); ?>
        </label>
        <select name="affilizz-organization" id="affilizz-organization">
            <option value="0" disabled selected>
                <?php _e( 'Select your organization', 'affilizz' ); ?>&hellip;
            </option>
        </select>
    </p>

    <p class="form__field">
        <label for="affilizz-media">
            <?php _e( 'Media', 'affilizz' ); ?>
        </label>
        <select disabled name="affilizz-media" id="affilizz-media">
            <option value="0" disabled selected>
                <?php _e( 'Select your media', 'affilizz' ); ?>
            </option>
        </select>
    </p>

    <p class="form__field">
        <label for="affilizz-channel">
            <?php _e( 'Channel', 'affilizz' ); ?>
        </label>
        <select disabled name="affilizz-channel" id="affilizz-channel">
            <option value="0" disabled selected>
                <?php _e( 'Select your channel', 'affilizz' ); ?>
            </option>
        </select>
    </p>

    <?php wp_nonce_field( 'affilizz-save-wizard-data' ); ?>

    <div id="step-select__error" class="step__error step__error--select step__error--ajax"></div>

    <div class="step__actions">
        <button type="button" class="step__button affilizz-button affilizz-button--secondary" id="step-select-back">
            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/wizard/arrow-left-blue.svg" width="8" alt="<?php _e( 'Icon of a blue left arrow', 'affilizz' ); ?>" />
            <?php _e( 'Back', 'affilizz' ); ?>
        </button>
        <button type="button" id="step-select-button" class="step__button affilizz-button affilizz-button--primary">
            <?php _e( 'Next', 'affilizz' ); ?>
            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/wizard/arrow-right-white.svg" width="8" alt="<?php _e( 'Icon of a white right arrow', 'affilizz' ); ?>" />
        </button>
    </div>
</section>