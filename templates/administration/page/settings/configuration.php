<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    global $wp;
    $submit_button = str_replace( '"button ', '"', get_submit_button( __( 'Save changes', 'affilizz' ), 'affilizz-button affilizz-button--primary affilizz-plugin__pane__button affilizz-plugin__pane__button--submit', 'affilizz-save-settings', true ) );
    $current_url = rtrim( add_query_arg( [ 'bust_cache' => 1 ] ), '/' );

    // File information about cache busting
    $local_file = trailingslashit( AFFILIZZ_DIRECTORY ) . 'assets/js/affilizz.local.js';
    $cache_generated = ( file_exists( $local_file ) && filemtime( $local_file ) ) ? human_time_diff( filemtime( $local_file ) ) : false;
?>
<div class="affilizz-plugin__pane affilizz-plugin__pane--active" id="affilizz-pane-configuration">
    <div class="affilizz-group affilizz-context-default affilizz-form">
        <div class="affilizz-plugin__pane__header">
            <div class="affilizz-plugin__pane__header__icon">
                <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/icon/configuration-knobs.svg" alt="<?php _e( 'General plugin setup', 'affilizz' ); ?>" />
            </div>
            <div class="affilizz-plugin__pane__header__text">
                <h3 class="affilizz-group-headline"><?php _e( 'Configuration', 'affilizz' ); ?></h3>
                <p class="affilizz-plugin__pane__header__description"><?php _e( 'General settings & variables for the Affilizz Plugin', 'affilizz' ); ?></p>
            </div>
            <?php echo wp_kses( $submit_button, \Affilizz\Core::get_extended_allowed_tags() ); ?>
        </div>

        <h4 class="affilizz-plugin__pane__section__title">
            <span><?php _e( 'API', 'affilizz' ); ?>
        </h4>

        <div class="affilizz-field-wrapper affilizz-field-wrapper--api-key">
            <div class="affilizz-field text">
                <div class="affilizz-field-main">
                    <label for="affilizz-api-key"><?php _e( 'API key', 'affilizz' ); ?></label>
                    <input type="text" value="<?php echo esc_attr( get_option( 'affilizz_api_key' ) ); ?>" name="affilizz-api-key" id="affilizz-api-key" />
                </div>
            </div>
            <a href="<?php echo esc_url( AFFILIZZ_API_KEY_HELP_URL ); ?>" title="<?php _e( 'Go to the key location help page', 'affilizz' ); ?>" target="_blank">
                <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>assets/dist/images/wizard/help-icon-black.svg" width="16" alt="<?php _e( 'Illustration of the link between WordPress and Affilizz', 'affilizz' ); ?>" />
                <?php _e( 'Where can I find my key?', 'affilizz' ); ?>
            </a>
            <p class="description"><?php _e( 'Make sure to use the WordPress API Key from your Affilizz user account, do not confuse it with an Affilizz access token.', 'affilizz' ); ?></p>
        </div>

        <h4 class="affilizz-plugin__pane__section__title">
            <span><?php _e( 'Debug information', 'affilizz' ); ?>
        </h4>

        <fieldset>
            <legend><?php _e( 'Identification', 'affilizz' ); ?></legend>
            <div class="affilizz-field text">
                <div class="affilizz-field-main">
                    <label for="affilizz-organization"><?php _e( 'Organization ID', 'affilizz' ); ?></label>
                    <input type="text" value="<?php echo esc_attr( get_option( 'affilizz_organization' ) ); ?>" name="affilizz-organization" id="affilizz-organization" disabled />
                    <p class="description"><?php printf(
                        _x( 'This ID refers to the organization : %1$s%3$s%2$s.', 'Configuration debug panel', 'affilizz' ),
                        '<span>', '</span>', (
                            empty( get_option( 'affilizz_organization_label' ) ) ? __( 'Undefined organization', 'affilizz' ) : stripcslashes( get_option( 'affilizz_organization_label' ) )
                    ) ); ?></p>
                </div>
            </div>

            <div class="affilizz-field text">
                <div class="affilizz-field-main">
                    <label for="affilizz-media"><?php _e( 'Media ID', 'affilizz' ); ?></label>
                    <input type="text" value="<?php echo esc_attr( get_option( 'affilizz_media' ) ); ?>" name="affilizz-media" id="affilizz-media" disabled />
                    <p class="description"><?php printf(
                        _x( 'This ID refers to the media : %1$s%3$s%2$s.', 'Configuration debug panel', 'affilizz' ),
                        '<span>', '</span>', (
                            empty( get_option( 'affilizz_media_label' ) ) ? __( 'Undefined media', 'affilizz' ) : stripcslashes( get_option( 'affilizz_media_label' ) )
                    ) ); ?></p>
                </div>
            </div>

            <div class="affilizz-field text">
                <div class="affilizz-field-main">
                    <label for="affilizz-media"><?php _e( 'Channel ID', 'affilizz' ); ?></label>
                    <input type="text" value="<?php echo esc_attr( get_option( 'affilizz_channel' ) ); ?>" name="affilizz-channel" id="affilizz-channel" disabled />
                    <p class="description"><?php printf(
                        _x( 'This ID refers to the channel : %1$s%3$s%2$s.', 'Configuration debug panel', 'affilizz' ),
                        '<span>', '</span>', (
                            empty( get_option( 'affilizz_channel_label' ) ) ? __( 'Undefined channel', 'affilizz' ) : stripcslashes( get_option( 'affilizz_channel_label' ) )
                    ) ); ?></p>
                </div>
            </div>

            <?php wp_nonce_field( 'affilizz-save-configuration-data' ); ?>


            <p><?php printf( _x( 'If this configuration seems incomplete or faulty, please %1$srun the installation wizard%2$s again or contact us.', 'Configuration debug panel', 'affilizz' ), '<a href="' . admin_url( 'admin.php?page=affilizz-wizard' ) . '">', '</a>' ); ?></p>
        </fieldset>

        <h4 class="affilizz-plugin__pane__section__title">
            <span><?php _e( 'Cache busting', 'affilizz' ); ?>
        </h4>

        <div style="padding: 15px 0">
            <a href="<?php echo $current_url; ?>" class="affilizz-button affilizz-button--secondary affilizz-plugin__pane__button affilizz-plugin__pane__button--submit"><?php _e( 'Clear local file cache', 'affilizz' ); ?></a>
        </div>
        <p class="description"><?php
            if ( $cache_generated === false ) {
                _e( 'Local cache has not been generated yet.', 'affilizz' );
            } else {
                printf( __( 'Last local file cache generation : %s ago (%s)', 'affilizz' ), $cache_generated, date( 'd/m/Y H:i:s', filemtime( $local_file ) ) );
        } ?></p>
    </div>
</div>