<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<section id="step-success"  class="step__content">
    <div class="step__illustration">
        <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/illustration/trophy.svg" width="80" alt="<?php _e( 'Illustration of a trophy', 'affilizz' ); ?>" />
        <h2><?php _e( 'Congratulations!', 'affilizz' ); ?></h2>
    </div>

    <div class="step__help step__help--no-icon">
        <div class="step__help__content">
            <h3><?php _e( 'You are all set!', 'affilizz' ); ?></h3>
            <p><?php _e( 'You can now insert any affiliate content from Affilizz into your pages. Start by :', 'affilizz' ); ?></p>
            <ol class="list list--numbered list--numbered-accent">
                <li><?php _e( 'If you haven\'t already, create affiliate content into Affilizz', 'affilizz' ); ?></li>
                <li><?php _e( 'Insert your affiliate content into your pages using the “insert affiliate content” button.', 'affilizz' ); ?></li>
            </ol>
        </div>
    </div>

    <div class="step__actions">
        <button type="button" class="step__button affilizz-button affilizz-button--secondary" id="step-success-back">
            <img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/wizard/arrow-left-blue.svg" width="8" alt="<?php _e( 'Icon of a blue left arrow', 'affilizz' ); ?>" />
            <?php _e( 'Back', 'affilizz' ); ?>
        </button>
        <a href="<?php echo esc_url( get_admin_url() ); ?>" id="step-success-button" class="step__button affilizz-button affilizz-button--primary">
            <?php _e( 'Finish and back to your dashboard', 'affilizz' ); ?>
        </a>
    </div>
</section>