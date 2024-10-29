<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="affilizz-wizard-wrapper">
    <div class="affilizz-wizard">
        <nav class="steps">
            <ol>
                <li class="step step--current">
                    <a href="#step-connect" class="affilizz-wizard__step__trigger" id="affilizz-wizard-step-connect-trigger" title="<?php printf( esc_attr( __( 'Access to the "%s" step of the wizard', 'affilizz' ) ), __( 'Connect with Affilizz', 'affilizz' ) ); ?>">
                        <span class="step__number">1</span>
                        <?php _e( 'Connect with Affilizz', 'affilizz' ); ?>
                    </a>
                </li>
                <li class="step step--inactive">
                    <a href="#step-select" class="affilizz-wizard__step__trigger" id="affilizz-wizard-step-select-trigger" title="<?php printf( esc_attr( __( 'Access to the "%s" step of the wizard', 'affilizz' ) ), __( 'Link an organization', 'affilizz' ) ); ?>">
                        <span class="step__number">2</span>
                        <?php _e( 'Link an organization', 'affilizz' ); ?>
                    </a>
                </li>
                <li class="step step--inactive">
                    <a href="#step-success" class="affilizz-wizard__step__trigger" id="affilizz-wizard-step-success-trigger"  title="<?php printf( esc_attr( __( 'Access to the "%s" step of the wizard', 'affilizz' ) ), __( 'This is it!', 'affilizz' ) ); ?>">
                        <span class="step__number">3</span>
                        <?php _e( 'This is it!', 'affilizz' ); ?>
                    </a>
                </li>
            </ol>
        </nav>

        <?php include_once( trailingslashit( AFFILIZZ_DIRECTORY ) . 'templates/administration/page/wizard/connect.php' ); ?>
        <?php include_once( trailingslashit( AFFILIZZ_DIRECTORY ) . 'templates/administration/page/wizard/select.php' ); ?>
        <?php include_once( trailingslashit( AFFILIZZ_DIRECTORY ) . 'templates/administration/page/wizard/success.php' ); ?>

        <p class="affilizz-wizard__skip">
            <a href="<?php echo esc_url( admin_url() ); ?>" title="<?php _e( 'Skip this wizard', 'affilizz' ); ?>">
                <?php _e( 'Skip this wizard', 'affilizz' ); ?> &rarr;
            </a>
        </p>

        <footer class="affilizz-wizard__footer">
            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/logo/logo-type-green-light.svg" width="30" alt="<?php _e( 'Affilizz logo', 'affilizz' ); ?>" />
            <span class="affilizz-wizard__footer__brand">Affilizz</span>
        </footer>
    </div>
</div>