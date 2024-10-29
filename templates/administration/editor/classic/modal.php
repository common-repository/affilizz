<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<form class="affilizz-form" action="#" method="post" id="affilizz-modal-form">
    <div class="affilizz-loader">
        <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>assets/dist/images/logo/logo-type-green.svg" class="affilizz-modal__logo" alt="<?php _e( 'Affilizz logo', 'affilizz' ); ?>" width="40" />
        <p><?php _e( 'Loading in progress', 'affilizz' ); ?>&hellip;</p>
    </div>

    <div class="affilizz-modal__wrapper">
        <div class="affilizz-modal__header">
            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>assets/dist/images/logo/logo-type-green-light.svg" class="affilizz-modal__logo" id="affilizz-modal-logo" alt="<?php _e( 'Affilizz logo', 'affilizz' ); ?>" width="32" />

            <h2 class="affilizz-modal__heading">
                <small class="affilizz-modal__overtitle"></small>
                <span class="affilizz-modal__title"></span>
            </h2>

            <div class="affilizz-modal__header__actions">
                <a id="affilizz-refresh-lists" class="affilizz-button affilizz-button--hollow" href="#">
                    <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>assets/dist/images/icon/swap-refresh.svg" alt="<?php _e( 'Refresh', 'affilizz' ); ?>" width="10" />
                    <?php _e( 'Refresh', 'affilizz' ); ?>
                </a>
            </div>

            <a class="affilizz-modal__close" href="#">&times;</a>
        </div>
        <div class="affilizz-modal__body">
            <div class="affilizz-modal__content">
                <div class="affilizz-modal__content--default">
                    <h3><?php _e( 'Insert affiliate content from Affilizz', 'affilizz' ); ?></h3>
                    <h4><?php _e( 'Choose an Affilizz publication', 'affilizz' ); ?></h4>
                    <p class="affilizz-form__field">
                        <label class="affilizz-form__label" for="affilizz-publication">
                            <?php _e( 'Publication', 'affilizz' ); ?>
                        </label>
                        <div id="affilizz-publication-id-wrapper">
                            <input name="original-affilizz-publication-id" type="hidden" value="<?php echo esc_attr( $id ) ?? ''; ?>" />
                            <select name="affilizz-publication-id" id="affilizz-publication-id"></select>
                        </div>
                        <div class="affilizz-faux-select hidden affilizz-select-loader" id="affilizz-publication-id-loader">
                            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>assets/dist/images/inline-loader.svg" width="12" />
                            <span><?php _e( 'Fetching content…', 'affilizz' ); ?></span>
                        </div>

                        <div id="affilizz-edit-publication-call" class="hidden">
                            <a id="affilizz-edit-publication-link" target="_blank" href="<?php echo esc_url( AFFILIZZ_EDIT_PUBLICATION_URL ); ?>" title="<?php _e( 'Open this publication in Affilizz', 'affilizz' ); ?>">
                                <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/icon/external-link.svg" alt="<?php _e( 'Open this publication in Affilizz', 'affilizz' ); ?>" />
                                <?php _e( 'Open this publication in Affilizz', 'affilizz' ); ?>
                            </a>
                        </div>
                    </p>

                    <h4>&hellip;<?php echo esc_html( strtolower( _x( 'And which content from it', 'List of contents in the insert publication modal.', 'affilizz' ) ) ); ?></h4>
                    <p class="affilizz-form__field">
                        <label class="affilizz-form__label" for="affilizz-publication-contents">
                            <?php _e( 'Affiliate content(s)', 'affilizz' ); ?>
                            <small class="affilizz-form__label__hint"><?php _e( 'You can add multiple content', 'affilizz' ); ?></small>
                        </label>
                        <div id="affilizz-publication-content-id-wrapper">
                            <select name="affilizz-publication-content-id" multiple placeholder="<?php _e( 'Search for a publication content', 'affilizz' ); ?>" autocomplete="off" id="affilizz-publication-content-id" disabled></select>
                        </div>
                        <div class="affilizz-faux-select hidden affilizz-select-loader" id="affilizz-publication-content-id-loader">
                            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>assets/dist/images/inline-loader.svg" width="12" />
                            <span><?php _e( 'Fetching content…', 'affilizz' ); ?></span>
                        </div>
                    </p>

                    <p class="affilizz-modal__hint">
                        <?php _e( 'If your publication or content does not appear, make sure you have created it in Affilizz and refresh this page.', 'affilizz' ); ?>
                        <a href="<?php echo esc_url( add_query_arg( array( 'mediaId' => get_option( 'affilizz_media' ) ?? '' ), AFFILIZZ_CREATE_PUBLICATION_URL ) ); ?>" title="<?php _e( 'Open Affilizz in a new tab.', 'affilizz' ); ?>" target="_blank">
                            <?php _e( 'Open Affilizz in a new tab.', 'affilizz' ); ?>
                        </a>
                    </p>
                </div>
                <div class="affilizz-modal__content--message" style="display: none">
                    <div class="affilizz-modal-message">
                        <strong class="affilizz-modal-message__title"></strong>
                        <span class="affilizz-modal-message__content"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="affilizz-modal__actions">
            <input type="submit" name="affilizz-modal-submit" class="affilizz-button affilizz-button--primary" value="<?php echo esc_attr( __( 'Insert content', 'affilizz' ) ); ?>">
        </div>
    </div>
</form>