<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<h4 class="affilizz-plugin__pane__section__title">
    <span><?php _e( 'Request proxy', 'affilizz' ); ?>
</h4>

<div class="affilizz-field text">
    <div class="affilizz-field-main">
        <input type="hidden" value="<?php echo esc_attr( get_option( 'affilizz_proxy_uuid' ) ); ?>" name="affilizz-proxy-uuid" id="affilizz-proxy-uuid" />
        <button type="submit" class="affilizz-button affilizz-button--secondary" name="refresh-uuid" value="1" style="margin: 15px 0">
            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/icon/refresh.svg" width="10" alt="<?php _e( 'Icon of two arrows representing a refresh action', 'affilizz' ); ?>" />&nbsp;
            <?php _e( 'Refresh / regenerate', 'affilizz' ); ?>
        </button>
    </div>

    <p class="description"><?php _e( 'This feature is used to help adblockers and third-party plugins to discern our affiliated links from standard advertisement.', 'affilizz' ); ?></p>
    <p class="description"><?php _e( 'Only click on this button :', 'affilizz' ); ?></p>
    <ul class="description">
        <li><?php _e( 'If you encounter issued where ads are displayed in the back-office editor but not on the live website', 'affilizz' ); ?> ;</li>
        <li><?php _e( 'If you notice a script being rejected by standard adblockers', 'affilizz' ); ?> ;</li>
        <li><?php _e( 'If you are requested to do so by an affilizz administrator', 'affilizz' ); ?>.</li>
    </ul>
</div>

<h4 class="affilizz-plugin__pane__section__title">
    <span><?php _e( 'Script management', 'affilizz' ); ?>
</h4>

<div class="affilizz-field text">
    <div class="affilizz-field checkbox">
        <div class="affilizz-field-main">
            <label for="affilizz-selective-enqueue">
                <input type="checkbox" id="affilizz-selective-enqueue" name="affilizz-selective-enqueue" value="1" <?php checked( 1 == (int) get_option( 'affilizz_selective_enqueue' ), true, true ); ?> />
                <span class="affilizz-field__label">
                    <?php _e( 'Enable selective script enqueuing', 'affilizz' ); ?>
                    <small><?php _e( 'Only load the script on pages containing our rendering tags.', 'affilizz' ); ?></small>
                </span>
            </label>
        </div>
    </div>
</div>

<div class="affilizz-plugin__pane--danger">
    <h4 class="affilizz-plugin__pane__section__title">
        <span><?php _e( 'Danger zone', 'affilizz' ); ?></span>
    </h4>
    <div class="affilizz-plugin__pane__inner">
        <div class="affilizz-field checkbox">
            <div class="affilizz-field-main">
                <label for="affilizz-disable-javascript">
                    <input type="checkbox" id="affilizz-disable-javascript" name="affilizz-disable-javascript" value="1" <?php checked( 1 == (int) get_option( 'affilizz_disable_javascript' ), true, true ); ?> />
                    <span class="affilizz-field__label"><?php _e( 'Disable javascript load of the rendering script', 'affilizz' ); ?></span>
                </label>
                <p class="description"><?php _e( 'Only disable this if you want to load a distinct, local version of the script.', 'affilizz' ); ?></p>
            </div>
        </div>
        <hr>
        <div class="affilizz-field checkbox">
            <div class="affilizz-field-main">
                <label for="affilizz-delete-codes-table">
                    <input type="checkbox" id="affilizz-delete-codes-table" name="affilizz-delete-codes-table" value="1" />
                    <span class="affilizz-field__label">
                        <?php _e( 'Delete the rendered publications database table', 'affilizz' ); ?>
                    </span>
                </label>
                <p class="description"><?php _e( 'Checking this option will make the previous rendering blocks void.', 'affilizz' ); ?></p>
            </div>
        </div>
        <div class="affilizz-field">        
            <div class="affilizz-field-main">
                <label for="affilizz-confirm-table-deletion"><?php printf( __( 'To confirm this action, please enter the text below in the following input THEN save: %s%s%s', 'affilizz' ), '<br><strong>', AFFILIZZ_DELETE_TABLE_CONFIRMATION_TEXT, '</strong>' ); ?></label>
                <input type="text" value="" name="affilizz-confirm-table-deletion" id="affilizz-confirm-table-deletion">
            </div>
        </div>
    </div>
</div>